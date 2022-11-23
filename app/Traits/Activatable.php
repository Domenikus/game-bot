<?php

namespace App\Traits;

use App\Scopes\ActiveScope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * @mixin Model
 *
 * @method static Builder<static> withInactive()
 *
 * @property bool $active
 */
trait Activatable
{
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

    public static function getActiveColumn(): string
    {
        return 'active';
    }

    public function isActive(): bool
    {
        return $this->active;
    }
}
