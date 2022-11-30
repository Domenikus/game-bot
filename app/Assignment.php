<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property string $value
 * @property int $type_id
 * @property int $game_id
 * @property int $ts3_server_group_id
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class Assignment extends Model
{
    /**
     * @return BelongsTo<Game, Assignment>
     */
    public function game(): BelongsTo
    {
        return $this->belongsTo(Game::class);
    }

    /**
     * @return BelongsTo<Type, Assignment>
     */
    public function type(): BelongsTo
    {
        return $this->belongsTo(Type::class);
    }

    /**
     * @return Attribute<string, string>
     */
    protected function value(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value,
            set: fn ($value) => strtolower($value)
        );
    }
}
