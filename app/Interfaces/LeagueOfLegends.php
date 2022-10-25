<?php

namespace App\Interfaces;

use App\Assignment;
use App\GameUser;
use App\Type;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;


class LeagueOfLegends implements GameApi
{
    const NUMBER_OF_MATCHES = 20;
    const MATCH_TYPE_RANKED = 'ranked';


    public function getApiKey(): ?string
    {
        return config('game.lol-api-key');
    }

    public function getPlayerData(GameUser $gameUser): ?array
    {
        $stats = null;
        if ($matches = $this->getMatches($gameUser, 0, self::NUMBER_OF_MATCHES, self::MATCH_TYPE_RANKED)) {
            $stats['matches'] = $matches;
        }

        if ($leagues = $this->getLeagues($gameUser)) {
            $stats['leagues'] = $leagues;
        }

        return $stats;
    }

    protected function getMatches(GameUser $gameUser, int $offset, int $count, string $type): array
    {
        $matchIdsResponse = Http::withHeaders(['X-Riot-Token' => $this->getApiKey()])
            ->get('https://europe.api.riotgames.com/lol/match/v5/matches/by-puuid/' . $gameUser->options['puuid'] . '/ids',
                [
                    'start' => $offset,
                    'count' => $count,
                    'type' => $type
                ]
            );

        $matchIds = [];
        if ($matchIdsResponse->successful()) {
            $matchIds = json_decode($matchIdsResponse->body(), true);
        } else {
            Log::error('Could not get match id\'s from Riot API for League of Legends',
                ['apiKey' => $this->getApiKey(), 'gameUser' => $gameUser, 'response' => $matchIdsResponse]);
        }

        $matches = [];
        foreach ($matchIds as $matchId) {
            $matchResponse = Http::withHeaders(['X-Riot-Token' => $this->getApiKey()])
                ->get('https://europe.api.riotgames.com/lol/match/v5/matches/' . $matchId);

            if ($matchResponse->successful()) {
                $matches[] = json_decode(($matchResponse->body()), true);
            } else {
                Log::error('Could not get matches from Riot API for League of Legends',
                    ['apiKey' => $this->getApiKey(), 'gameUser' => $gameUser, 'response' => $matchResponse]);
            }
        }

        return $matches;
    }

    protected function getLeagues(GameUser $gameUser): array
    {
        $leagues = [];
        $leagueResponse = Http::withHeaders(['X-Riot-Token' => $this->getApiKey()])
            ->get('https://euw1.api.riotgames.com/lol/league/v4/entries/by-summoner/' . $gameUser->options['id']);

        if ($leagueResponse->successful()) {
            $leagues = json_decode($leagueResponse->body(), true);
        } else {
            Log::error('Could not get leagues from Riot API for League of Legends',
                ['apiKey' => $this->getApiKey(), 'gameUser' => $gameUser, 'response' => $leagueResponse]);
        }

        return $leagues;
    }

    public function mapStats(GameUser $gameUser, array $stats, Collection $assignments, Collection $queues): array
    {
        $ts3ServerGroups = [];
        $matchData = [];

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

        if (isset($stats['matches'])) {
            $matchData = $this->mapMatches($gameUser, $stats['matches'], $assignments);
        }

        return array_merge($ts3ServerGroups, $matchData);
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

    protected function mapMatches(GameUser $gameUser, array $matches, Collection $assignments): array
    {
        $result = [];

        $championPlayCount = [];
        $lanePlayCount = [];
        foreach ($matches as $match) {
            if ($championAssignment = $this->mapChampion($gameUser, $match, $assignments->filter(function ($value) {
                return $value->type->name == Type::TYPE_CHARACTER;
            }))) {
                if (!isset($championPlayCount[$championAssignment->ts3_server_group_id])) {
                    $championPlayCount[$championAssignment->ts3_server_group_id] = 0;
                }

                $championPlayCount[$championAssignment->ts3_server_group_id]++;
            }

            if ($championAssignment = $this->mapLane($gameUser, $match, $assignments->filter(function ($value) {
                return $value->type->name == Type::TYPE_POSITION;
            }))) {
                if (!isset($lanePlayCount[$championAssignment->ts3_server_group_id])) {
                    $lanePlayCount[$championAssignment->ts3_server_group_id] = 0;
                }

                $lanePlayCount[$championAssignment->ts3_server_group_id]++;
            }
        }

        if (!empty($championPlayCount)) {
            arsort($championPlayCount);
            $result[Type::TYPE_CHARACTER] = array_key_first($championPlayCount);
        }

        if (!empty($lanePlayCount)) {
            arsort($lanePlayCount);
            $result[Type::TYPE_POSITION] = array_key_first($lanePlayCount);
        }

        return $result;
    }

    protected function mapChampion(GameUser $gameUser, array $match, Collection $assignments): ?Assignment
    {
        foreach ($match['info']['participants'] as $participant) {
            if ($participant['puuid'] !== $gameUser->options['puuid']) {
                continue;
            }

            return $assignments->where('value', strtolower($participant['championName']))->first();
        }

        return null;
    }

    protected function mapLane(GameUser $gameUser, array $match, Collection $assignments): ?Assignment
    {
        foreach ($match['info']['participants'] as $participant) {
            if ($participant['puuid'] !== $gameUser->options['puuid']) {
                continue;
            }

            return $assignments->where('value', strtolower($participant['individualPosition']))->first();
        }

        return null;
    }

    public function getPlayerIdentity(array $params): ?array
    {
        if (!isset($params[2])) {
            return null;
        }

        $summonerResponse = Http::withHeaders(['X-Riot-Token' => $this->getApiKey()])
            ->get('https://euw1.api.riotgames.com/lol/summoner/v4/summoners/by-name/' . $params[2]);

        $result = null;
        if ($summonerResponse->successful()) {
            $summoner = json_decode($summonerResponse->body(), true);
            $result = $summoner;
        } else {
            Log::error('Could not get player identity from Riot API for League of Legends',
                ['apiKey' => $this->getApiKey(), 'params' => $params, 'response' => $summonerResponse]);
        }

        return $result;
    }
}
