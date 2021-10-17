@php
    use App\Models\Person;
    use App\Models\Rank;
    use Illuminate\Support\Collection;
    /**
     * @var Rank[]|Collection $ranks;
     * @var Person $person;
     * @var Rank $actualRank;
     * @var array $protocolLinesIds;
     */
@endphp

@extends('layouts.app')

@section('title', __('app.ranks'))

@section('content')
    <div class="row"><h4>{{ $person->lastname }} {{ $person->firstname }}</h4></div>
    <div class="row"><h4>{{ $actualRank->rank }} {{ __('app.common.do') }} {{ $actualRank->finish_date->format('Y-m-d') }}</h4></div>
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
               data-sticky-header="true"
               data-sticky-header-offset-y="56"
               data-custom-sort="customSort"
               data-pagination-next-text="{{ __('pagination.next') }}"
               data-pagination-pre-text="{{ __('pagination.previous') }}"
        >
            <thead class="table-dark">
                <tr>
                    <th>{{ __('app.common.rank') }}</th>
                    <th data-sortable="true">{{ __('app.rank.completed_date') }}</th>
                    <th data-sortable="true">{{ __('app.rank.finished_date') }}</th>
                    <th data-sortable="true">{{ __('app.event.title') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($ranks as $rank)
                    <tr>
                        <td>{{ $rank->rank }}</td>
                        <td>{{ $rank->event ? $rank->event->date->format('Y-m-d') : $rank->start_date->format('Y-m-d') }}</td>
                        <td>{{ $rank->finish_date->format('Y-m-d') }}</td>
                        <td>
                            @if ($rank->event_id !== null)
                                <a href="{{ action(\App\Http\Controllers\Event\ShowEventAction::class, [$rank->event->id, $rank->event->distances->first()]) }}#{{ $protocolLinesIds[$rank->id] }}"
                                >{{ $rank->event->competition->name }} ({{ $rank->event->name }})</a>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection

@section('table_extracted_columns', '[3]')
