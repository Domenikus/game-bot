<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;


/**
 * @property string $value
 * @property string $ts3_server_group_id
 * @method static where(string $col, int|string|void|null $value)
 */
class Assignment extends Model
{
    public function game(): BelongsTo
    {
        return $this->belongsTo(Game::class);
    }

    public function type(): BelongsTo
    {
        return $this->belongsTo(Type::class);
    }
}
