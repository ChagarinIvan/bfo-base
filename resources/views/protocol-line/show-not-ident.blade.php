@php
    /**
     * @var array $persons
     */
@endphp
@extends('layouts.app')

@section('title', 'Не идентифицированные результаты')

@section('content')
    <h4>Представлены неидентифированные результаты для групп начиная с 18</h4>
    <table class="table table-bordered table-fixed"
           id="table"
           data-toggle="table"
           data-sticky-header="true"
    >
        <thead>
        <tr class="table-info">
            <th scope="col">Фамилия</th>
            <th scope="col">Имя</th>
            <th scope="col">Соревнования</th>
            <th scope="col">Этап</th>
            <th scope="col">Клуб</th>
            <th scope="col">Дата</th>
            <th scope="col">Группа</th>
            <th scope="col">Результат</th>
            <th scope="col">Место</th>
        </tr>
        </thead>
        <tbody>
        @foreach($persons as $lines)
            @foreach($lines as $line)
                @php
                    /** @var App\Models\ProtocolLine $line */
                    $link = "/protocol-lines/{$line->id}/edit-person";
                @endphp
                <tr>
                    @if($loop->first)
                        <td rowspan="{{ count($lines) }}"><a href="{{ $link }}"><u>{{ $line->lastname }}</u></a></td>
                        <td rowspan="{{ count($lines) }}"><a href="{{ $link }}"><u>{{ $line->firstname }}</u></a></td>
                    @endif
                    <td><a href="/competitions/{{ $line->event->competition_id }}/show"><u>{{ Str::limit($line->event->competition->name, 20, '...') }}</u></a></td>
                    <td><a href="/competitions/events/{{ $line->event_id }}/show"><u>{{ Str::limit($line->event->name, 20, '...') }}</u></a></td>
                    <td>{{ $line->club }}</td>
                    <td>{{ $line->event->date->format('Y-m-d') }}</td>
                    <td>{{ $line->group->name }}</td>
                    <td>{{ $line->time ? $line->time->format('H:i:s') : '-' }}</td>
                    <td>{{ $line->place }}</td>
                </tr>
            @endforeach
        @endforeach
        </tbody>
    </table>
@endsection
