<?php

return [
    "ip" => env('TEAMSPEAK_IP', ""),
    "port" => env('TEAMSPEAK_PORT', 9987),
    "query_user" => env('TEAMSPEAK_QUERY_USER', ""),
    "query_password" => env('TEAMSPEAK_QUERY_PASSWORD', ""),
    "query_port" => env('TEAMSPEAK_QUERY_PORT', 10011),
    "bot_name" => env('TEAMSPEAK_BOT_NAME', "apex-bot"),

    "server_groups_ranked" => [
        "Bronze 1" => env('TEAMSPEAK_GROUP_RANKED_BRONZE1', 9),
        "Bronze 2" => env('TEAMSPEAK_GROUP_RANKED_BRONZE1', 10),
        "Bronze 3" => env('TEAMSPEAK_GROUP_RANKED_BRONZE1', 11),
        "Bronze 4" => env('TEAMSPEAK_GROUP_RANKED_BRONZE1', 12),

        "Silver 1" => env('TEAMSPEAK_GROUP_RANKED_BRONZE1', 13),
        "Silver 2" => env('TEAMSPEAK_GROUP_RANKED_BRONZE1', 14),
        "Silver 3" => env('TEAMSPEAK_GROUP_RANKED_BRONZE1', 15),
        "Silver 4" => env('TEAMSPEAK_GROUP_RANKED_BRONZE1', 16),

        "Gold 1" => env('TEAMSPEAK_GROUP_RANKED_BRONZE1', 17),
        "Gold 2" => env('TEAMSPEAK_GROUP_RANKED_BRONZE1', 18),
        "Gold 3" => env('TEAMSPEAK_GROUP_RANKED_BRONZE1', 19),
        "Gold 4" => env('TEAMSPEAK_GROUP_RANKED_BRONZE1', 20),

        "Platinum 1" => env('TEAMSPEAK_GROUP_RANKED_BRONZE1', 21),
        "Platinum 2" => env('TEAMSPEAK_GROUP_RANKED_BRONZE1', 22),
        "Platinum 3" => env('TEAMSPEAK_GROUP_RANKED_BRONZE1', 23),
        "Platinum 4" => env('TEAMSPEAK_GROUP_RANKED_BRONZE1', 24),

        "Diamond 1" => env('TEAMSPEAK_GROUP_RANKED_BRONZE1', 26),
        "Diamond 2" => env('TEAMSPEAK_GROUP_RANKED_BRONZE1', 27),
        "Diamond 3" => env('TEAMSPEAK_GROUP_RANKED_BRONZE1', 28),
        "Diamond 4" => env('TEAMSPEAK_GROUP_RANKED_BRONZE1', 29),

        "Apex Predator" => env('TEAMSPEAK_GROUP_RANKED_BRONZE1', 25)
    ]
];
