<?php
declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;

/**
 * @property int $person_id
 * @property int $year
 * @property Carbon $date
 *
 * @property-read Person $person
 *
 * @method static PersonPayment|Builder where(string $column, int $year)
 * @method static PersonPayment|Builder wherePersonId(int $personId)
 * @method static PersonPayment|null first()
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
