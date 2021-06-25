@php
    use \App\Models\Flag;
    use \App\Models\Event;
    use Illuminate\Support\Collection;
    /**
     * @var Flag[]|Collection $flags;
     * @var Event $event;
     */
    $eventFlags = $event->flags;
    $eventFlags = $eventFlags->keyBy('id');
@endphp

@extends('layouts.app')

@section('title', __('app.flags.add_flags_title'))

@section('content')
    <h3>{{ __('app.flags.add_flags_title') }}</h3>
    <h4>{{ __('app.event.title') }} — {{ $event->name }}</h4>
    <div class="row pt-2">
        <a class="btn btn-success mr-2" href="/competitions/{{ $event->competition_id }}/show">{{ __('app.common.back') }}</a>
    </div>
    <div class="row pt-3">
        @foreach($eventFlags as $flag)
            <span class="badge" style="background: {{ $flag->color }}"><a href="/flags/{{ $flag->id }}/show-events">{{ $flag->name }}</a></span>
        @endforeach
    </div>
    <div class="row pt-3">
        <table class="table table-bordered" id="table">
            <thead>
            <tr class="table-info">
                <th>{{ __('app.common.title') }}</th>
                <th>{{ __('app.common.description') }}</th>
                <th></th>
            </tr>
            </thead>
            <tbody>
            @foreach ($flags as $flag)
                <tr style="background: {{ $flag->color }}">
                    <td>{{ $flag->name }}</td>
                    <td></td>
                    <td>
                        @if(!$eventFlags->has($flag->id))
                            <a href="/competitions/events/{{ $event->id }}/set-flag/{{ $flag->id }}">{{ __('app.common.new') }}</a>
                        @else
                            <a href="/competitions/events/{{ $event->id }}/delete-flag/{{ $flag->id }}">{{ __('app.common.delete') }}</a>
                        @endif
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
@endsection
