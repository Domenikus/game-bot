<?php

use App\Game;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        if (Game::where('name', Game::GAME_NAME_TEAMFIGHT_TACTICS)->first()) {
            return;
        }

        $game = new Game();
        $game->name = Game::GAME_NAME_TEAMFIGHT_TACTICS;
        $game->label = 'Teamfight Tactics';
        $game->saveOrFail();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if ($game = Game::where('name', Game::GAME_NAME_TEAMFIGHT_TACTICS)->first()) {
            $game->delete();
        }
    }
};
