@php
    use App\Application\Dto\Competition\ViewCompetitionDto;
    use App\Bridge\Laravel\Http\Controllers\Competition\UpdateCompetitionAction;
    /**
     * @var ViewCompetitionDto $competition;
     */
@endphp

@extends('layouts.app')

@section('title', __('app.competition.edit'))

@section('content')
    <div class="row">
        <form method="POST"
              action="{{ action(UpdateCompetitionAction::class, [$competition->id]) }}"
        >
            @csrf
            <div class="form-floating mb-3">
                <input class="form-control" id="name" name="name" value="{{ $competition->name }}">
                <label for="name">{{ __('app.competition.name') }}</label>
            </div>
            <div class="form-floating mb-3">
                <input class="form-control" id="description" name="description" value="{{ $competition->description }}">
                <label for="description">{{ __('app.competition.description') }}</label>
            </div>
            <div class="form-floating mb-3">
                <input class="form-control" type="date" id="from" name="from"
                       value="{{ $competition->from }}">
                <label for="from">{{ __('app.competition.from_date') }}</label>
            </div>
            <div class="form-floating mb-3">
                <input class="form-control" type="date" id="to" name="to"
                       value="{{ $competition->to }}">
                <label for="to">{{ __('app.competition.to_date') }}</label>
            </div>
            <div class="col-12 col-sm-12 col-md-6 col-lg-4 col-xl-3 col-xxl-2">
                <input type="submit" class="btn btn-outline-primary btn-sm" value="{{ __('app.common.save') }}">
                <x-back-button/>
            </div>
        </form>
    </div>
@endsection
