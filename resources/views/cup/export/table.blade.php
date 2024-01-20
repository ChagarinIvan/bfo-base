@php
use App\Models\Cup;
use App\Models\CupEvent;
use App\Models\CupEventPoint;
use App\Models\Person;
use Illuminate\Support\Collection;
/**
* @var Cup $cup;
* @var CupEvent[] $cupEvents;
* @var array<int, CupEventPoint[]> $cupPoints;
* @var Person[]|Collection $persons;
*/
$place = 1;
@endphp

<table>
<colgroup><col span="{{ 6 + $cupEvents->count() }}"></colgroup>
<thead>
<tr>
<th>№</th>
<th>ФІ</th>
<th>ГР</th>
@foreach($cupEvents as $cupEvent)
<th>{{ $cupEvent->event->date->format('d.m') }}</th>
@endforeach
<th>{{ __('app.common.points') }}</th>
<th>{{ __('app.common.average') }}</th>
<th>{{ __('app.common.place') }}</th>
</tr>
</thead>
<tbody>
@foreach($cupPoints as $personId => $cupEventPoints)
@php
/** @var Person $person */
$person = $persons->get($personId);
$sum = 0;
$count = 0;
@endphp
<tr>
<td>{{ $place }}</td>
<td>{{ $person->lastname.' '.$person->firstname }}</td>
<td>{{ $person->birthday?->year }}</td>
@foreach($cupEvents as $cupEvent)
@php
$find = false;
foreach ($cupEventPoints as $cupEventPoint) {
if ($cupEventPoint->eventCupId === $cupEvent->id) {
$find = true;
break;
}
}
@endphp
@if($find)
@php
$isBold = false;
foreach (array_values($cupEventPoints) as $index => $cupEventPointsValue) {
if ($index >= $cup->events_count) {
break;
}
if ($cupEventPointsValue->equal($cupEventPoint)) {
$isBold = true;
}
}
@endphp
@if ($isBold)
@php
$sum += $cupEventPoint->points;
$count = $cupEventPoint->points === 0 ? $count : $count + 1;
@endphp
<td><b>{{ $cupEventPoint->points }}</b></td>
@else
<td>{{ $cupEventPoint->points }}</td>
@endif
@else
<td></td>
@endif
@endforeach
<td>{{ $sum }}</td>
<td>{{ ($count === 0) ? 0 : round($sum / $count) }}</td>
<td>{{ $place++ }}</td>
</tr>
@endforeach
</tbody>
</table>
