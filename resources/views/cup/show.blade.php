@php
    use App\Models\Cup;
    use App\Models\CupEvent;
    use Illuminate\Support\Collection;
    /**
     * @var Cup $cup;
     * @var CupEvent[] $cupEvents;
     * @var Collection $cupEventsParticipateCount;
     */
@endphp

@extends('layouts.app')

@section('title', \Illuminate\Support\Str::limit($cup->name, 20, '...'))

@section('content')
    <div class="row">
        <h1>{{ $cup->name }} - {{ $cup->year }}</h1>
    </div>
    <div class="row pt-5">
        @auth
            <a class="btn btn-info mr-2"
               href="{{ action(\App\Http\Controllers\Cups\ShowEditCupFormAction::class, [$cup]) }}"
            >{{ __('app.common.edit') }}</a>
            <a class="btn btn-success mr-2"
               href="{{ action(\App\Http\Controllers\CupEvents\ShowCreateCupEventFormAction::class, [$cup]) }}"
            >{{ __('app.competition.add_event') }}</a>
        @endauth
        <a class="btn btn-secondary mr-2"
           href="{{ action(\App\Http\Controllers\Cups\ShowCupTableAction::class, [$cup, $cup->groups->first()]) }}"
        >{{ __('app.cup.table') }}</a>
        <a class="btn btn-danger mr-2" href="{{ action(\App\Http\Controllers\BackAction::class) }}">{{ __('app.common.back') }}</a>
    </div>
    <div class="row pt-3">
        @foreach($cup->getGroups() as $group)
            @php
                /** @var \App\Models\Group $group */
            @endphp
            <span class="badge" style="background: {{ \App\Facades\Color::getColor($group->name) }}">
                <a href="{{ action(\App\Http\Controllers\Cups\ShowCupTableAction::class, [$cup, $group]) }}">{{ $group->name }}</a>
            </span>
        @endforeach
    </div>
    <div class="row">
        <h3>{{ __('app.cup.events') }}</h3>
    </div>
    <div class="row pt-3">
        <table class="table">
            <thead>
            <tr>
                <th scope="col">№</th>
                <th scope="col">{{ __('app.common.title') }}</th>
                <th scope="col">{{ __('app.common.date') }}</th>
                <th scope="col">{{ __('app.common.competitors') }}</th>
                <th scope="col">{{ __('app.common.points') }}</th>
                @auth<th scope="col"></th>@endauth
            </tr>
            </thead>
            <tbody>
            @foreach($cupEvents as $index => $cupEvent)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>
                        <a href="{{ action(\App\Http\Controllers\CupEvents\ShowCupEventGroupAction::class, [$cup, $cupEvent, $cup->groups->first()]) }}">
                            <u>{{ $cupEvent->event->competition->name.' - '.$cupEvent->event->name }}</u>
                        </a>
                    </td>
                    <td>{{ $cupEvent->event->date->format('Y-m-d') }}</td>
                    <td>{{ $cupEventsParticipateCount->get($cupEvent->id) ?? 0 }}</td>
                    <td>{{ $cupEvent->points }}</td>
                    @auth
                        <td>
                            <a href="{{ action(\App\Http\Controllers\CupEvents\ShowEditCupEventFormAction::class, [$cup, $cupEvent]) }}"
                               class="text-primary"
                            >{{ __('app.common.edit') }}</a>
                            <a href="{{ action(\App\Http\Controllers\CupEvents\DeleteCupEventAction::class, [$cup, $cupEvent]) }}"
                               class="text-danger"
                            >{{ __('app.common.delete') }}</a>
                        </td>
                    @endauth
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
@endsection
