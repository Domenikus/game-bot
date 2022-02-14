<?php

namespace App\Interfaces;

use Exception;
use TeamSpeak3;
use TeamSpeak3_Adapter_ServerQuery_Exception;
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


    /**
     * @return TeamSpeak3_Node_Server
     * @throws Exception
     */
    public static function connectToTeamspeakServer(): TeamSpeak3_Node_Server
    {
        $uri = "serverquery://"
            . config('teamspeak.query_user') . ":"
            . config('teamspeak.query_password') . "@"
            . config('teamspeak.ip') . ":"
            . config('teamspeak.query_port') . "/?server_port="
            . config('teamspeak.port') . "&blocking=0&nickname="
            . config('teamspeak.bot_name');
        return TeamSpeak3::factory($uri);
    }

    /**
     * @param string $clientId
     * @return TeamSpeak3_Node_Client|null
     */
    public function getClient(string $clientId): ?TeamSpeak3_Node_Client
    {
        $result = null;

        try {
            $result = $this->server->clientGetByUid($clientId);
        } catch (Exception) {}

        return $result;
    }

    public function addServerGroup(TeamSpeak3_Node_Client $client, int $serverGroupId): bool
    {
        try {
            $client->addServerGroup($serverGroupId);
        } catch (Exception) {
            return false;
        }

        return true;
    }

    public function removeServerGroup(TeamSpeak3_Node_Client $client, int $serverGroupId): bool
    {
        try {
            $client->remServerGroup($serverGroupId);
        } catch (Exception) {
            return false;
        }

        return true;
    }

    public function sendMessageToClient(TeamSpeak3_Node_Client $client, string $message): bool
    {
        try {
            $client->message($message);
        } catch (Exception) {
            return false;
        }

        return true;
    }
}
