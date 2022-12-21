<?php

use App\User;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $users = User::with('games')->get();

        foreach ($users as $user) {
            foreach ($user->games as $game) {
                $game->game_user->types()->sync($game->types()->pluck('id'));
            }
        }
    }

    public function down(): void
    {
        $users = User::with('games')->get();

        foreach ($users as $user) {
            foreach ($user->games as $game) {
                $game->game_user->types()->sync([]);
            }
        }
    }
};
