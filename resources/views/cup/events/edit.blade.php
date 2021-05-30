@php
    use \App\Models\Cup;
    use App\Models\CupEvent;
    use App\Models\Event;
    use Illuminate\Support\Collection;
    /**
     * @var Cup $cup;
     * @var CupEvent $cupEvent;
     * @var Collection|Event[] $events;
     */
@endphp

@extends('layouts.app')

@section('title', Str::limit($cup->name, 20, '...'))

@section('content')
    <div class="row">
        <h1>{{ $cup->name }} - {{ $cup->year }}</h1>
    </div>
    <div class="row">
        <h3>{{ __('app.event.edit') }}</h3>
    </div>
    <div class="row pt-3">
        @foreach($cup->groups as $group)
            <span class="badge" style="background: {{ \App\Facades\Color::getColor($group->name) }}">{{ $group->name }}</span>
        @endforeach
    </div>
    <form class="pt-5" method="POST" action="/cups/{{ $cup->id }}/events/{{ $cupEvent->id }}/update">
        @csrf
        <div class="form-group">
            <label for="event">{{ __('app.event.title') }}</label>
            <select class="custom-select" id="event" name="event">
                @foreach($events as $event)
                    <option value="{{ $event->id }}"
                        {{ $event->id === $cupEvent->event_id ? 'selected' : ''}}
                    >{{ $event->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group">
            <label for="points">{{ __('app.common.points') }}</label>
            <input class="form-control" id="points" name="points" value="{{ $cupEvent->points }}">
        </div>
        <div class="row">
            <input type="submit" class="btn btn-primary" value="{{ __('app.common.update') }}">
            <a href="/cups/{{ $cup->id }}/show" class="btn btn-danger ml-1">{{ __('app.common.cancel') }}</a>
        </div>
    </form>
@endsection