<?php

namespace App\Services;

use App\User;
use Illuminate\Support\Facades\Http;
use TeamSpeak3;
use TeamSpeak3_Adapter_ServerQuery;
use TeamSpeak3_Adapter_ServerQuery_Event;
use TeamSpeak3_Helper_Signal;
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
     * @throws \Exception
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

    public function initializeEventListeners(callable $callback)
    {
        $this->server->notifyRegister("server");
        $this->server->notifyRegister("textserver");

        $this->initGlobalChatListener($callback);
        $this->initClientEnterViewListener($callback);
        $this->initTimeoutListener($callback);
        $this->initTimeoutListener($callback);
    }

    private function initGlobalChatListener(callable $callback)
    {
        TeamSpeak3_Helper_Signal::getInstance()->subscribe("notifyTextmessage", function (TeamSpeak3_Adapter_ServerQuery_Event $event) use ($callback) {
            $data = $event->getData();
            if ($data['msg']->startsWith("!register")) {
                $params = explode(' ', $data['msg']->toString(), 3);
                $identityId = $data['invokeruid']->toString();

                if (isset($params[1], $params[2]) && $this->registerUser($identityId, $params[1], $params[2])) {
                    call_user_func($callback, "Registration completed successfully");
                } else {
                    call_user_func($callback, "Registration failed");
                }
            }
        });
    }

    private function initClientEnterViewListener(callable $callback)
    {
        TeamSpeak3_Helper_Signal::getInstance()->subscribe("notifyCliententerview", function (TeamSpeak3_Adapter_ServerQuery_Event $event) use ($callback) {
            $data = $event->getData();
            $identityId = $data['client_unique_identifier']->toString();
            $user = User::find($identityId);

            if ($user) {
                $response = Http::withHeaders(['TRN-Api-Key' => 'a5979db2-d166-42f3-a17b-5e33444c243d'])
                    ->get('https://public-api.tracker.gg/v2/apex/standard/profile/' . $user->plattform . '/' . $user->name);

                if ($response->status() == 200) {
                    $user->stats = $response->body();
                    $user->save();
                    $this->assignServerGroups($user);
                } else {
                    call_user_func($callback, "Wrong user configuration");
                }
            }
        });
    }

    private function initTimeoutListener(callable $callback)
    {
        TeamSpeak3_Helper_Signal::getInstance()->subscribe("serverqueryWaitTimeout", function ($seconds, TeamSpeak3_Adapter_ServerQuery $adapter) use ($callback) {
            if ($adapter->getQueryLastTimestamp() < time() - 260) {
                call_user_func($callback, "No reply from the server for " . $seconds . " seconds. Sending keep alive command.");
                $adapter->request("clientupdate");
            }
        });
    }

    private function assignServerGroups(User $user)
    {
        $this->assignRankStatus($user);
    }

    private function assignRankStatus(User $user)
    {
        $client = $this->server->clientGetByUid($user->getKey());
        $stats = json_decode($user->stats, true);
        $newRankName = $stats['data']["segments"][0]["stats"]["rankScore"]['metadata']['rankName'];

        foreach ($client->memberOf() as $group) {
            if (isset($group['sgid']) && in_array($group['name']->toString(), array_keys(config('teamspeak.server_groups_ranked')))) {
                if ($group['name']->toString() !== $newRankName) {
                    $client->remServerGroup($group['sgid']);
                    $client->addServerGroup(config('teamspeak.server_groups_ranked.' . $newRankName));
                }

                return;
            }
        }

        $client->addServerGroup(config('teamspeak.server_groups_ranked.' . $newRankName));
    }

    public function listen()
    {
        while (true) {
            $this->server->getAdapter()->wait();
        }
    }

    private function registerUser(string $identityId, string $name, string $plattform)
    {
        if (empty($identityId) || empty($name) || empty($plattform)) {
            return false;
        }

        if (!in_array($plattform, self::PLATFORMS)) {
            return false;
        }

        $user = User::find($identityId);
        if (!$user) {
            $user = new User();
            $user->identity_id = $identityId;
        }

        $user->name = $name;
        $user->plattform = $plattform;

        return $user->save();
    }
}
