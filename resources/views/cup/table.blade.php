@php
    use App\Models\Cup;
    use App\Models\CupEvent;
    use App\Models\CupEventPoint;
    use App\Models\Group\CupGroup;
    use App\Models\Person;
    use Illuminate\Support\Collection;
    /**
     * @var Cup $cup;
     * @var CupEvent[] $cupEvents;
     * @var array<int, CupEventPoint[]> $cupPoints;
     * @var Person[]|Collection $persons;
     * @var CupGroup $activeGroup;
     */
    $place = 1;
@endphp

@extends('layouts.app')

@section('title', $cup->name.' - '.$cup->year)

@section('content')
    <div class="row mb-3">
        <div class="col-12">
            <x-back-button/>
        </div>
    </div>
    <div class="row">
        <ul class="nav nav-tabs">
            @foreach($cup->getCupType()->getGroups() as $group)
                @php
                    /** @var \App\Models\Group\CupGroup $group */
                @endphp
                <li class="nav-item">
                    <a href="{{ action(\App\Http\Controllers\Cups\ShowCupTableAction::class, [$cup, $group->id]) }}"
                       class="text-decoration-none nav-link {{ $activeGroup->id === $group->id ? 'active' : ''}}"
                    >
                        <b>{{ $group->name }}</b>
                    </a>
                </li>
            @endforeach
        </ul>
        <div class="tab-content">
            <div class="tab-pane fade show active">
                <table id="table"
                       data-cookie="true"
                       data-cookie-id-table="cup-table"
                       data-mobile-responsive="true"
                       data-check-on-init="true"
                       data-min-width="800"
                       data-toggle="table"
                       data-sort-class="table-active"
                       data-pagination="true"
                       data-page-list="[10,25,50,100,All]"
                       data-search="true"
                       data-search-highlight="true"
                       data-resizable="true"
                       data-custom-sort="customSort"
                       data-pagination-next-text="{{ __('app.pagination.next') }}"
                       data-pagination-pre-text="{{ __('app.pagination.previous') }}"
                >
                    <thead class="table-dark">
                        <tr>
                            <th data-sortable="true">№</th>
                            <th data-sortable="true">{{ __('app.common.fio') }}</th>
                            @foreach($cupEvents as $cupEvent)
                                <th data-sortable="true">
                                    <a href="{{ action(\App\Http\Controllers\CupEvents\ShowCupEventGroupAction::class, [$cup->id, $cupEvent->id, $activeGroup->id]) }}"
                                        class="text-white">
                                        {{ $cupEvent->event->date->format('m-d') }}
                                    </a>
                                </th>
                            @endforeach
                            <th data-sortable="true">{{ __('app.common.points') }}</th>
                            <th data-sortable="true">{{ __('app.common.average') }}</th>
                            <th data-sortable="true">{{ __('app.common.place') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($cupPoints as $personId => $cupEventPoints)
                            @php
                                /** @var \App\Models\Person $person */
                                $person = $persons->get($personId);
                                $sum = 0;
                                $count = 0;
                            @endphp
                            <tr>
                                <td>{{ $place }}</td>
                                <td>
                                    <b>
                                        <a href="{{ action(\App\Http\Controllers\Person\ShowPersonAction::class, [$person]) }}">{{ $person->lastname.' '.$person->firstname }}</a>
                                    </b>
                                </td>
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
                                                $count++;
                                            @endphp
                                            <td>
                                                <a href="{{ action(\App\Http\Controllers\Event\ShowEventAction::class, [$cupEvent->event_id, $cupEventPoint->protocolLine->distance_id]) }}#{{ $cupEventPoint->protocolLine->id }}">
                                                    <b class="text-info">{{ $cupEventPoint->points }}</b>
                                                </a>
                                            </td>
                                        @else
                                            <td>
                                                <a href="{{ action(\App\Http\Controllers\Event\ShowEventAction::class,[$cupEvent->event_id, $cupEventPoint->protocolLine->distance_id]) }}#{{ $cupEventPoint->protocolLine->id }}">
                                                    <b class="text-dark">{{ $cupEventPoint->points }}</b>
                                                </a>
                                            </td>
                                        @endif
                                    @else
                                        <td></td>
                                    @endif
                                @endforeach
                                <td><b>{{ $sum }}</b></td>
                                <td><b>{{ ($count === 0) ? 0 : round($sum / $count) }}</b></td>
                                <td><b>{{ $place++ }}</b></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@section('table_extracted_columns', '[1]')
