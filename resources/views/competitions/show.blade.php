@php
    use \App\Models\Competition;
    /**
     * @var Competition $competition;
     */
@endphp


@extends('layouts.app')

@section('title', Str::limit($competition->name, 20, '...'))

@section('content')
    <div class="row">
        <h1>{{ $competition->name }}</h1>
    </div>
    <div class="row pt-5">
        <a class="btn btn-success mr-2" href="/competitions/{{ $competition->id }}/events/add">{{ __('app.competition.add_event') }}</a>
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
                <th scope="col"></th>
            </tr>
            </thead>
            <tbody>
            @foreach ($competition->events as $event)
                <tr>
                    <td><a href="/competitions/events/{{ $event->id }}/show">{{ $event->name }}</a></td>
                    <td>
                        @foreach($event->cups as $cupEvent)
                            <span class="badge" style="background: {{ \App\Facades\Color::getColor($cupEvent->cup->name) }}">
                                <a href="/cups/{{ $cupEvent->cup->id }}/show">{{ $cupEvent->cup->name }}</a>
                            </span>
                        @endforeach
                        @foreach($event->flags as $flag)
                            <span class="badge" style="background: {{ $flag->color }}"><a href="/flags/{{ $flag->id }}/show-events">{{ $flag->name }}</a></span>
                        @endforeach
                    </td>
                    <td><small>{{ Str::limit($event->description, 100, '...') }}</small></td>
                    <td>{{ $event->date->format('Y-m-d') }}</td>
                    <td>{{ count($event->protocolLines) }}</td>
                    <td>
                        <a href="/competitions/events/{{ $event->id }}/add-flags" class="text-info">{{ __('app.common.add_flags') }}</a>
                        <a href="/competitions/events/{{ $event->id }}/edit" class="text-primary">{{ __('app.common.edit') }}</a>
                        <a href="/competitions/events/{{ $event->id }}/delete" class="text-danger">{{ __('app.common.delete') }}</a>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
@endsection
