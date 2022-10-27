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
        $apex = Game::where('name', 'apex')->firstOrFail();

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
        $apex = Game::where('name', 'apex')->firstOrFail();

        if ($solo = $apex->queues()->where('name', 'rankScore')->first()) {
            $solo->delete();
        }

        if ($solo = $apex->queues()->where('name', 'arenaRankScore')->first()) {
            $solo->delete();
        }
    }
};
