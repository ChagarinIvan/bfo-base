@php
    use App\Application\Dto\Cup\ViewCupDto;
    use App\Application\Dto\Event\ViewEventDto;
    use App\Bridge\Laravel\Http\Controllers\Cup\ShowCupTableAction;
    use App\Bridge\Laravel\Http\Controllers\CupEvents\UpdateCupEventAction;
    use App\Domain\Cup\CupEvent\CupEvent;
    use Illuminate\Support\Str;
    /**
     * @var ViewCupDto $cup;
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
            @foreach($cup->groups as $group)
                <x-badge name="{{ $group->name }}"
                         url="{{ action(ShowCupTableAction::class, [$cup->id, $group->id]) }}"
                />
            @endforeach
        </div>
    </div>
    <div class="row">
        <form method="POST" action="{{ action(UpdateCupEventAction::class, [$cup->id, $cupEvent]) }}">
            @csrf
            <div class="form-floating mb-3">
                <select class="form-select" id="event" name="event">
                    @foreach($events as $event)
                        <option value="{{ $event->id }}" {{ $event->id === (string) $cupEvent->event_id ? 'selected' : ''}}>{{ $event->date." - ".$event->competitionName.' - '.$event->name . '(' . Str::limit($event->description, 30). ')' }}</option>
                    @endforeach
                </select>
                <label for="event">{{ __('app.event.title') }}</label>
            </div>
            <div class="form-floating mb-3">
                <input class="form-control @error('points') is-invalid @enderror" id="points" name="points" value="{{ $cupEvent->points }}">
                <label for="points">@error('points') {{ __($message) }} @else {{ __('app.common.points') }} @enderror</label>
            </div>
            <div class="col-12">
                <input type="submit" class="btn btn-outline-primary btn-sm" value="{{ __('app.common.update') }}">
                <x-back-button/>
            </div>
        </form>
    </div>
@endsection
