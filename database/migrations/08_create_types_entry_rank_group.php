<?php

use App\Type;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        if (Type::where('name', 'rank_group')->first()) {
            return;
        }

        $type = new Type();
        $type->name = 'rank_group';
        $type->label = 'Rank group';
        $type->saveOrFail();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if ($type = Type::where('name', 'rank_group')->first()) {
            $type->delete();
        }
    }
};
