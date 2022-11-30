<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * @property int $id
 * @property int $game_id
 * @property int $type_id
 * @property string $label
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class GameType extends Pivot
{
    public $incrementing = true;
}
