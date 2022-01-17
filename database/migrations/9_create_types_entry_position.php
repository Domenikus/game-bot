<?php

use App\Type;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {

    public function up(): void
    {
        if (Type::where('name', Type::TYPE_POSITION)->first()) {
            return;
        }

        $type = new Type();
        $type->name = Type::TYPE_POSITION;
        $type->saveOrFail();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (!Type::where('name', Type::TYPE_POSITION)->first()) {
            return;
        }

        Type::where('name', Type::TYPE_POSITION)->delete();
    }
};
