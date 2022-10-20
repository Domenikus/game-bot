<?php

namespace App\Interfaces;

use Exception;
use Illuminate\Support\Facades\Log;
use TeamSpeak3;
use TeamSpeak3_Node_Client;
use TeamSpeak3_Node_Server;

class Teamspeak
{
    protected TeamSpeak3_Node_Server $server;

    /**
     * @param $server
     */
    public function __construct($server)
    {
        $this->server = $server;
    }

    public static function connectToTeamspeakServer(): ?TeamSpeak3_Node_Server
    {
        $ts3NodeServer = null;
        try {
            $uri = "serverquery://"
                . config('teamspeak.query_user') . ":"
                . config('teamspeak.query_password') . "@"
                . config('teamspeak.ip') . ":"
                . config('teamspeak.query_port') . "/?server_port="
                . config('teamspeak.port') . "&blocking=0&nickname="
                . config('teamspeak.bot_name');
            $ts3NodeServer = TeamSpeak3::factory($uri);
        } catch (Exception $e) {
            report($e);
        }

        return $ts3NodeServer;
    }

    public function getClient(string $clientId): ?TeamSpeak3_Node_Client
    {
        $result = null;

        try {
            $result = $this->server->clientGetByUid($clientId);
        } catch (Exception $e) {
            Log::error($e, ['clientId' => $clientId]);
        }

        return $result;
    }

    public function getServerGroupsAssignedToClient(TeamSpeak3_Node_Client $client): array
    {
        $actualServerGroups = [];
        $actualGroups = $client->memberOf();
        foreach ($actualGroups as $group) {
            if (isset($group['sgid'])) {
                $actualServerGroups[] = $group['sgid'];
            }
        }

        return $actualServerGroups;
    }

    public function addServerGroupToClient(TeamSpeak3_Node_Client $client, int $serverGroupId): bool
    {
        try {
            $client->addServerGroup($serverGroupId);
            return true;
        } catch (Exception $e) {
            Log::error($e, ['client' => $client, 'serverGroupId' => $serverGroupId]);
        }

        return false;
    }

    public function removeServerGroupFromClient(TeamSpeak3_Node_Client $client, int $serverGroupId): bool
    {
        try {
            $client->remServerGroup($serverGroupId);
            return true;
        } catch (Exception $e) {
            Log::error($e, ['client' => $client, 'serverGroupId' => $serverGroupId]);
        }

        return false;
    }

    public function sendMessageToClient(TeamSpeak3_Node_Client $client, string $message): bool
    {
        try {
            $client->message($message);
            return true;
        } catch (Exception $e) {
            Log::error($e, ['client' => $client, 'message' => $message]);
        }

        return false;
    }
}
