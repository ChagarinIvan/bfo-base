@php
    use App\Models\Competition;
    use App\Models\Event;
    /**
     * @var Competition $competition;
     * @var Event $events;
     */
@endphp

@extends('layouts.app')

@section('title', \Illuminate\Support\Str::limit($competition->name, 20))

@section('content')
    <div class="row">
        <h1>{{ $competition->name }}</h1>
    </div>
    <div class="row pt-5">
        @auth
            <a class="btn btn-success mr-2"
               href="{{ action(\App\Http\Controllers\Event\ShowCreateEventFormAction::class, [$competition->id]) }}"
            >{{ __('app.competition.add_event') }}</a>
            <a class="btn btn-info mr-2"
               href="{{ action(\App\Http\Controllers\Event\ShowUnitEventsFormAction::class, [$competition->id]) }}"
            >{{ __('app.competition.sum') }}</a>
        @endauth
        <a class="btn btn-danger mr-2" href="{{ action(\App\Http\Controllers\BackAction::class) }}">{{ __('app.common.back') }}</a>
    </div>
    <div class="row pt-3">
        <table class="table">
            <thead>
            <tr>
                <th scope="col">{{ __('app.common.title') }}</th>
                <th scope="col">{{ __('app.event.flags') }}</th>
                <th scope="col">{{ __('app.competition.description') }}</th>
                <th scope="col">{{ __('app.common.date') }}</th>
                <th scope="col">{{ __('app.common.competitors') }}</th>
                @auth<th scope="col"></th>@endauth
            </tr>
            </thead>
            <tbody>
            @foreach ($events as $event)
                <tr>
                    <td><a href="{{ action(\App\Http\Controllers\Event\ShowEventAction::class, [$event->id]) }}">{{ $event->name }}</a></td>
                    <td>
                        @foreach($event->cups as $cupEvent)
                            <span class="badge" style="background: {{ \App\Facades\Color::getColor($cupEvent->cup->name) }}">
                                <a href="{{ action(\App\Http\Controllers\Cups\ShowCupAction::class, [$cupEvent->cup]) }}"
                                >{{ $cupEvent->cup->name }} {{ $cupEvent->cup->year }}</a>
                            </span>
                        @endforeach
                        @foreach($event->flags as $flag)
                            <span class="badge" style="background: {{ $flag->color }}">
                                <a href="{{ action(\App\Http\Controllers\Flags\ShowFlagEventsAction::class, [$flag]) }}">{{ $flag->name }}</a>
                            </span>
                        @endforeach
                    </td>
                    <td><small>{{ \Illuminate\Support\Str::limit($event->description) }}</small></td>
                    <td>{{ $event->date->format('Y-m-d') }}</td>
                    <td>{{ count($event->protocolLines) }}</td>
                    @auth
                        <td>
                            <a href="{{ action(\App\Http\Controllers\Event\ShowAddFlagToEventFormAction::class, [$event]) }}"
                               class="text-info"
                            >{{ __('app.common.add_flags') }}</a>
                            <a href="{{ action(\App\Http\Controllers\Event\ShowEditEventFormAction::class, [$event]) }}"
                               class="text-primary"
                            >{{ __('app.common.edit') }}</a>
                            <a class="text-danger"
                               href="{{ action(\App\Http\Controllers\Event\DeleteEventAction::class, [$event->id]) }}"
                            >{{ __('app.common.delete') }}</a>
                        </td>
                    @endauth
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
@endsection
