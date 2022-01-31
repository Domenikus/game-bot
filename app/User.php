<?php

namespace App;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * @method static find($identityId)
 * @property Collection $games
 * @property string $identity_id
 * @property mixed $blocked
 */
class User extends Model
{
    use HasFactory;

    protected $primaryKey = 'identity_id';
    public $incrementing = false;


    public function isAdmin(): bool
    {
        return strrpos(config('game-bot.admins'), $this->identity_id) !== false;
    }

    public function isBlocked(): bool
    {
        return (boolean) $this->blocked;
    }

    public function games(): BelongsToMany
    {
        return $this->belongsToMany(Game::class)->using(GameUser::class)->as('game_user')->withPivot('options')->withTimestamps();
    }
}
