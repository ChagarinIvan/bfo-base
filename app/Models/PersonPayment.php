<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;

/**
 * Class PersonPayment
 *
 * @package App\Models
 * @property int $person_id
 * @property int $year
 * @property Carbon $date
 * @property Person $person
 */
class PersonPayment extends Model
{
    public $timestamps = false;

    protected $table = 'persons_payments';
    protected $dates = ['date'];

    public function person(): HasOne
    {
        return $this->hasOne(Person::class, 'id', 'person_id');
    }
}
