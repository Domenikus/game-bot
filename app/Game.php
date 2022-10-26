<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;


/**
 * @method static where(string $col, string $value)
 * @property string $name
 */
class Game extends Model
{
    const GAME_NAME_APEX_LEGENDS = 'apex';
    const GAME_NAME_LEAGUE_OF_LEGENDS = 'lol';
    const GAME_NAME_TEAMFIGHT_TACTICS = 'tft';


    public function assignments(): HasMany
    {
        return $this->hasMany(Assignment::class);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)->using(GameUser::class)->as('game_user')->withPivot('options')->withTimestamps();
    }

    public function queues(): HasMany
    {
        return $this->hasMany(Queue::class);
    }
}
