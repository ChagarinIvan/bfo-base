@php
    use App\Models\Person;
    /**
     * @var Person $person
     */
@endphp

@extends('layouts.app')

@section('title', $person->lastname)

@section('content')
    <h3>{{ $person->lastname }} {{ $person->firstname }}</h3>
    <h4>{{ $person->patronymic }}</h4>
    <h4>{{ $person->birthday ? $person->birthday->format('Y-m-d') : '' }}</h4>
    @if($person->protocolLines->count() > 0)
        <table class="table table-bordered table-fixed"
               id="table"
               data-toggle="table"
               data-sticky-header="true"
        >
            <thead>
            <tr class="table-info">
                <th scope="col">Соревнования</th>
                <th scope="col">Этап</th>
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
                    <td><a href="/competitions/events/{{ $line->event_id }}/show"><u>{{ Str::limit($line->event->name, 20, '...') }}</u></a></td>
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
