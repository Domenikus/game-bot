<?php

use App\Game;
use App\Type;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $game = Game::where('name', Game::GAME_NAME_TEAMFIGHT_TACTICS)->firstOrFail();

        $rankSolo = Type::where('name', Type::NAME_RANK_SOLO)->firstOrFail();
        $game->types()->attach($rankSolo->getKey(), ['label' => 'Solo']);
    }

    public function down(): void
    {
        $game = Game::with('types')->where('name', Game::GAME_NAME_TEAMFIGHT_TACTICS)->firstOrFail();
        $game->types()->sync([]);
    }
};
