@php
    use App\Models\Cup;
    use App\Models\CupEventPoint;
    use App\Models\Group;
    use App\Models\ProtocolLine;
    /**
     * @var Cup $cup;
     * @var array<int, CupEventPoint[]> $cupPoints;
     * @var array<int, ProtocolLine> $protocolLines;
     * @var Group $activeGroup;
     */
    $place = 1;
    $events = []
@endphp

@extends('layouts.app')

@section('title', Str::limit($cup->name, 20, '...'))

@section('content')
    <div class="row"><h1>{{ $cup->name }}</h1></div>
    <div class="row">
        <a class="btn btn-danger mr-2" href="/cups/{{ $cup->id }}/show">{{ __('app.common.back') }}</a>
    </div>
    <ul class="nav nav-tabs pt-2">
        @foreach($cup->groups as $group)
            <li class="nav-item">
                <a href="/cups/{{ $cup->id }}/table/{{ $group->id }}"
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
                    @foreach($cup->events as $event)
                        @php
                            $events[] = $event->id;
                        @endphp
                        <th scope="col"><a href="/competitions/events/{{ $event->event_id }}/show#{{ $activeGroup->name }}"><u>{{ $event->event->date->format('Y-m-d') }}</u></a></th>
                    @endforeach
                    <th scope="col">{{ __('app.common.points') }}</th>
                    <th scope="col">{{ __('app.common.place') }}</th>
                </tr>
                </thead>
                <tbody>
                @foreach($cupPoints as $personId => $cupEventPoints)
                    @php
                        /** @var \App\Models\Person $person */
                        $person = $protocolLines->get($personId)->first()->person;
                        $sum = 0;
                        $cupEventPointsValues = array_values($cupEventPoints);
                    @endphp
                    <tr>
                        <td>{{ $place++ }}</td>
                        <td><a href="/persons/{{ $person->id }}/show"><b><u>{{ $person->lastname.' '.$person->firstname }}</u></b></a></td>
                        @foreach($events as $eventId)
                            @if(isset($cupEventPoints[$eventId]))
                                @php
                                    $cupEventPoint = $cupEventPoints[$eventId];
                                    $isBold = false;
                                    foreach ($cupEventPointsValues as $index => $cupEventPointsValue) {
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
                                    <td><b class="text-info">{{ $cupEventPoints[$eventId]->points }}</b></td>
                                @else
                                    <td>{{ $cupEventPoints[$eventId]->points }}</td>
                                @endif
                            @else
                                <td></td>
                            @endif
                        @endforeach
                        <td><b>{{ $sum }}</b></td>
                        <td><b>{{ $place }}</b></td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
