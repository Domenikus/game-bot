<?php

namespace App\Interfaces;

use App\GameUser;
use App\Type;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ApexLegends extends AbstractGameInterface
{
    const PLATFORMS = [
        'origin',
        'xbl',
        'psn'
    ];


    public function getApiKey(): ?string
    {
        return config('game.apex-api-key');
    }

    public function getPlayerData(GameUser $gameUser): ?array
    {
        $stats = null;

        $response = Http::withHeaders(['TRN-Api-Key' => $this->getApiKey()])
            ->get('https://public-api.tracker.gg/v2/apex/standard/profile/' . $gameUser->options['platform'] . '/' . $gameUser->options['name']);

        if ($response->successful()) {
            $stats = json_decode($response->body(), true);
        } else {
            Log::error('Could not get player data from TRN API for Apex Legends',
                ['apiKey' => $this->getApiKey(), 'response' => $response]);
        }

        return $stats;
    }

    public function mapStats(GameUser $gameUser, array $stats, Collection $assignments, Collection $queues): array
    {
        $ts3ServerGroups = [];

        if (isset($stats['data']["segments"][0]["stats"])) {
            foreach ($queues as $queue) {
                if ($rank = $this->mapRank($stats['data']["segments"][0]["stats"],
                    $assignments->filter(function ($value) use ($queue) {
                        return $value->type->name == $queue->type->name;
                    }), $queue->name)) {
                    $ts3ServerGroups[$queue->type->name] = $rank;
                }
            }
        }

        if ($character = $this->mapLegend($stats, $assignments->filter(function ($value) {
            return $value->type->name == Type::TYPE_CHARACTER;
        }))) {
            $ts3ServerGroups[Type::TYPE_CHARACTER] = $character;
        }

        return $ts3ServerGroups;
    }

    protected function mapRank(array $stats, Collection $assignments, string $queueType): ?int
    {
        $newRankName = '';
        foreach ($stats as $key => $stat) {
            if ($key == $queueType) {
                $newRankName = $stat['metadata']['rankName'];
            }
        }

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

    public function getPlayerIdentity($params): ?array
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
        if ($this->getPlayerData($gameUser)) {
            return $options;
        }

        return null;
    }
}
