@php
    use App\Models\Cup;
    use App\Models\CupEvent;
    use App\Models\CupEventPoint;
    use App\Models\Group;
    use App\Models\Person;
    use Illuminate\Support\Collection;
    /**
     * @var Cup $cup;
     * @var CupEvent[] $events;
     * @var array<int, CupEventPoint[]> $cupPoints;
     * @var Person[]|Collection $persons;
     * @var Group $activeGroup;
     */
    $place = 1;
@endphp

@extends('layouts.app')

@section('title', \Illuminate\Support\Str::limit($cup->name, 20, '...'))

@section('content')
    <div class="row"><h1>{{ $cup->name }}</h1></div>
    <div class="row">
        <a class="btn btn-danger mr-2"
           href="{{ action(\App\Http\Controllers\Cups\ShowCupAction::class, [$cup]) }}"
        >{{ __('app.common.back') }}</a>
    </div>
    <ul class="nav nav-tabs pt-2">
        @foreach($cup->groups as $group)
            <li class="nav-item">
                <a href="{{ action(\App\Http\Controllers\Cups\ShowCupTableAction::class, [$cup, $group]) }}"
                   class="nav-link {{ $activeGroup->id === $group->id ? 'active' : ''}}"
                >{{ $group->name }}</a>
            </li>
        @endforeach
    </ul>
    <div class="tab-content">
        <div class="tab-pane fade show active">
            <table class="table table-bordered" id="table">
                <thead>
                <tr class="table-info">
                    <th scope="col"></th>
                    <th scope="col">{{ __('app.common.fio') }}</th>
                    @foreach($events as $event)
                        <th scope="col">
                            <a href="{{ action(\App\Http\Controllers\Event\ShowEventAction::class,[$event->event_id]) }}#{{ $activeGroup->name }}">
                                <u>{{ $event->event->date->format('Y-m-d') }}</u>
                            </a>
                        </th>
                    @endforeach
                    <th scope="col">{{ __('app.common.points') }}</th>
                    <th scope="col">{{ __('app.common.place') }}</th>
                </tr>
                </thead>
                <tbody>
                @foreach($cupPoints as $personId => $cupEventPoints)
                    @php
                        /** @var \App\Models\Person $person */
                        $person = $persons->get($personId);
                        $sum = 0;
                    @endphp
                    <tr>
                        <td>{{ $place }}</td>
                        <td>
                            <a href="{{ action(\App\Http\Controllers\Person\ShowPersonAction::class, [$person]) }}">
                                <b>
                                    <u>{{ $person->lastname.' '.$person->firstname }}</u>
                                </b>
                            </a>
                        </td>
                        @foreach($events as $event)
                            @php
                                $find = false;
                                foreach ($cupEventPoints as $cupEventPoint) {
                                    if ($cupEventPoint->eventCupId === $event->id) {
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
                                    @endphp
                                    <td>
                                        <a href="{{ action(\App\Http\Controllers\Event\ShowEventAction::class,[$event->event_id]) }}#{{ $cupEventPoint->protocolLine->id }}">
                                            <u><b class="text-info">{{ $cupEventPoint->points }}</b></u>
                                        </a>
                                    </td>
                                @else
                                    <td>
                                        <a href="{{ action(\App\Http\Controllers\Event\ShowEventAction::class,[$event->event_id]) }}#{{ $cupEventPoint->protocolLine->id }}">
                                            <u>{{ $cupEventPoint->points }}</u>
                                        </a>
                                    </td>
                                @endif
                            @else
                                <td></td>
                            @endif
                        @endforeach
                        <td><b>{{ $sum }}</b></td>
                        <td><b>{{ $place++ }}</b></td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
