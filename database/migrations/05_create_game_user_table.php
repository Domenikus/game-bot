<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('game_user')) {
            return;
        }

        Schema::create('game_user', function (Blueprint $table) {
            $table->id();
            $table->string('user_identity_id');
            $table->unsignedBigInteger('game_id');
            $table->json('options')->nullable();
            $table->timestamps();

            $table->unique(['user_identity_id', 'game_id']);
            $table->foreign('user_identity_id')->references('identity_id')->on('users')->onDelete('cascade');
            $table->foreign('game_id')->references('id')->on('games')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('game_user');
    }
};
