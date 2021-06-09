<?php

return [
    "ip" => env('TEAMSPEAK_IP', ""),
    "port" => env('TEAMSPEAK_PORT', 9987),
    "query_user" => env('TEAMSPEAK_QUERY_USER', ""),
    "query_password" => env('TEAMSPEAK_QUERY_PASSWORD', ""),
    "query_port" => env('TEAMSPEAK_QUERY_PORT', 10011),
    "bot_name" => env('TEAMSPEAK_BOT_NAME', "apex-bot"),

    "server_groups_ranked" => [
        "Bronze 1" => 9,
        "Bronze 2" => 10,
        "Bronze 3" => 11,
        "Bronze 4" => 12,

        "Silver 1" => 13,
        "Silver 2" => 14,
        "Silver 3" => 15,
        "Silver 4" => 16,

        "Gold 1" => 17,
        "Gold 2" => 18,
        "Gold 3" => 19,
        "Gold 4" => 20,

        "Platinum 1" => 21,
        "Platinum 2" => 22,
        "Platinum 3" => 23,
        "Platinum 4" => 24,

        "Diamond 1" => 26,
        "Diamond 2" => 27,
        "Diamond 3" => 28,
        "Diamond 4" => 29,

        "Apex Predator" => 25
    ]
];
