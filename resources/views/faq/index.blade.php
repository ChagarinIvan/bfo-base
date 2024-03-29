@php
    use App\Bridge\Laravel\Http\Controllers\Faq\ShowApiFaqAction;
@endphp

@extends('layouts.app')

@section('title', __('app.navbar.faq'))

@section('content')
    <h6>{!! __('app.faq.error_notify') !!}</h6>
    <h5>{{ __('app.faq.description') }}</h5>
    <ul>
        <li>{{ __('app.faq.create_competition') }}</li>
        <li>{{ __('app.faq.add_events') }}.</li>
        <li>{{ __('app.faq.store_event') }}.</li>
        <li>{{ __('app.faq.parsing_start') }}.</li>
        <li>{{ __('app.faq.relation_faq') }}.</li>
        <li>{{ __('app.faq.relation_edit') }}.</li>
        <li>{{ __('app.faq.flags_adding') }}.</li>
        <li>{{ __('app.faq.api_link') }} <a href="{{ action(ShowApiFaqAction::class) }}">API</a>.</li>
    </ul>

    <h5>{{ __('app.faq.dev_plan') }}:</h5>
    <ol>
        <li>{{ __('app.faq.dev_protocol') }}.</li>
        <li>{{ __('app.faq.dev_relation') }}.</li>
        <li>{{ __('app.faq.dev_ui') }}.</li>
        <li>{{ __('app.faq.dev_contact_us') }}.</li>
        <li>{{ __('app.faq.dev_exports') }}.</li>
    </ol>

    @auth
        <h5>{{ __('app.faq.check') }}</h5>
        <ol>
            <li>{{ __('app.faq.check_1') }}</li>
            <li>{!! __('app.faq.check_2') !!}</li>
            <li>{!! __('app.faq.check_3') !!}</li>
        </ol>
    @endauth
@endsection
