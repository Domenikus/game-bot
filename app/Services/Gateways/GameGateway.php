<?php

namespace App\Services\Gateways;

use App\Assignment;
use App\GameUser;
use Illuminate\Database\Eloquent\Collection;

interface GameGateway
{
    public function grabPlayerData(GameUser $gameUser): ?array;

    public function grabPlayerIdentity(array $params): ?array;

    public function grabCharacterImage(string $characterName): ?string;

    public function grabCharacters(): ?array;

    public function grabPositionImage(string $positionName): ?string;

    public function grabPositions(): ?array;

    public function grabRankImage(string $rankName): ?string;

    public function grabRanks(): ?array;

    /**
     * @param  Collection<int, Assignment>  $assignments
     */
    public function mapStats(
        GameUser $gameUser,
        array $stats,
        Collection $assignments
    ): array;
}
