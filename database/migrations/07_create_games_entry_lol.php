<?php

use App\Game;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        if (Game::where('name', Game::GAME_NAME_LEAGUE_OF_LEGENDS)->first()) {
            return;
        }

        $game = new Game();
        $game->name = Game::GAME_NAME_LEAGUE_OF_LEGENDS;
        $game->label = 'League of Legends';
        $game->saveOrFail();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if ($game = Game::where('name', Game::GAME_NAME_LEAGUE_OF_LEGENDS)->first()) {
            $game->delete();
        }
    }
};
