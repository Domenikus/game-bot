<?php

use App\Type;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        if (Type::where('name', 'character')->first()) {
            return;
        }

        $type = new Type;
        $type->name = 'character';
        $type->label = 'Character';
        $type->saveOrFail();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if ($type = Type::where('name', 'character')->first()) {
            $type->delete();
        }
    }
};
