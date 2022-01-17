<?php

namespace App\Interfaces;

use App\GameUser;
use Illuminate\Database\Eloquent\Collection;

abstract class AbstractGameInterface
{
    public abstract function getStats(GameUser $gameUser): ?array;

    public abstract function register(array $params): ?array;

    public abstract function mapStats(GameUser $gameUser, array $stats, Collection $assignments): array;
}
