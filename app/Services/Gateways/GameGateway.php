<?php

namespace App\Services\Gateways;

use App\Assignment;
use App\GameUser;
use App\Queue;
use Illuminate\Database\Eloquent\Collection;

interface GameGateway
{
    public function getPlayerData(GameUser $gameUser): ?array;

    public function getPlayerIdentity(array $params): ?array;

    /**
     * @param GameUser $gameUser
     * @param array $stats
     * @param Collection<int, Assignment> $assignments
     * @param Collection<int, Queue> $queues
     * @return array
     */
    public function mapStats(
        GameUser $gameUser,
        array $stats,
        Collection $assignments,
        Collection $queues
    ): array;
}
