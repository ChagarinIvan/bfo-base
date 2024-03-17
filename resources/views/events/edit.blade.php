@php
    use App\Bridge\Laravel\Http\Controllers\Event\UpdateEventAction;
    use \App\Application\Dto\Event\ViewEventDto;
    /**
     * @var ViewEventDto  $event;
     */
@endphp

@extends('layouts.app')

@section('title', __('app.event.edit'))

@section('content')
    <div class="row">
        <form method="POST"
              action="{{ action(UpdateEventAction::class, [$event->id]) }}"
              enctype="multipart/form-data"
        >
            @csrf
            <div class="form-floating mb-3">
                <input class="form-control" id="name" name="name" value="{{ $event->name }}" />
                <label for="name">{{ __('app.common.title') }}</label>
            </div>
            <div class="form-floating mb-3">
                <input class="form-control" id="description" name="description" value="{{ $event->description }}" />
                <label for="description">{{ __('app.competition.description') }}</label>
            </div>
            <div class="form-floating mb-3">
                <input class="form-control" type="date" id="date" name="date"
                       value="{{ $event->date }}">
                <label for="date">{{ __('app.common.date') }}</label>
            </div>

            @include('events.accordion')

            <div class="col-12 col-sm-12 col-md-6 col-lg-4 col-xl-3 col-xxl-2">
                <input type="submit" class="btn btn-outline-primary btn-sm" value="{{ __('app.common.save') }}">
                <x-back-button/>
            </div>
        </form>
    </div>
@endsection
