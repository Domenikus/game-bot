<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $name
 * @property string $label
 * @property GameUser $game_user
 * @property GameType $game_type
 * @property Carbon $created_at
 * @property Carbon $updated_at
 *
 * @method static Builder<static> active()
 * @method static Builder<static> inactive()
 */
class Game extends Model
{
    const GAME_NAME_APEX_LEGENDS = 'apex';

    const GAME_NAME_LEAGUE_OF_LEGENDS = 'lol';

    const GAME_NAME_TEAMFIGHT_TACTICS = 'tft';

    protected $casts = [
        'blocked' => 'bool',
    ];

    /**
     * @return HasMany<Assignment>
     */
    public function assignments(): HasMany
    {
        return $this->hasMany(Assignment::class);
    }

    /**
     * @param  Builder<Game>  $query
     * @return Builder<Game>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->has('assignments');
    }

    /**
     * @param  Builder<Game>  $query
     * @return Builder<Game>
     */
    public function scopeInactive(Builder $query): Builder
    {
        return $query->doesntHave('assignments');
    }

    /**
     * @return BelongsToMany<Type>
     */
    public function types(): BelongsToMany
    {
        return $this->belongsToMany(Type::class)->using(GameType::class)->as('game_type')->withPivot('label')->withTimestamps();
    }

    /**
     * @return BelongsToMany<User>
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)->using(GameUser::class)->as('game_user')->withPivot('options', 'refreshed_at')->withTimestamps();
    }
}
