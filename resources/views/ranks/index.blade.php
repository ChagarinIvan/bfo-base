@php
    use App\Models\Rank;
    use Illuminate\Support\Collection;
    /**
     * @var Collection|Rank[] $ranks;
     * @var string $selectedRank;
     */
@endphp

@extends('layouts.app')

@section('title', __('app.navbar.ranks'))

@section('content')
    <div class="row">
        <ul class="nav nav-tabs pt-2">
            @foreach(\App\Models\Rank::RANKS as $rank)
                @if($rank !== \App\Models\Rank::WITHOUT_RANK)
                    <li class="nav-item">
                        <a href="{{ action(\App\Http\Controllers\Rank\ShowRanksListAction::class, [$rank]) }}"
                           class="nav-link {{ $rank === $selectedRank ? 'active' : '' }}"
                        >{{ $rank }}</a>
                    </li>
                @endif
            @endforeach
        </ul>
        <div class="tab-content">
            <div class="tab-pane fade show active">
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
                       data-pagination-next-text="{{ __('pagination.next') }}"
                       data-pagination-pre-text="{{ __('pagination.previous') }}"
                >
                    <thead class="table-dark">
                        <tr>
                            <th data-sortable="true">{{ __('app.common.fio') }}</th>
                            <th data-sortable="true">{{ __('app.rank.completed_date') }}</th>
                            <th data-sortable="true">{{ __('app.rank.recompleted_date') }}</th>
                            <th data-sortable="true">{{ __('app.rank.finished_date') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach ($ranks as $rank)
                        <tr>
                            <td>
                                <a href="{{ action(\App\Http\Controllers\Person\ShowPersonRanksAction::class, [$rank->person_id]) }}"
                                >{{ $rank->person->lastname }} {{ $rank->person->firstname }}</a>
                            </td>
                            <td>{{ $rank->start_date->format('Y-m-d') }}</td>
                            <td>
                                @if ($rank->event_id !== null)
                                    <a href="{{ action(\App\Http\Controllers\Event\ShowEventAction::class, [$rank->event, $rank->event->distances->first()]) }}"
                                    >{{ $rank->event->date->format('Y-m-d') }}</a>
                                @endif
                            </td>
                            <td>{{ $rank->finish_date->format('Y-m-d') }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@section('table_extracted_columns', '[0,2]')
