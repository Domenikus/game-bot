<?php

namespace App\Services;

use App\Facades\TeamSpeak3;
use Exception;
use Illuminate\Support\Facades\Log;
use TeamSpeak3_Node_Client;

class Teamspeak
{
    public static function getClient(string $clientId): ?TeamSpeak3_Node_Client
    {
        $result = null;

        try {
            $result = TeamSpeak3::clientGetByUid($clientId);
        } catch (Exception $e) {
            Log::error($e, ['clientId' => $clientId]);
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
            Log::error($e, ['client' => $client, 'serverGroupId' => $serverGroupId]);
        }

        return false;
    }

    public static function removeServerGroupFromClient(TeamSpeak3_Node_Client $client, int $serverGroupId): bool
    {
        try {
            $client->remServerGroup($serverGroupId);

            return true;
        } catch (Exception $e) {
            Log::error($e, ['client' => $client, 'serverGroupId' => $serverGroupId]);
        }

        return false;
    }

    public static function sendMessageToClient(TeamSpeak3_Node_Client $client, string $message): bool
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
