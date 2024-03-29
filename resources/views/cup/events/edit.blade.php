@php
    use App\Bridge\Laravel\Http\Controllers\CupEvents\UpdateCupEventAction;
    use App\Bridge\Laravel\Http\Controllers\Cups\ShowCupTableAction;
    use App\Models\Cup;
    use App\Models\CupEvent;
    use App\Application\Dto\Event\ViewEventDto;
    /**
     * @var Cup $cup;
     * @var CupEvent $cupEvent;
     * @var ViewEventDto[] $events;
     */
@endphp

@extends('layouts.app')

@section('title', __('app.event.edit'))

@section('content')
    <div class="row mb-3">
        <h4>{{ $cup->name }} - {{ $cup->year }}</h4>
    </div>
    <div class="row mb-3">
        <div class="col-12">
            @foreach($cup->getCupType()->getGroups() as $group)
                <x-badge name="{{ $group->name() }}"
                         url="{{ action(ShowCupTableAction::class, [$cup, $group->id()]) }}"
                />
            @endforeach
        </div>
    </div>
    <div class="row">
        <form method="POST"
              action="{{ action(UpdateCupEventAction::class, [$cup, $cupEvent]) }}"
        >
            @csrf
            <div class="form-floating mb-3">
                <select class="form-select" id="event" name="event">
                    @foreach($events as $event)
                        <option value="{{ $event->id }}"
                                {{ $event->id === $cupEvent->event_id ? 'selected' : ''}}
                        >{{ $event->date." - ".$event->competitionName.' - '.$event->name }}</option>
                    @endforeach
                </select>
                <label for="event">{{ __('app.event.title') }}</label>
            </div>
            <div class="form-floating mb-3">
                <input class="form-control @error('points') is-invalid @enderror" id="points" name="points"
                       value="{{ $cupEvent->points }}">
                <label for="points">@error('points') {{ __($message) }} @else {{ __('app.common.points') }} @enderror</label>
            </div>
            <div class="col-12">
                <input type="submit" class="btn btn-outline-primary btn-sm" value="{{ __('app.common.update') }}">
                <x-back-button/>
            </div>
        </form>
    </div>
@endsection
