<?php

use Illuminate\Support\Str;

return [
    'ip' => env('TEAMSPEAK_IP', ''),
    'port' => env('TEAMSPEAK_PORT', 9987),
    'query_user' => env('TEAMSPEAK_QUERY_USER', ''),
    'query_password' => env('TEAMSPEAK_QUERY_PASSWORD', ''),
    'query_port' => env('TEAMSPEAK_QUERY_PORT', 10011),
    'bot_name' => 'game-bot_'.Str::random(4),

    'auto_update_interval' => env('AUTO_UPDATE_INTERVAL', 1800),
];
