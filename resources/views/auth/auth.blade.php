@php
    use App\Bridge\Laravel\Http\Controllers\Login\ShowLoginFormAction;
    /**
     * @var string $email;
     */
@endphp

@extends('layouts.app')

@section('title', __('app.common.registration'))

@section('content')
    <h3>{{ __('app.registration.success') }}.</h3>
    <h5>{{ __('app.registration.send-email') }}.</h5>
    <a href="{{ action(ShowLoginFormAction::class) }}">{{ __('app.common.sign-in') }}</a>
@endsection
