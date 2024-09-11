@php
    use App\Bridge\Laravel\Http\Controllers\Rank\UpdateRankActivationDateAction;
    use App\Application\Dto\Rank\ViewRankDto;
    /**
     * @var ViewRankDto $rank;
     */
@endphp

@extends('layouts.app')

@section('title', __('app.rank.activation'))

@section('content')
    <div class="row">
        <form method="POST" action="{{ action(UpdateRankActivationDateAction::class, [$rank->id]) }}">
            @csrf
            <div class="form-floating mb-3">
                <input class="form-control" type="date" id="date" name="date" value="{{ $rank->startDate }}">
                <label for="date">{{ __('app.common.date') }}</label>
            </div>
            <div class="col-12">
                <button type="submit" class="btn btn-sm btn-outline-primary me-1">
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
