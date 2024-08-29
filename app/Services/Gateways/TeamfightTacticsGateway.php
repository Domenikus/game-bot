<?php

namespace App\Services\Gateways;

use App\Assignment;
use App\GameUser;
use App\Stores\TftRateLimiterStore;
use App\Type;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Spatie\GuzzleRateLimiterMiddleware\RateLimiterMiddleware;

class TeamfightTacticsGateway extends LeagueOfLegendsGateway implements GameGateway
{
    const QUEUE_TYPE_RANKED = 'RANKED_TFT';

    public function __construct(string $apiKey, string $plattformBaseUrl, string $regionBaseUrl, string $realmUrl, int $rateLimit)
    {
        parent::__construct($apiKey, $plattformBaseUrl, $regionBaseUrl, $realmUrl, 0, $rateLimit);
    }

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
            ->withMiddleware(RateLimiterMiddleware::perSecond($this->rateLimit, new TftRateLimiterStore))
            ->retry(3, 1000, throw: false)
            ->get($url);

        /** @var Response $leagueResponse */
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

    public function grabSummoner(string $puuid): ?array
    {
        $url = $this->getPlattformBaseUrl().'/tft/summoner/v1/summoners/by-puuid/'.$puuid;
        /** @var Response $summonerResponse */
        $summonerResponse = Http::withHeaders(['X-Riot-Token' => $this->getApiKey()])
            ->withMiddleware(RateLimiterMiddleware::perSecond($this->getRateLimit(), new TftRateLimiterStore))
            ->retry(3, 1000, throw: false)
            ->get($url);

        $result = null;
        if ($summonerResponse->successful()) {
            /** @var array $result */
            $result = $summonerResponse->json();
        } else {
            Log::warning('Could not get summoner from Riot API',
                ['apiKey' => $this->getApiKey(), 'puuid' => $puuid, 'responseStatus' => $summonerResponse->status(), 'url' => $url]);
        }

        return $result;
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
     * @param  Collection<int, Assignment>  $assignments
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
     * @param  Collection<int, Assignment>  $assignments
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
