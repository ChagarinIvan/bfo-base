@php
    use App\Models\Cup;
    use App\Models\CupEvent;
    use App\Models\CupEventPoint;
    use App\Models\ProtocolLine;
    use Illuminate\Support\Collection;
    /**
     * @var Cup $cup;
     * @var CupEvent $cupEvent;
     * @var ProtocolLine[]|Collection $protocolLines;
     * @var array<int, CupEventPoint> $cupEventPoints;
     * @var int $groupId;
     */
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
                <a href="/cups/{{ $cup->id }}/events/{{ $cupEvent->id }}/show/{{ $group->id }}"
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
                    <th scope="col">{{ __('app.common.lastname') }}</th>
                    <th scope="col">{{ __('app.common.name') }}</th>
                    <th scope="col">{{ __('app.common.year') }}</th>
                    <th scope="col">{{ __('app.common.time') }}</th>
                    <th scope="col">{{ __('app.common.points') }}</th>
                </tr>
                </thead>
                <tbody>
                @foreach($protocolLines as $index => $line)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        @if ($line->person_id)
                            <td><a href="/persons/{{ $line->person_id }}/show"><u>{{ $line->lastname }}</u></a></td>
                        @else
                            <td>{{ $line->lastname }}</td>
                        @endif
                        <td>{{ $line->firstname }}</td>
                        <td>{{ $line->year }}</td>
                        <td>{{ $line->time ? $line->time->format('H:i:s') : '-' }}</td>
                        @if($cupEventPoints[$line->id]->points === $cupEvent->points)
                            <td><b class="text-info">{{ $cupEventPoints[$line->id]->points }}</b></td>
                        @else
                            <td>{{ $cupEventPoints[$line->id]->points }}</td>
                        @endif
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
