@php
    use App\Bridge\Laravel\Http\Controllers\Competition\ShowCompetitionAction;
    use App\Bridge\Laravel\Http\Controllers\Cup\ShowCupEventGroupAction;
    use App\Bridge\Laravel\Http\Controllers\Event\ShowEventDistanceAction;
    use App\Bridge\Laravel\Http\Controllers\Person\ShowPersonAction;
    use App\Application\Dto\Cup\ViewCalculatedCupEventDto;

    /**
     * @var ViewCalculatedCupEventDto $calculatedCupEvent;
     * @var string $groupId;
     */
    $index = 0;
@endphp

@extends('layouts.app')

@section('title', $calculatedCupEvent->cupName.' - '.$calculatedCupEvent->cupYear)

@section('content')
    <div class="row mb-3">
        <h4>
            <a href="{{ action(ShowCompetitionAction::class, [$calculatedCupEvent->cupEvent->event->competitionId]) }}">{{ $calculatedCupEvent->cupEvent->event->competitionName }}</a>
        </h4>
    </div>
    <div class="row mb-3">
        <h5>
            <a href="{{ action(ShowEventDistanceAction::class, [$calculatedCupEvent->cupEvent->event->firstDistance]) }}">{{ $calculatedCupEvent->cupEvent->event->name }} - {{ $calculatedCupEvent->cupEvent->event->date }}</a>
        </h5>
    </div>
    <div class="row mb-3">
        <div class="col-12">
            <x-back-button/>
        </div>
    </div>
    <div class="row">
        <ul class="nav nav-tabs">
            @foreach($calculatedCupEvent->cupGroups as $group)
                <li class="nav-item">
                    <a href="{{ action(ShowCupEventGroupAction::class, [$calculatedCupEvent->cupEvent->cupId, $calculatedCupEvent->cupEvent->id, $group->id]) }}" class="text-decoration-none nav-link {{ $groupId === $group->id ? 'active' : ''}}">
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
                    @foreach($calculatedCupEvent->points as $point)
                        <tr>
                            <td>{{ ++$index }}</td>
                            <td>
                                <a href="{{ action(ShowPersonAction::class, [$point->personId]) }}">{{ $point->personName }}</a>
                            </td>
                            <td>{{ $point->personYear }}</td>
                            <td>
                                <x-club-link :clubId="$point->personClubId"></x-club-link>
                            </td>
                            <td>{{ $point->time }}</td>
                            @if($point->points === $calculatedCupEvent->cupEvent->points)
                                <td><b class="text-info">{{ $point->points }}</b></td>
                            @else
                                <td>{{ $point->points }}</td>
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
