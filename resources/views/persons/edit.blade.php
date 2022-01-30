@php
    use App\Models\Club;
    use App\Models\Person;
    /**
     * @var Person $person;
     * @var Club[] $clubs;
     */
@endphp

@extends('layouts.app')

@section('title', __('app.person.edit_title'))

@section('content')
    <div class="row">
        <form method="POST"
              action="{{ action(\App\Http\Controllers\Person\UpdatePersonAction::class, [$person]) }}"
        >
            @csrf
            <div class="form-floating mb-3">
                <input class="form-control" id="lastname" name="lastname" value="{{ $person->lastname }}">
                <label for="lastname">{{ __('app.common.lastname') }}</label>
            </div>
            <div class="form-floating mb-3">
                <input class="form-control" id="firstname" name="firstname" value="{{ $person->firstname }}">
                <label for="firstname">{{ __('app.common.name') }}</label>
            </div>
            <div class="form-floating mb-3">
                <input class="form-control" type="date" id="birthday" name="birthday" value="{{ ($person->birthday === null) ? '' : $person->birthday->format('Y-m-d') }}">
                <label for="birthday">{{ __('app.common.birthday') }}</label>
            </div>
            <div class="form-floating mb-3">
                <select class="form-select" id="club_id" name="club_id">
                    <option value="0" {{ $person->club_id === null ? 'selected' : '' }}></option>
                    @foreach($clubs as $club)
                        <option value="{{ $club->id }}" {{ $club->id === $person->club_id ? 'selected' : '' }}>{{ $club->name }}</option>
                    @endforeach
                </select>
                <label for="club_id">{{ __('app.club.name') }}</label>
            </div>
            <div class="col-12">
                <button type="submit"
                        class="btn btn-sm btn-outline-primary me-1"
                >
                    <i class="bi bi-clipboard-check me-1"
                       data-bs-toggle="tooltip"
                       data-bs-placement="top"
                       title="{{ __('app.common.edit') }}"
                    ></i>
                    <span class="d-none d-xl-inline">{{ __('app.common.edit') }}</span>
                </button>
                <x-back-button/>
            </div>
        </form>
    </div>
@endsection
