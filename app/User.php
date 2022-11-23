<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * @property string $identity_id
 * @property bool $blocked
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class User extends Model
{
    public $incrementing = false;

    /**
     * @var string
     */
    protected $primaryKey = 'identity_id';

    /**
     * @var string
     */
    protected $keyType = 'string';

    protected $casts = [
        'blocked' => 'bool',
    ];

    /**
     * @return BelongsToMany<Game>
     */
    public function games(): BelongsToMany
    {
        return $this->belongsToMany(Game::class)->using(GameUser::class)->as('game_user')->withPivot('options')->withTimestamps();
    }

    public function isAdmin(): bool
    {
        $admins = config('game-bot.admins', '');
        if (is_string($admins)) {
            return strrpos($admins, $this->identity_id) !== false;
        }

        return false;
    }

    public function isBlocked(): bool
    {
        return $this->blocked;
    }
}
