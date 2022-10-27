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
        $lol = Game::where('name', 'lol')->firstOrFail();

        if (! $lol->queues()->where('name', 'RANKED_SOLO_5x5')->first()) {
            $rankSolo = Type::where('name', 'rank_solo')->first();
            if ($rankSolo) {
                $rankedSoloQueue = new Queue();
                $rankedSoloQueue->name = 'RANKED_SOLO_5x5';
                $rankedSoloQueue->type()->associate($rankSolo);
                $lol->queues()->save($rankedSoloQueue);
            }
        }

        if (! $lol->queues()->where('name', 'RANKED_FLEX_SR')->first()) {
            $rankGroup = Type::where('name', 'rank_group')->first();
            if ($rankGroup) {
                $rankedGroupQueue = new Queue();
                $rankedGroupQueue->name = 'RANKED_FLEX_SR';
                $rankedGroupQueue->type()->associate($rankGroup);
                $lol->queues()->save($rankedGroupQueue);
            }
        }

        if (! $lol->queues()->where('name', 'RANKED_TFT_DOUBLE_UP')->first()) {
            $rankDuo = Type::where('name', 'rank_duo')->first();
            if ($rankDuo) {
                $rankedDuoQueue = new Queue();
                $rankedDuoQueue->name = 'RANKED_TFT_DOUBLE_UP';
                $rankedDuoQueue->type()->associate($rankDuo);
                $lol->queues()->save($rankedDuoQueue);
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
        $lol = Game::where('name', 'lol')->firstOrFail();

        if ($solo = $lol->queues()->where('name', 'RANKED_SOLO_5x5')->first()) {
            $solo->delete();
        }

        if ($group = $lol->queues()->where('name', 'RANKED_FLEX_SR')->first()) {
            $group->delete();
        }

        if ($duo = $lol->queues()->where('name', 'RANKED_TFT_DOUBLE_UP')->first()) {
            $duo->delete();
        }
    }
};
