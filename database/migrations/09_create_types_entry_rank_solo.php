<?php

use App\Type;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {

    public function up(): void
    {
        if (Type::where('name', 'rank_solo')->first()) {
            return;
        }

        $type = new Type();
        $type->name = 'rank_solo';
        $type->saveOrFail();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        if (!Type::where('name', 'rank_solo')->first()) {
            return;
        }

        Type::where('name', 'rank_solo')->delete();
    }
};
