@php
    use App\Models\Club;
    /**
     * @var Club[] $clubs;
     */
@endphp

@extends('layouts.app')

@section('title', __('app.person.create_title'))

@section('content')
    <div class="row">
        <h1>{{ __('app.person.create_title') }}</h1>
    </div>
    <form class="pt-5" method="POST" action="/persons/store">
        @csrf
        <div class="form-group">
            <label for="lastname">{{ __('app.common.lastname') }}</label>
            <input class="form-control" id="lastname" name="lastname">
        </div>
        <div class="form-group">
            <label for="name">{{ __('app.common.name') }}</label>
            <input class="form-control" id="name" name="name">
        </div>
        <div class="form-group row">
            <label for="birthday" class="col-2 col-form-label">{{ __('app.common.birthday') }}</label>
            <div class="col-10">
                <input class="form-control" type="date" id="birthday" name="birthday">
            </div>
        </div>
        <div class="form-group">
            <label for="club">{{ __('app.club.name') }}</label>
            <input class="form-control" id="club" name="club">
        </div>
{{--        <div class="row">--}}
{{--            <input type="submit" class="btn btn-primary" value="{{ __('app.common.create') }}">--}}
{{--            <a href="/persons" class="btn btn-danger ml-1">{{ __('app.common.cancel') }}</a>--}}
{{--        </div>--}}
    </form>
@endsection
