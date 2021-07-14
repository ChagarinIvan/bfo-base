@php
    use App\Models\Competition;
    /**
     * @var Competition $competition;
     */
@endphp

@extends('layouts.app')

@section('title', __('app.competition.sum'))

@section('content')
    <div class="row">
        <h1>{{ __('app.competition.sum') }}</h1>
    </div>
    <form class="pt-5" method="POST" action="/competitions/{{ $competition->id }}/events/unit" enctype="multipart/form-data">
        @csrf
        <div class="form-group">
            <label for=events" class="col-2 col-form-label">{{ __('app.cup.events') }}</label>
            <select class="selectpicker form-control" multiple id="events" name="events[]'">
                @foreach($competition->events as $event)
                    @php
                        $eventName = $event->date->format('d.m').' - '.$event->competition->name.' - '.$event->name;
                    @endphp
                    <option value="{{ $event->id }}"
                            data-content="<span class='badge' style='background: {{ \Color::getColor($eventName) }}'
                                          >{{ $eventName }}</span>"
                    >{{ $eventName }}</option>
                @endforeach
            </select>
        </div>
        <div class="row">
            <input type="submit" class="btn btn-primary" value="{{ __('app.common.sum') }}">
            <a href="/competitions/{{ $competition->id }}/show" class="btn btn-danger ml-1">{{ __('app.common.cancel') }}</a>
        </div>
    </form>
@endsection

@section('script')
    <script>
        $('#events').selectpicker();
    </script>
@endsection
