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
            $regionRouting = config('static-data.lol.regionRouting.'.config('league-of-legends.region'));
            if (is_string($lolApiKey) && is_array($regionRouting)) {
                $registry->register(Game::GAME_NAME_LEAGUE_OF_LEGENDS,
                    new LeagueOfLegendsGateway($lolApiKey, $regionRouting['plattformBaseUrl'], $regionRouting['regionBaseUrl'], $regionRouting['realmUrl']));
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
