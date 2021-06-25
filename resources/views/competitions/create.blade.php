@extends('layouts.app')

@section('title', __('app.competition.add'))

@section('content')
    <div class="row">
        <h1>{{ __('app.competition.add') }}</h1>
    </div>
    <form class="pt-5" method="POST" action="/competitions/store">
        @csrf
        <div class="form-group">
            <label for="name">{{ __('app.competition.name') }}</label>
            <input class="form-control" id="name" name="name">
        </div>
        <div class="form-group">
            <label for="description">{{ __('app.competition.description') }}</label>
            <input class="form-control" id="description" name="description">
        </div>
        <div class="form-group row">
            <label for="from" class="col-2 col-form-label">{{ __('app.competition.from_date') }}</label>
            <div class="col-10">
                <input class="form-control" type="date" id="from" name="from">
            </div>
        </div>
        <div class="form-group row">
            <label for="to" class="col-2 col-form-label">{{ __('app.competition.to_date') }}</label>
            <div class="col-10">
                <input class="form-control" type="date" id="to" name="to">
            </div>
        </div>
        <div class="row">
            <input type="submit" class="btn btn-primary" value="{{ __('app.common.create') }}">
            <a href="/competitions" class="btn btn-danger ml-1">{{ __('app.common.cancel') }}</a>
        </div>
    </form>
@endsection
