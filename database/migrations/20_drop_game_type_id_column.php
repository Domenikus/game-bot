<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('game_type', function (Blueprint $table) {
            $table->dropColumn('id');
        });
    }

    public function down(): void
    {
        Schema::table('game_type', function (Blueprint $table) {
            $table->id();
        });
    }
};
