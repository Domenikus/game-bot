<?php

namespace App\Interfaces;

use App\Assignment;
use App\Game;
use App\GameUser;
use App\Type;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Http;

class Apex extends AbstractGameInterface
{
    const PLATFORMS = [
        'origin',
        'xbl',
        'psn'
    ];


    public function getPlayerStats(GameUser $gameUser): array
    {
        $stats = [];

        $response = Http::withHeaders(['TRN-Api-Key' => config('app.apex-api-key')])
            ->get('https://public-api.tracker.gg/v2/apex/standard/profile/' . $gameUser->options['platform'] . '/' . $gameUser->options['name']);

        if ($response->successful()) {
            $stats = json_decode($response->body(), true);
        }

        return $stats;
    }

    protected function mapRank(array $stats, Collection $assignments): ?int
    {
        $assignments = Assignment::with(['type' => function ($query) {
            $query->where('name', Type::NAME_RANK);
        }, 'game' => function ($query) {
            $query->where('name', Game::NAME_APEX);
        }])->get();

        $newRankName = $stats['data']["segments"][0]["stats"]["rankScore"]['metadata']['rankName'];

        foreach ($assignments as $assignment) {
            if ($assignment->value == $newRankName) {
                return $assignment->ts3_server_group_id;
            }
        }

        return null;
    }

    protected function mapCharacter(array $stats, Collection $assignments): ?int
    {
        $characterWithMostKills = [
            'name' => '',
            'kills' => 0
        ];

        foreach ($stats['data']['segments'] as $segment) {
            if ($segment['type'] == 'legend' && $segment['stats']['kills']['value'] > $characterWithMostKills['kills']) {
                $characterWithMostKills['name'] = $segment['metadata']['name'];
                $characterWithMostKills['kills'] = $segment['stats']['kills']['value'];
            }
        }

        foreach ($assignments as $assignment) {
            if ($assignment->value == $characterWithMostKills['name']) {
                return $assignment->ts3_server_group_id;
            }
        }

        return null;
    }

    // !register apex name plattform
    public function mapRegistration($params): ?array
    {
        if (!isset($params[2])) {
            throw new Exception('No name given');
        }

        if (!isset($params[3]) || !in_array($params[3], self::PLATFORMS)) {
            throw new Exception('No platform given');
        }

        return [
            'name' => $params[2],
            'platform' => $params[3]
        ];
    }
}
