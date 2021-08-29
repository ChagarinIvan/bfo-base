@php
    use App\Models\Event;
    use App\Models\Flag;
    use Illuminate\Support\Collection;
    /**
     * @var Flag $flag;
     * @var Collection|Event[] $events;
     */
@endphp


@extends('layouts.app')

@section('title', \Illuminate\Support\Str::limit($flag->name, 20, '...'))

@section('content')
    <div class="row">
        <h1 style="color: {{ $flag->color }}">{{ $flag->name }}</h1>
    </div>
    <div class="row pt-5">
        <a class="btn btn-danger mr-2" href="{{ action(\App\Http\Controllers\BackAction::class) }}">{{ __('app.common.back') }}</a>
    </div>
    <div class="row pt-3">
        <table class="table table-bordered pt-3" id="table">
            <thead>
            <tr>
                <th scope="col">{{ __('app.competition.title') }}</th>
                <th scope="col">{{ __('app.common.title') }}</th>
                <th scope="col">{{ __('app.common.description') }}</th>
                <th scope="col">{{ __('app.common.date') }}</th>
                <th scope="col">{{ __('app.common.competitors') }}</th>
                @auth<th scope="col"></th>@endauth
            </tr>
            </thead>
            <tbody>
            @foreach ($events as $event)
                <tr>
                    <td>
                        <a href="{{ action(\App\Http\Controllers\Competition\ShowCompetitionAction::class, [$event->competition]) }}">{{ $event->competition->name }}</a>
                    </td>
                    <td>
                        <a href="{{ action(\App\Http\Controllers\Event\ShowEventAction::class, [$event->id]) }}">{{ $event->name }}</a>
                    </td>
                    <td><small>{{ \Illuminate\Support\Str::limit($event->description, 100, '...') }}</small></td>
                    <td>{{ $event->date->format('Y-m-d') }}</td>
                    <td>{{ count($event->protocolLines) }}</td>
                    @auth
                        <td>
                            <a href="{{ action(\App\Http\Controllers\Event\ShowAddFlagToEventFormAction::class, [$event]) }}"
                               class="text-info"
                            >{{ __('app.common.add_flags') }}</a>
                        </td>
                    @endauth
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
@endsection
