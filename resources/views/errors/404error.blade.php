@php
    use App\Bridge\Laravel\Http\Controllers\Competition\ShowCompetitionsListAction;
@endphp

@extends('layouts.app')

@section('title', '404...')

@section('content')
    <h3>{{ __('app.errors.title') }}.</h3>
    <h5>{{ __('app.errors.404error') }}.</h5>
    <ul>
        <li>
            <a href="{{ action(ShowCompetitionsListAction::class, [(string)\App\Models\Year::actualYear()->value]) }}">{{ __('app.errors.description404') }}
                .</a></li>
        <li>{{ __('app.errors.support') }}.</li>
        <li>{{ __('app.errors.thanks') }}.</li>
    </ul>
@endsection
