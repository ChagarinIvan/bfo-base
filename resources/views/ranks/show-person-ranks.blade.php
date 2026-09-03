@php
    use App\Application\Dto\Person\LegacyViewPersonDto;
    use App\Bridge\Laravel\Http\Controllers\Event\ShowEventDistanceAction;
    use App\Bridge\Laravel\Http\Controllers\Rank\ShowActivationFormAction;
    use App\Bridge\Laravel\Http\Controllers\Rank\ShowEditActivationDateFormAction;
    use App\Domain\Rank\Rank;
    /**
     * @var \App\Application\Dto\Rank\PersonRankHistoryDto[] $ranks;
     * @var LegacyViewPersonDto $person;
     * @var string $actualRank;
     */
@endphp

@extends('layouts.app')

@section('title', __('app.ranks'))

@section('content')
    <div class="row"><h4>{{ $person->lastname }} {{ $person->firstname }}</h4></div>
    @if ($actualRank !== Rank::WithoutRank->value)
        <div class="row">
            <h4>{{ Rank::from($actualRank)->label() }} {{ __('app.common.do') }} {{ $actualRankFinishedOn ?? '-' }}</h4>
        </div>
    @else
        <div class="row"><h4>{{ Rank::WithoutRank->label() }}</h4></div>
    @endif
    <div class="row">
        <table id="table"
               data-cookie="true"
               data-cookie-id-table="ranks-list"
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
                <th>{{ __('app.common.rank') }}</th>
                <th data-sortable="true">{{ __('app.rank.completed_date') }}</th>
                <th data-sortable="true">{{ __('app.rank.activated_date') }}</th>
{{--                <th data-sortable="true">{{ __('app.rank.formal_start_date') }}</th>--}}
                <th data-sortable="true">{{ __('app.rank.finished_date') }}</th>
                <th data-sortable="true">{{ __('app.event.title') }}</th>
                @auth
                    <th></th>
                @endauth
                @auth
                    <th></th>
                @endauth
            </tr>
            </thead>
            <tbody>
            @foreach ($ranks as $rank)
                <tr @if($rank->activatedOn) class="table-info" @else class="table-secondary" @endif>
                    <td>{{ $rank->rank }}</td>
                    <td>{{ $rank->eventDate ?? $rank->achievedOn }}</td>
                    <td>{{ $rank->activatedOn ?: '-' }}</td>
{{--                    <td>{{ $formalStartDate }}</td>--}}
                    <td>{{ $rank->finishedOn ?? '-' }}</td>
                    <td>
                        @if ($rank->distanceId !== '')
                            <a href="{{ action(ShowEventDistanceAction::class, [$rank->distanceId]) }}#{{ $rank->protocolLineId }}"
                            >{{ $rank->competitionName }} ({{ $rank->eventName }})</a>
                        @endif
                    </td>
                    @auth
                        <td>
                            @if ($rank->activatedOn)
                                <x-button text="app.rank.activation.edit" color="success" icon="radioactive"
                                          url="{{ action(ShowEditActivationDateFormAction::class, [$rank->protocolLineId]) }}" />
                            @else
                                <x-button text="app.rank.activation" color="info" icon="radioactive"
                                          url="{{ action(ShowActivationFormAction::class, [$rank->protocolLineId]) }}" />
                            @endif
                        </td>
                    @endauth
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
@endsection

@section('table_extracted_columns', '[3]')
