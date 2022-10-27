<?php

namespace App\Services\Gateways;

use App\Assignment;
use App\GameUser;
use App\Queue;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TeamfightTacticsGateway implements GameGateway
{
    protected string $apiKey;

    public function __construct(string $apiKey)
    {
        $this->apiKey = $apiKey;
    }

    public function getPlayerData(GameUser $gameUser): ?array
    {
        $stats = null;
        $leagueResponse = Http::withHeaders(['X-Riot-Token' => $this->apiKey])
            ->get('https://euw1.api.riotgames.com/tft/league/v1/entries/by-summoner/' . $gameUser->options['id']);

        if ($leagueResponse->successful()) {
            $decodedBody = json_decode($leagueResponse->body(), true);
            if (is_array($decodedBody)) {
                $stats['leagues'] = $decodedBody;
            }
        } else {
            Log::error('Could not get player data from Riot API for Teamfight Tactics',
                ['apiKey' => $this->apiKey, 'gameUser' => $gameUser, 'response' => $leagueResponse]);
        }

        return $stats;
    }

    public function getPlayerIdentity(array $params): ?array
    {
        if (!isset($params[2])) {
            return null;
        }

        $summonerResponse = Http::withHeaders(['X-Riot-Token' => $this->apiKey])
            ->get('https://euw1.api.riotgames.com/tft/summoner/v1/summoners/by-name/' . $params[2]);

        $summoner = null;
        if ($summonerResponse->successful()) {
            $decodedBody = json_decode($summonerResponse->body(), true);
            if (is_array($decodedBody)) {
                $summoner = $decodedBody;
            }
        } else {
            Log::error('Could not get player identity from Riot API for Teamfight Tactics',
                ['apiKey' => $this->apiKey, 'params' => $params, 'response' => $summonerResponse]);
        }

        return $summoner;
    }

    /**
     * @param GameUser $gameUser
     * @param array $stats
     * @param Collection<int, Assignment> $assignments
     * @param Collection<int, Queue> $queues
     * @return array
     */
    public function mapStats(GameUser $gameUser, array $stats, Collection $assignments, Collection $queues): array
    {
        $ts3ServerGroups = [];

        if (isset($stats['leagues'])) {
            foreach ($queues as $queue) {
                if ($rankAssignment = $this->mapRank($stats['leagues'],
                    $assignments->filter(function ($value) use ($queue) {
                        return $value->type?->name == $queue->type?->name;
                    }), $queue->name)) {
                    $ts3ServerGroups[$queue->type?->name] = $rankAssignment->ts3_server_group_id;
                }
            }
        }

        return $ts3ServerGroups;
    }

    /**
     * @param array $leagues
     * @param Collection<int, Assignment> $assignments
     * @param string $queueType
     * @return Assignment|null
     */
    protected function mapRank(array $leagues, Collection $assignments, string $queueType): ?Assignment
    {
        $newRankName = '';
        foreach ($leagues as $league) {
            if ($league['queueType'] == $queueType) {
                $newRankName = $league['tier'] . ' ' . $league['rank'];
            }
        }

        return $assignments->where('value', strtolower($newRankName))->first();
    }
}
