<?php

use App\Game;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {

    public function up(): void
    {
        if (Game::where('name', Game::NAME_APEX)->first()) {
            return;
        }

        $game = new Game();
        $game->name = Game::NAME_APEX;
        $game->saveOrFail();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (!Game::where('name', Game::NAME_APEX)->first()) {
            return;
        }

        Game::where('name', Game::NAME_APEX)->delete();
    }
};
