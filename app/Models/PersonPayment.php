<?php

declare(strict_types=1);

namespace App\Models;

use App\Domain\Auth\Impression;
use App\Infrastracture\Laravel\Eloquent\Auth\ImpressionCast;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Query\Builder;

/**
 * @property int $id
 * @property int $person_id
 * @property int $year
 * @property Carbon $date
 * @property Impression $created
 * @property Impression $updated
 *
 * @method static PersonPayment|Builder where(string $column, int $value)
 */
class PersonPayment extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $table = 'persons_payments';

    protected $casts = [
        'date' => 'datetime:Y-m-d',
        'created' => ImpressionCast::class,
        'updated' => ImpressionCast::class,
    ];

    public function person(): HasOne
    {
        return $this->hasOne(Person::class, 'id', 'person_id');
    }

    public function updateDate(Carbon $date, Impression $impression): void
    {
        $this->date = $date;
        $this->updated = $impression;
    }
}
