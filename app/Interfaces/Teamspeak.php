<?php

namespace App\Interfaces;

use Exception;
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

    public function getClient(string $clientId): TeamSpeak3_Node_Client
    {
        return $this->server->clientGetByUid($clientId);
    }

    public function addServerGroup(TeamSpeak3_Node_Client $client, int $serverGroupId): bool
    {
        try {
            $client->addServerGroup($serverGroupId);
        } catch (Exception $e) {
            return false;
        }

        return true;
    }

    public function removeServerGroup(TeamSpeak3_Node_Client $client, int $serverGroupId): bool
    {
        try {
            $client->remServerGroup($serverGroupId);
        } catch (Exception $e) {
            return false;
        }

        return true;
    }

    public function sendMessageToClient(TeamSpeak3_Node_Client $client, string $message): bool
    {
        try {
            $client->message($message);
        } catch (Exception $e) {
            return false;
        }

        return true;
    }
}
