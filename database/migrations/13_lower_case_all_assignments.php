<?php

use App\Assignment;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $assignments = Assignment::all();

        foreach ($assignments as $assignment) {
            $assignment->value = strtolower($assignment->value);
            $assignment->save();
        }
    }

    public function down(): void
    {
    }
};
