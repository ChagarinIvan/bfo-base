@php
    use App\Application\Dto\Person\ViewPersonDto;
    use App\Bridge\Laravel\Http\Controllers\Event\ShowEventDistanceAction;
    use App\Bridge\Laravel\Http\Controllers\Rank\ShowActivationFormAction;
    use App\Bridge\Laravel\Http\Controllers\Rank\ShowEditActivationDateFormAction;
    use App\Application\Dto\Rank\ViewRankDto;
    use App\Domain\Rank\Rank;
    use App\Bridge\Laravel\Http\Controllers\Rank\RefillPersonRanksAction
    /**
     * @var ViewRankDto[] $ranks;
     * @var ViewPersonDto $person;
     * @var ViewRankDto|null $actualRank;
     */
@endphp

@extends('layouts.app')

@section('title', __('app.ranks'))

@section('content')
    <div class="row"><h4>{{ $person->lastname }} {{ $person->firstname }}</h4></div>
    @if ($actualRank)
        <div class="row">
            <h4>{{ $actualRank->rank ?? '' }} {{ __('app.common.do') }} {{ $actualRank->finishDate }}</h4>
        </div>
    @else
        <div class="row"><h4>{{ Rank::WITHOUT_RANK }}</h4></div>
    @endif
    @auth
        <div class="row mb-3">
            <div class="col-12">
                <form method="POST" action="{{ action(RefillPersonRanksAction::class, [$person->id]) }}">
                    <input type="submit" class="btn btn-outline-primary btn-sm m-1" value="{{ __('app.rank.refill') }}">
                    <x-back-button/>
                </form>
            </div>
        </div>
    @elseauth
    <div class="row mb-3">
        <div class="col-12">
            <x-back-button/>
        </div>
    </div>
    @endauth
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
                <th data-sortable="true">{{ __('app.rank.finished_date') }}</th>
                <th data-sortable="true">{{ __('app.event.title') }}</th>
                @auth
                    <th></th>
                @endauth
            </tr>
            </thead>
            <tbody>
            @foreach ($ranks as $rank)
                <tr @if($rank->activatedDate) class="table-info" @else class="table-secondary" @endif>
                    <td>{{ $rank->rank }}</td>
                    <td>{{ $rank->eventDate ?: $rank->startDate }}</td>
                    <td>{{ $rank->activatedDate ?: '-' }}</td>
                    <td>{{ $rank->finishDate }}</td>
                    <td>
                        @if ($rank->distanceId !== null)
                            <a href="{{ action(ShowEventDistanceAction::class, [$rank->distanceId]) }}#{{ $rank->protocolLineId }}"
                            >{{ $rank->competitionName }} ({{ $rank->eventName }})</a>
                        @endif
                    </td>
                    @auth
                        <td>
                            @if($rank->activatedDate && !Rank::autoActivation($rank->rank))
                                <x-button text="app.rank.activation.edit"
                                          color="success"
                                          icon="radioactive"
                                          url="{{ action(ShowEditActivationDateFormAction::class, [$rank->id]) }}"
                                />
                            @endif
                            @if(!$rank->activatedDate)
                                <x-button text="app.rank.activation"
                                          color="info"
                                          icon="radioactive"
                                          url="{{ action(ShowActivationFormAction::class, [$rank->id]) }}"
                                />
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
