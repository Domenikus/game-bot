<?php

use App\Type;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    public function up(): void
    {
        if (Type::where('name', 'rank_pair')->first()) {
            return;
        }

        $type = new Type();
        $type->name = 'rank_pair';
        $type->saveOrFail();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        if ($pair = Type::where('name', 'rank_pair')->first()) {
            $pair->delete();
        }
    }
};
