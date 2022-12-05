<?php

namespace App\Providers;

use App\Services\Listeners\ChatListener;
use App\Services\Listeners\EnterViewListener;
use App\Services\Listeners\TeamspeakListenerRegistry;
use App\Services\Listeners\TimeoutListener;
use App\Services\UserServiceInterface;
use Illuminate\Support\ServiceProvider;

class TeamspeakListenerServiceProvider extends ServiceProvider
{
    public function boot(TeamspeakListenerRegistry $registry, UserServiceInterface $service): void
    {
        $chatCommandPrefix = config('teamspeak.chat_command_prefix');
        if (is_string($chatCommandPrefix)) {
            $registry
                ->register(new ChatListener($service, $chatCommandPrefix))
                ->register(new EnterViewListener($service));
        }

        $autoUpdateInterval = config('teamspeak.auto_update_interval');
        if (is_numeric($autoUpdateInterval)) {
            $registry->register(new TimeoutListener($service, (int) $autoUpdateInterval));
        }
    }

    public function register(): void
    {
        $this->app->singleton(TeamspeakListenerRegistry::class);
    }
}
