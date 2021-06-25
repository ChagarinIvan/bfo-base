@php
    use \App\Models\Cup;
    use App\Models\CupEvent;
    /**
     * @var Cup $cup;
     * @var CupEvent[] $events;
     */
@endphp

@extends('layouts.app')

@section('title', Str::limit($cup->name, 20, '...'))

@section('content')
    <div class="row">
        <h1>{{ $cup->name }} - {{ $cup->year }}</h1>
    </div>
    <div class="row pt-5">
        <a class="btn btn-info mr-2" href="/cups/{{ $cup->id }}/edit">{{ __('app.common.edit') }}</a>
        <a class="btn btn-success mr-2" href="/cups/{{ $cup->id }}/events/create">{{ __('app.competition.add_event') }}</a>
        <a class="btn btn-secondary mr-2" href="/cups/{{ $cup->id }}/table/0">{{ __('app.cup.table') }}</a>
        <a class="btn btn-danger mr-2" href="/cups">{{ __('app.common.back') }}</a>
    </div>
    <div class="row pt-3">
        @foreach($cup->groups as $group)
            <span class="badge" style="background: {{ \App\Facades\Color::getColor($group->name) }}">{{ $group->name }}</span>
        @endforeach
    </div>
    <div class="row">
        <h3>{{ __('app.cup.events') }}</h3>
    </div>
    <div class="row pt-3">
        <table class="table">
            <thead>
            <tr>
                <th scope="col">№</th>
                <th scope="col">{{ __('app.common.title') }}</th>
                <th scope="col">{{ __('app.common.date') }}</th>
                <th scope="col">{{ __('app.common.competitors') }}</th>
                <th scope="col">{{ __('app.common.points') }}</th>
                <th scope="col"></th>
            </tr>
            </thead>
            <tbody>
            @foreach($events as $index => $event)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>
                        <a href="/cups/{{ $cup->id }}/events/{{ $event->id }}/show/0">
                            <u>{{ $event->event->competition->name.' - '.$event->event->name }}</u>
                        </a>
                    </td>
                    <td>{{ $event->event->date->format('Y-m-d') }}</td>
                    <td>{{ $event->event
                        ->protocolLines()
                        ->whereIn('group_id', $cup->groups->pluck('id'))
                        ->count() }}</td>
                    <td>{{ $event->points }}</td>
                    <td>
                        <a href="/cups/{{ $cup->id }}/events/{{ $event->id }}/edit" class="text-primary">{{ __('app.common.edit') }}</a>
                        <a href="/cups/{{ $cup->id }}/events/{{ $event->id }}/delete" class="text-danger">{{ __('app.common.delete') }}</a>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
@endsection
