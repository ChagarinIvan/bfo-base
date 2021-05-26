@php
    use App\Models\Event;
    /**
     * @var Event $event
     * array $groupAnchors
     */
@endphp
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
    <a href="/competitions/{{ $event->competition->id }}/show"><h1>{{ $event->competition->name }}</h1></a>
    <h2 id="up">{{ $event->name }} : {{ $event->date->format('d.m.Y') }}</h2>
    <div class="pt-5 pb-3 m-3">
        <a class="btn btn-info mr-2" href="/competitions/events/{{ $event->id }}/edit">{{ __('app.common.edit') }}</a>
        <a class="btn btn-danger mr-2" href="/competitions/{{ $event->competition_id }}/show">{{ __('app.common.back') }}</a>
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
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            @foreach($groupAnchors as $groupAnchor)
                <a class="text-danger" href="#{{ $groupAnchor }}">{{ $groupAnchor }}</a>&nbsp;&nbsp;
            @endforeach
            <a class="text-success" href="#up">{{ __('app.up') }}</a>
        </div>
    </footer>
@endsection
