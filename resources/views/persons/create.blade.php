@php
    use App\Bridge\Laravel\Http\Controllers\Person\StorePersonAction;
    use App\Application\Dto\Club\ViewClubDto;
    /**
     * @var ViewClubDto[] $clubs
     */
@endphp

@extends('layouts.app')

@section('title', __('app.person.edit_title'))

@section('content')
    <div class="row">
        <form method="POST"
              action="{{ action(StorePersonAction::class) }}"
        >
            @csrf
            <div class="form-floating mb-3">
                <input class="form-control @error('lastname') is-invalid @enderror"
                       id="lastname"
                       name="lastname"
                />
                <label for="lastname">{{ __('app.common.lastname') }}</label>
            </div>
            <div class="form-floating mb-3">
                <input class="form-control @error('firstname') is-invalid @enderror"
                       id="firstname"
                       name="firstname"
                />
                <label for="firstname">{{ __('app.common.name') }}</label>
            </div>
            <div class="form-floating mb-3">
                <input class="form-control @error('birthday') is-invalid @enderror"
                       type="date"
                       id="birthday"
                       name="birthday"
                />
                <label for="birthday">{{ __('app.common.birthday') }}</label>
            </div>
            <div class="form-floating mb-3">
                <select class="form-select"
                        id="club_id"
                        name="club_id"
                >
                    <option value="0"></option>
                    @foreach($clubs as $club)
                        <option value="{{ $club->id }}">{{ $club->name }}</option>
                    @endforeach
                </select>
                <label for="club_id">{{ __('app.club.name') }}</label>
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
