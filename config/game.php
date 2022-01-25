<?php

use App\Interfaces\ApexLegends;
use App\Interfaces\LeagueOfLegends;
use App\Interfaces\TeamfightTactics;

return [
    'apex-api-key' => env('APEX_API_KEY'),
    'lol-api-key' => env('LOL_API_KEY'),
    'tft-api-key' => env('TFT_API_KEY'),
    'riot-verification-code' => env('RIOT_VERIFICATION_CODE'),

    'gameInterfaces' => [
        'apex' => ApexLegends::class,
        'lol' => LeagueOfLegends::class,
        'tft' => TeamfightTactics::class
    ]
];
