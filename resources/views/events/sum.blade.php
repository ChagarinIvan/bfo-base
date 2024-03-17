@php
    use App\Bridge\Laravel\Facades\Color;
    use App\Bridge\Laravel\Http\Controllers\Event\UnitEventsAction;
    use \App\Application\Dto\Event\ViewEventDto;
    /**
     * @var string $competitionId;
     * @var ViewEventDto[] $events;
     */
@endphp

@extends('layouts.app')

@section('title', __('app.competition.sum'))

@section('content')
    <div class="row">
        <form method="POST"
              action="{{ action(UnitEventsAction::class, [$competitionId]) }}"
              enctype="multipart/form-data"
        >
            @csrf
            <div class="form-group mb-3">
                <select class="selectpicker form-control" multiple id="events" name="events[]'"
                        title="{{ __('app.cup.events') }}">
                    @foreach($events as $event)
                        @php
                            $eventName = $event->date.' - '.$event->name;
                        @endphp
                        <option value="{{ $event->id }}"
                                data-content="<span class='badge' style='background: {{ Color::getColor($eventName) }}'>{{ $eventName }}</span>"
                        >{{ $eventName }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-12">
                <input type="submit" class="btn btn-outline-primary btn-sm" value="{{ __('app.common.sum') }}">
                <x-back-button/>
            </div>
        </form>
    </div>
@endsection
