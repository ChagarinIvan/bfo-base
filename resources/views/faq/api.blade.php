@extends('layouts.app')

@section('title', __('app.navbar.faq'))

@section('content')
    <h5>{{ __('app.api.methods') }}</h5>
    <ul>
        <li>{{ __('app.api.competitions_list') }} <a href="">/api/competitions</a></li>
        <li>{{ __('app.api.events_list') }} <a href="/api/competition/3/events">/api/competition/{COMPETITION_ID}/events</a></li>
        <li>{{ __('app.api.results_list') }} <a href="/api/event/5/results">/api/event/{EVENT_ID}/results</a></li>
        <li>{{ __('app.api.clubs_list') }} <a href="/api/club">/api/club</a></li>
        <li>{{ __('app.api.athletes_list') }} <a href="/api/person?per_page=10&page=1&sort_by=fio&sort_mode=0&search=93">/api/person?per_page={PER_PAGE}&page={PAGE}&sort_by={FIELD}&sort_mode=0&search={SEARCH}</a></li>
    </ul>
@endsection
