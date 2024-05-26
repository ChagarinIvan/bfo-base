@php
    use App\Bridge\Laravel\Http\Controllers\Person\UpdatePersonAction;
    use App\Application\Dto\Person\ViewPersonDto;
    use App\Application\Dto\Club\ViewClubDto;
    /**
     * @var ViewPersonDto $person
     * @var ViewClubDto[] $clubs
     */
@endphp

@extends('layouts.app')

@section('title', __('app.person.edit_title'))

@section('content')
    <div class="row">
        <form method="POST"
              action="{{ action(UpdatePersonAction::class, [$person->id]) }}"
        >
            @csrf
            <div class="form-floating mb-3">
                <input class="form-control @error('lastname') is-invalid @enderror"
                       id="lastname"
                       name="lastname"
                       value="{{ $person->lastname }}"
                />
                <label for="lastname">{{ __('app.common.lastname') }}</label>
            </div>
            <div class="form-floating mb-3">
                <input class="form-control @error('firstname') is-invalid @enderror"
                       id="firstname"
                       name="firstname"
                       value="{{ $person->firstname }}"
                />
                <label for="firstname">{{ __('app.common.name') }}</label>
            </div>
            <div class="form-floating mb-3">
                <input class="form-control @error('birthday') is-invalid @enderror"
                       type="date"
                       id="birthday"
                       name="birthday"
                       value="{{ $person->birthday ?: '' }}"
                />
                <label for="birthday">{{ __('app.common.birthday') }}</label>
            </div>
            <div class="form-floating mb-3">
                <select class="form-control @error('club_id') is-invalid @enderror"
                        id="clubId"
                        name="clubId"
                >
                    <option value="0"
                            @if(empty($person->clubId))
                                selected
                            @endif
                    ></option>
                    @foreach($clubs as $club)
                        <option value="{{ $club->id }}"
                                @if($club->id === $person->clubId)
                                    selected
                                @endif
                        >{{ $club->name }}</option>
                    @endforeach
                </select>
                <label for="clubId">{{ __('app.club.name') }}</label>
            </div>
            <div class="col-12 col-sm-12 col-md-6 col-lg-4 col-xl-3 col-xxl-2">
                <input type="submit" class="btn btn-outline-primary btn-sm" value="{{ __('app.common.save') }}">
                <x-back-button/>
            </div>
        </form>
    </div>
@endsection
