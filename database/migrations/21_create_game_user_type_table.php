<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('game_user_type')) {
            return;
        }

        Schema::create('game_user_type', function (Blueprint $table) {
            $table->unsignedBigInteger('game_user_id');
            $table->unsignedBigInteger('type_id');
            $table->timestamps();

            $table->unique(['game_user_id', 'type_id']);
            $table->foreign('game_user_id')->references('id')->on('game_user')->onDelete('cascade');
            $table->foreign('type_id')->references('id')->on('types')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('game_user_type');
    }
};
