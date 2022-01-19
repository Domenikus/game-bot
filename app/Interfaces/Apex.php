<?php

namespace App\Interfaces;

use App\GameUser;
use App\Type;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Http;

class Apex extends AbstractGameInterface
{
    const PLATFORMS = [
        'origin',
        'xbl',
        'psn'
    ];


    public function getStats(GameUser $gameUser): ?array
    {
        $stats = null;

        $response = Http::withHeaders(['TRN-Api-Key' => config('game.apex-api-key')])
            ->get('https://public-api.tracker.gg/v2/apex/standard/profile/' . $gameUser->options['platform'] . '/' . $gameUser->options['name']);

        if ($response->successful()) {
            $stats = json_decode($response->body(), true);
        }

        return $stats;
    }

    public function mapStats(GameUser $gameUser, array $stats, Collection $assignments): array
    {
        $ts3ServerGroups = [];
        if ($rank = $this->mapRank($stats, $assignments->filter(function ($value) {
            return $value->type->name == Type::TYPE_RANK_SOLO;
        }))) {
            $ts3ServerGroups[Type::TYPE_RANK_SOLO] = $rank;
        }

        if ($character = $this->mapLegend($stats, $assignments->filter(function ($value) {
            return $value->type->name == Type::TYPE_CHARACTER;
        }))) {
            $ts3ServerGroups[Type::TYPE_CHARACTER] = $character;
        }

        return $ts3ServerGroups;
    }

    protected function mapRank(array $stats, Collection $assignments): ?int
    {
        $newRankName = $stats['data']["segments"][0]["stats"]["rankScore"]['metadata']['rankName'];
        return $this->getTs3ServerGroupIdForValueInGivenAssignments($assignments, $newRankName);
    }

    protected function mapLegend(array $stats, Collection $assignments): ?int
    {
        $characterWithMostKills = [
            'name' => '',
            'kills' => 0
        ];

        foreach ($stats['data']['segments'] as $segment) {
            if (empty($segment['type']) || !isset($segment['stats']) || !isset($segment['stats']['kills']['value'])) {
                continue;
            }

            if ($segment['type'] == 'legend' && $segment['stats']['kills']['value'] > $characterWithMostKills['kills']) {
                $characterWithMostKills['name'] = $segment['metadata']['name'];
                $characterWithMostKills['kills'] = $segment['stats']['kills']['value'];
            }
        }

        return $this->getTs3ServerGroupIdForValueInGivenAssignments($assignments, $characterWithMostKills['name']);
    }

    public function getPlayerData($params): ?array
    {
        if (!isset($params[2], $params[3]) || !in_array($params[3], self::PLATFORMS)) {
            return null;
        }

        $options = [
            'name' => $params[2],
            'platform' => $params[3]
        ];

        $gameUser = new GameUser();
        $gameUser->options = $options;
        if ($this->getStats($gameUser)) {
            return $options;
        }

        return null;
    }
}
