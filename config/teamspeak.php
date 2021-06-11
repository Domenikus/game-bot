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
        "Bronze 2" => env('TEAMSPEAK_GROUP_RANKED_BRONZE2', 10),
        "Bronze 3" => env('TEAMSPEAK_GROUP_RANKED_BRONZE3', 11),
        "Bronze 4" => env('TEAMSPEAK_GROUP_RANKED_BRONZE4', 12),

        "Silver 1" => env('TEAMSPEAK_GROUP_RANKED_SILVER1', 13),
        "Silver 2" => env('TEAMSPEAK_GROUP_RANKED_SILVER2', 14),
        "Silver 3" => env('TEAMSPEAK_GROUP_RANKED_SILVER3', 15),
        "Silver 4" => env('TEAMSPEAK_GROUP_RANKED_SILVER4', 16),

        "Gold 1" => env('TEAMSPEAK_GROUP_RANKED_GOLD1', 17),
        "Gold 2" => env('TEAMSPEAK_GROUP_RANKED_GOLD2', 18),
        "Gold 3" => env('TEAMSPEAK_GROUP_RANKED_GOLD3', 19),
        "Gold 4" => env('TEAMSPEAK_GROUP_RANKED_GOLD4', 20),

        "Platinum 1" => env('TEAMSPEAK_GROUP_RANKED_PLATINUM1', 21),
        "Platinum 2" => env('TEAMSPEAK_GROUP_RANKED_PLATINUM2', 22),
        "Platinum 3" => env('TEAMSPEAK_GROUP_RANKED_PLATINUM3', 23),
        "Platinum 4" => env('TEAMSPEAK_GROUP_RANKED_PLATINUM4', 24),

        "Diamond 1" => env('TEAMSPEAK_GROUP_RANKED_DIAMOND1', 26),
        "Diamond 2" => env('TEAMSPEAK_GROUP_RANKED_DIAMOND2', 27),
        "Diamond 3" => env('TEAMSPEAK_GROUP_RANKED_DIAMOND3', 28),
        "Diamond 4" => env('TEAMSPEAK_GROUP_RANKED_DIAMOND4', 29),

        "Apex Predator" => env('TEAMSPEAK_GROUP_RANKED_PREDATOR', 25)
    ],

    "server_groups_legends" => [
        "Wraith" => env('TEAMSPEAK_GROUP_LEGENDS_WRAITH', 30),
        "Gibraltar" => env('TEAMSPEAK_GROUP_LEGENDS_GIBRALTAR', 31),
        "Bloodhound" => env('TEAMSPEAK_GROUP_LEGENDS_BLOODHOUND', 32),
        "Wattson" => env('TEAMSPEAK_GROUP_LEGENDS_WATTSON', 33),
        "Loba" => env('TEAMSPEAK_GROUP_LEGENDS_LOBA', 34),
        "Horizon" => env('TEAMSPEAK_GROUP_LEGENDS_HORIZON', 35),
        "Pathfinder" => env('TEAMSPEAK_GROUP_LEGENDS_PATHFINDER', 36),
        "Mirage" => env('TEAMSPEAK_GROUP_LEGENDS_MIRAGE', 37),
        "Octane" => env('TEAMSPEAK_GROUP_LEGENDS_OCTANE', 38),
        "Bangalore" => env('TEAMSPEAK_GROUP_LEGENDS_BANGALORE', 39),
        "Lifeline" => env('TEAMSPEAK_GROUP_LEGENDS_LIFELINE', 40),
        "Rampart" => env('TEAMSPEAK_GROUP_LEGENDS_RAMPART', 41),
        "Valkyrie" => env('TEAMSPEAK_GROUP_LEGENDS_VALKYRIE', 42),
        "Fuse" => env('TEAMSPEAK_GROUP_LEGENDS_FUSE', 43),
        "Caustic" => env('TEAMSPEAK_GROUP_LEGENDS_CAUSTIC', 44),
        "Revenant" => env('TEAMSPEAK_GROUP_LEGENDS_REVENANT', 45),
        "Crypto" => env('TEAMSPEAK_GROUP_LEGENDS_CRYPTO', 46)
    ]
];
