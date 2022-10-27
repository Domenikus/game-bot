<?php

namespace App\Services\Gateways;

use App\Assignment;
use App\GameUser;
use App\Type;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ApexLegendsGateway implements GameGateway
{
    const PLATFORMS = [
        'origin',
        'xbl',
        'psn',
    ];

    protected string $apiKey;

    public function __construct(string $apiKey)
    {
        $this->apiKey = $apiKey;
    }

    public function getPlayerData(GameUser $gameUser): ?array
    {
        $stats = null;

        $response = Http::withHeaders(['TRN-Api-Key' => $this->apiKey])
            ->get('https://public-api.tracker.gg/v2/apex/standard/profile/'.$gameUser->options['platform'].'/'.$gameUser->options['name']);

        if ($response->successful()) {
            $decodedBody = json_decode($response->body(), true);
            if (is_array($decodedBody)) {
                $stats = $decodedBody;
            }
        } else {
            Log::error('Could not get player data from TRN API for Apex Legends',
                ['apiKey' => $this->apiKey, 'response' => $response]);
        }

        return $stats;
    }

    public function mapStats(GameUser $gameUser, array $stats, Collection $assignments, Collection $queues): array
    {
        $ts3ServerGroups = [];

        if (isset($stats['data']['segments'][0]['stats'])) {
            foreach ($queues as $queue) {
                if ($rankAssignment = $this->mapRank($stats['data']['segments'][0]['stats'],
                    $assignments->filter(function ($value) use ($queue) {
                        return $value->type?->name == $queue->type?->name;
                    }), $queue->name)) {
                    $ts3ServerGroups[$queue->type?->name] = $rankAssignment->ts3_server_group_id;
                }
            }
        }

        if ($characterAssignment = $this->mapLegend($stats, $assignments->filter(function ($value) {
            return $value->type?->name == Type::TYPE_CHARACTER;
        }))) {
            $ts3ServerGroups[Type::TYPE_CHARACTER] = $characterAssignment->ts3_server_group_id;
        }

        return $ts3ServerGroups;
    }

    /**
     * @param  array  $stats
     * @param  Collection<int, Assignment>  $assignments
     * @param  string  $queueType
     * @return Assignment|null
     */
    protected function mapRank(array $stats, Collection $assignments, string $queueType): ?Assignment
    {
        $newRankName = '';
        foreach ($stats as $key => $stat) {
            if ($key == $queueType) {
                $newRankName = $stat['metadata']['rankName'];
            }
        }

        return $assignments->where('value', strtolower($newRankName))->first();
    }

    /**
     * @param  array  $stats
     * @param  Collection<int, Assignment>  $assignments
     * @return Assignment|null
     */
    protected function mapLegend(array $stats, Collection $assignments): ?Assignment
    {
        $characterWithMostKills = [
            'name' => '',
            'kills' => 0,
        ];

        foreach ($stats['data']['segments'] as $segment) {
            if (empty($segment['type']) || ! isset($segment['stats']) || ! isset($segment['stats']['kills']['value'])) {
                continue;
            }

            if ($segment['type'] == 'legend' && $segment['stats']['kills']['value'] > $characterWithMostKills['kills']) {
                $characterWithMostKills['name'] = $segment['metadata']['name'];
                $characterWithMostKills['kills'] = $segment['stats']['kills']['value'];
            }
        }

        return $assignments->where('value', strtolower($characterWithMostKills['name']))->first();
    }

    public function getPlayerIdentity(array $params): ?array
    {
        if (! isset($params[2], $params[3]) || ! in_array($params[3], self::PLATFORMS)) {
            return null;
        }

        $options = [
            'name' => $params[2],
            'platform' => $params[3],
        ];

        $gameUser = new GameUser();
        $gameUser->options = $options;
        if ($this->getPlayerData($gameUser)) {
            return $options;
        }

        return null;
    }
}
