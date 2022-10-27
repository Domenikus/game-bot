<?php

use App\Type;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        if ($type = Type::where('name', 'rank_pair')->first()) {
            $type->name = 'rank_duo';
            $type->saveOrFail();
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        if ($type = Type::where('name', 'rank_duo')->first()) {
            $type->name = 'rank_pair';
            $type->saveOrFail();
        }
    }
};
