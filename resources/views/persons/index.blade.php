@extends('layouts.app')

@section('title', __('app.navbar.persons'))

@section('content')
    <Persons @auth init-auth @endauth></Persons>
@endsection
