<?php

declare(strict_types=1);

namespace App\Domain\PersonPayment;

use App\Domain\Auth\Impression;
use App\Domain\Person\Person;
use App\Domain\PersonPayment\Event\PersonPaymentCreated;
use App\Domain\PersonPayment\Event\PersonPaymentUpdated;
use App\Domain\Shared\AggregatedModel;
use App\Infrastructure\Laravel\Eloquent\Auth\ImpressionCast;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\WithoutTimestamps;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property int $id
 * @property int $person_id
 * @property int $year
 * @property Carbon $date
 *
 * @property Impression $created
 * @property Impression $updated
 */
#[Table(name: 'persons_payments')]
#[WithoutTimestamps]
class PersonPayment extends AggregatedModel
{
    use HasFactory;

    public function person(): HasOne
    {
        return $this->hasOne(Person::class, 'id', 'person_id');
    }

    public function updateDate(Carbon $date, Impression $impression): void
    {
        $this->date = $date;
        $this->updated = $impression;

        $this->recordThat(new PersonPaymentUpdated($this));
    }

    public function sameDate(Carbon $date): bool
    {
        return $this->date->isSameDay($date);
    }

    public function create(): void
    {
        $this->recordThat(new PersonPaymentCreated($this));

        $this->save();
    }

    protected function casts(): array
    {
        return [
            'date' => 'datetime:Y-m-d',
            'created' => ImpressionCast::class,
            'updated' => ImpressionCast::class,
        ];
    }
}
