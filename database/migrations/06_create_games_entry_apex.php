<?php

use App\Game;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        if (Game::where('name', 'apex')->first()) {
            return;
        }

        $game = new Game();
        $game->name = 'apex';
        $game->saveOrFail();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        if ($game = Game::where('name', 'apex')->first()) {
            $game->delete();
        }
    }
};
