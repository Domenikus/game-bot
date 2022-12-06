<?php

namespace App\Providers;

use App\Services\GameService;
use App\Services\GameServiceInterface;
use App\Services\UserService;
use App\Services\UserServiceInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public array $bindings = [
        GameServiceInterface::class => GameService::class,
        UserServiceInterface::class => UserService::class,
    ];

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot(): void
    {
        Model::preventLazyLoading(! $this->app->isProduction());
        Model::preventSilentlyDiscardingAttributes(! $this->app->isProduction());
        Model::preventAccessingMissingAttributes(! $this->app->isProduction());

        config([
            'logging.channels.daily.path' => \Phar::running()
                ? dirname(\Phar::running(false)).'/logs/game-bot.log'
                : storage_path('logs/game-bot.log'),
        ]);

        config([
            'view.compiled' => \Phar::running()
                ? dirname(\Phar::running(false)).'/views'
                : realpath(storage_path('framework/views')),
        ]);
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register(): void
    {
        //
    }
}
