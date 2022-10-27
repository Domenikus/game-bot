<?php

use App\Game;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        if (Game::where('name', 'lol')->first()) {
            return;
        }

        $game = new Game();
        $game->name = 'lol';
        $game->saveOrFail();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        if ($game = Game::where('name', 'lol')->first()) {
            $game->delete();
        }
    }
};
