@php
    use App\Models\Distance;
    use App\Models\Event;
    use App\Models\Group;
    use Illuminate\Support\Collection;
    /**
     * @var Event $event
     * @var Collection|Group[] $groupAnchors
     * @var Distance $selectedDistance
     */
@endphp

@extends('layouts.app')

@section('title', $event->name.' ('.$event->date->format('d.m.Y').')')

@section('content')
    <div class="row mb-3">
        <div class="col-12">
            <a href="{{ action(\App\Http\Controllers\Competition\ShowCompetitionAction::class, [$event->competition_id]) }}">
                <h4>{{ $event->competition->name }}</h4>
            </a>
        </div>
    </div>
    @auth
    <div class="row mb-3">
        <div class="col-12">
            <x-edit-button url="{{ action(\App\Http\Controllers\Event\ShowEditEventFormAction::class, [$event]) }}"/>
            <x-back-button/>
        </div>
    </div>
    @endauth
    <div class="row">
        <ul class="nav nav-tabs">
            @foreach($event->distances as $distance)
                <li class="nav-item">
                    <a href="{{ action(\App\Http\Controllers\Event\ShowEventAction::class, [$event, $distance]) }}"
                       class="text-decoration-none nav-link {{ $distance->id === $selectedDistance->id ? 'active' : '' }}"
                    >
                        <b>{{ $distance->group->name }}</b>
                    </a>
                </li>
            @endforeach
        </ul>
        <div class="tab-content">
            <div class="tab-pane fade show active">
                <table id="table"
                       data-cookie="true"
                       data-cookie-id-table="show-event"
                       data-mobile-responsive="true"
                       data-check-on-init="true"
                       data-min-width="800"
                       data-toggle="table"
                       data-sort-class="table-active"
                       data-search="true"
                       data-search-highlight="true"
                       data-resizable="true"
                       data-pagination="true"
                       data-page-list="[10,25,50,100,All]"
                       data-sticky-header="true"
                       data-sticky-header-offset-y="54"
                       data-custom-sort="customSort"
                       data-pagination-next-text="{{ __('pagination.next') }}"
                       data-pagination-pre-text="{{ __('pagination.previous') }}"
                >
                    <thead class="table-dark">
                        <tr>
                            <th data-sortable="true">#</th>
                            <th data-sortable="true">{{ __('app.common.lastname') }}</th>
                            <th data-sortable="true">{{ __('app.common.name') }}</th>
                            <th data-sortable="true">{{ __('app.club.name') }}</th>
                            <th data-sortable="true">{{ __('app.common.year') }}</th>
                            <th data-sortable="true">{{ __('app.common.rank') }}</th>
                            <th data-sortable="true">{{ __('app.common.time') }}</th>
                            <th data-sortable="true">{{ __('app.common.place') }}</th>
                            <th data-sortable="true">{{ __('app.common.complete') }}</th>
                            @if($withPoints)<th data-sortable="true">{{ __('app.common.points') }}</th>@endif
                        </tr>
                    </thead>
                    <tbody>
                        @yield('groups')
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@section('table_extracted_columns', '[1,2,3]')
