<?php

namespace App\Interfaces;

use App\GameUser;
use App\Type;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Http;


class LeagueOfLegends extends AbstractGameInterface
{
    const QUEUE_TYPE_RANKED_SOLO = 'RANKED_SOLO_5x5';
    const QUEUE_TYPE_NAME_RANKED_GROUP = 'RANKED_FLEX_SR';
    // Riot put tft double up into lol league endpoint. This is a workaround until they fix this issue
    const QUEUE_TYPE_NAME_RANKED_TFT_DOUBLE_UP = 'RANKED_TFT_DOUBLE_UP';
    const NUMBER_OF_MATCHES = 20;
    const MATCH_TYPE_RANKED = 'ranked';


    public function getApiKey(): ?string
    {
        return config('game.lol-api-key');
    }

    public function getPlayerData(GameUser $gameUser): ?array
    {
        $stats = null;
        $leagueResponse = Http::withHeaders(['X-Riot-Token' => $this->getApiKey()])
            ->get('https://euw1.api.riotgames.com/lol/league/v4/entries/by-summoner/' . $gameUser->options['id']);

        if ($matches = $this->getMatches($gameUser, 0, self::NUMBER_OF_MATCHES, self::MATCH_TYPE_RANKED)) {
            $stats['matches'] = $matches;
        }

        if ($leagueResponse->successful()) {
            $stats['leagues'] = json_decode($leagueResponse->body(), true);
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
        }

        $matches = [];
        foreach ($matchIds as $matchId) {
            $matchResponse = Http::withHeaders(['X-Riot-Token' => $this->getApiKey()])
                ->get('https://europe.api.riotgames.com/lol/match/v5/matches/' . $matchId);

            if ($matchResponse->successful()) {
                $matches[] = json_decode(($matchResponse->body()), true);
            }
        }

        return $matches;
    }

    public function mapStats(GameUser $gameUser, array $stats, Collection $assignments): array
    {
        $ts3ServerGroups = [];
        $matchData = [];

        if (isset($stats['leagues'])) {
            if ($rank = $this->mapRank($stats['leagues'], $assignments->filter(function ($value) {
                return $value->type->name == Type::TYPE_RANK_SOLO;
            }), self::QUEUE_TYPE_RANKED_SOLO)) {
                $ts3ServerGroups[Type::TYPE_RANK_SOLO] = $rank;
            }

            if ($rank = $this->mapRank($stats['leagues'], $assignments->filter(function ($value) {
                return $value->type->name == Type::TYPE_RANK_GROUP;
            }), self::QUEUE_TYPE_NAME_RANKED_GROUP)) {
                $ts3ServerGroups[Type::TYPE_RANK_GROUP] = $rank;
            }

            if ($rank = $this->mapRank($stats['leagues'], $assignments->filter(function ($value) {
                return $value->type->name == Type::TYPE_RANK_PAIR;
            }), self::QUEUE_TYPE_NAME_RANKED_TFT_DOUBLE_UP)) {
                $ts3ServerGroups[Type::TYPE_RANK_PAIR] = $rank;
            }
        }

        if (isset($stats['matches'])) {
            $matchData = $this->mapMatches($gameUser, $stats['matches'], $assignments);
        }

        return array_merge($ts3ServerGroups, $matchData);
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

    protected function mapMatches(GameUser $gameUser, array $matches, Collection $assignments): array
    {
        $result = [];

        $championPlayCount = [];
        $lanePlayCount = [];
        foreach ($matches as $match) {
            if ($champion = $this->mapChampion($gameUser, $match, $assignments->filter(function ($value) {
                return $value->type->name == Type::TYPE_CHARACTER;
            }))) {
                if (!isset($championPlayCount[$champion])) {
                    $championPlayCount[$champion] = 0;
                }

                $championPlayCount[$champion]++;
            }

            if ($champion = $this->mapLane($gameUser, $match, $assignments->filter(function ($value) {
                return $value->type->name == Type::TYPE_POSITION;
            }))) {
                if (!isset($lanePlayCount[$champion])) {
                    $lanePlayCount[$champion] = 0;
                }

                $lanePlayCount[$champion]++;
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

    protected function mapChampion(GameUser $gameUser, array $match, Collection $assignments): ?string
    {
        foreach ($match['info']['participants'] as $participant) {
            if ($participant['puuid'] !== $gameUser->options['puuid']) {
                continue;
            }

            return $this->getTs3ServerGroupIdForValueInGivenAssignments($assignments, $participant['championName']);
        }

        return null;
    }

    protected function mapLane(GameUser $gameUser, array $match, Collection $assignments): ?string
    {
        foreach ($match['info']['participants'] as $participant) {
            if ($participant['puuid'] !== $gameUser->options['puuid']) {
                continue;
            }

            return $this->getTs3ServerGroupIdForValueInGivenAssignments($assignments, $participant['individualPosition']);
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
        }

        return $result;
    }
}
