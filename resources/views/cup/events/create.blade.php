@php
    use App\Application\Dto\Event\ViewEventDto;
    use App\Application\Dto\Cup\ViewCupDto;
    use App\Bridge\Laravel\Http\Controllers\CupEvents\StoreCupEventAction;
    /**
     * @var ViewCupDto $cup;
     * @var ViewEventDto[] $events;
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
            @foreach($cup->groups as $group)
                <x-badge name="{{ $group->name }}"/>
            @endforeach
        </div>
    </div>
    <div class="row">
        <form method="POST" action="{{ action(StoreCupEventAction::class, [$cup->id]) }}">
            @csrf
            <div class="form-floating mb-3">
                <select class="form-select" id="event" name="event">
                    @foreach($events as $event)
                        <option value="{{ $event->id }}">{{ $event->date." - ".$event->competitionName.' - '.$event->name }}</option>
                    @endforeach
                </select>
                <label for="event">{{ __('app.event.title') }}</label>
            </div>
            <div class="form-floating mb-3">
                <input class="form-control @error('points') is-invalid @enderror" id="points" name="points" value="{{ 1000 }}">
                <label for="points">@error('points') {{ __($message) }} @else {{ __('app.common.points') }} @enderror</label>
            </div>
            <div class="col-12">
                <input type="submit" class="btn btn-outline-primary btn-sm" value="{{ __('app.common.new') }}">
                <x-back-button/>
            </div>
        </form>
    </div>
@endsection
