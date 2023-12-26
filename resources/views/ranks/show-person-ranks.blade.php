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
                <th></th>
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
                    <td>
                        @if($rank->active)
                            <x-modal-button modal-id="activateRank{{ $rank->id }}" text="app.common.edit" color="success" icon="radioactive" />
                        @endif
                    </td>
                </tr>
                <div class="modal modal-dark fade" id="activateRank{{ $rank->id }}" tabindex="-1" aria-labelledby="activateRank{{ $rank->id }}Label" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <form method="POST"
                                  action="{{ action(ActivatePersonRankAction::class, [$person, $rank]) }}"
                                  enctype="multipart/form-data"
                            >
                                <div class="modal-header">
                                    <h5 class="modal-title" id="activateRank{{ $rank->id }}Label">{{ __('app.rank.activate') }}</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('app.common.close') }}"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="form-floating mb-3">
                                        <input class="form-control" type="date" id="start_date" name="start_date">
                                        <label for="start_date">{{ __('app.common.date') }}</label>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <input type="submit" class="btn btn-outline-primary btn-sm" value="{{ __('app.rank.submit') }}">
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            @endforeach
            </tbody>
        </table>
    </div>
@endsection

@section('table_extracted_columns', '[3]')
