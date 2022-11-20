<?php

namespace App\Services\Gateways;

use App\Assignment;
use App\GameUser;
use App\Type;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TeamfightTacticsGateway implements GameGateway
{
    const QUEUE_TYPE_RANKED = 'RANKED_TFT';

    protected string $apiKey;

    public function __construct(string $apiKey)
    {
        $this->apiKey = $apiKey;
    }

    public function getPlayerData(GameUser $gameUser): ?array
    {
        $stats = null;
        $leagueResponse = Http::withHeaders(['X-Riot-Token' => $this->apiKey])
            ->get('https://euw1.api.riotgames.com/tft/league/v1/entries/by-summoner/'.$gameUser->options['id']);

        if ($leagueResponse->successful()) {
            $decodedBody = json_decode($leagueResponse->body(), true);
            if (is_array($decodedBody)) {
                $stats['leagues'] = $decodedBody;
            }
        } else {
            Log::warning('Could not get player data from Riot API for Teamfight Tactics',
                ['apiKey' => $this->apiKey, 'gameUser' => $gameUser, 'response' => $leagueResponse]);
        }

        return $stats;
    }

    public function getPlayerIdentity(array $params): ?array
    {
        if (! isset($params[2])) {
            return null;
        }

        $summonerResponse = Http::withHeaders(['X-Riot-Token' => $this->apiKey])
            ->get('https://euw1.api.riotgames.com/tft/summoner/v1/summoners/by-name/'.$params[2]);

        $summoner = null;
        if ($summonerResponse->successful()) {
            $decodedBody = json_decode($summonerResponse->body(), true);
            if (is_array($decodedBody)) {
                $summoner = $decodedBody;
            }
        } else {
            Log::warning('Could not get player identity from Riot API for Teamfight Tactics',
                ['apiKey' => $this->apiKey, 'params' => $params, 'response' => $summonerResponse]);
        }

        return $summoner;
    }

    public function grabCharacterImage(string $characterName): ?string
    {
        // TODO: Implement grabCharacterImage() method.
        return null;
    }

    public function grabCharacters(): ?array
    {
        // TODO: Implement grabCharacters() method.
        return null;
    }

    public function grabPositionImage(string $positionName): ?string
    {
        // TODO: Implement grabPositionImage() method.
        return null;
    }

    public function grabPositions(): ?array
    {
        // TODO: Implement grabPositions() method.
        return null;
    }

    public function grabRankImage(string $rankName): ?string
    {
        // TODO: Implement grabRankImage() method.
        return null;
    }

    public function grabRanks(): ?array
    {
        // TODO: Implement grabRanks() method.
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
