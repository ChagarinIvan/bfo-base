@php
    use App\Models\Club;
    /**
     * @var Club[] $clubs;
     */
@endphp

@extends('layouts.app')

@section('title', __('app.person.create_title'))

@section('content')
    <div class="row">
        <form method="POST"
              action="{{ action(\App\Http\Controllers\Person\StorePersonAction::class) }}"
        >
            @csrf
            <div class="form-floating mb-3">
                <input class="form-control" id="lastname" name="lastname">
                <label for="lastname">{{ __('app.common.lastname') }}</label>
            </div>
            <div class="form-floating mb-3">
                <input class="form-control" id="name" name="firstname">
                <label for="firstname">{{ __('app.common.name') }}</label>
            </div>
            <div class="form-floating mb-3">
                <input class="form-control" type="date" id="birthday" name="birthday">
                <label for="birthday">{{ __('app.common.birthday') }}</label>
            </div>
            <div class="form-floating mb-3">
                <select class="form-select" id="club_id" name="club_id">
                    <option value="0"></option>
                    @foreach($clubs as $club)
                        <option value="{{ $club->id }}">{{ $club->name }}</option>
                    @endforeach
                </select>
                <label for="club_id">{{ __('app.club.name') }}</label>
            </div>
            <div class="col-12">
                <input type="submit" class="btn btn-primary" value="{{ __('app.common.create') }}">
                <x-back-button/>
            </div>
        </form>
    </div>
@endsection
