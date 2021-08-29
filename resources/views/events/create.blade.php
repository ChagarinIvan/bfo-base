@php
    use \App\Models\Flag;
    use Illuminate\Support\Collection;
    /**
     * @var Flag[]|Collection $flags;
     */
@endphp

@extends('layouts.app')

@section('title', __('app.event.add'))

@section('content')
    <div class="row">
        <h1>{{ __('app.event.add') }}</h1>
    </div>
    <form class="pt-5"
          method="POST"
          action="{{ action(\App\Http\Controllers\Event\StoreEventAction::class, [$competitionId]) }}"
          enctype="multipart/form-data"
    >
        @csrf
        <div class="form-group">
            <label for="name">{{ __('app.common.title') }}</label>
            <input class="form-control" id="name" name="name">
        </div>
        <div class="form-group">
            <label for="description">{{ __('app.common.description') }}</label>
            <input class="form-control" id="description" name="description">
        </div>
        <div class="form-group row">
            <label for="date" class="col-2 col-form-label">{{ __('app.common.date') }}</label>
            <div class="col-10">
                <input class="form-control" type="date" id="date" name="date">
            </div>
        </div>
        <div class="form-group">
            <input class="form-control" type="file" name="protocol"/>
        </div>
        <div class="row">
            <input type="submit" class="btn btn-primary" value="{{ __('app.common.create') }}">
            <a href="{{ action(\App\Http\Controllers\Competition\ShowCompetitionAction::class, [$competitionId]) }}"
               class="btn btn-danger ml-1"
            >{{ __('app.common.cancel') }}</a>
        </div>
    </form>
@endsection
