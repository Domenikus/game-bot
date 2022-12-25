<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('game_user', function (Blueprint $table) {
            $table->timestamp('refreshed_at')->after('options')->nullable()
                ->comment('Last time game data was fetched from api');
        });
    }

    public function down(): void
    {
        Schema::table('game_user', function (Blueprint $table) {
            $table->dropColumn('refreshed_at');
        });
    }
};
