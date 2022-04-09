@extends('layouts.app')

@section('title', __('app.navbar.persons'))

@section('content')
    <persons :auth=@auth"1"@else"0"@endauth></persons>
@endsection
