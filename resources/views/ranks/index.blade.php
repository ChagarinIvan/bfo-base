@php
    use App\Bridge\Laravel\Http\Controllers\Event\ShowEventDistanceAction;
    use App\Bridge\Laravel\Http\Controllers\Rank\ShowCheckPersonsRanksFormAction;
    use App\Bridge\Laravel\Http\Controllers\Rank\ShowPersonRanksAction;
    use App\Bridge\Laravel\Http\Controllers\Rank\ShowRanksListAction;
    use App\Application\Dto\Rank\ViewRankDto;
    use Illuminate\Support\Collection;
    /**
     * @var Collection|ViewRankDto[] $ranks;
     * @var string $selectedRank;
     */
@endphp

@extends('layouts.app')

@section('title', __('app.navbar.ranks'))

@section('content')
    @auth
        <div class="row mb-3">
            <div class="col-12">
                <x-button text="app.rank.check"
                          color="info"
                          icon="bi-patch-question-fill"
                          url="{{ action(ShowCheckPersonsRanksFormAction::class) }}"
                />
            </div>
        </div>
    @endauth
    <div class="row">
        <ul class="nav nav-tabs pt-2">
            @foreach(Rank::RANKS as $rank)
                @if($rank !== Rank::WITHOUT_RANK)
                    <li class="nav-item">
                        <a href="{{ action(ShowRanksListAction::class, [$rank]) }}"
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
                       data-pagination-next-text="{{ __('app.pagination.next') }}"
                       data-pagination-pre-text="{{ __('app.pagination.previous') }}"
                >
                    <thead class="table-dark">
                    <tr>
                        <th data-sortable="true">{{ __('app.common.fio') }}</th>
                        <th data-sortable="true">{{ __('app.rank.completed_date') }}</th>
                        <th data-sortable="true">{{ __('app.rank.activated_date') }}</th>
                        <th data-sortable="true">{{ __('app.rank.recompleted_date') }}</th>
                        <th data-sortable="true">{{ __('app.rank.finished_date') }}</th>
                    </tr>
                    </thead>
                    <tbody>personFirstName
                    @foreach ($ranks as $rank)
                        <tr>
                            <td>
                                <a href="{{ action(ShowPersonRanksAction::class, [$rank->personId]) }}"
                                >{{ $rank->personLastname }} {{ $rank->personFirstname }}</a>
                            </td>
                            <td>{{ $rank->startDate) }}</td>
                            <td>{{ $rank->activatedDate }}</td>
                            <td>
                                @if ($rank->distanceId)
                                    <a href="{{ action(ShowEventDistanceAction::class, [$rank->distanceId]) }}"
                                    >{{ $rank->eventDate }}</a>
                                @endif
                            </td>
                            <td>{{ $rank->finishDate }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@section('table_extracted_columns', '[0,2]')
