@php
    use App\Models\Group;
    /**
     * @var Group $group
     */
@endphp

@extends('layouts.app')

@section('title', __('app.common.group').' '.$group->name)

@section('content')
    <div class="row">
        <div class="col-12">
            <x-back-button/>
        </div>
    </div>
    <div class="row">
        <table id="table"
               data-cookie="true"
               data-cookie-id-table="group"
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
               data-sticky-header="true"
               data-sticky-header-offset-y="56"
               data-custom-sort="customSort"
               data-pagination-next-text="{{ __('pagination.next') }}"
               data-pagination-pre-text="{{ __('pagination.previous') }}"
        >
            <thead class="table-dark">
                <tr>
                    <th data-sortable="true">{{ __('app.competition.title') }}</th>
                    <th data-sortable="true">{{ __('app.event.title') }}</th>
                    <th data-sortable="true">{{ __('app.common.competitors') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($group->distances as $distance)
                    <tr>
                        <td>
                            <a href="{{ action(\App\Http\Controllers\Competition\ShowCompetitionAction::class, [$distance->event->competition_id]) }}">
                                {{ $distance->event->competition->name }} ({{ $distance->event->competition->from->format('Y') }})
                            </a>
                        </td>
                        <td>
                            <a href="{{ action(\App\Http\Controllers\Event\ShowEventAction::class, [$distance->event_id, $distance]) }}#{{ $group->id }}">
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

@section('table_extracted_columns', '[0, 1]')
