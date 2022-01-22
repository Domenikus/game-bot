<?php

use App\Type;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {

    public function up(): void
    {
        if (Type::where('name', Type::TYPE_RANK_SOLO)->first()) {
            return;
        }

        $type = Type::where('name', 'rank')->first();
        if (!$type) {
            $type = new Type();
        }

        $type->name = Type::TYPE_RANK_SOLO;
        $type->saveOrFail();
    }
};
