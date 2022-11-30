<?php

use App\Type;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        if (Type::where('name', 'rank_duo')->first()) {
            return;
        }

        $type = new Type();
        $type->name = 'rank_duo';
        $type->label = 'Rank duo';
        $type->saveOrFail();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        if ($pair = Type::where('name', 'rank_duo')->first()) {
            $pair->delete();
        }
    }
};
