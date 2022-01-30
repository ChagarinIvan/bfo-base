@php
    /**
     * @var int $year;
     */
@endphp

@extends('layouts.app')

@section('title', __('app.competition.add'))

@section('content')
    <div class="row">
        <form method="POST"
              action="{{ action(\App\Http\Controllers\Competition\StoreCompetitionAction::class) }}"
        >
            @csrf
            <div class="form-floating mb-3">
                <input name="name" class="form-control" id="name">
                <label for="name">{{ __('app.competition.name') }}</label>
            </div>
            <div class="form-floating mb-3">
                <input class="form-control" id="description" name="description">
                <label for="description">{{ __('app.competition.description') }}</label>
            </div>
            <div class="form-floating mb-3">
                <input class="form-control" type="date" id="from" name="from">
                <label for="from">{{ __('app.competition.from_date') }}</label>
            </div>
            <div class="form-floating mb-3">
                <input class="form-control" type="date" id="to" name="to">
                <label for="to">{{ __('app.competition.to_date') }}</label>
            </div>
            <div class="col-12">
                <button type="submit"
                        class="btn btn-sm btn-outline-primary me-1"
                >
                    <i class="bi bi-clipboard-check me-1"
                       data-bs-toggle="tooltip"
                       data-bs-placement="top"
                       title="{{ __('app.common.create') }}"
                    ></i>
                    <span class="d-none d-xl-inline">{{ __('app.common.create') }}</span>
                </button>
                <x-back-button/>
            </div>
        </form>
    </div>
@endsection
