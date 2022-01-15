<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $key
 * @property string $volume
 */
class SystemFlag extends Model
{
    public $timestamps = false;
    protected $table = 'system_flags';
}
