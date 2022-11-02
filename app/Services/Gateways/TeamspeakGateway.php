<?php

namespace App\Services\Gateways;

use App\Facades\TeamSpeak3;
use Exception;
use Illuminate\Support\Facades\Log;
use TeamSpeak3_Node_Client;

class TeamspeakGateway
{
    public static function getClient(string $clientId): ?TeamSpeak3_Node_Client
    {
        $result = null;

        try {
            $result = TeamSpeak3::clientGetByUid($clientId);
        } catch (Exception $e) {
            Log::debug($e->getMessage(), ['clientId' => $clientId]);
            report($e);
        }

        return $result;
    }

    /**
     * @return TeamSpeak3_Node_Client[]
     */
    public static function getActiveClients(): array
    {
        return TeamSpeak3::clientList();
    }

    /**
     * @param  TeamSpeak3_Node_Client  $client
     * @return array<string>
     */
    public static function getServerGroupsAssignedToClient(TeamSpeak3_Node_Client $client): array
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

    public static function addServerGroupToClient(TeamSpeak3_Node_Client $client, int $serverGroupId): bool
    {
        try {
            $client->addServerGroup($serverGroupId);

            return true;
        } catch (Exception $e) {
            Log::debug($e->getMessage(), ['client' => $client, 'serverGroupId' => $serverGroupId]);
            report($e);
        }

        return false;
    }

    public static function removeServerGroupFromClient(TeamSpeak3_Node_Client $client, int $serverGroupId): bool
    {
        try {
            $client->remServerGroup($serverGroupId);

            return true;
        } catch (Exception $e) {
            Log::debug($e->getMessage(), ['client' => $client, 'serverGroupId' => $serverGroupId]);
            report($e);
        }

        return false;
    }

    public static function sendMessageToClient(TeamSpeak3_Node_Client $client, string $message): bool
    {
        try {
            $client->message($message);

            return true;
        } catch (Exception $e) {
            Log::debug($e->getMessage(), ['client' => $client, 'message' => $message]);
            report($e);
        }

        return false;
    }
}
