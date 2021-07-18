<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Class CupEvent
 *
 * @package App\Models
 * @property int $id
 * @property int $cup_id
 * @property int $event_id
 * @property int $points
 * @property-read Cup $cup
 * @property-read Event $event
 * @method static Builder|CupEvent find(mixed $ids)
 * @method static Builder|CupEvent with(mixed $params)
 * @method static Builder|CupEvent whereCupId(int $id)
 * @method static Builder|CupEvent whereEventId(int $id)
 */
class CupEvent extends Model
{
    public function cup(): HasOne
    {
        return $this->hasOne(Cup::class, 'id', 'cup_id');
    }

    public function event(): HasOne
    {
        return $this->hasOne(Event::class, 'id', 'event_id');
    }

    /**
     * @param Group $group
     * @return Collection
     */
    public function getGroupPersonsIds(Group $group): Collection
    {
        $startYear = $this->cup->year - $group->years();
        $finishYear = $startYear - 5;

        return Person::selectRaw(DB::raw('person.id AS id, persons_payments.date AS date'))
            ->join('persons_payments', 'person.id', '=', 'persons_payments.person_id')
            ->where('person.birthday', '<=', "{$startYear}-01-01")
            ->where('person.birthday', '>', "{$finishYear}-01-01")
            ->where('persons_payments.year', $this->cup->year)
            ->havingRaw(DB::raw("persons_payments.date <= '{$this->event->date}'"))
            ->get();
    }
}
