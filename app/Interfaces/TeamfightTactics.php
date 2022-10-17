<?php

namespace App\Interfaces;

use App\GameUser;
use App\Type;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Http;

class TeamfightTactics extends AbstractGameInterface
{
    const QUEUE_TYPE_RANK_TFT = 'RANKED_TFT';


    public function getApiKey(): ?string
    {
        return config('game.tft-api-key');
    }

    public function getPlayerData(GameUser $gameUser): ?array
    {
        $stats = null;
        $leagueResponse = Http::withHeaders(['X-Riot-Token' => $this->getApiKey()])
            ->get('https://euw1.api.riotgames.com/tft/league/v1/entries/by-summoner/' . $gameUser->options['id']);

        if ($leagueResponse->successful()) {
            $stats['leagues'] = json_decode($leagueResponse->body(), true);
        }

        return $stats;
    }

    public function getPlayerIdentity(array $params): ?array
    {
        if (!isset($params[2])) {
            return null;
        }

        $summonerResponse = Http::withHeaders(['X-Riot-Token' => $this->getApiKey()])
            ->get('https://euw1.api.riotgames.com/tft/summoner/v1/summoners/by-name/' . $params[2]);

        $summoner = null;
        if ($summonerResponse->successful()) {
            $summoner = json_decode($summonerResponse->body(), true);
        }

        return $summoner;
    }

    public function mapStats(GameUser $gameUser, array $stats, Collection $assignments, Collection $queues): array
    {
        $ts3ServerGroups = [];
        if ($rank = $this->mapRank($stats['leagues'], $assignments->filter(function ($value) {
            return $value->type->name == Type::TYPE_RANK_SOLO;
        }), self::QUEUE_TYPE_RANK_TFT)) {
            $ts3ServerGroups[Type::TYPE_RANK_SOLO] = $rank;
        }

        return $ts3ServerGroups;
    }

    protected function mapRank(array $leagues, Collection $assignments, string $queueType): ?int
    {
        $newRankName = '';
        foreach ($leagues as $league) {
            if ($league['queueType'] == $queueType) {
                $newRankName = $league['tier'] . ' ' . $league['rank'];
            }
        }

        return $this->getTs3ServerGroupIdForValueInGivenAssignments($assignments, $newRankName);
    }
}
