<?php

use App\Game;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {

    public function up(): void
    {
        if (Game::where('name', 'tft')->first()) {
            return;
        }

        $game = new Game();
        $game->name = 'tft';
        $game->saveOrFail();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        if ($game = Game::where('name', 'tft')->first()) {
            $game->delete();
        }
    }
};
