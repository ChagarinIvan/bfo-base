@php
    use App\Models\Cup;
    use App\Models\CupEvent;
    use App\Models\CupEventPoint;
    /**
     * @var Cup $cup;
     * @var CupEvent $cupEvent;
     * @var array<int, CupEventPoint> $cupEventPoints;
     * @var int $groupId;
     */
    $index = 0;
@endphp

@extends('layouts.app')

@section('title', Str::limit($cup->name.' '.$cupEvent->event->competition->name, 20, '...'))

@section('content')
    <div class="row"><h1>{{ $cup->name }}</h1></div>
    <div class="row"><h2>{{ $cupEvent->event->competition->name }}</h2></div>
    <div class="row"><h3>{{ $cupEvent->event->name }}</h3></div>
    <div class="row pt-5">
        <a class="btn btn-danger mr-2" href="/cups/{{ $cup->id }}/show">{{ __('app.common.back') }}</a>
    </div>
    <ul class="nav nav-tabs pt-2">
        @foreach($cup->groups as $group)
            <li class="nav-item">
                <a href="/cups/{{ $cup->id }}/events/{{ $cupEvent->event_id }}/show/{{ $group->id }}"
                   class="nav-link {{ $groupId === $group->id ? 'active' : ''}}"
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
                    <th scope="col">{{ __('app.common.year') }}</th>
                    <th scope="col">{{ __('app.common.time') }}</th>
                    <th scope="col">{{ __('app.common.points') }}</th>
                </tr>
                </thead>
                <tbody>
                @foreach($cupEventPoints as $cupEventPoint)
                    <tr>
                        <td>{{ ++$index }}</td>
                        <td>
                            <a href="/persons/{{ $cupEventPoint->protocolLine->person_id }}/show">
                                <u>{{ $cupEventPoint->protocolLine->lastname }} {{ $cupEventPoint->protocolLine->firstname }}</u>
                            </a>
                        </td>
                        <td>{{ $cupEventPoint->protocolLine->year }}</td>
                        <td>{{ $cupEventPoint->protocolLine->time ? $cupEventPoint->protocolLine->time->format('H:i:s') : '-' }}</td>
                        @if($cupEventPoint->points === $cupEvent->points)
                            <td><b class="text-info">{{ $cupEventPoint->points }}</b></td>
                        @else
                            <td>{{ $cupEventPoint->points }}</td>
                        @endif
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
