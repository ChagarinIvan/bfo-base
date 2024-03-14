@php
    use App\Bridge\Laravel\Http\Controllers\Club\StoreClubsAction;
@endphp

@extends('layouts.app')

@section('title', __('app.clubs.create.title'))

@section('content')
    <div class="row">
        <form method="POST"
              action="{{ action(StoreClubsAction::class) }}"
              enctype="multipart/form-data"
        >
            @csrf
            <div class="form-floating mb-3">
                <input class="form-control" id="name" name="name">
                <label for="name">{{ __('app.clubs.name') }}</label>
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
