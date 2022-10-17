<?php

use App\Game;
use App\Queue;
use App\Type;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        $tft = Game::where('name', 'tft')->first();

        if (!$tft->queues()->where('name', 'RANKED_TFT')->first()) {
            $rankSolo = Type::where('name', 'rank_solo')->first();
            if ($rankSolo) {
                $rankedSoloQueue = new Queue();
                $rankedSoloQueue->name = 'RANKED_TFT';
                $rankedSoloQueue->type()->associate($rankSolo);
                $tft->queues()->save($rankedSoloQueue);
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
        $tft = Game::where('name', 'tft')->first();

        if ($solo = $tft->queues()->where('name', 'RANKED_TFT')->first()) {
            $solo->delete();
        }
    }
};
