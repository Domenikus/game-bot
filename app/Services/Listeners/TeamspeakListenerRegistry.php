<?php

namespace App\Services\Listeners;

use Illuminate\Support\Facades\Log;

class TeamspeakListenerRegistry
{
    /**
     * @var array<TeamspeakListener>
     */
    protected array $listeners = [];

    /**
     * @return array<TeamspeakListener>
     */
    public function getAll(): array
    {
        return $this->listeners;
    }

    public function register(TeamspeakListener $instance): static
    {
        $this->listeners[] = $instance;
        LOG::debug('Listener registered in teamspeak listener registry', ['listener' => $instance]);

        return $this;
    }
}
