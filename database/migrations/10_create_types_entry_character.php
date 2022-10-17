<?php

use App\Type;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {

    public function up(): void
    {
        if (Type::where('name', 'character')->first()) {
            return;
        }

        $type = new Type();
        $type->name = 'character';
        $type->saveOrFail();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        if (!Type::where('name', 'character')->first()) {
            return;
        }

        Type::where('name', 'character')->delete();
    }
};
