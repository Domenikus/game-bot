<?php

use App\Type;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        if (Type::where('name', 'rank_solo')->first()) {
            return;
        }

        $type = new Type();
        $type->name = 'rank_solo';
        $type->label = 'Rank solo';
        $type->saveOrFail();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if ($type = Type::where('name', 'rank_solo')->first()) {
            $type->delete();
        }
    }
};
