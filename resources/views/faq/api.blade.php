@extends('layouts.app')

@section('title', __('app.navbar.faq'))

@section('content')
    <h3>{{ __('app.navbar.faq') }}</h3>
    <h5>Методы Api</h5>
    <ul>
        <li>Список соревнований <a href="/api/competitions">/api/competitions</a></li>
        <li>Список этапов соревнования <a href="/api/competition/3/events">/api/competition/{COMPETITION_ID}/events</a></li>
        <li>Результаты этапа <a href="/api/event/1/results">/api/event/{EVENT_ID}/results</a></li>
        <li>Список клубов <a href="/api/clubs">/api/clubs</a></li>
        <li>Список спортсменов <a href="/api/persons">/api/persons</a></li>
    </ul>
@endsection
