@extends('layouts.app')

@section('title', Str::limit($event->name, 20, '...'))

@section('content')
    <h1>{{ $event->name }} : {{ $event->date->format('d.m.Y') }}</h1>
    <div class="pt-5 pb-3 m-3">
        <a class="btn btn-info mr-2" href="/competitions/events/{{ $event->id }}/edit">Редактировать</a>
        <a class="btn btn-danger" href="/competitions/{{ $event->competition_id }}/show">Назад</a>
    </div>
    <table class="table table-bordered table-fixed"
           id="table"
           data-toggle="table"
           data-sticky-header="true"
    >
        <thead>
        <tr class="table-info">
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
        <tr class="table-info">
            <th class="text-left text-danger" colspan="{{ $withPoints ? 9 : 8 }}">
                @foreach($groupAnchors as $groupAnchor)
                    <a href="#{{ $groupAnchor }}">{{ $groupAnchor }}</a>&nbsp;&nbsp;
                @endforeach
            </th>
        </tr>
        </thead>
        <tbody>
            @yield('groups')
        </tbody>
        </table>
    </div>
@endsection
