<?php

namespace App\Interfaces;

use App\GameUser;
use Illuminate\Database\Eloquent\Collection;

abstract class AbstractGameInterface
{
    public abstract function getStats(GameUser $gameUser): ?array;

    public abstract function getPlayerData(array $params): ?array;

    public abstract function mapStats(GameUser $gameUser, array $stats, Collection $assignments): array;

    protected function getTs3ServerGroupIdForValueInGivenAssignments(Collection $assignments, string $value): ?int
    {
        foreach ($assignments as $assignment) {
            if ($assignment->value == strtolower($value)) {
                return $assignment->ts3_server_group_id;
            }
        }

        return null;
    }
}
