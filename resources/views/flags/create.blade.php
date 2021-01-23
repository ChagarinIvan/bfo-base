@extends('layouts.app')

@section('title', __('app.flags.create.title'))

@section('content')
    <div class="row">
        <h1>{{ __('app.flags.create.title') }}</h1>
    </div>
    <form class="pt-5" method="POST" action="/flags/store" enctype="multipart/form-data">
        @csrf
        <div class="form-group">
            <label for="name">{{ __('app.flags.name') }}</label>
            <input class="form-control" id="name" name="name">
        </div>
        <div class="form-group">
            <label for="type">{{ __('app.flags.color') }}</label>
            <input class="form-control" id="color" name="color" type="color">
        </div>
        <div class="row">
            <input type="submit" class="btn btn-primary" value="{{ __('app.common.create') }}">
        </div>
    </form>
@endsection
