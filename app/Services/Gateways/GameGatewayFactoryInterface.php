<?php

namespace App\Services\Gateways;

interface GameGatewayFactoryInterface
{
    public function create(string $gameName): GameGateway;
}
