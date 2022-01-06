<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('assignments', function (Blueprint $table) {
            $table->id();
            $table->string('value');
            $table->unsignedBigInteger('type_id');
            $table->unsignedBigInteger('game_id');
            $table->string('ts3_server_group_id');
            $table->timestamps();

            $table->unique(array('value', 'ts3_server_group_id', 'game_id', 'type_id'));
            $table->foreign('type_id')->references('id')->on('types')->onDelete('cascade');
            $table->foreign('game_id')->references('id')->on('games')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('assignments');
    }
};