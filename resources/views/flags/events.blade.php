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

@section('title', __('app.common.flag').' '.$flag->name)

@section('content')
    <div class="row mb-3">
        <div class="col-12">
            <x-back-button/>
        </div>
    </div>
    <div class="row mb-3">
        <table id="table"
               data-cookie="true"
               data-cookie-id-table="flag-events"
               data-mobile-responsive="true"
               data-check-on-init="true"
               data-min-width="800"
               data-toggle="table"
               data-search="true"
               data-search-highlight="true"
               data-sort-class="table-active"
               data-pagination="true"
               data-page-list="[10,25,50,100,All]"
               data-resizable="true"
               data-custom-sort="customSort"
               data-pagination-next-text="{{ __('app.pagination.next') }}"
               data-pagination-pre-text="{{ __('app.pagination.previous') }}"
        >
            <thead class="table-dark">
                <tr>
                    <th data-sortable="true">{{ __('app.competition.title') }}</th>
                    <th data-sortable="true">{{ __('app.common.title') }}</th>
                    <th data-sortable="true">{{ __('app.common.description') }}</th>
                    <th data-sortable="true">{{ __('app.common.date') }}</th>
                    <th data-sortable="true">{{ __('app.common.competitors') }}</th>
                    @auth<th></th>@endauth
                </tr>
            </thead>
            <tbody>
                @foreach ($events as $event)
                    <tr>
                        <td>
                            <a class="d-none d-md-inline" href="{{ action(\App\Http\Controllers\Competition\ShowCompetitionAction::class, [$event->competition]) }}">{{ \Illuminate\Support\Str::limit($event->competition->name, 30) }}</a>
                        </td>
                        <td>
                            <a href="{{ action(\App\Http\Controllers\Event\ShowEventAction::class, [$event->id, $event->distances->first()]) }}">{{ \Illuminate\Support\Str::limit($event->name, 30) }}</a>
                        </td>
                        <td><small class="d-none d-xl-inline">{{ \Illuminate\Support\Str::limit($event->description, 80) }}</small></td>
                        <td>{{ $event->date->format('Y-m-d') }}</td>
                        <td>{{ count($event->protocolLines) }}</td>
                        @auth
                            <td>
                                <x-button text="app.common.add_flags"
                                          color="info"
                                          icon="bi-flag-fill"
                                          url="{{ action(\App\Http\Controllers\Event\ShowAddFlagToEventFormAction::class, [$event]) }}"
                                />
                            </td>
                        @endauth
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection

@section('table_extracted_columns', '[0,1]')
@section('table_extracted_dates_columns', '[3]')
