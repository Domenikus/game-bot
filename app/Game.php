<?php

namespace App;

use App\Traits\Activatable;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $name
 * @property string $label
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property GameUser $game_user
 */
class Game extends Model
{
    use Activatable;

    const GAME_NAME_APEX_LEGENDS = 'apex';

    const GAME_NAME_LEAGUE_OF_LEGENDS = 'lol';

    const GAME_NAME_TEAMFIGHT_TACTICS = 'tft';

    /**
     * @return HasMany<Assignment>
     */
    public function assignments(): HasMany
    {
        return $this->hasMany(Assignment::class);
    }

    /**
     * @return BelongsToMany<User>
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)->using(GameUser::class)->as('game_user')->withPivot('options')->withTimestamps();
    }

    /**
     * @return HasMany<Queue>
     */
    public function queues(): HasMany
    {
        return $this->hasMany(Queue::class);
    }
}
