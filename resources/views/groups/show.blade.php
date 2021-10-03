@php
    use App\Models\Group;
    /**
     * @var Group $group
     */
@endphp

@extends('layouts.app')

@section('title', __('app.common.group').' '.$group->name)

@section('content')
    <h3>{{ $group->name }}</h3>
    <div class="row">
        <a class="btn btn-danger mr-2"
           href="{{ action(\App\Http\Controllers\BackAction::class) }}"
        >{{ __('app.common.back') }}</a>
    </div>
    <div class="row pt-3">
        <table class="table table-bordered" id="table">
            <thead>
            <tr class="table-info">
                <th>{{ __('app.competition.title') }}</th>
                <th>{{ __('app.event.title') }}</th>
                <th>{{ __('app.common.competitors') }}</th>
            </tr>
            </thead>
            <tbody>
            @foreach ($group->distances as $distance)
                <tr>
                    <td>
                        <a href="{{ action(\App\Http\Controllers\Competition\ShowCompetitionAction::class, [$distance->event->competition_id]) }}">
                            {{ $distance->event->competition->name }}
                        </a>
                    </td>
                    <td>
                        <a href="{{ action(\App\Http\Controllers\Event\ShowEventAction::class, [$distance->event_id]) }}#{{ $group->id }}">
                            {{ $distance->event->name }}
                        </a>
                    </td>
                    <td>{{ $distance->event->protocolLines->count() }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
@endsection
