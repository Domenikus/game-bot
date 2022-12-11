<?php

namespace App\Stores;

use Illuminate\Support\Facades\Cache;
use Spatie\GuzzleRateLimiterMiddleware\Store;

class LolRateLimiterStore implements Store
{
    public function get(): array
    {
        return (array) Cache::get('lol-rate-limiter', []);
    }

    public function push(int $timestamp, int $limit): void
    {
        Cache::put('lol-rate-limiter', array_merge($this->get(), [$timestamp]));
    }
}
