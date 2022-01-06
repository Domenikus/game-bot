<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;


class Type extends Model
{
    const NAME_RANK = 'rank';
    const NAME_CHARACTER = 'character';

    public function assignments(): HasMany
    {
        return $this->hasMany(Assignment::class);
    }
}
