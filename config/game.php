<?php

return [
    'apex-api-key' => env('APEX_API_KEY'),
    'lol-api-key' => env('LOL_API_KEY'),
    'tft-api-key' => env('TFT_API_KEY'),
    'riot-verification-code' => env('RIOT_VERIFICATION_CODE'),

    'gameInterfaces' => [
        'apex' => \App\Interfaces\ApexLegends::class,
        'lol' => \App\Interfaces\LeagueOfLegends::class,
        'tft' => \App\Interfaces\TeamfightTactics::class
    ]
];
