<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $name;
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class Type extends Model
{
    const TYPE_CHARACTER = 'character';

    const TYPE_POSITION = 'position';

    /**
     * @return HasMany<Assignment>
     */
    public function assignments(): HasMany
    {
        return $this->hasMany(Assignment::class);
    }

    /**
     * @return HasMany<Queue>
     */
    public function queues(): HasMany
    {
        return $this->hasMany(Queue::class);
    }
}
