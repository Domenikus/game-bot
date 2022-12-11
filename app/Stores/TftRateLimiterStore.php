<?php

namespace App\Stores;

use Illuminate\Support\Facades\Cache;
use Spatie\GuzzleRateLimiterMiddleware\Store;

class TftRateLimiterStore implements Store
{
    public function get(): array
    {
        return (array) Cache::get('tft-rate-limiter', []);
    }

    public function push(int $timestamp, int $limit): void
    {
        Cache::put('tft-rate-limiter', array_merge($this->get(), [$timestamp]));
    }
}
