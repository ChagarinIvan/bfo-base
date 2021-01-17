@php
    use App\Models\Club;
    /**
     * @var Club $club
     */
@endphp

@extends('layouts.app')

@section('title', $club->name)

@section('content')
    <h3>{{ __('app.club.name') }}{{ $club->name }}</h3>

    @if($club->persons->count() > 0)
        <table class="table table-bordered table-fixed"
               id="table"
               data-toggle="table"
               data-sticky-header="true"
        >
            <thead>
            <tr class="table-info">
                <th scope="col">Фамилия Имя</th>
                <th scope="col">Этап</th>
                <th scope="col">Фамилия Имя</th>
                <th scope="col">Дата</th>
                <th scope="col">Группа</th>
                <th scope="col">Результат</th>
                <th scope="col">Место</th>
                <th scope="col">Очки</th>
                <th scope="col">Выполненный разряд</th>
            </tr>
            </thead>
            <tbody>
            @foreach($person->protocolLines as $line)
                <tr>
                    <td><a href="/competitions/{{ $line->event->competition_id }}/show"><u>{{ Str::limit($line->event->competition->name, 20, '...') }}</u></a></td>
                    <td><a href="/competitions/events/{{ $line->event_id }}/show#{{ $line->id }}"><u>{{ Str::limit($line->event->name, 20, '...') }}</u></a></td>
                    <td>{{ $line->lastname }} {{ $line->firstname }}</td>
                    <td>{{ $line->event->date->format('Y-m-d') }}</td>
                    <td>{{ $line->group->name }}</td>
                    <td>{{ $line->time ? $line->time->format('H:i:s') : '-' }}</td>
                    <td>{{ $line->place }}</td>
                    <td>{{ $line->points }}</td>
                    <td>{{ $line->complete_rank }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    @endif
@endsection
