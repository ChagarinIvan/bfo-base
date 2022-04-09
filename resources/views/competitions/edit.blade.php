@php
    use App\Models\Competition;
    /**
     * @var int $year;
     * @var Competition $competition;
     */
@endphp

@extends('layouts.app')

@section('title', __('app.competition.edit'))

@section('content')
    <div class="row">
        <form method="POST"
              action="{{ action(\App\Http\Controllers\Competition\UpdateCompetitionAction::class) }}"
        >
            @csrf
            <div class="form-floating mb-3">
                <input name="name" class="form-control" id="name" value="{{ $competition->name }}">
                <label for="name">{{ __('app.competition.name') }}</label>
            </div>
            <div class="form-floating mb-3">
                <input class="form-control" id="description" name="description" value="{{ $competition->description }}">
                <label for="description">{{ __('app.competition.description') }}</label>
            </div>
            <div class="form-floating mb-3">
                <input class="form-control" type="date" id="from" name="from" value="{{ $competition->from->format('Y-m-d') }}">
                <label for="from">{{ __('app.competition.from_date') }}</label>
            </div>
            <div class="form-floating mb-3">
                <input class="form-control" type="date" id="to" name="to" value="{{ $competition->to->format('Y-m-d') }}">
                <label for="to">{{ __('app.competition.to_date') }}</label>
            </div>
            <div class="col-12 col-sm-12 col-md-6 col-lg-4 col-xl-3 col-xxl-2">
                <input type="submit" class="btn btn-outline-primary btn-sm" value="{{ __('app.common.save') }}">
                <x-back-button/>
            </div>
        </form>
    </div>
@endsection
