@extends('layouts.app')

@section('title', Str::limit($event->name, 20, '...'))

@section('style')
    main.container-fluid {
        position: fixed;
        top: 60px;
        bottom: 60px;
        left: 0;
        right: 0;
        overflow:auto;
    }
@endsection

@section('content')
    <h1 id="up">{{ $event->name }} : {{ $event->date->format('d.m.Y') }}</h1>
    <div class="pt-5 pb-3 m-3">
        <a class="btn btn-info mr-2" href="/competitions/events/{{ $event->id }}/edit">Редактировать</a>
    </div>
    <table class="table table-bordered" id="table">
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
        </thead>
        <tbody>
        @yield('groups')
        </tbody>
    </table>
@endsection

@section('footer')
    <footer class="footer bg-dark">
        <div class="container-relative">
            @foreach($groupAnchors as $groupAnchor)
                <a class="text-danger" href="#{{ $groupAnchor }}">{{ $groupAnchor }}</a>&nbsp;&nbsp;
            @endforeach
            <a class="text-success" href="#up">Вверх</a>
        </div>
    </footer>
@endsection
