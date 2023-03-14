<?php

use App\Game;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        if (Game::where('name', Game::GAME_NAME_APEX_LEGENDS)->first()) {
            return;
        }

        $game = new Game();
        $game->name = Game::GAME_NAME_APEX_LEGENDS;
        $game->label = 'Apex Legends';
        $game->saveOrFail();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if ($game = Game::where('name', Game::GAME_NAME_APEX_LEGENDS)->first()) {
            $game->delete();
        }
    }
};
