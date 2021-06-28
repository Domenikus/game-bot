<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @method static find($identityId)
 */
class User extends Model
{
    protected $primaryKey = 'identity_id';
    public $incrementing = false;
}
