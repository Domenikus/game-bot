<?php

namespace App\Interfaces;

use App\GameUser;
use Illuminate\Database\Eloquent\Collection;

interface GameApi
{
    public function getApiKey(): ?string;

    public function getPlayerData(GameUser $gameUser): ?array;

    public function getPlayerIdentity(array $params): ?array;

    public function mapStats(
        GameUser $gameUser,
        array $stats,
        Collection $assignments,
        Collection $queues
    ): array;
}
