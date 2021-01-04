<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * Class Person
 *
 * @package App\Models
 * @property int $id
 * @property string $lastname
 * @property string $firstname
 * @property string|null $patronymic
 * @property Carbon|null $birthday
 */
class Person extends Model
{
    public $timestamps = false;
    protected $table = 'person';
}
