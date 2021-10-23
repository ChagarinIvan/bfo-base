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

@section('title', $cup->name.' - '.$cup->year)

@section('content')
    <div class="row mb-3">
        <div class="col-12">
            @auth
                <x-edit-button url="{{ action(\App\Http\Controllers\Cups\ShowEditCupFormAction::class, [$cup]) }}"/>
                <x-button text="app.competition.add_event"
                          color="success"
                          icon="bi-file-earmark-plus-fill"
                          url="{{ action(\App\Http\Controllers\CupEvents\ShowCreateCupEventFormAction::class, [$cup]) }}"
                />
            @endauth
            <x-button text="app.cup.table"
                      color="secondary"
                      icon="bi-table"
                      url="{{ action(\App\Http\Controllers\Cups\ShowCupTableAction::class, [$cup, $cup->getCupType()->getGroups()->first()]) }}"
            />
            <x-back-button/>
        </div>
    </div>
    <div class="row mb-3">
        <div class="col-12">
            @foreach($cup->getCupType()->getGroups() as $group)
                <x-badge color="{{ \App\Facades\Color::getColor($group->name) }}"
                         name="{{ $group->name }}"
                         url="{{ action(\App\Http\Controllers\Cups\ShowCupTableAction::class, [$cup, $group]) }}"/>
            @endforeach
        </div>
    </div>
    <div class="row">
        <table id="table"
               data-cookie="true"
               data-cookie-id-table="cup-event"
               data-mobile-responsive="true"
               data-check-on-init="true"
               data-min-width="800"
               data-toggle="table"
               data-sort-class="table-active"
               data-resizable="true"
               data-search="true"
               data-search-highlight="true"
               data-sticky-header="true"
               data-sticky-header-offset-y="54"
               data-custom-sort="customSort"
               data-pagination-next-text="{{ __('pagination.next') }}"
               data-pagination-pre-text="{{ __('pagination.previous') }}"
        >
            <thead class="table-dark">
                <tr>
                    <th data-sortable="true">№</th>
                    <th data-sortable="true">{{ __('app.common.title') }}</th>
                    <th data-sortable="true">{{ __('app.common.date') }}</th>
                    <th data-sortable="true">{{ __('app.common.competitors') }}</th>
                    <th data-sortable="true">{{ __('app.common.points') }}</th>
                    @auth<th></th>@endauth
                </tr>
            </thead>
            <tbody>
                @foreach($cupEvents as $index => $cupEvent)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>
                            <a href="{{ action(\App\Http\Controllers\CupEvents\ShowCupEventGroupAction::class, [$cup, $cupEvent, $cup->getCupType()->getGroups()->first()]) }}">
                                <u class="">{{ \Illuminate\Support\Str::limit($cupEvent->event->competition->name, 30).' - '.\Illuminate\Support\Str::limit($cupEvent->event->name, 30) }}</u>
                            </a>
                        </td>
                        <td>{{ $cupEvent->event->date->format('Y-m-d') }}</td>
                        <td>{{ $cupEventsParticipateCount->get($cupEvent->id) ?? 0 }}</td>
                        <td>{{ $cupEvent->points }}</td>
                        @auth
                            <td>
                                <x-edit-button url="{{ action(\App\Http\Controllers\CupEvents\ShowEditCupEventFormAction::class, [$cup, $cupEvent]) }}"/>
                                <x-delete-button modal-id="deleteModal{{ $cupEvent->id }}"/>
                            </td>
                        @endauth
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @foreach ($cupEvents as $cupEvent)
        <x-modal modal-id="deleteModal{{ $cupEvent->id }}"
                 url="{{ action(\App\Http\Controllers\CupEvents\DeleteCupEventAction::class, [$cup, $cupEvent]) }}"
        />
    @endforeach
@endsection

@section('table_extracted_columns', '[1]')
@section('table_extracted_dates_columns', '[2]')
