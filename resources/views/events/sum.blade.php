@php
    use App\Bridge\Laravel\Http\Controllers\Event\UnitEventsAction;
    use App\Facades\Color;
    use App\Models\Competition;
    /**
     * @var Competition $competition;
     */
@endphp

@extends('layouts.app')

@section('title', __('app.competition.sum'))

@section('content')
    <div class="row">
        <form method="POST"
              action="{{ action(UnitEventsAction::class, [$competition->id]) }}"
              enctype="multipart/form-data"
        >
            @csrf
            <div class="form-group mb-3">
                <select class="selectpicker form-control" multiple id="events" name="events[]'"
                        title="{{ __('app.cup.events') }}">
                    @foreach($competition->events as $event)
                        @php
                            $eventName = $event->date->format('d.m').' - '.$event->name;
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
