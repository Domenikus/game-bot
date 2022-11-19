<?php

namespace App\Services\Gateways;

use App\Assignment;
use App\GameUser;
use App\Queue;
use App\Type;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use ZanySoft\Zip\Facades\Zip;

class LeagueOfLegendsGateway implements GameGateway
{
    const MATCH_TYPE_RANKED = 'ranked';

    const NUMBER_OF_MATCHES = 20;

    protected string $apiKey;

    protected string $gameVersion;

    protected string $languageCode;

    protected string $rankImageFolderPath = '';

    protected string $positionImageFolderPath = '';

    protected array $rankImages = [];

    public function __construct(string $apiKey, string $gameVersion, string $languageCode)
    {
        $this->apiKey = $apiKey;
        $this->gameVersion = $gameVersion;
        $this->languageCode = $languageCode;
    }

    public function __destruct()
    {
        File::deleteDirectory($this->rankImageFolderPath);
        File::deleteDirectory($this->positionImageFolderPath);
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
        $matchIdsResponse = Http::withHeaders(['X-Riot-Token' => $this->apiKey])
            ->get('https://europe.api.riotgames.com/lol/match/v5/matches/by-puuid/'.$gameUser->options['puuid'].'/ids',
                [
                    'start' => $offset,
                    'count' => $count,
                    'type' => $type,
                ]
            );

        $matchIds = [];
        if ($matchIdsResponse->successful()) {
            $decodedBody = json_decode($matchIdsResponse->body(), true);
            if (is_array($decodedBody)) {
                $matchIds = $decodedBody;
            }
        } else {
            Log::warning('Could not get match id\'s from Riot API for League of Legends',
                ['apiKey' => $this->apiKey, 'gameUser' => $gameUser, 'response' => $matchIdsResponse]);
        }

        $matches = [];
        foreach ($matchIds as $matchId) {
            $matchResponse = Http::withHeaders(['X-Riot-Token' => $this->apiKey])
                ->get('https://europe.api.riotgames.com/lol/match/v5/matches/'.$matchId);

            if ($matchResponse->successful()) {
                $matches[] = json_decode(($matchResponse->body()), true);
            } else {
                Log::warning('Could not get matches from Riot API for League of Legends',
                    ['apiKey' => $this->apiKey, 'gameUser' => $gameUser, 'response' => $matchResponse]);
            }
        }

        return $matches;
    }

    protected function getLeagues(GameUser $gameUser): array
    {
        $leagues = [];
        $leagueResponse = Http::withHeaders(['X-Riot-Token' => $this->apiKey])
            ->get('https://euw1.api.riotgames.com/lol/league/v4/entries/by-summoner/'.$gameUser->options['id']);

        if ($leagueResponse->successful()) {
            $decodedBody = json_decode($leagueResponse->body(), true);
            if (is_array($decodedBody)) {
                $leagues = $decodedBody;
            }
        } else {
            Log::warning('Could not get leagues from Riot API for League of Legends',
                ['apiKey' => $this->apiKey, 'gameUser' => $gameUser, 'response' => $leagueResponse]);
        }

        return $leagues;
    }

    public function getPlayerIdentity(array $params): ?array
    {
        if (! isset($params[2])) {
            return null;
        }

        $summonerResponse = Http::withHeaders(['X-Riot-Token' => $this->apiKey])
            ->get('https://euw1.api.riotgames.com/lol/summoner/v4/summoners/by-name/'.$params[2]);

        $result = null;
        if ($summonerResponse->successful()) {
            $decodedBody = json_decode($summonerResponse->body(), true);
            if (is_array($decodedBody)) {
                $result = $decodedBody;
            }
        } else {
            Log::warning('Could not get player identity from Riot API for League of Legends',
                ['apiKey' => $this->apiKey, 'params' => $params, 'response' => $summonerResponse]);
        }

        return $result;
    }

    public function grabCharacterImage(string $characterName): ?string
    {
        $characterImage = null;

        $characterImageResponse = Http::get('http://ddragon.leagueoflegends.com/cdn/'.$this->gameVersion.'/img/champion/'.$characterName.'.png');

        if ($characterImageResponse->successful()) {
            $characterImage = $characterImageResponse->body();
        } else {
            Log::error('Could not get character image', ['characterName' => $characterName]);
        }

        return $characterImage;
    }

    public function grabCharacters(): array
    {
        $result = [];

        $championsResponse = Http::get('https://ddragon.leagueoflegends.com/cdn/'.$this->gameVersion.'/data/'.$this->languageCode.'/champion.json');
        if ($championsResponse->successful()) {
            $decodedBody = json_decode($championsResponse->body(), true);
            if (is_array($decodedBody)) {
                $result = Arr::pluck($decodedBody['data'], 'id');
            }
        } else {
            Log::error('Could not get champions from Riot\'s Data-Dragon CDN',
                [
                    'gameVersion' => $this->gameVersion,
                    'languageCode' => $this->languageCode,
                    'response' => $championsResponse,
                ]);
        }

        return $result;
    }

    public function grabPositionImage(string $positionName): ?string
    {
        $positionImage = null;
        if (! $this->positionImageFolderPath) {
            if ($archiveFilePath = $this->downloadArchive('https://static.developer.riotgames.com/docs/lol/ranked-positions.zip', 'positions-icons')) {
                $positionImageFolderFilePathPath = getcwd().'/storage/positions-icons';
                if ($this->extractArchive($archiveFilePath, $positionImageFolderFilePathPath)) {
                    $this->positionImageFolderPath = $positionImageFolderFilePathPath;
                    File::delete($archiveFilePath);
                }
            }
        }

        /** @var array $positionMapping */
        $positionMapping = config('static-data.lol.positionMapping');
        $positionName = $positionMapping[$positionName] ?? $positionName;
        $fileName =
            'Position_Challenger-'.$positionName.'.png';

        if (File::exists($this->positionImageFolderPath.'/'.$fileName)) {
            $positionImage = File::get($this->positionImageFolderPath.'/'.$fileName, true);
        } else {
            Log::error('No position image found', ['rankName' => $positionName, 'fileName' => $fileName]);
        }

        return $positionImage;
    }

    protected function downloadArchive(string $url, string $fileName): ?string
    {
        $result = null;

        $rankImagesResponse = Http::get($url);
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
        if (! $this->rankImageFolderPath) {
            if ($archiveFilePath = $this->downloadArchive('https://static.developer.riotgames.com/docs/lol/ranked-emblems.zip', 'rank-icons')) {
                $rankImageFolderPath = getcwd().'/storage/rank-icons';
                if ($this->extractArchive($archiveFilePath, $rankImageFolderPath)) {
                    $this->rankImageFolderPath = $rankImageFolderPath;
                    File::delete($archiveFilePath);
                }
            }
        }

        $fileName =
            'Emblem_'.
            substr($rankName, 0, strpos($rankName, ' ') ?: strlen($rankName)).
            '.png';

        if (File::exists($this->rankImageFolderPath.'/'.$fileName)) {
            $rankImage = File::get($this->rankImageFolderPath.'/'.$fileName, true);
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
     * @param  GameUser  $gameUser
     * @param  array  $stats
     * @param  Collection<int, Assignment>  $assignments
     * @param  Collection<int, Queue>  $queues
     * @return array
     */
    public function mapStats(GameUser $gameUser, array $stats, Collection $assignments, Collection $queues): array
    {
        $ts3ServerGroups = [];
        $matchData = [];

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

        if (isset($stats['matches'])) {
            $matchData = $this->mapMatches($gameUser, $stats['matches'], $assignments);
        }

        return array_merge($ts3ServerGroups, $matchData);
    }

    /**
     * @param  array  $leagues
     * @param  Collection<int, Assignment>  $assignments
     * @param  string  $queueType
     * @return Assignment|null
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

    /**
     * @param  GameUser  $gameUser
     * @param  array  $matches
     * @param  Collection<int, Assignment>  $assignments
     * @return array
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
     * @param  GameUser  $gameUser
     * @param  array  $match
     * @param  Collection<int, Assignment>  $assignments
     * @return Assignment|null
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
     * @param  GameUser  $gameUser
     * @param  array  $match
     * @param  Collection<int, Assignment>  $assignments
     * @return Assignment|null
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
}
