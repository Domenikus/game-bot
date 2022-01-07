<?php

namespace App\Interfaces;

use App\GameUser;
use App\Type;
use Illuminate\Database\Eloquent\Collection;

abstract class AbstractGameInterface
{
    public abstract function getStats(GameUser $gameUser): ?array;

    public abstract function register(array $params): ?array;

    public function mapStats(array $stats, Collection $assignments): array
    {
        $ts3ServerGroups = [];
        if ($rank = $this->mapRank($stats, $assignments->filter(function ($value) {
            return $value->type->name == Type::NAME_RANK;
        }))) {
            $ts3ServerGroups[Type::NAME_RANK] = $rank;
        }

        if ($character = $this->mapCharacter($stats, $assignments->filter(function ($value) {
            return $value->type->name == Type::NAME_CHARACTER;
        }))) {
            $ts3ServerGroups[Type::NAME_CHARACTER] = $character;
        }

        return $ts3ServerGroups;
    }

    protected function mapRank(array $stats, Collection $assignments): ?int
    {
        return null;
    }

    protected function mapCharacter(array $stats, Collection $assignments): ?int
    {
        return null;
    }
}
