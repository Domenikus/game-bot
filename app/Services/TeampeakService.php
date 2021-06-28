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
     * TeampeakService constructor.
     * @param TeamSpeak3_Node_Server $server
     * @param callable $callback
     */
    public function __construct(TeamSpeak3_Node_Server $server, callable $callback)
    {
        $this->server = $server;
        $this->callback = $callback;
    }


    public function assignServerGroups(User $user)
    {
        $this->assignRankStatus($user);
        $this->assignLegend($user);
        call_user_func($this->callback, "Player stats updated");
    }

    public function removeServerGroups(User $user)
    {
        $this->removeRankStatus($user);
        $this->removeLegend($user);
    }

    public function getPlayerStats(string $name, string $plattform): ?string
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

    public function registerUser(string $identityId, string $name, string $plattform): ?User
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
