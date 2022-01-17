<?php

namespace App;

use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * @method static where(array[] $array)
 * @property string $user_identity_id
 * @property array $options
 */
class GameUser extends Pivot
{
    protected $casts = [
        'options' => 'array',
    ];


}
