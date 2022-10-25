<?php

namespace App\Interfaces;

use App\Assignment;
use App\GameUser;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TeamfightTactics implements GameApi
{
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
        } else {
            Log::error('Could not get player data from Riot API for Teamfight Tactics',
                ['apiKey' => $this->getApiKey(), 'gameUser' => $gameUser, 'response' => $leagueResponse]);
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
        } else {
            Log::error('Could not get player identity from Riot API for Teamfight Tactics',
                ['apiKey' => $this->getApiKey(), 'params' => $params, 'response' => $summonerResponse]);
        }

        return $summoner;
    }

    public function mapStats(GameUser $gameUser, array $stats, Collection $assignments, Collection $queues): array
    {
        $ts3ServerGroups = [];

        if (isset($stats['leagues'])) {
            foreach ($queues as $queue) {
                if ($rankAssignment = $this->mapRank($stats['leagues'],
                    $assignments->filter(function ($value) use ($queue) {
                        return $value->type->name == $queue->type->name;
                    }), $queue->name)) {
                    $ts3ServerGroups[$queue->type->name] = $rankAssignment->ts3_server_group_id;
                }
            }
        }

        return $ts3ServerGroups;
    }

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
