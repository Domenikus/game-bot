<?php

namespace App\Services\Gateways;

use App\GameUser;
use Illuminate\Database\Eloquent\Collection;

interface GameGateway
{
    public function getPlayerData(GameUser $gameUser): ?array;

    public function getPlayerIdentity(array $params): ?array;

    public function mapStats(
        GameUser $gameUser,
        array $stats,
        Collection $assignments,
        Collection $queues
    ): array;
}
