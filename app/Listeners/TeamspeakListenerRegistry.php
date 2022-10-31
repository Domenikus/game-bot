<?php

namespace App\Listeners;

class TeamspeakListenerRegistry
{
    /**
     * @var array<TeamspeakListener>
     */
    protected array $listeners = [];

    public function register(TeamspeakListener $instance): static
    {
        $this->listeners[] = $instance;

        return $this;
    }

    /**
     * @return array<TeamspeakListener>
     */
    public function getAll(): array
    {
        return $this->listeners;
    }
}
