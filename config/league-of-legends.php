<?php

return [
    'apiKey' => env('LOL_API_KEY', ''),
    'region' => env('LOL_REGION', 'euw1'),
    'match_count' => env('LOL_MATCH_COUNT', 20),
    'rate_limit' => env('LOL_RATE_LIMIT', 1),
];
