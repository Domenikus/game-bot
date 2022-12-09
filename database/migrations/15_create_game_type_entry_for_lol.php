<?php

use App\Game;
use App\Type;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $game = Game::where('name', Game::GAME_NAME_LEAGUE_OF_LEGENDS)->firstOrFail();

        $rankSolo = Type::where('name', Type::NAME_RANK_SOLO)->firstOrFail();
        $game->types()->attach($rankSolo->getKey(), ['label' => 'Solo/Duo']);

        $rankGroup = Type::where('name', Type::NAME_RANK_GROUP)->firstOrFail();
        $game->types()->attach($rankGroup->getKey(), ['label' => 'Flex']);

        $rankDuo = Type::where('name', Type::NAME_RANK_DUO)->firstOrFail();
        $game->types()->attach($rankDuo->getKey(), ['label' => 'Double Up']);

        $position = Type::where('name', Type::NAME_POSITION)->firstOrFail();
        $game->types()->attach($position->getKey(), ['label' => 'Lane']);

        $character = Type::where('name', Type::NAME_CHARACTER)->firstOrFail();
        $game->types()->attach($character->getKey(), ['label' => 'Champion']);
    }

    public function down(): void
    {
        $game = Game::with('types')->where('name', Game::GAME_NAME_LEAGUE_OF_LEGENDS)->firstOrFail();
        $game->types()->sync([]);
    }
};
