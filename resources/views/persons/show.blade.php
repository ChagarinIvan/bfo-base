@php
    use App\Models\Person;
    /**
     * @var Person $person
     */
@endphp

@extends('layouts.app')

@section('title', $person->lastname)

@section('content')
    <h3>{{ $person->lastname }} {{ $person->firstname }}</h3>
    @foreach($person->protocolLines as $line)
        <h4>{{ $line->event->name }} : {{ $line->rank }}</h4>
    @endforeach
@endsection
