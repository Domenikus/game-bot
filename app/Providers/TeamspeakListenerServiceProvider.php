<?php

namespace App\Providers;

use App\Services\Listeners\ChatListener;
use App\Services\Listeners\EnterViewListener;
use App\Services\Listeners\TimeoutListener;
use App\Services\UserServiceInterface;
use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;

class TeamspeakListenerServiceProvider extends ServiceProvider implements DeferrableProvider
{
    const TAG_NAME = 'teamspeak-listener';

    public function boot(): void
    {
    }

    public function register(): void
    {
        $service = $this->app->make(UserServiceInterface::class);
        if (! $service instanceof UserServiceInterface) {
            return;
        }

        $chatCommandPrefix = config('teamspeak.chat_command_prefix');
        if (is_string($chatCommandPrefix)) {
            $this->app->bind(ChatListener::class, function () use ($chatCommandPrefix, $service) {
                return new ChatListener($service, $chatCommandPrefix);
            });
        }

        $autoUpdateInterval = config('teamspeak.auto_update_interval');
        if (is_numeric($autoUpdateInterval)) {
            $this->app->bind(TimeoutListener::class, function () use ($autoUpdateInterval, $service) {
                return new TimeoutListener($service, (int) $autoUpdateInterval);
            });
        }

        $this->app->bind(EnterViewListener::class, function () use ($service) {
            return new EnterViewListener($service);
        });

        $this->app->tag([ChatListener::class, TimeoutListener::class, EnterViewListener::class], self::TAG_NAME);
    }

    public function provides(): array
    {
        return [ChatListener::class, TimeoutListener::class, EnterViewListener::class];
    }
}
