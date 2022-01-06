<?php

use App\Game;
use App\Type;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;


class AssignmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $typeRank = Type::where('name', Type::NAME_RANK);
        $typeChar = Type::where('name', Type::NAME_CHARACTER);

        $gameApex = Game::where('name', Game::NAME_APEX);

        DB::table('assignments')->insert([
            [
                'value' => 'Bronze 1',
                'type_id' => $typeRank->getKey(),
                'game_id' => $gameApex->getKey(),
                'ts3_server_group_id' => '22',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'value' => 'Bronze 2',
                'type_id' => $typeRank->getKey(),
                'game_id' => $gameApex->getKey(),
                'ts3_server_group_id' => '23',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'value' => 'Bronze 3',
                'type_id' => $typeRank->getKey(),
                'game_id' => $gameApex->getKey(),
                'ts3_server_group_id' => '24',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'value' => 'Bronze 4',
                'type_id' => $typeRank->getKey(),
                'game_id' => $gameApex->getKey(),
                'ts3_server_group_id' => '25',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'value' => 'Silver 1',
                'type_id' => $typeRank->getKey(),
                'game_id' => $gameApex->getKey(),
                'ts3_server_group_id' => '26',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'value' => 'Silver 2',
                'type_id' => $typeRank->getKey(),
                'game_id' => $gameApex->getKey(),
                'ts3_server_group_id' => '27',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'value' => 'Silver 3',
                'type_id' => $typeRank->getKey(),
                'game_id' => $gameApex->getKey(),
                'ts3_server_group_id' => '28',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'value' => 'Silver 4',
                'type_id' => $typeRank->getKey(),
                'game_id' => $gameApex->getKey(),
                'ts3_server_group_id' => '29',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'value' => 'Gold 1',
                'type_id' => $typeRank->getKey(),
                'game_id' => $gameApex->getKey(),
                'ts3_server_group_id' => '30',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'value' => 'Gold 2',
                'type_id' => $typeRank->getKey(),
                'game_id' => $gameApex->getKey(),
                'ts3_server_group_id' => '31',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'value' => 'Gold 3',
                'type_id' => $typeRank->getKey(),
                'game_id' => $gameApex->getKey(),
                'ts3_server_group_id' => '32',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'value' => 'Gold 4',
                'type_id' => $typeRank->getKey(),
                'game_id' => $gameApex->getKey(),
                'ts3_server_group_id' => '33',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'value' => 'Platinum 1',
                'type_id' => $typeRank->getKey(),
                'game_id' => $gameApex->getKey(),
                'ts3_server_group_id' => '34',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'value' => 'Platinum 2',
                'type_id' => $typeRank->getKey(),
                'game_id' => $gameApex->getKey(),
                'ts3_server_group_id' => '35',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'value' => 'Platinum 3',
                'type_id' => $typeRank->getKey(),
                'game_id' => $gameApex->getKey(),
                'ts3_server_group_id' => '36',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'value' => 'Platinum 4',
                'type_id' => $typeRank->getKey(),
                'game_id' => $gameApex->getKey(),
                'ts3_server_group_id' => '37',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'value' => 'Diamond 1',
                'type_id' => $typeRank->getKey(),
                'game_id' => $gameApex->getKey(),
                'ts3_server_group_id' => '38',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'value' => 'Diamond 2',
                'type_id' => $typeRank->getKey(),
                'game_id' => $gameApex->getKey(),
                'ts3_server_group_id' => '39',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'value' => 'Diamond 3',
                'type_id' => $typeRank->getKey(),
                'game_id' => $gameApex->getKey(),
                'ts3_server_group_id' => '40',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'value' => 'Diamond 4',
                'type_id' => $typeRank->getKey(),
                'game_id' => $gameApex->getKey(),
                'ts3_server_group_id' => '41',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'value' => 'Apex Predator',
                'type_id' => $typeRank->getKey(),
                'game_id' => $gameApex->getKey(),
                'ts3_server_group_id' => '42',
                'created_at' => now(),
                'updated_at' => now(),
            ],


            [
                'value' => 'Wraith',
                'type_id' => $typeChar->getKey(),
                'game_id' => $gameApex->getKey(),
                'ts3_server_group_id' => '44',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'value' => 'Gibraltar',
                'type_id' => $typeChar->getKey(),
                'game_id' => $gameApex->getKey(),
                'ts3_server_group_id' => '45',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'value' => 'Bloodhound',
                'type_id' => $typeChar->getKey(),
                'game_id' => $gameApex->getKey(),
                'ts3_server_group_id' => '46',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'value' => 'Wattson',
                'type_id' => $typeChar->getKey(),
                'game_id' => $gameApex->getKey(),
                'ts3_server_group_id' => '47',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'value' => 'Loba',
                'type_id' => $typeChar->getKey(),
                'game_id' => $gameApex->getKey(),
                'ts3_server_group_id' => '48',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'value' => 'Horizon',
                'type_id' => $typeChar->getKey(),
                'game_id' => $gameApex->getKey(),
                'ts3_server_group_id' => '49',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'value' => 'Pathfinder',
                'type_id' => $typeChar->getKey(),
                'game_id' => $gameApex->getKey(),
                'ts3_server_group_id' => '50',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'value' => 'Mirage',
                'type_id' => $typeChar->getKey(),
                'game_id' => $gameApex->getKey(),
                'ts3_server_group_id' => '51',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'value' => 'Octane',
                'type_id' => $typeChar->getKey(),
                'game_id' => $gameApex->getKey(),
                'ts3_server_group_id' => '52',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'value' => 'Bangalore',
                'type_id' => $typeChar->getKey(),
                'game_id' => $gameApex->getKey(),
                'ts3_server_group_id' => '53',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'value' => 'Lifeline',
                'type_id' => $typeChar->getKey(),
                'game_id' => $gameApex->getKey(),
                'ts3_server_group_id' => '54',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'value' => 'Rampart',
                'type_id' => $typeChar->getKey(),
                'game_id' => $gameApex->getKey(),
                'ts3_server_group_id' => '55',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'value' => 'Valkyrie',
                'type_id' => $typeChar->getKey(),
                'game_id' => $gameApex->getKey(),
                'ts3_server_group_id' => '56',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'value' => 'Fuse',
                'type_id' => $typeChar->getKey(),
                'game_id' => $gameApex->getKey(),
                'ts3_server_group_id' => '57',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'value' => 'Caustic',
                'type_id' => $typeChar->getKey(),
                'game_id' => $gameApex->getKey(),
                'ts3_server_group_id' => '58',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'value' => 'Revenant',
                'type_id' => $typeChar->getKey(),
                'game_id' => $gameApex->getKey(),
                'ts3_server_group_id' => '59',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'value' => 'Crypto',
                'type_id' => $typeChar->getKey(),
                'game_id' => $gameApex->getKey(),
                'ts3_server_group_id' => '60',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'value' => 'Seer',
                'type_id' => $typeChar->getKey(),
                'game_id' => $gameApex->getKey(),
                'ts3_server_group_id' => '63',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}