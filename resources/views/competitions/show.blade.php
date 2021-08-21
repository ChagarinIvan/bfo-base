@php
    use \App\Models\Competition;
    use App\Models\Event;
    /**
     * @var Competition $competition;
     * @var Event $events;
     */
@endphp

@extends('layouts.app')

@section('title', Str::limit($competition->name, 20, '...'))

@section('content')
    <div class="row">
        <h1>{{ $competition->name }}</h1>
    </div>
    <div class="row pt-5">
        @auth
            <a class="btn btn-success mr-2" href="/competitions/{{ $competition->id }}/events/add">{{ __('app.competition.add_event') }}</a>
            <a class="btn btn-info mr-2" href="/competitions/{{ $competition->id }}/events/sum">{{ __('app.competition.sum') }}</a>
        @endauth
        <a class="btn btn-danger mr-2"
           href="{{ action(\App\Http\Controllers\Competition\ShowCompetitionsTableAction::class, [$competition->from->format('Y')]) }}"
        >{{ __('app.common.back') }}</a>
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
                    <td><a href="/competitions/events/{{ $event->id }}/show">{{ $event->name }}</a></td>
                    <td>
                        @foreach($event->cups as $cupEvent)
                            <span class="badge" style="background: {{ \App\Facades\Color::getColor($cupEvent->cup->name) }}">
                                <a href="/cups/{{ $cupEvent->cup->id }}/show">{{ $cupEvent->cup->name }} {{ $cupEvent->cup->year }}</a>
                            </span>
                        @endforeach
                        @foreach($event->flags as $flag)
                            <span class="badge" style="background: {{ $flag->color }}"><a href="/flags/{{ $flag->id }}/show-events">{{ $flag->name }}</a></span>
                        @endforeach
                    </td>
                    <td><small>{{ Str::limit($event->description, 100, '...') }}</small></td>
                    <td>{{ $event->date->format('Y-m-d') }}</td>
                    <td>{{ count($event->protocolLines) }}</td>
                    @auth
                        <td>
                            <a href="/competitions/events/{{ $event->id }}/add-flags" class="text-info">{{ __('app.common.add_flags') }}</a>
                            <a href="/competitions/events/{{ $event->id }}/edit" class="text-primary">{{ __('app.common.edit') }}</a>
                            <a href="/competitions/events/{{ $event->id }}/delete" class="text-danger">{{ __('app.common.delete') }}</a>
                        </td>
                    @endauth
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
@endsection
