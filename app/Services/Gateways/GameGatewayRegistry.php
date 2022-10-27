<?php

namespace App\Services\Gateways;

use App\Exceptions\InvalidGatewayException;

class GameGatewayRegistry
{
    protected array $gateways = [];

    public function register(string $name, GameGateway $instance): static
    {
        $this->gateways[$name] = $instance;

        return $this;
    }

    /**
     * @throws InvalidGatewayException
     */
    public function get(string $name): GameGateway
    {
        if (isset($this->gateways[$name])) {
            return $this->gateways[$name];
        } else {
            throw new InvalidGatewayException($name);
        }
    }
}
