<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * @property int $game_user_id
 * @property int $type_id
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class GameUserType extends Pivot
{
}
