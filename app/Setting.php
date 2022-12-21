<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $game_id
 * @property int $user_identity_id
 * @property int $type_id
 * @property Carbon $created_at
 * @property Carbon $updated_at
 *
 * @method static Builder<static> active()
 * @method static Builder<static> inactive()
 */
class Setting extends Model
{
    protected $casts = [
        'show' => 'bool',
    ];
}
