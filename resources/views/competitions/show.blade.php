@php
    use App\Application\Dto\Competition\ViewCompetitionDto;
    use App\Application\Dto\Event\ViewEventDto;
    use App\Bridge\Laravel\Http\Controllers\Cups\ShowCupAction;
    use App\Bridge\Laravel\Http\Controllers\Event\DeleteEventAction;
    use App\Bridge\Laravel\Http\Controllers\Event\ShowAddFlagToEventFormAction;
    use App\Bridge\Laravel\Http\Controllers\Event\ShowCreateEventFormAction;
    use App\Bridge\Laravel\Http\Controllers\Event\ShowEditEventFormAction;
    use App\Bridge\Laravel\Http\Controllers\Event\ShowEventDistanceAction;
    use App\Bridge\Laravel\Http\Controllers\Event\ShowUnitEventsFormAction;
    use App\Bridge\Laravel\Http\Controllers\Flags\ShowFlagEventsAction;
    use App\Bridge\Laravel\Http\Controllers\Event\ShowEventAction;
    use Illuminate\Support\Str;
    /**
     * @var ViewCompetitionDto $competition
     * @var ViewEventDto[] $events;
     */
@endphp

@extends('layouts.app')

@section('title', $competition->name)

@section('content')
    <div class="row">
        @auth
        <div class="col-12">
            <h4>
                {{ __('app.common.created') }}:
                <x-impression :impression="$competition->created"/>
                {{ __('app.common.updated') }}:
                <x-impression :impression="$competition->updated"/>
            </h4>
        </div>
        @endauth
        <div class="col-12">
            @auth
                <x-button text="app.competition.add_event"
                          color="success"
                          icon="bi-file-earmark-plus-fill"
                          url="{{ action(ShowCreateEventFormAction::class, [$competition->id]) }}"
                />
                <x-button text="app.competition.sum"
                          color="info"
                          icon="bi-stickies"
                          url="{{ action(ShowUnitEventsFormAction::class, [$competition->id]) }}"
                />
            @endauth
            <x-back-button/>
        </div>
    </div>
    @if (count($events) > 0)
        <div class="row pt-3">
            <table id="table"
                   data-cookie="true"
                   data-cookie-id-table="competition-{{ $competition->id }}"
                   data-mobile-responsive="true"
                   data-check-on-init="true"
                   data-min-width="800"
                   data-toggle="table"
                   data-sort-class="table-active"
                   data-resizable="true"
                   data-custom-sort="customSort"
                   data-pagination-next-text="{{ __('app.pagination.next') }}"
                   data-pagination-pre-text="{{ __('app.pagination.previous') }}"
            >
                <thead class="table-dark">
                <tr>
                    <th data-sortable="true">{{ __('app.common.title') }}</th>
                    <th>{{ __('app.event.flags') }}</th>
                    <th data-sortable="true">{{ __('app.competition.description') }}</th>
                    <th data-sortable="true">{{ __('app.common.date') }}</th>
                    <th data-sortable="true">{{ __('app.common.competitors') }}</th>
                    @auth
                        <th data-sortable="true">{{ __('app.common.created') }}</th>
                        <th data-sortable="true">{{ __('app.common.updated') }}</th>
                        <th></th>
                    @endauth
                </tr>
                </thead>
                <tbody>
                @foreach ($events as $event)
                    <tr>
                        <td>
                            <a href="{{ $event->firstDistance ? action(ShowEventDistanceAction::class, [$event->firstDistance]) : action(ShowEventAction::class, [$event->id]) }}">{{ $event->name }}</a>
                        </td>
                        <td>
                            @foreach($event->cups as $cupEvent)
                                <x-badge name="{{ $cupEvent->cup->name }} {{ $cupEvent->cup->year }}"
                                         url="{{ action(ShowCupAction::class, [$cupEvent->cup]) }}"
                                />
                            @endforeach
                            @foreach($event->flags as $flag)
                                <x-badge color="{{ $flag->color }}"
                                         name="{{ $flag->name }}"
                                         url="{{ action(ShowFlagEventsAction::class, [$flag]) }}"
                                />
                            @endforeach
                        </td>
                        <td><small>{{ Str::limit($event->description) }}</small></td>
                        <td>{{ $event->date }}</td>
                        <td>{{ $event->protocolLinesCount }}</td>
                        @auth
                            <td>
                                <x-impression :impression="$event->created"/>
                            </td>
                            <td>
                                <x-impression :impression="$event->updated"/>
                            </td>
                            <td>
                                <x-button text="app.common.add_flags"
                                          color="info"
                                          icon="bi-flag-fill"
                                          url="{{ action(ShowAddFlagToEventFormAction::class, [$event->id]) }}"
                                />
                                <x-edit-button url="{{ action(ShowEditEventFormAction::class, [$event->id]) }}"/>
                                <x-modal-button modal-id="deleteModal{{ $event->id }}"/>
                            </td>
                        @endauth
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        @foreach ($events as $event)
            <x-modal modal-id="deleteModal{{ $event->id }}"
                     url="{{ action(DeleteEventAction::class, [$event->id]) }}"
            />
        @endforeach
    @endif
@endsection

@section('table_extracted_dates_columns', '[3]')
