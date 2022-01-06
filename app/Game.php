<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;


class Game extends Model
{
    const NAME_APEX = 'apex';

    public function assignments(): HasMany
    {
        return $this->hasMany(Assignment::class);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)->using(GameUser::class)->as('game_user')->withPivot('options')->withTimestamps();
    }
}
