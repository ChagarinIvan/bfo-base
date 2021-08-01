@php
    /**
     * @var string $email;
     */
@endphp
@extends('layouts.app')

@section('title', __('app.common.registration'))

@section('content')
    <h3>{{ __('app.registration.title') }}.</h3>
    <h5>{{ __('app.registration.email') }}.</h5>
    <h5>{{ $email }}.</h5>
@endsection
