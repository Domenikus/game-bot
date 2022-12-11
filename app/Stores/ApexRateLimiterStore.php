<?php

namespace App\Stores;

use Illuminate\Support\Facades\Cache;
use Spatie\GuzzleRateLimiterMiddleware\Store;

class ApexRateLimiterStore implements Store
{
    public function get(): array
    {
        return (array) Cache::get('apex-rate-limiter', []);
    }

    public function push(int $timestamp, int $limit): void
    {
        Cache::put('apex-rate-limiter', array_merge($this->get(), [$timestamp]));
    }
}
