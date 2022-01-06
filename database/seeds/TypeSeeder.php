<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;


class TypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('types')->insert([
            [
                'name' => 'rank',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'character',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);
    }
}
