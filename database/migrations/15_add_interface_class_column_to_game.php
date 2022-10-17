<?php

use App\Game;
use App\Interfaces\ApexLegends;
use App\Interfaces\LeagueOfLegends;
use App\Interfaces\TeamfightTactics;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void
    {
        Schema::table('games', function (Blueprint $table) {
            $table->string('interface');
        });

        $apex = Game::where('name', 'apex')->first();
        $apex->interface = ApexLegends::class;
        $apex->saveOrFail();

        $lol = Game::where('name', 'lol')->first();
        $lol->interface = LeagueOfLegends::class;
        $lol->saveOrFail();

        $tft = Game::where('name', 'tft')->first();
        $tft->interface = TeamfightTactics::class;
        $tft->saveOrFail();
    }

    public function down(): void
    {
        Schema::table('games', function (Blueprint $table) {
            $table->dropColumn('interface');
        });
    }
};
