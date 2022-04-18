@php
    use App\Models\Person;
    use App\Models\Rank;
    use Illuminate\Support\Collection;
    /**
     * @var Collection $list;
     * @var Collection|Rank[] $ranks;
     * @var Collection|Person[] $persons;
     * @var Collection $personsList;
     */
@endphp

@extends('layouts.app')

@section('title', __('app.rank.check'))

@section('content')
    <div class="row mb-3">
        <div class="col-12">
            <x-back-button/>
        </div>
    </div>
    <div class="row">
        <table id="table"
               data-cookie="true"
               data-cookie-id-table="ranks-check-list"
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
                <th data-sortable="true">{{ __('app.common.group') }}</th>
                <th data-sortable="true">{{ __('app.common.fio') }}</th>
                <th data-sortable="true">{{ __('app.club.name') }}</th>
                <th data-sortable="true">{{ __('app.common.rank') }}</th>
                <th data-sortable="true">{{ __('app.common.birthday_year') }}</th>
                <th data-sortable="true">{{ __('app.common.is_equal') }}</th>
                <th data-sortable="true">{{ __('app.common.has_in_base') }}</th>
            </tr>
            </thead>
            <tbody>
                @foreach($list as $preparedLine => $lineData)
                    @php
                        $equalYear = false;
                        $equalClub = false;
                        $equalRank = false;
                        $equalName = false;
                        $hasPerson = false;

                        if ($personsList->has($preparedLine)) {
                            /** @var App\Models\Person $person */
                            $personId = $personsList->get($preparedLine);
                            if ($persons->has($personId)) {
                                $hasPerson = true;
                                $person = $persons->get($personId);
                                $equalClub = (($person->club === null) ? '' : $person->club->name) === $lineData['club'];
                                $equalYear = (($person->birthday === null) ? '' : $person->birthday->format('Y')) === (string)$lineData['year'];
                                $equalName = "{$person->lastname} {$person->firstname}" === trim($lineData['name']);
                                $equalRank = ($ranks->has($personId) && $ranks->get($personId)->rank === \App\Models\Rank::getRank($lineData['rank'])) ||
                                    (!$ranks->has($personId) && \App\Models\Rank::getRank($lineData['rank']) === \App\Models\Rank::WITHOUT_RANK);
                            }
                        }
                    @endphp
                    <tr>
                    {{-- group --}}
                        <td>{{ $lineData['group'] }}</td>
                    {{-- name --}}
                        @if ($hasPerson && $equalName)
                            <td><b><a class="text-success" href="{{ action(\App\Http\Controllers\Person\ShowPersonAction::class, $person) }}">{{ $lineData['name'] }}</a></b></td>
                        @elseif ($hasPerson)
                            <td><del>{{ $lineData['name'] }}</del>(<b class="text-success">
                                    <a  class="text-success" href="{{ action(\App\Http\Controllers\Person\ShowPersonAction::class, $person) }}">{{ "{$person->lastname} {$person->firstname}" }}</a>
                                </b>)</td>
                        @else
                            <td>{{ $lineData['name'] }}</td>
                        @endif
                    {{-- club --}}
                        @if ($hasPerson && $equalClub)
                            <td><b>{{ $lineData['club'] }}</b></td>
                        @elseif ($hasPerson)
                            <td><del>{{ $lineData['club'] }}</del>(<b class="text-success">{{ $person->club ? $person->club->name : '-' }}</b>)</td>
                        @else
                            <td>{{ $lineData['club'] }}</td>
                        @endif
                    {{-- rank --}}
                        @if ($hasPerson && $equalRank)
                            <td>
                                <b>
                                    @if ($ranks->has($person->id))
                                        <a class="text-success" href="{{ action(\App\Http\Controllers\Rank\ShowPersonRanksAction::class, $person) }}">{{ \App\Models\Rank::getRank($lineData['rank']) }}</a>
                                    @else
                                        {{ \App\Models\Rank::getRank($lineData['rank']) }}
                                    @endif
                                </b>
                            </td>
                        @elseif ($hasPerson)
                            <td>
                                <del>{{ \App\Models\Rank::getRank($lineData['rank']) }}</del>(<b class="text-success">
                                    @if ($ranks->has($person->id))
                                        <a class="text-success" href="{{ action(\App\Http\Controllers\Rank\ShowPersonRanksAction::class, $person) }}">{{ $ranks->get($person->id)->rank }}</a>
                                    @else
                                        {{ \App\Models\Rank::WITHOUT_RANK }}
                                    @endif
                                </b>)</td>
                        @else
                            <td>{{ $lineData['rank'] }}</td>
                        @endif
                    {{-- year --}}
                        @if ($hasPerson && $equalYear)
                            <td><b class="text-success">{{ $lineData['year'] }}</b></td>
                        @elseif ($hasPerson)
                            <td><del>{{ $lineData['year'] }}</del>(<b class="text-success">{{ $person->birthday?->format('Y') }}</b>)</td>
                        @else
                            <td>{{ $lineData['year'] }}</td>
                        @endif

                        <td>
                            @if ($equalYear && $equalRank && $equalClub && $equalName)
                                <i class="bi bi-plus-circle text-success"><span hidden>1</span></i>
                            @else
                                <i class="bi bi-dash-circle text-danger"><span hidden>0</span></i>
                            @endif
                        </td>
                        <td>
                            @if ($hasPerson)
                                <i class="bi bi-plus-circle text-success"><span hidden>1</span></i>
                            @else
                                <i class="bi bi-dash-circle text-danger"><span hidden>0</span></i>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection
