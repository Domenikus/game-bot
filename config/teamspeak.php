<?php

return [
    'ip' => env('TEAMSPEAK_IP', ''),
    'port' => env('TEAMSPEAK_PORT', 9987),
    'query_user' => env('TEAMSPEAK_QUERY_USER', ''),
    'query_password' => env('TEAMSPEAK_QUERY_PASSWORD', ''),
    'query_port' => env('TEAMSPEAK_QUERY_PORT', 10011),
    'bot_name' => env('TEAMSPEAK_BOT_NAME', 'game-bot'),

    'listener' => [
        'globalChat' => env('LISTENER_GLOBAL_CHAT', true),
        'enterView' => env('LISTENER_ENTER_VIEW', true),
        'privateChat' => env('LISTENER_PRIVATE_CHAT', true),
    ],

    'auto_update_interval' => env('AUTO_UPDATE_INTERVAL'),
];
