@php
    use App\Models\Competition;
    use App\Models\Event;
    use Illuminate\Support\Collection;
    /**
     * @var Competition $competition;
     * @var Event[]|Collection $events;
     */
@endphp

@extends('layouts.app')

@section('title', $competition->name)

@section('content')
    <div class="row">
        <div class="col-12">
            @auth
                <x-button text="app.competition.add_event"
                          color="success"
                          icon="bi-file-earmark-plus-fill"
                          url="{{ action(\App\Http\Controllers\Event\ShowCreateEventFormAction::class, [$competition->id]) }}"
                />
                <x-button text="app.competition.sum"
                          color="info"
                          icon="bi-stickies"
                          url="{{ action(\App\Http\Controllers\Event\ShowUnitEventsFormAction::class, [$competition->id]) }}"
                />
            @endauth
            <x-back-button/>
        </div>
    </div>
    @if ($events->count() > 0)
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
                   data-pagination-next-text="{{ __('app.pagination.nex') }}"
                   data-pagination-pre-text="{{ __('app.pagination.previous') }}"
            >
                <thead class="table-dark">
                    <tr>
                        <th data-sortable="true">{{ __('app.common.title') }}</th>
                        <th>{{ __('app.event.flags') }}</th>
                        <th data-sortable="true">{{ __('app.competition.description') }}</th>
                        <th data-sortable="true">{{ __('app.common.date') }}</th>
                        <th data-sortable="true">{{ __('app.common.competitors') }}</th>
                        @auth<th></th>@endauth
                    </tr>
                </thead>
                <tbody>
                @foreach ($events as $event)
                    <tr>
                        <td>
                            <a href="{{ action(\App\Http\Controllers\Event\ShowEventAction::class, [$event->id, $event->distances->first()]) }}">{{ $event->name }}</a>
                        </td>
                        <td>
                            @foreach($event->cups as $cupEvent)
                                <x-badge color="{{ \App\Facades\Color::getColor($cupEvent->cup->name) }}"
                                         name="{{ $cupEvent->cup->name }} {{ $cupEvent->cup->year }}"
                                         url="{{ action(\App\Http\Controllers\Cups\ShowCupAction::class, [$cupEvent->cup]) }}"
                                />
                            @endforeach
                            @foreach($event->flags as $flag)
                                <x-badge color="{{ \App\Facades\Color::getColor($flag->color) }}"
                                         name="{{ $flag->name }}"
                                         url="{{ action(\App\Http\Controllers\Flags\ShowFlagEventsAction::class, [$flag]) }}"
                                />
                            @endforeach
                        </td>
                        <td><small>{{ \Illuminate\Support\Str::limit($event->description) }}</small></td>
                        <td>{{ $event->date->format('Y-m-d') }}</td>
                        <td>{{ count($event->protocolLines) }}</td>
                        @auth
                            <td>
                                <x-button text="app.common.add_flags"
                                          color="info"
                                          icon="bi-flag-fill"
                                          url="{{ action(\App\Http\Controllers\Event\ShowAddFlagToEventFormAction::class, [$event]) }}"
                                />
                                <x-edit-button url="{{ action(\App\Http\Controllers\Event\ShowEditEventFormAction::class, [$event]) }}"/>
                                <x-delete-button modal-id="deleteModal{{ $event->id }}"/>
                            </td>
                        @endauth
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        @foreach ($events as $event)
            <x-modal modal-id="deleteModal{{ $event->id }}"
                     url="{{ action(\App\Http\Controllers\Event\DeleteEventAction::class, [$event->id]) }}"
            />
        @endforeach
    @endif
@endsection

@section('table_extracted_dates_columns', '[3]')
