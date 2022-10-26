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
        $this->app->make(GameGatewayRegistry::class)
            ->register(Game::GAME_NAME_APEX_LEGENDS, new ApexLegendsGateway(config('apex-legends.apiKey')));

        $this->app->make(GameGatewayRegistry::class)
            ->register(Game::GAME_NAME_LEAGUE_OF_LEGENDS,
                new LeagueOfLegendsGateway(config('league-of-legends.apiKey')));

        $this->app->make(GameGatewayRegistry::class)
            ->register(Game::GAME_NAME_TEAMFIGHT_TACTICS,
                new TeamfightTacticsGateway(config('teamfight-tactics.apiKey')));
    }

    public function register(): void
    {
        $this->app->singleton(GameGatewayRegistry::class);
    }
}
