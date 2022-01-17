<?php

use App\Type;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {

    public function up(): void
    {
        if (Type::where('name', Type::TYPE_RANK_GROUP)->first()) {
            return;
        }

        $type = new Type();
        $type->name = Type::TYPE_RANK_GROUP;
        $type->saveOrFail();
    }
};
