<?php

use App\Game;
use App\Queue;
use App\Type;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        /** @var Game $apex */
        $apex = Game::withInactive()->where('name', Game::GAME_NAME_APEX_LEGENDS)->first();

        if (! $apex->queues()->where('name', 'rankScore')->first()) {
            $rankGroup = Type::where('name', 'rank_solo')->first();
            if ($rankGroup) {
                $rankedSoloQueue = new Queue();
                $rankedSoloQueue->name = 'rankScore';
                $rankedSoloQueue->type()->associate($rankGroup);
                $apex->queues()->save($rankedSoloQueue);
            }
        }

        if (! $apex->queues()->where('name', 'arenaRankScore')->first()) {
            $rankGroup = Type::where('name', 'rank_group')->first();
            if ($rankGroup) {
                $rankedSoloQueue = new Queue();
                $rankedSoloQueue->name = 'arenaRankScore';
                $rankedSoloQueue->type()->associate($rankGroup);
                $apex->queues()->save($rankedSoloQueue);
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        /** @var Game $apex */
        $apex = Game::withInactive()->where('name', Game::GAME_NAME_APEX_LEGENDS)->first();

        if ($solo = $apex->queues()->where('name', 'rankScore')->first()) {
            $solo->delete();
        }

        if ($solo = $apex->queues()->where('name', 'arenaRankScore')->first()) {
            $solo->delete();
        }
    }
};
