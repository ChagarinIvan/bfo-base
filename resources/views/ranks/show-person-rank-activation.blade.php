@php
    use App\Bridge\Laravel\Http\Controllers\Rank\ActivatePersonRankAction;use App\Domain\Person\Person;use App\Models\Rank;
    /**
     * @var Person $year;
     * @var Rank $rank;
     */
@endphp

@extends('layouts.app')

@section('title', __('app.rank.activation'))

@section('content')
    <div class="row">
        <form method="POST"
              action="{{ action(ActivatePersonRankAction::class, [$person, $rank]) }}"
        >
            @csrf
            <div class="form-floating mb-3">
                <input class="form-control" type="date" id="date" name="date">
                <label for="date">{{ __('app.common.date') }}</label>
            </div>
            <div class="col-12">
                <button type="submit"
                        class="btn btn-sm btn-outline-primary me-1"
                >
                    <i class="bi bi-clipboard-check me-1"
                       data-bs-toggle="tooltip"
                       data-bs-placement="top"
                       title="{{ __('app.rank.submit') }}"
                    ></i>
                    <span class="d-none d-xl-inline">{{ __('app.rank.submit') }}</span>
                </button>
                <x-back-button/>
            </div>
        </form>
    </div>
@endsection
