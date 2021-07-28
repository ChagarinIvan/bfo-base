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

@section('title', Str::limit($flag->name, 20, '...'))

@section('content')
    <div class="row">
        <h1 style="color: {{ $flag->color }}">{{ $flag->name }}</h1>
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
                <th scope="col"></th>
            </tr>
            </thead>
            <tbody>
            @foreach ($events as $event)
                <tr>
                    <td><a href="/competitions/{{ $event->competition->id }}/show">{{ $event->competition->name }}</a></td>
                    <td><a href="/competitions/events/{{ $event->id }}/show">{{ $event->name }}</a></td>
                    <td><small>{{ Str::limit($event->description, 100, '...') }}</small></td>
                    <td>{{ $event->date->format('Y-m-d') }}</td>
                    <td>{{ count($event->protocolLines) }}</td>
                    <td>
                        <a href="/competitions/events/{{ $event->id }}/add-flags" class="text-info">{{ __('app.common.add_flags') }}</a>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
@endsection
