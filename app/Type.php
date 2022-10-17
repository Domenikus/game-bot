<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;


/**
 * @method static where(string $col, string $value)
 * @property string $name
 */
class Type extends Model
{
    const TYPE_RANK_SOLO = 'rank_solo';
    const TYPE_CHARACTER = 'character';
    const TYPE_POSITION = 'position';

    public function assignments(): HasMany
    {
        return $this->hasMany(Assignment::class);
    }

    public function queues(): HasMany
    {
        return $this->hasMany(Queue::class);
    }
}
