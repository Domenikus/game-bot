<?php

namespace App\Services\Gateways;

use App\Assignment;
use App\GameUser;
use Illuminate\Database\Eloquent\Collection;

interface GameGateway
{
    public function getPlayerData(GameUser $gameUser): ?array;

    public function getPlayerIdentity(array $params): ?array;

    public function grabCharacterImage(string $characterName): ?string;

    public function grabCharacters(): ?array;

    public function grabPositionImage(string $positionName): ?string;

    public function grabPositions(): ?array;

    public function grabRankImage(string $rankName): ?string;

    public function grabRanks(): ?array;

    /**
     * @param  GameUser  $gameUser
     * @param  array  $stats
     * @param  Collection<int, Assignment>  $assignments
     * @return array
     */
    public function mapStats(
        GameUser $gameUser,
        array $stats,
        Collection $assignments
    ): array;
}
