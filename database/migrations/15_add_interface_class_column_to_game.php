<?php

use App\Game;
use App\Services\Gateways\ApexLegendsGateway;
use App\Services\Gateways\LeagueOfLegendsGateway;
use App\Services\Gateways\TeamfightTacticsGateway;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('games', function (Blueprint $table) {
            $table->string('interface');
        });

        $apex = Game::where('name', 'apex')->firstOrFail();
        $apex->interface = ApexLegendsGateway::class;
        $apex->saveOrFail();

        $lol = Game::where('name', 'lol')->firstOrFail();
        $lol->interface = LeagueOfLegendsGateway::class;
        $lol->saveOrFail();

        $tft = Game::where('name', 'tft')->firstOrFail();
        $tft->interface = TeamfightTacticsGateway::class;
        $tft->saveOrFail();
    }

    public function down(): void
    {
        Schema::table('games', function (Blueprint $table) {
            $table->dropColumn('interface');
        });
    }
};
