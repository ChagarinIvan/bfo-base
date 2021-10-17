@php
    use App\Models\Cup;
    use App\Models\CupEvent;
    use App\Models\CupEventPoint;
    /**
     * @var Cup $cup;
     * @var CupEvent $cupEvent;
     * @var array<int, CupEventPoint> $cupEventPoints;
     * @var int $groupId;
     */
    $index = 0;
@endphp

@extends('layouts.app')

@section('title', $cup->name.' - '.$cup->year)

@section('content')
    <div class="row mb-3">
        <h4>{{ $cupEvent->event->competition->name }}</h4>
    </div>
    <div class="row mb-3">
        <h4>{{ $cupEvent->event->name }} - {{ $cupEvent->event->date->format('Y-m-d') }}</h4>
    </div>
    <div class="row mb-3">
        <div class="col-12">
            <x-back-button/>
        </div>
    </div>
    <div class="row">
        <ul class="nav nav-tabs">
            @foreach($cup->getGroups() as $group)
                @php
                    /** @var \App\Models\Group $group */
                @endphp
                <li class="nav-item">
                    <a href="{{ action(\App\Http\Controllers\CupEvents\ShowCupEventGroupAction::class, [$cup, $cupEvent, $group]) }}"
                       class="text-decoration-none nav-link {{ $groupId === $group->id ? 'active' : ''}}"
                    >
                        <b>{{ $group->name }}</b>
                    </a>
                </li>
            @endforeach
        </ul>
        <div class="tab-content">
            <div class="tab-pane fade show active">
                <table id="table"
                       data-cookie="true"
                       data-cookie-id-table="cup-event-show"
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
                >
                    <thead class="table-dark">
                        <tr>
                            <th data-sortable="true">№</th>
                            <th data-sortable="true">{{ __('app.common.fio') }}</th>
                            <th data-sortable="true">{{ __('app.common.year') }}</th>
                            <th data-sortable="true">{{ __('app.common.time') }}</th>
                            <th data-sortable="true">{{ __('app.common.points') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach($cupEventPoints as $cupEventPoint)
                        <tr>
                            <td>{{ ++$index }}</td>
                            <td>
                                <a href="{{ action(\App\Http\Controllers\Person\ShowPersonAction::class, [$cupEventPoint->protocolLine->person_id]) }}">
                                    {{ $cupEventPoint->protocolLine->lastname }} {{ $cupEventPoint->protocolLine->firstname }}
                                </a>
                            </td>
                            <td>{{ $cupEventPoint->protocolLine->year }}</td>
                            <td>{{ $cupEventPoint->protocolLine->time ? $cupEventPoint->protocolLine->time->format('H:i:s') : '-' }}</td>
                            @if($cupEventPoint->points === $cupEvent->points)
                                <td><b class="text-info">{{ $cupEventPoint->points }}</b></td>
                            @else
                                <td>{{ $cupEventPoint->points }}</td>
                            @endif
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@section('table_extracted_columns', '[1]')
