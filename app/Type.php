<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $name;
 * @property string $label;
 * @property GameType $game_type
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class Type extends Model
{
    const NAME_CHARACTER = 'character';

    const NAME_POSITION = 'position';

    const NAME_RANK_DUO = 'rank_duo';

    const NAME_RANK_GROUP = 'rank_group';

    const NAME_RANK_SOLO = 'rank_solo';

    /**
     * @return HasMany<Assignment>
     */
    public function assignments(): HasMany
    {
        return $this->hasMany(Assignment::class);
    }

    /**
     * @return BelongsToMany<Game>
     */
    public function games(): BelongsToMany
    {
        return $this->belongsToMany(Game::class)->using(GameType::class)->as('game_type')->withPivot('label')->withTimestamps();
    }

    /**
     * @return BelongsToMany<GameUser>
     */
    public function gameUsers(): BelongsToMany
    {
        return $this->belongsToMany(GameUser::class)->using(GameUserType::class)->as('game_type_type')->withTimestamps();
    }
}
