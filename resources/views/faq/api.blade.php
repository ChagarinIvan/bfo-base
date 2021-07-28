@extends('layouts.app')

@section('title', __('app.navbar.faq'))

@section('content')
    <h3>{{ __('app.navbar.faq') }}</h3>
    <h5>{{ __('app.api.methods') }}</h5>
    <ul>
        <li>{{ __('app.api.competitions_list') }}<a href="/api/competitions">/api/competitions</a></li>
        <li>{{ __('app.api.events_list') }}<a href="/api/competition/3/events">/api/competition/{COMPETITION_ID}/events</a></li>
        <li>{{ __('app.api.results_list') }}<a href="/api/event/5/results">/api/event/{EVENT_ID}/results</a></li>
        <li>{{ __('app.api.clubs_list') }}<a href="/api/clubs">/api/clubs</a></li>
        <li>{{ __('app.api.athletes_list') }}<a href="/api/persons">/api/persons</a></li>
    </ul>
@endsection
