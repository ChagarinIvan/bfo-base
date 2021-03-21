@extends('layouts.app')

@section('title', '404...')

@section('content')
    <h3>{{ __('app.errors.title') }}.</h3>
    <h5>{{ __('app.errors.error') }}.</h5>
    <ul>
        <li>{{ __('app.errors.description') }}.</li>
        <li>{{ __('app.errors.next') }}.</li>
        <li>{{ __('app.errors.thanks') }}.</li>
    </ul>
@endsection
