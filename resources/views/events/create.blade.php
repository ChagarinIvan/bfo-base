@php
    use App\Models\Flag;
    use Illuminate\Support\Collection;
    /**
     * @var Flag[]|Collection $flags;
     */
@endphp

@extends('layouts.app')

@section('title', __('app.event.add'))

@section('content')
    <div class="row">
        <form method="POST"
              action="{{ action(\App\Http\Controllers\Event\StoreEventAction::class, [$competitionId]) }}"
              enctype="multipart/form-data"
        >
            @csrf
            <div class="form-floating mb-3">
                <input class="form-control" id="name" name="name">
                <label for="name">{{ __('app.common.title') }}</label>
            </div>
            <div class="form-floating mb-3">
                <input class="form-control" id="description" name="description">
                <label for="description">{{ __('app.common.description') }}</label>
            </div>
            <div class="form-floating mb-3">
                <input class="form-control" type="date" id="date" name="date">
                <label for="date">{{ __('app.common.date') }}</label>
            </div>

            @include('events.accordion')

            <div class="col-12 col-sm-12 col-md-6 col-lg-4 col-xl-3 col-xxl-2">
                <input type="submit" class="btn btn-outline-primary btn-sm" value="{{ __('app.common.create') }}">
                <x-back-button/>
            </div>
        </form>
    </div>
@endsection
