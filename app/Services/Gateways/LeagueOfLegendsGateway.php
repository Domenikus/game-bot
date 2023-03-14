<?php

namespace App\Services\Gateways;

use App\Assignment;
use App\GameUser;
use App\Stores\LolRateLimiterStore;
use App\Type;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;
use Spatie\GuzzleRateLimiterMiddleware\RateLimiterMiddleware;
use ZanySoft\Zip\Facades\Zip;

class LeagueOfLegendsGateway implements GameGateway
{
    const MATCH_TYPE_RANKED = 'ranked';

    const QUEUE_TYPE_NAME_RANKED_GROUP = 'RANKED_FLEX_SR';

    // Riot put tft double up into lol league endpoint. This is a workaround until they fix this issue
    const QUEUE_TYPE_NAME_RANKED_TFT_DOUBLE_UP = 'RANKED_TFT_DOUBLE_UP';

    const QUEUE_TYPE_RANKED_SOLO = 'RANKED_SOLO_5x5';

    protected string $apiKey;

    protected string $championVersion;

    protected string $dataDragonBaseUrl;

    protected string $languageCode;

    protected int $matchCount;

    protected string $plattformBaseUrl;

    protected string $positionImageFolderPath = '';

    protected string $rankImageFolderPath = '';

    protected int $rateLimit;

    protected string $regionBaseUrl;

    public function __construct(string $apiKey, string $plattformBaseUrl, string $regionBaseUrl, string $realmUrl, int $matchCount, int $rateLimit)
    {
        $this->setApiKey($apiKey)
            ->setPlattformBaseUrl($plattformBaseUrl)
            ->setRegionBaseUrl($regionBaseUrl)
            ->setMatchCount($matchCount)
            ->setRateLimit($rateLimit);

        $realmResponse = Http::get($realmUrl);
        /** @var Response $realmResponse */
        if ($realmResponse->successful()) {
            $realm = $realmResponse->json();
            if (is_array($realm)) {
                $this->setLanguageCode($realm['l'])
                    ->setChampionVersion($realm['n']['champion'])
                    ->setDataDragonBaseUrl($realm['cdn']);
            }
        } else {
            throw new InvalidArgumentException('Could not get realm file');
        }
    }

    public function __destruct()
    {
        File::deleteDirectory($this->rankImageFolderPath);
        File::deleteDirectory($this->positionImageFolderPath);
    }

    public function getApiKey(): string
    {
        return $this->apiKey;
    }

    public function setApiKey(string $apiKey): LeagueOfLegendsGateway
    {
        $this->apiKey = $apiKey;

        return $this;
    }

    public function getChampionVersion(): string
    {
        return $this->championVersion;
    }

    public function setChampionVersion(string $championVersion): LeagueOfLegendsGateway
    {
        $this->championVersion = $championVersion;

        return $this;
    }

    public function getDataDragonBaseUrl(): string
    {
        return $this->dataDragonBaseUrl;
    }

    public function setDataDragonBaseUrl(string $dataDragonBaseUrl): LeagueOfLegendsGateway
    {
        $this->dataDragonBaseUrl = $dataDragonBaseUrl;

        return $this;
    }

    public function getLanguageCode(): string
    {
        return $this->languageCode;
    }

    public function setLanguageCode(string $languageCode): LeagueOfLegendsGateway
    {
        $this->languageCode = $languageCode;

        return $this;
    }

    public function getMatchCount(): int
    {
        return $this->matchCount;
    }

    public function setMatchCount(int $matchCount): LeagueOfLegendsGateway
    {
        $this->matchCount = $matchCount;

        return $this;
    }

    public function getPlattformBaseUrl(): string
    {
        return $this->plattformBaseUrl;
    }

    public function setPlattformBaseUrl(string $plattformBaseUrl): LeagueOfLegendsGateway
    {
        $this->plattformBaseUrl = $plattformBaseUrl;

        return $this;
    }

    public function getPositionImageFolderPath(): string
    {
        return $this->positionImageFolderPath;
    }

    public function setPositionImageFolderPath(string $positionImageFolderPath): LeagueOfLegendsGateway
    {
        $this->positionImageFolderPath = $positionImageFolderPath;

        return $this;
    }

    public function getRankImageFolderPath(): string
    {
        return $this->rankImageFolderPath;
    }

    public function setRankImageFolderPath(string $rankImageFolderPath): LeagueOfLegendsGateway
    {
        $this->rankImageFolderPath = $rankImageFolderPath;

        return $this;
    }

    public function getRateLimit(): int
    {
        return $this->rateLimit;
    }

    public function setRateLimit(int $rateLimit): LeagueOfLegendsGateway
    {
        $this->rateLimit = $rateLimit;

        return $this;
    }

    public function getRegionBaseUrl(): string
    {
        return $this->regionBaseUrl;
    }

    public function setRegionBaseUrl(string $regionBaseUrl): LeagueOfLegendsGateway
    {
        $this->regionBaseUrl = $regionBaseUrl;

        return $this;
    }

    public function grabCharacterImage(string $characterName): ?string
    {
        $characterImage = null;

        $url = $this->getDataDragonBaseUrl().'/'.$this->getChampionVersion().'/img/champion/'.$characterName.'.png';
        $characterImageResponse = Http::get($url);

        /** @var Response $characterImageResponse */
        if ($characterImageResponse->successful()) {
            $characterImage = $characterImageResponse->body();
        } else {
            Log::error('Could not get character image', ['characterName' => $characterName, 'responseStatus' => $characterImageResponse->status(), 'url' => $url]);
        }

        return $characterImage;
    }

    public function grabCharacters(): array
    {
        $result = [];

        $url = $this->getDataDragonBaseUrl().'/'.$this->getChampionVersion().'/data/'.$this->getLanguageCode().'/champion.json';
        $championsResponse = Http::get($url);
        /** @var Response $championsResponse */
        if ($championsResponse->successful()) {
            $characters = $championsResponse->json();
            if (is_array($characters)) {
                $result = Arr::pluck($characters['data'], 'id');
            }
        } else {
            Log::error('Could not get champions from Riot\'s Data-Dragon CDN',
                [
                    'gameVersion' => $this->getChampionVersion(),
                    'languageCode' => $this->getLanguageCode(),
                    'responseStatus' => $championsResponse->status(),
                    'url' => $url,
                ]);
        }

        return $result;
    }

    public function grabPlayerData(GameUser $gameUser): ?array
    {
        $stats = null;
        if ($matches = $this->grabMatches($gameUser, 0, $this->getMatchCount(), self::MATCH_TYPE_RANKED)) {
            $stats['matches'] = $matches;
        }

        if ($leagues = $this->grabLeagues($gameUser)) {
            $stats['leagues'] = $leagues;
        }

        return $stats;
    }

    public function grabPlayerIdentity(array $params): ?array
    {
        if (! isset($params[2])) {
            return null;
        }

        $url = $this->getPlattformBaseUrl().'/lol/summoner/v4/summoners/by-name/'.$params[2];
        $summonerResponse = Http::withHeaders(['X-Riot-Token' => $this->getApiKey()])
            ->withMiddleware(RateLimiterMiddleware::perSecond($this->getRateLimit(), new LolRateLimiterStore))
            ->retry(3, 1000, throw: false)
            ->get($url);

        $identity = null;
        /** @var Response $summonerResponse */
        if ($summonerResponse->successful()) {
            $result = $summonerResponse->json();
            if (is_array($result)) {
                $identity = $result;
            }
        } else {
            Log::warning('Could not get player identity from Riot API for League of Legends',
                ['apiKey' => $this->getApiKey(), 'params' => $params, 'responseStatus' => $summonerResponse->status(), 'url' => $url]);
        }

        return $identity;
    }

    public function grabPositionImage(string $positionName): ?string
    {
        $positionImage = null;
        if (! $this->getPositionImageFolderPath()) {
            if ($archiveFilePath = $this->downloadArchive('https://static.developer.riotgames.com/docs/lol/ranked-positions.zip', 'positions-icons')) {
                $positionImageFolderFilePathPath = getcwd().'/storage/positions-icons';
                if ($this->extractArchive($archiveFilePath, $positionImageFolderFilePathPath)) {
                    $this->setPositionImageFolderPath($positionImageFolderFilePathPath);
                    File::delete($archiveFilePath);
                }
            }
        }

        /** @var array $positionMapping */
        $positionMapping = config('static-data.lol.positionMapping');
        $positionName = $positionMapping[$positionName] ?? $positionName;
        $fileName = 'Position_Challenger-'.$positionName.'.png';

        if (File::exists($this->getPositionImageFolderPath().'/'.$fileName)) {
            $positionImage = File::get($this->getPositionImageFolderPath().'/'.$fileName, true);
        } else {
            Log::error('No position image found', ['rankName' => $positionName, 'fileName' => $fileName]);
        }

        return $positionImage;
    }

    public function grabPositions(): ?array
    {
        // Riot offers no static json or endpoint to get this data. If so this will be replaced
        $positions = config('static-data.lol.positions');
        if (! is_array($positions)) {
            return null;
        }

        return $positions;
    }

    public function grabRankImage(string $rankName): ?string
    {
        $rankImage = null;
        if (! $this->getRankImageFolderPath()) {
            if ($archiveFilePath = $this->downloadArchive('https://static.developer.riotgames.com/docs/lol/ranked-emblems.zip', 'rank-icons')) {
                $rankImageFolderPath = getcwd().'/storage/rank-icons';
                if ($this->extractArchive($archiveFilePath, $rankImageFolderPath)) {
                    $this->setRankImageFolderPath($rankImageFolderPath);
                    File::delete($archiveFilePath);
                }
            }
        }

        $fileName = 'Emblem_'.substr($rankName, 0, strpos($rankName, ' ') ?: strlen($rankName)).'.png';

        if (File::exists($this->getRankImageFolderPath().'/'.$fileName)) {
            $rankImage = File::get($this->getRankImageFolderPath().'/'.$fileName, true);
        } else {
            Log::error('No rank image found', ['rankName' => $rankName, 'fileName' => $fileName]);
        }

        return $rankImage;
    }

    public function grabRanks(): ?array
    {
        // Riot offers no static json or endpoint to get this data. If so this will be replaced
        $ranks = config('static-data.lol.ranks');
        if (! is_array($ranks)) {
            return null;
        }

        return $ranks;
    }

    /**
     * @param  Collection<int, Assignment>  $assignments
     */
    public function mapStats(GameUser $gameUser, array $stats, Collection $assignments): array
    {
        $ts3ServerGroups = [];
        $matchData = [];

        if (isset($stats['leagues'])) {
            if ($rankAssignment = $this->mapRank($stats['leagues'],
                $assignments->filter(function ($value) {
                    return $value->type?->name == Type::NAME_RANK_SOLO;
                }), self::QUEUE_TYPE_RANKED_SOLO)) {
                $ts3ServerGroups[Type::NAME_RANK_SOLO] = $rankAssignment->ts3_server_group_id;
            }

            if ($rankAssignment = $this->mapRank($stats['leagues'],
                $assignments->filter(function ($value) {
                    return $value->type?->name == Type::NAME_RANK_GROUP;
                }), self::QUEUE_TYPE_NAME_RANKED_GROUP)) {
                $ts3ServerGroups[Type::NAME_RANK_GROUP] = $rankAssignment->ts3_server_group_id;
            }

            if ($rankAssignment = $this->mapRank($stats['leagues'],
                $assignments->filter(function ($value) {
                    return $value->type?->name == Type::NAME_RANK_DUO;
                }), self::QUEUE_TYPE_NAME_RANKED_TFT_DOUBLE_UP)) {
                $ts3ServerGroups[Type::NAME_RANK_DUO] = $rankAssignment->ts3_server_group_id;
            }
        }

        if (isset($stats['matches'])) {
            $matchData = $this->mapMatches($gameUser, $stats['matches'], $assignments);
        }

        return array_merge($ts3ServerGroups, $matchData);
    }

    protected function downloadArchive(string $url, string $fileName): ?string
    {
        $result = null;

        $rankImagesResponse = Http::get($url);
        /** @var Response $rankImagesResponse */
        if ($rankImagesResponse->successful()) {
            $savePath = getcwd().'/storage/'.$fileName.'.zip';
            if (File::put($savePath, $rankImagesResponse->body())) {
                if (Zip::check($savePath)) {
                    $result = $savePath;
                }
            }
        } else {
            Log::error('Error while downloading archive', ['url' => $url, 'fileName' => $fileName]);
        }

        return $result;
    }

    protected function extractArchive(string $filePath, string $extractPath): bool
    {
        $result = false;

        try {
            $zip = Zip::open($filePath);
            $result = $zip->extract($extractPath);
        } catch (Exception $e) {
            Log::error('Could not extract zip archive', ['filePath' => $filePath, 'extractPath' => $extractPath]);
            report($e);
        }

        return $result;
    }

    protected function grabLeagues(GameUser $gameUser): array
    {
        $leagues = [];
        $url = $this->getPlattformBaseUrl().'/lol/league/v4/entries/by-summoner/'.$gameUser->options['id'];
        $leagueResponse = Http::withHeaders(['X-Riot-Token' => $this->getApiKey()])
            ->withMiddleware(RateLimiterMiddleware::perSecond($this->getRateLimit(), new LolRateLimiterStore))
            ->retry(3, 1000, throw: false)
            ->get($url);

        /** @var Response $leagueResponse */
        if ($leagueResponse->successful()) {
            $result = $leagueResponse->json();
            if (is_array($result)) {
                $leagues = $result;
            }
        } else {
            Log::warning('Could not get leagues from Riot API for League of Legends',
                ['apiKey' => $this->getApiKey(), 'gameUser' => $gameUser, 'responseStatus' => $leagueResponse->status(), 'url' => $url]);
        }

        return $leagues;
    }

    protected function grabMatches(GameUser $gameUser, int $offset, int $count, string $type): array
    {
        $url = $this->getRegionBaseUrl().'/lol/match/v5/matches/by-puuid/'.$gameUser->options['puuid'].'/ids';
        $matchIdsResponse = Http::withHeaders(['X-Riot-Token' => $this->getApiKey()])
            ->withMiddleware(RateLimiterMiddleware::perSecond($this->getRateLimit(), new LolRateLimiterStore))
            ->retry(3, 1000, throw: false)
            ->get($url,
                [
                    'start' => $offset,
                    'count' => $count,
                    'type' => $type,
                ]
            );

        $matchIds = [];
        /** @var Response $matchIdsResponse */
        if ($matchIdsResponse->successful()) {
            $result = $matchIdsResponse->json();
            if (is_array($result)) {
                $matchIds = $result;
            }
        } else {
            Log::warning('Could not get match id\'s from Riot API for League of Legends',
                ['apiKey' => $this->getApiKey(), 'gameUser' => $gameUser, 'status' => $matchIdsResponse->status(), 'url' => $url]);
        }

        $matches = [];
        foreach ($matchIds as $matchId) {
            $url = $this->getRegionBaseUrl().'/lol/match/v5/matches/'.$matchId;
            $matchResponse = Http::withHeaders(['X-Riot-Token' => $this->getApiKey()])
                ->withMiddleware(RateLimiterMiddleware::perSecond($this->getRateLimit(), new LolRateLimiterStore()))
                ->retry(3, 1000, throw: false)
                ->get($url);
            /** @var Response $matchResponse */
            if ($matchResponse->successful()) {
                $matches[] = $matchResponse->json();
            } else {
                Log::warning('Could not get match from Riot API for League of Legends',
                    ['apiKey' => $this->getApiKey(), 'gameUser' => $gameUser, 'status' => $matchResponse->status(), 'url' => $url]);
            }
        }

        return $matches;
    }

    /**
     * @param  Collection<int, Assignment>  $assignments
     */
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

    /**
     * @param  Collection<int, Assignment>  $assignments
     */
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

    /**
     * @param  Collection<int, Assignment>  $assignments
     */
    protected function mapMatches(GameUser $gameUser, array $matches, Collection $assignments): array
    {
        $result = [];

        $championPlayCount = [];
        $lanePlayCount = [];
        foreach ($matches as $match) {
            if ($championAssignment = $this->mapChampion($gameUser, $match, $assignments->filter(function ($value) {
                return $value->type?->name == Type::NAME_CHARACTER;
            }))) {
                if (! isset($championPlayCount[$championAssignment->ts3_server_group_id])) {
                    $championPlayCount[$championAssignment->ts3_server_group_id] = 0;
                }

                $championPlayCount[$championAssignment->ts3_server_group_id]++;
            }

            if ($championAssignment = $this->mapLane($gameUser, $match, $assignments->filter(function ($value) {
                return $value->type?->name == Type::NAME_POSITION;
            }))) {
                if (! isset($lanePlayCount[$championAssignment->ts3_server_group_id])) {
                    $lanePlayCount[$championAssignment->ts3_server_group_id] = 0;
                }

                $lanePlayCount[$championAssignment->ts3_server_group_id]++;
            }
        }

        if (! empty($championPlayCount)) {
            arsort($championPlayCount);
            $result[Type::NAME_CHARACTER] = array_key_first($championPlayCount);
        }

        if (! empty($lanePlayCount)) {
            arsort($lanePlayCount);
            $result[Type::NAME_POSITION] = array_key_first($lanePlayCount);
        }

        return $result;
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
