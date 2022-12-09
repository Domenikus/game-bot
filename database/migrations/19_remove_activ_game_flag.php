<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('games', function (Blueprint $table) {
            $table->dropColumn('active');
        });
    }

    public function down(): void
    {
        Schema::table('games', function (Blueprint $table) {
            $table->boolean('active')->default(false)->after('label');
        });
    }
};
