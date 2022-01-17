<?php

use App\Game;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {

    public function up(): void
    {
        if (Game::where('name', Game::NAME_LEAGUE_OF_LEGENDS)->first()) {
            return;
        }

        $game = new Game();
        $game->name = Game::NAME_LEAGUE_OF_LEGENDS;
        $game->saveOrFail();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (!Game::where('name', Game::NAME_LEAGUE_OF_LEGENDS)->first()) {
            return;
        }

        Game::where('name', Game::NAME_LEAGUE_OF_LEGENDS)->delete();
    }
};
