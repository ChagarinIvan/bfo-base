@php
    use App\Bridge\Laravel\Http\Controllers\Competition\ShowCompetitionAction;use App\Bridge\Laravel\Http\Controllers\CupEvents\ShowCupEventGroupAction;use App\Bridge\Laravel\Http\Controllers\Event\ShowEventDistanceAction;use App\Bridge\Laravel\Http\Controllers\Person\ShowPersonAction;use App\Domain\Cup\Cup;use App\Domain\Cup\Group\CupGroup;use App\Domain\CupEvent\CupEvent;use App\Models\CupEventPoint;
    /**
     * @var Cup $cup;
     * @var CupEvent $cupEvent;
     * @var array<int, CupEventPoint> $cupEventPoints;
     * @var string $groupId;
     */
    $index = 0;
@endphp

@extends('layouts.app')

@section('title', $cup->name.' - '.$cup->year)

@section('content')
    <div class="row mb-3">
        <h4>
            <a href="{{ action(ShowCompetitionAction::class, [$cupEvent->event->competition_id]) }}"
            >{{ $cupEvent->event->competition->name }}</a>
        </h4>
    </div>
    <div class="row mb-3">
        <h5>
            <a href="{{ action(ShowEventDistanceAction::class, [$cupEvent->event->distances->first()]) }}"
            >{{ $cupEvent->event->name }} - {{ $cupEvent->event->date->format('Y-m-d') }}</a>
        </h5>
    </div>
    <div class="row mb-3">
        <div class="col-12">
            <x-back-button/>
        </div>
    </div>
    <div class="row">
        <ul class="nav nav-tabs">
            @foreach($cup->getCupType()->getGroups() as $group)
                @php
                    /** @var CupGroup $group */
                @endphp
                <li class="nav-item">
                    <a href="{{ action(ShowCupEventGroupAction::class, [$cup, $cupEvent, $group->id()]) }}"
                       class="text-decoration-none nav-link {{ $groupId === $group->id() ? 'active' : ''}}"
                    >
                        <b>{{ $group->name() }}</b>
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
                       data-custom-sort="customSort"
                >
                    <thead class="table-dark">
                    <tr>
                        <th data-sortable="true">№</th>
                        <th data-sortable="true">{{ __('app.common.fio') }}</th>
                        <th data-sortable="true">{{ __('app.common.birthday_year') }}</th>
                        <th data-sortable="true">{{ __('app.club.name') }}</th>
                        <th data-sortable="true">{{ __('app.common.time') }}</th>
                        <th data-sortable="true">{{ __('app.common.points') }}</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($cupEventPoints as $cupEventPoint)
                        <tr>
                            <td>{{ ++$index }}</td>
                            <td>
                                <a href="{{ action(ShowPersonAction::class, [$cupEventPoint->protocolLine->person_id]) }}">
                                    {{ $cupEventPoint->protocolLine->lastname }} {{ $cupEventPoint->protocolLine->firstname }}
                                </a>
                            </td>
                            <td>{{ $cupEventPoint->protocolLine->year }}</td>
                            <td>
                                <x-club-link :clubId="$cupEventPoint->protocolLine->person->club_id"></x-club-link>
                            </td>
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
