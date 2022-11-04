<?php

namespace App\Traits;

use App\Scopes\ActiveScope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder as QueryBuilder;

/**
 * @mixin Model
 *
 * @property bool $active
 *
 * @method static Builder|QueryBuilder withInactive()
 */
trait Activatable
{
    public static function getActiveColumn(): string
    {
        return 'active';
    }

    protected static function bootActivatable(): void
    {
        static::addGlobalScope(new ActiveScope);
    }

    public function initializeActivatable(): void
    {
        if (! isset($this->casts[self::getActiveColumn()])) {
            $this->casts[self::getActiveColumn()] = 'boolean';
        }

        if (! isset($this->attributes[self::getActiveColumn()])) {
            $this->attributes[self::getActiveColumn()] = false;
        }
    }

    public function isActive(): bool
    {
        return $this->active;
    }
}
