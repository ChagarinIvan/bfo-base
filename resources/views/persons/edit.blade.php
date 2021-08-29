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
        <h1>{{ __('app.person.edit_title') }}</h1>
    </div>
    <form class="pt-5"
          method="POST"
          action="{{ action(\App\Http\Controllers\Person\UpdatePersonAction::class, [$person]) }}"
    >
        @csrf
        <div class="form-group">
            <label for="lastname">{{ __('app.common.lastname') }}</label>
            <input class="form-control" id="lastname" name="lastname" value="{{ $person->lastname }}">
        </div>
        <div class="form-group">
            <label for="firstname">{{ __('app.common.name') }}</label>
            <input class="form-control" id="firstname" name="firstname" value="{{ $person->firstname }}">
        </div>
        <div class="form-group row">
            <label for="birthday" class="col-2 col-form-label">{{ __('app.common.birthday') }}</label>
            <div class="col-10">
                <input class="form-control" type="date" id="birthday" name="birthday" value="{{ $person->birthday->format('Y-m-d') }}">
            </div>
        </div>
        <div class="form-group">
            <label for="club_id">{{ __('app.club.name') }}</label>
            <select class="custom-select" id="club_id" name="club_id">
                <option value="0" {{ $person->club_id === null ? 'selected' : '' }}></option>
                @foreach($clubs as $club)
                    <option value="{{ $club->id }}" {{ $club->id === $person->club_id ? 'selected' : '' }}>{{ $club->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="row">
            <input type="submit" class="btn btn-primary" value="{{ __('app.common.edit') }}">
            <a href="{{ action(\App\Http\Controllers\BackAction::class) }}" class="btn btn-danger ml-1">{{ __('app.common.cancel') }}</a>
        </div>
    </form>
@endsection
