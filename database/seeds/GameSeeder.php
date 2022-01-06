<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;


class GameSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('games')->insert([
            [
                'name' => 'apex',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);
    }
}
