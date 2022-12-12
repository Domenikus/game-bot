<?php

namespace App\Providers;

use App\Services\Gateways\ApexLegendsGateway;
use App\Services\Gateways\LeagueOfLegendsGateway;
use App\Services\Gateways\TeamfightTacticsGateway;
use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;

class GameGatewayServiceProvider extends ServiceProvider implements DeferrableProvider
{
    public function boot(): void
    {
    }

    public function register(): void
    {
        $this->registerApex();
        $this->registerRiot();
    }

    protected function registerApex(): void
    {
        $apexApiKey = config('apex-legends.apiKey');
        $apexRateLimit = config('apex-legends.rate_limit');

        if (is_string($apexApiKey) && is_numeric($apexRateLimit)) {
            $this->app->singleton(ApexLegendsGateway::class, function () use ($apexRateLimit, $apexApiKey) {
                return new ApexLegendsGateway($apexApiKey, (int) $apexRateLimit);
            });
        }
    }

    protected function registerRiot(): void
    {
        $lolApiKey = config('league-of-legends.apiKey');
        $regionRouting = config('static-data.lol.regionRouting.'.config('league-of-legends.region'));
        $lolRateLimit = config('league-of-legends.rate_limit');

        if (is_string($lolApiKey) && is_array($regionRouting) && is_numeric($lolRateLimit)) {
            $this->app->singleton(LeagueOfLegendsGateway::class, function () use ($lolRateLimit, $regionRouting, $lolApiKey) {
                return new LeagueOfLegendsGateway($lolApiKey, $regionRouting['plattformBaseUrl'], $regionRouting['regionBaseUrl'], $regionRouting['realmUrl'], (int) $lolRateLimit);
            });
        }

        $tftApiKey = config('teamfight-tactics.apiKey');
        $tftRateLimit = config('teamfight-tactics.rate_limit');

        if (is_string($tftApiKey) && is_array($regionRouting) && is_numeric($tftRateLimit)) {
            $this->app->singleton(TeamfightTacticsGateway::class, function () use ($tftRateLimit, $regionRouting, $tftApiKey) {
                return new TeamfightTacticsGateway($tftApiKey, $regionRouting['plattformBaseUrl'], $regionRouting['regionBaseUrl'], $regionRouting['realmUrl'], (int) $tftRateLimit);
            });
        }
    }

    public function provides(): array
    {
        return [ApexLegendsGateway::class, LeagueOfLegendsGateway::class, TeamfightTacticsGateway::class];
    }
}
