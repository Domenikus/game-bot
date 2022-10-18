<?php

use App\Type;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {

    public function up(): void
    {
        if (Type::where('name', 'position')->first()) {
            return;
        }

        $type = new Type();
        $type->name = 'position';
        $type->saveOrFail();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        if ($type = Type::where('name', 'position')->first()) {
            $type->delete();
        }
    }
};
