@php
    use App\Http\Controllers\Event\ShowEventAction;
    use App\Http\Controllers\Rank\ActivatePersonRankAction;
    use App\Models\Person;
    use App\Models\Rank;
    use Illuminate\Support\Collection;
    /**
     * @var Rank[]|Collection $ranks;
     * @var Person $person;
     * @var Rank|null $actualRank;
     * @var array $protocolLinesIds;
     */
@endphp

@extends('layouts.app')

@section('title', __('app.ranks'))

@section('content')
    <div class="row"><h4>{{ $person->lastname }} {{ $person->firstname }}</h4></div>
    @if ($actualRank)
        <div class="row">
            <h4>{{ $actualRank->rank ?? '' }} {{ __('app.common.do') }} {{ $actualRank->finish_date->format('Y-m-d') }}</h4>
        </div>
    @else
        <div class="row"><h4>{{ Rank::WITHOUT_RANK }}</h4></div>
    @endif
    <div class="row mb-3">
        <div class="col-12">
            <x-back-button/>
        </div>
    </div>
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
                <th data-sortable="true">{{ __('app.rank.finished_date') }}</th>
                <th data-sortable="true">{{ __('app.event.title') }}</th>
                @auth
                    <th></th>
                @endauth
            </tr>
            </thead>
            <tbody>
            @foreach ($ranks as $rank)
                <tr @if($rank->active) class="table-info" @else class="table-secondary" @endif>
                    <td>{{ $rank->rank }}</td>
                    <td>{{ $rank->event ? $rank->event->date->format('Y-m-d') : $rank->start_date->format('Y-m-d') }}</td>
                    <td>{{ $rank->finish_date->format('Y-m-d') }}</td>
                    <td>
                        @if ($rank->event_id !== null)
                            <a href="{{ action(ShowEventAction::class, [$rank->event->id, $rank->event->distances->first()]) }}#{{ $protocolLinesIds[$rank->id] }}"
                            >{{ $rank->event->competition->name }} ({{ $rank->event->name }})</a>
                        @endif
                    </td>
                    @auth
                        <td>
                            @if(!$rank->active)
{{--                                <x-button text="app.rank.submit"--}}
{{--                                          color="success"--}}
{{--                                          icon="radioactive"--}}
{{--                                          url="{{ action(ActivatePersonRankAction::class, [$person, $rank]) }}"--}}
{{--                                />--}}
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
