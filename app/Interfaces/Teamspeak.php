<?php

namespace App\Interfaces;

use Exception;
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
}
