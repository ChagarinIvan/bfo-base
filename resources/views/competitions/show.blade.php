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
        <a class="btn btn-success mr-2" href="/competitions/{{ $competition->id }}/events/add">Добавить этап</a>
    </div>
    <div class="row pt-3">
        <table class="table">
            <thead>
            <tr>
                <th scope="col">Название</th>
                <th scope="col">{{ __('app.events.flags') }}</th>
                <th scope="col">Описание</th>
                <th scope="col">Дата</th>
                <th scope="col">Число участников</th>
                <th scope="col"></th>
            </tr>
            </thead>
            <tbody>
            @foreach ($competition->events as $event)
                <tr>
                    <td><a href="/competitions/events/{{ $event->id }}/show">{{ $event->name }}</a></td>
                    <td>
                        @foreach($event->flags as $flag)
                            <span class="badge" style="background: {{ $flag->color }}"><a href="/flags/{{ $flag->id }}/show-events">{{ $flag->name }}</a></span>
                        @endforeach
                    </td>
                    <td><small>{{ Str::limit($event->description, 100, '...') }}</small></td>
                    <td>{{ $event->date->format('Y-m-d') }}</td>
                    <td>{{ count($event->protocolLines) }}</td>
                    <td>
                        <a href="/competitions/events/{{ $event->id }}/add-flags" class="text-info">{{ __('app.common.add_flags') }}</a>
                        <a href="/competitions/events/{{ $event->id }}/edit" class="text-primary">Edit</a>
                        <a href="/competitions/events/{{ $event->id }}/delete" class="text-danger">Delete</a>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
@endsection
