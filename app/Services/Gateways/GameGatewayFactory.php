<?php

namespace App\Services\Gateways;

use App\Exceptions\InvalidGatewayException;
use App\Game;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;

class GameGatewayFactory implements GameGatewayFactoryInterface
{
    /**
     * @throws InvalidGatewayException
     */
    public function create(string $gameName): GameGateway
    {
        $gameGateway = null;

        switch ($gameName) {
            case Game::GAME_NAME_APEX_LEGENDS:
                $gameGateway = App::make(ApexLegendsGateway::class);
                break;
            case Game::GAME_NAME_LEAGUE_OF_LEGENDS:
                $gameGateway = App::make(LeagueOfLegendsGateway::class);
                break;
            case Game::GAME_NAME_TEAMFIGHT_TACTICS:
                $gameGateway = App::make(TeamfightTacticsGateway::class);
        }

        if (! $gameGateway instanceof GameGateway) {
            Log::error('Could not create game gateway', ['game' => $gameName]);
            throw new InvalidGatewayException;
        }

        return $gameGateway;
    }
}
