<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @method static find($identityId)
 */
class User extends Model
{
    protected $primaryKey = 'identity_id';
    public $incrementing = false;

    public function games(): BelongsToMany
    {
        return $this->belongsToMany(Game::class)->using(GameUser::class)->as('game_user')->withPivot('options')->withTimestamps();
    }
}
