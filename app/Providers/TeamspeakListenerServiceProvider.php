<?php

namespace App\Providers;

use App\Services\Listeners\ChatListener;
use App\Services\Listeners\EnterViewListener;
use App\Services\Listeners\TeamspeakListenerRegistry;
use App\Services\Listeners\TimeoutListener;
use App\Services\UserServiceInterface;
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
        $service = $this->app->make(UserServiceInterface::class);
        if ($registry instanceof TeamspeakListenerRegistry && $service instanceof UserServiceInterface) {
            $registry
                ->register(new ChatListener($service))
                ->register(new EnterViewListener($service));

            $autoUpdateInterval = config('teamspeak.auto_update_interval');
            if (is_int($autoUpdateInterval)) {
                $registry->register(new TimeoutListener($service, $autoUpdateInterval));
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
