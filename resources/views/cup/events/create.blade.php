@php
    use \App\Models\Cup;
    use App\Models\Event;
    use Illuminate\Support\Collection;
    /**
     * @var Cup $cup;
     * @var Collection|Event[] $events;
     */
@endphp

@extends('layouts.app')

@section('title', __('app.event.add'))

@section('content')
    <div class="row mb-3">
        <h4>{{ $cup->name }} - {{ $cup->year }}</h4>
    </div>
    <div class="row mb-3">
        <div class="col-12">
            @foreach($cup->getCupType()->getGroups() as $group)
                <x-badge color="{{ \App\Facades\Color::getColor($group->name) }}"
                         name="{{ $group->name }}"
                />
            @endforeach
        </div>
    </div>
    <div class="row">
        <form method="POST"
              action="{{ action(\App\Http\Controllers\CupEvents\StoreCupEventAction::class, [$cup]) }}"
        >
            @csrf
            <div class="form-floating mb-3">
                <select class="form-select" id="event" name="event">
                    @foreach($events as $event)
                        <option value="{{ $event->id }}">{{ $event->date->format('d.m')." - ".$event->competition->name.' - '.$event->name }}</option>
                    @endforeach
                </select>
                <label for="event">{{ __('app.event.title') }}</label>
            </div>
            <div class="form-floating mb-3">
                <input class="form-control" id="points" name="points" value="{{ 1000 }}">
                <label for="points">{{ __('app.common.points') }}</label>
            </div>
            <div class="col-12">
                <input type="submit" class="btn btn-outline-primary btn-sm" value="{{ __('app.common.new') }}">
                <x-back-button/>
            </div>
        </form>
    </div>
@endsection
