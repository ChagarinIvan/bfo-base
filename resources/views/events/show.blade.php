@extends('layouts.app')

@section('title', Str::limit($event->name, 20, '...'))

@section('content')
    <div class="row">
        <h1>{{ $event->name }} : {{ $event->date->format('d.m.Y') }}</h1>
    </div>
    <div class="row pt-5">
        <a class="btn btn-info mr-2" href="/competitions/events/{{ $event->id }}/edit">Редактировать</a>
        <a class="btn btn-danger" href="/competitions/{{ $event->competition_id }}/show">Назад</a>
    </div>
    <div class="row pt-3">
        <table class="table">
            <thead>
            <tr>
                <th scope="col">Фамилия</th>
                <th scope="col">Имя</th>
                <th scope="col">Клуб</th>
                <th scope="col">Год</th>
                <th scope="col">Разряд</th>
                <th scope="col">Время</th>
                <th scope="col">Место</th>
                <th scope="col">Выполнил</th>
                @if($withPoints)<th scope="col">Очки</th>@endif
            </tr>
            </thead>
            <tbody>
            @foreach ($groups as $group)
                <tr class="table-info">
                    <td class="text-left text-danger" colspan="{{ $withPoints ? 9 : 8 }}">
                        @foreach($groupAnchors as $groupAnchor)
                            <a href="#{{ $groupAnchor }}">{{ $groupAnchor }}</a>&nbsp;&nbsp;
                        @endforeach
                    </td>
                </tr>
                <tr>
                    <td class="text-center" colspan="{{ $withPoints ? 8 : 7 }}"><b id="{{ $group->name }}">{{ $group->name }}</b></td>
                </tr>
                @foreach($lines->get($group->id) as $line)
                    <tr>
                        <td>{{ $line->lastname }}</td>
                        <td>{{ $line->firstname }}</td>
                        <td>{{ $line->club }}</td>
                        <td>{{ $line->year }}</td>
                        <td>{{ $line->rank }}</td>
                        <td>{{ $line->time ? $line->time->format('H:i:s') : '-' }}</td>
                        <td>{{ $line->place }}</td>
                        <td>{{ $line->complete_rank }}</td>
                        @if($withPoints)<td>{{ $line->points }}</td>@endif
                    </tr>
                @endforeach
            @endforeach
            </tbody>
        </table>
    </div>
@endsection
