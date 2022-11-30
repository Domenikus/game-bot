<?php

use App\Game;
use App\Type;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $game = Game::withInactive()->where('name', Game::GAME_NAME_APEX_LEGENDS)->firstOrFail();

        $rankSolo = Type::where('name', Type::NAME_RANK_SOLO)->firstOrFail();
        $game->types()->attach($rankSolo->getKey(), ['label' => 'Solo']);

        $rankGroup = Type::where('name', Type::NAME_RANK_GROUP)->firstOrFail();
        $game->types()->attach($rankGroup->getKey(), ['label' => 'Arena']);

        $character = Type::where('name', Type::NAME_CHARACTER)->firstOrFail();
        $game->types()->attach($character->getKey(), ['label' => 'Legend']);
    }

    public function down(): void
    {
        $game = Game::withInactive()->with('types')->where('name', Game::GAME_NAME_APEX_LEGENDS)->firstOrFail();
        $game->types()->sync([]);
    }
};
