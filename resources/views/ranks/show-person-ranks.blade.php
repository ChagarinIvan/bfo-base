@php
    use App\Models\Person;
    use App\Models\Rank;
    use Illuminate\Support\Collection;
    /**
     * @var Rank[]|Collection $ranks;
     * @var Person $person;
     * @var Rank $actualRank;
     */
@endphp

@extends('layouts.app')

@section('title', __('app.ranks'))

@section('content')
    <h3>{{ __('app.ranks') }}</h3>
    <h4>{{ $person->lastname }} {{ $person->firstname }}</h4>
    <h4>{{ $actualRank->rank }} {{ __('app.common.do') }} {{ $actualRank->finish_date->format('Y-m-d') }}</h4>
    <div class="row pt-2">
        <a class="btn btn-success mr-2" href="{{ action(\App\Http\Controllers\BackAction::class) }}">{{ __('app.common.back') }}</a>
    </div>
    <div class="row pt-3">
        <table class="table table-bordered" id="table">
            <thead>
            <tr class="table-info">
                <th>{{ __('app.common.rank') }}</th>
                <th>{{ __('app.rank.completed_date') }}</th>
                <th>{{ __('app.event.title') }}</th>
                <th>
                    <a href="#">{{ __('app.common.edit') }}</a>
                </th>
            </tr>
            </thead>
            <tbody>
                @foreach ($ranks as $rank)
                    <tr>
                        <td>{{ $rank->rank }}</td>
                        <td>{{ $rank->event->date->format('Y-m-d') }}</td>
                        <td>
                            <a href="{{ action(\App\Http\Controllers\Event\ShowEventAction::class, [$rank->event->id]) }}">
                                {{ $rank->event->competition->name }} ({{ $rank->event->name }})
                            </a>
                        </td>
                        <td></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection