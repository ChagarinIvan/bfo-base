@php
    use App\Bridge\Laravel\Http\Controllers\Cup\StoreCupAction;
    use App\Domain\Cup\CupType;
@endphp

@extends('layouts.app')

@section('title', __('app.cup.add'))

@section('content')
    <div class="row">
        <form method="POST" action="{{ action(StoreCupAction::class) }}">
            @csrf
            <div class="form-floating mb-3">
                <input class="form-control" id="name" name="name">
                <label for="name">{{ __('app.cup.name') }}</label>
            </div>
            <div class="form-floating mb-3">
                <select class="form-select" id="type" name="type">
                    @foreach(CupType::cases() as $cupType)
                        <option value="{{ $cupType->value }}" {{ $loop->first ? 'selected' : '' }}>{{ __("app.cup.type.$cupType->value") }}</option>
                    @endforeach
                </select>
                <label for="type">{{ __('app.common.type') }}</label>
            </div>
            <div class="form-floating mb-3">
                <input class="form-control" id="eventsCount" name="eventsCount">
                <label for="events_count">{{ __('app.cup.events_count') }}</label>
            </div>
            <div class="form-floating mb-3">
                <select class="form-select" id="year" name="year">
                    @foreach(App\Models\Year::cases() as $year)
                        <option value="{{ $year->value }}">{{ $year->value }}</option>
                    @endforeach
                </select>
                <label for="year">{{ __('app.common.year') }}</label>
            </div>
            <div class="form-floating mb-5 form-check form-switch">
                <input class="form-check-input" type="checkbox" id="visible" name="visible" checked>
                <label class="form-check-label" for="visible">{{ __('app.common.visible') }}</label>
            </div>
            <div class="col-12 col-sm-12 col-md-6 col-lg-4 col-xl-3 col-xxl-2">
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
