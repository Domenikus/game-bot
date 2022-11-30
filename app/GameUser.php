<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * @property int $id
 * @property string $user_identity_id
 * @property int $game_id
 * @property array $options
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class GameUser extends Pivot
{
    public $incrementing = true;

    protected $casts = [
        'options' => 'array',
    ];
}
