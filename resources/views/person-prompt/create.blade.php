@php
    use App\Bridge\Laravel\Http\Controllers\PersonPrompt\StorePromptAction;
    /**
     * @var int $personId
     */
@endphp

@extends('layouts.app')

@section('title', __('app.prompt.create'))

@section('content')
    <div class="row">
        <form method="POST"
              action="{{ action(StorePromptAction::class, [$personId]) }}"
        >
            @csrf
            <div class="form-floating mb-3">
                <input class="form-control @error('prompt') is-invalid @enderror"
                       id="prompt"
                       name="prompt"
                />
                <label for="prompt">{{ __('app.common.prompt') }}</label>
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
