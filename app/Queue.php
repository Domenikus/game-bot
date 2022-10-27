<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property string $name
 * @property int $game_id
 * @property int $type_id
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class Queue extends Model
{
    /**
     * @return BelongsTo<Game, Queue>
     */
    public function game(): BelongsTo
    {
        return $this->belongsTo(Game::class);
    }

    /**
     * @return BelongsTo<Type, Queue>
     */
    public function type(): BelongsTo
    {
        return $this->belongsTo(Type::class);
    }
}
