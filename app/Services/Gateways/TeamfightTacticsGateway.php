<?php

namespace App\Services\Gateways;

use App\Assignment;
use App\GameUser;
use App\Type;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TeamfightTacticsGateway extends LeagueOfLegendsGateway implements GameGateway
{
    const QUEUE_TYPE_RANKED = 'RANKED_TFT';

    public function grabCharacterImage(string $characterName): ?string
    {
        return null;
    }

    public function grabCharacters(): array
    {
        return [];
    }

    public function grabPlayerData(GameUser $gameUser): ?array
    {
        $stats = null;
        $url = $this->getPlattformBaseUrl().'/tft/league/v1/entries/by-summoner/'.$gameUser->options['id'];
        $leagueResponse = Http::withHeaders(['X-Riot-Token' => $this->getApiKey()])
            ->get($url);

        if ($leagueResponse->successful()) {
            $result = $leagueResponse->json();
            if (is_array($result)) {
                $stats['leagues'] = $result;
            }
        } else {
            Log::warning('Could not get player data from Riot API for Teamfight Tactics',
                ['apiKey' => $this->getApiKey(), 'gameUser' => $gameUser, 'responseStatus' => $leagueResponse->status(), 'url' => $url]);
        }

        return $stats;
    }

    public function grabPlayerIdentity(array $params): ?array
    {
        if (! isset($params[2])) {
            return null;
        }

        $url = $this->getPlattformBaseUrl().'/tft/summoner/v1/summoners/by-name/'.$params[2];
        $summonerResponse = Http::withHeaders(['X-Riot-Token' => $this->getApiKey()])
            ->retry(10, 10000)
            ->get($url);

        $summoner = null;
        if ($summonerResponse->successful()) {
            $result = $summonerResponse->json();
            if (is_array($result)) {
                $summoner = $result;
            }
        } else {
            Log::warning('Could not get player identity from Riot API for Teamfight Tactics',
                ['apiKey' => $this->getApiKey(), 'params' => $params, 'responseStatus' => $summonerResponse->status(), 'url' => $url]);
        }

        return $summoner;
    }

    public function grabPositionImage(string $positionName): ?string
    {
        return null;
    }

    public function grabPositions(): ?array
    {
        return null;
    }

    /**
     * @param  GameUser  $gameUser
     * @param  array  $stats
     * @param  Collection<int, Assignment>  $assignments
     * @return array
     */
    public function mapStats(GameUser $gameUser, array $stats, Collection $assignments): array
    {
        $ts3ServerGroups = [];

        if (isset($stats['leagues'])) {
            if ($rankAssignment = $this->mapRank($stats['leagues'],
                $assignments->filter(function ($value) {
                    return $value->type?->name == Type::NAME_RANK_SOLO;
                }), self::QUEUE_TYPE_RANKED)) {
                $ts3ServerGroups[Type::NAME_RANK_SOLO] = $rankAssignment->ts3_server_group_id;
            }
        }

        return $ts3ServerGroups;
    }

    /**
     * @param  array  $leagues
     * @param  Collection<int, Assignment>  $assignments
     * @param  string  $queueType
     * @return Assignment|null
     */
    protected function mapRank(array $leagues, Collection $assignments, string $queueType): ?Assignment
    {
        $newRankName = '';
        foreach ($leagues as $league) {
            if ($league['queueType'] == $queueType) {
                $newRankName = $league['tier'].' '.$league['rank'];
            }
        }

        return $assignments->where('value', strtolower($newRankName))->first();
    }
}
