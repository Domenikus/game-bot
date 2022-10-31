<?php

namespace App\Providers;

use App\Listeners\ChatListener;
use App\Listeners\EnterViewListener;
use App\Listeners\TeamspeakListenerRegistry;
use App\Listeners\TimeoutListener;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;

class TeamspeakListenerServiceProvider extends ServiceProvider implements DeferrableProvider
{
    /**
     * @throws BindingResolutionException
     */
    public function boot(): void
    {
        $registry = $this->app->make(TeamspeakListenerRegistry::class);
        if ($registry instanceof TeamspeakListenerRegistry) {
            $registry
                ->register(new ChatListener())
                ->register(new EnterViewListener());

            $autoUpdateInterval = config('teamspeak.auto_update_interval');
            if (is_int($autoUpdateInterval)) {
                $registry->register(new TimeoutListener($autoUpdateInterval));
            }
        }
    }

    public function register(): void
    {
        $this->app->singleton(TeamspeakListenerRegistry::class);
    }

    public function provides(): array
    {
        return [TeamspeakListenerRegistry::class];
    }
}
