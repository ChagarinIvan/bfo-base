@php
    use \App\Models\Event;
    use \App\Models\Flag;
    use Illuminate\Support\Collection;
    /**
     * @var Event $event;
     * @var Flag[]|Collection $flags;
     */
@endphp

@extends('layouts.app')

@section('title', __('app.event.edit'))

@section('content')
    <div class="row">
        <h1>{{ __('app.event.edit') }}</h1>
    </div>
    <form class="pt-5" method="POST" action="/competitions/events/{{ $event->id }}/update" enctype="multipart/form-data">
        @csrf
        <div class="form-group">
            <label for="name">{{ __('app.common.title') }}</label>
            <input class="form-control" id="name" name="name" value="{{ $event->name }}"/>
        </div>
        <div class="form-group">
            <label for="description">{{ __('app.competition.description') }}</label>
            <input class="form-control" id="description" name="description" value="{{ $event->description }}">
        </div>
        <div class="form-group row">
            <label for="date" class="col-2 col-form-label">{{ __('app.common.date') }}</label>
            <div class="col-10">
                <input class="form-control" type="date" id="date" name="date" value="{{ $event->date->format('Y-m-d') }}">
            </div>
        </div>
        <div class="form-group">
            <label for="protocol" class="col-2 col-form-label">{{ __('app.protocol') }}</label>
            <p>{{ __('app.protocol-hint') }}</p>
            <input class="form-control" type="file" name="protocol" id="protocol"/>
        </div>
        <div class="row">
            <input type="submit" class="btn btn-primary" value="{{ __('app.common.save') }}">
            <a href="/competitions/events/{{ $event->id }}/show" class="btn btn-danger ml-1">{{ __('app.common.cancel') }}</a>
        </div>
    </form>
@endsection
