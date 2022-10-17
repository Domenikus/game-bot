<?php

use App\Type;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {

    public function up(): void
    {
        if (Type::where('name', 'rank_group')->first()) {
            return;
        }

        $type = new Type();
        $type->name = 'rank_group';
        $type->saveOrFail();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        if (!Type::where('name', 'rank_group')->first()) {
            return;
        }

        Type::where('name', 'rank_group')->delete();
    }
};
