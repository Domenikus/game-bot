<?php

use App\Game;
use App\User;
use Illuminate\Database\Seeder;


class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $apex = Game::where('name', Game::NAME_APEX)->get();

        $L0raaaaa = new User();
        $L0raaaaa->identity_id = 'brFyS2Lmm3wsiA0S4utfpw0tShs=';
        $L0raaaaa->save();


        $L0raaaaa->games()->attach($apex->getKey(), [['options' =>
        [
            'platform' => 'origin',
            'name' => 'L0raaaaa'
        ]]]);


        $r3v0l0 = new User();
        $r3v0l0->identity_id = 'wuzoMeaY9dcOURdjSU1+Jejfu8U=';
        $r3v0l0->save();


        $r3v0l0->games()->attach($apex->getKey(), [['options' =>
            [
                'platform' => 'origin',
                'name' => 'r3v0l0'
            ]]]);
    }
}
