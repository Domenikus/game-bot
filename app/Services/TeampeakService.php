<?php

namespace App\Services;

use App\Commands\Run;
use App\User;
use Exception;
use Illuminate\Support\Facades\Http;
use TeamSpeak3;
use TeamSpeak3_Adapter_ServerQuery;
use TeamSpeak3_Adapter_ServerQuery_Event;
use TeamSpeak3_Helper_Signal;
use TeamSpeak3_Node_Host;
use TeamSpeak3_Node_Server;

class TeampeakService
{
    const PLATFORMS = [
        'origin',
        'xbl',
        'psn'
    ];

    /**
     * @var TeamSpeak3_Node_Server
     */
    private $server;

    /**
     * @var callable
     */
    private $callback;

    /**
     * @throws Exception
     */
    public function connect(): void
    {
        $uri = "serverquery://"
            . config('teamspeak.query_user') . ":"
            . config('teamspeak.query_password') . "@"
            . config('teamspeak.ip') . ":"
            . config('teamspeak.query_port') . "/?server_port="
            . config('teamspeak.port') . "&blocking=0";
        $this->server = TeamSpeak3::factory($uri);
    }

    public function init(callable $callback)
    {
        $this->callback = $callback;

        $this->server->notifyRegister("server");
        $this->server->notifyRegister("textserver");

        $this->initGlobalChatListener();
        $this->initClientEnterViewListener();
        $this->initTimeoutListener();
        $this->initTimeoutListener();
    }

    private function initGlobalChatListener()
    {
        TeamSpeak3_Helper_Signal::getInstance()->subscribe("notifyTextmessage", function (TeamSpeak3_Adapter_ServerQuery_Event $event, TeamSpeak3_Node_Host $host) {
            $this->server = $host->serverGetSelected();

            $data = $event->getData();
            $identityId = $data['invokeruid']->toString();
            if ($data['msg']->startsWith("!register")) {
                $params = explode(' ', $data['msg']->toString(), 3);

                if (isset($params[1], $params[2]) && $user = $this->registerUser($identityId, $params[1], $params[2])) {
                    $this->assignServerGroups($user);
                    call_user_func($this->callback, "Registration successful");
                } else {
                    $this->server->clientGetByUid($identityId)->message("Registration failed. Please enter correct username and plattform.");
                    call_user_func($this->callback, "Registration failed", Run::LOG_TYPE_ERROR);
                }
            } else if ($data['msg']->startsWith("!update")) {
                $user = User::find($identityId);

                if ($user) {
                    $stats = $this->getPlayerStats($user->name, $user->plattform);
                    if ($stats) {
                        $user->stats = $stats;
                        $user->save();

                        $this->assignServerGroups($user);
                    }
                }
            } else if ($data['msg']->startsWith("!unregister")) {
                /** @var User $user */
                $user = User::find($identityId);

                if ($user) {
                    $user->delete();
                    $this->removeServerGroups($user);
                }
            }
        });
    }

    private function initClientEnterViewListener()
    {
        TeamSpeak3_Helper_Signal::getInstance()->subscribe("notifyCliententerview", function (TeamSpeak3_Adapter_ServerQuery_Event $event, TeamSpeak3_Node_Host $host) {
            $this->server = $host->serverGetSelected();

            $data = $event->getData();
            $identityId = $data['client_unique_identifier']->toString();
            $user = User::find($identityId);

            if ($user) {
                $stats = $this->getPlayerStats($user->name, $user->plattform);
                if ($stats) {
                    $user->stats = $stats;
                    $user->save();

                    $this->assignServerGroups($user);
                }
            }
        });
    }

    private function initTimeoutListener()
    {
        TeamSpeak3_Helper_Signal::getInstance()->subscribe("serverqueryWaitTimeout", function ($seconds, TeamSpeak3_Adapter_ServerQuery $adapter) {
            if ($adapter->getQueryLastTimestamp() < time() - 180) {
                call_user_func($this->callback, "No reply from the server for " . $seconds . " seconds. Sending keep alive command.", Run::LOG_TYPE_ERROR);
                $adapter->request("clientupdate");
                $this->server = $adapter->getHost()->serverGetSelected();
            }
        });
    }

    private function assignServerGroups(User $user)
    {
        $this->assignRankStatus($user);
        $this->assignLegend($user);
        call_user_func($this->callback, "Player stats updated");
    }

    private function removeServerGroups(User $user)
    {
        $this->removeRankStatus($user);
        $this->removeLegend($user);
    }

    private function getPlayerStats(string $name, string $plattform): ?string
    {
        $stats = null;
        $response = Http::withHeaders(['TRN-Api-Key' => config('app.apex-api-key')])
            ->get('https://public-api.tracker.gg/v2/apex/standard/profile/' . $plattform . '/' . $name);

        if ($response->successful()) {
            $stats = $response->body();
        } else {
            call_user_func($this->callback, "Combination of name and plattform not found", Run::LOG_TYPE_ERROR);
        }

        return $stats;
    }

    private function assignRankStatus(User $user)
    {
        $client = $this->server->clientGetByUid($user->getKey());
        $stats = json_decode($user->stats, true);
        $newRankName = $stats['data']["segments"][0]["stats"]["rankScore"]['metadata']['rankName'];

        foreach ($client->memberOf() as $group) {
            if (isset($group['sgid']) && in_array($group['sgid'], config('teamspeak.server_groups_ranked'))) {
                if ($group['sgid'] != config('teamspeak.server_groups_ranked.' . $newRankName)) {
                    $client->remServerGroup($group['sgid']);
                    $client->addServerGroup(config('teamspeak.server_groups_ranked.' . $newRankName));
                }

                return;
            }
        }

        $client->addServerGroup(config('teamspeak.server_groups_ranked.' . $newRankName));
    }


    private function removeRankStatus(User $user)
    {
        $client = $this->server->clientGetByUid($user->getKey());
        foreach ($client->memberOf() as $group) {
            if (isset($group['sgid']) && in_array($group['sgid'], config('teamspeak.server_groups_ranked'))) {
                $client->remServerGroup($group['sgid']);
            }
        }
    }

    private function assignLegend(User $user)
    {
        $client = $this->server->clientGetByUid($user->getKey());
        $stats = json_decode($user->stats, true);
        $newLegendName = $stats['data']["metadata"]['activeLegendName'];

        foreach ($client->memberOf() as $group) {
            if (isset($group['sgid']) && in_array($group['sgid'], config('teamspeak.server_groups_legends'))) {
                if ($group['sgid'] != config('teamspeak.server_groups_legends.' . $newLegendName)) {
                    $client->remServerGroup($group['sgid']);
                    $client->addServerGroup(config('teamspeak.server_groups_legends.' . $newLegendName));
                }

                return;
            }
        }

        $client->addServerGroup(config('teamspeak.server_groups_legends.' . $newLegendName));
    }

    private function removeLegend(User $user)
    {
        $client = $this->server->clientGetByUid($user->getKey());
        foreach ($client->memberOf() as $group) {
            if (isset($group['sgid']) && in_array($group['sgid'], config('teamspeak.server_groups_legends'))) {
                $client->remServerGroup($group['sgid']);
            }
        }
    }

    public function listen()
    {
        while (true) {
            $this->server->getAdapter()->wait();
        }
    }

    private function registerUser(string $identityId, string $name, string $plattform): ?User
    {
        if ((empty($identityId) || empty($name) || empty($plattform)) && !in_array($plattform, self::PLATFORMS)) {
            return null;
        }

        $stats = $this->getPlayerStats($name, $plattform);
        if (!$stats) {
            return null;
        }

        $user = User::find($identityId);
        if (!$user) {
            $user = new User();
            $user->identity_id = $identityId;
        }

        $user->name = $name;
        $user->plattform = $plattform;
        $user->stats = $stats;

        $user->saveOrFail();

        return $user;
    }
}
