<?php

namespace App\Providers;

use App\Game;
use App\Services\Gateways\ApexLegendsGateway;
use App\Services\Gateways\GameGatewayRegistry;
use App\Services\Gateways\LeagueOfLegendsGateway;
use App\Services\Gateways\TeamfightTacticsGateway;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\ServiceProvider;

class GameServiceProvider extends ServiceProvider
{
    /**
     * @throws BindingResolutionException
     */
    public function boot(): void
    {
        $registry = $this->app->make(GameGatewayRegistry::class);
        if ($registry instanceof GameGatewayRegistry) {
            $apexApiKey = config('apex-legends.apiKey');
            if (is_string($apexApiKey)) {
                $registry->register(Game::GAME_NAME_APEX_LEGENDS, new ApexLegendsGateway($apexApiKey));
            }

            $lolApiKey = config('league-of-legends.apiKey');
            $lolGameVersion = config('league-of-legends.gameVersion');
            $lolLanguageCode = config('league-of-legends.languageCode');
            if (is_string($lolApiKey) && is_string($lolGameVersion) && is_string($lolLanguageCode)) {
                $registry->register(Game::GAME_NAME_LEAGUE_OF_LEGENDS,
                    new LeagueOfLegendsGateway($lolApiKey, $lolGameVersion, $lolLanguageCode));
            }

            $tftApiKey = config('teamfight-tactics.apiKey');
            if (is_string($tftApiKey)) {
                $registry->register(Game::GAME_NAME_TEAMFIGHT_TACTICS,
                    new TeamfightTacticsGateway($tftApiKey));
            }
        }
    }

    public function register(): void
    {
        $this->app->singleton(GameGatewayRegistry::class);
    }
}
