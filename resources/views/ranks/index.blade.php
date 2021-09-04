@php
    use App\Models\Rank;
    use Illuminate\Support\Collection;
    /**
     * @var Collection|Rank[] $ranks;
     * @var string $selectedRank;
     */
@endphp

@extends('layouts.app')

@section('title', __('app.competition.title'))

@section('content')
    <h3 id="up">{{ __('app.navbar.ranks') }}</h3>
    <ul class="nav nav-tabs pt-2">
        @foreach(\App\Models\Rank::RANKS as $rank)
            <li class="nav-item">
                <a href="{{ action(\App\Http\Controllers\Rank\ShowRanksListAction::class, [$rank]) }}"
                   class="nav-link {{ $rank === $selectedRank ? 'active' : '' }}"
                >{{ $rank }}</a>
            </li>
        @endforeach
    </ul>
    <div class="tab-content">
        <div class="tab-pane fade show active">
            <table class="table table-bordered" id="table">
                <thead>
                <tr class="table-info">
                    <th>{{ __('app.common.fio') }}</th>
                    <th>{{ __('app.rank.completed_date') }}</th>
                    <th>{{ __('app.rank.recompleted_date') }}</th>
                    <th>{{ __('app.rank.finished_date') }}</th>
                </tr>
                </thead>
                <tbody>
                @foreach ($ranks as $rank)
                    <tr>
                        <td>
                            <a href="{{ action(\App\Http\Controllers\Person\ShowPersonRanksAction::class, [$rank->person_id]) }}">
                                <u>{{ $rank->person->lastname }} {{ $rank->person->firstname }}</u>
                            </a>
                        </td>
                        <td>{{ $rank->start_date->format('Y-m-d') }}</td>
                        <td>
                            <a href="{{ action(\App\Http\Controllers\Event\ShowEventAction::class, [$rank->event_id]) }}">
                                <u>{{ $rank->event->date->format('Y-m-d') }}</u>
                            </a>
                        </td>
                        <td>{{ $rank->finish_date->format('Y-m-d') }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection

@section('footer')
    <footer class="footer bg-dark">
        <div class="container-relative">
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            <a class="text-success" href="#up">{{ __('app.up') }}</a>
        </div>
    </footer>
@endsection
