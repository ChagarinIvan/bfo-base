@php
    use Illuminate\Support\Collection;
    /**
     * @var Collection $competitions;
     * @var int[] $years;
     * @var int $selectedYear;
     */
@endphp

@extends('layouts.app')

@section('title', __('app.competition.title'))

@section('content')
    <h3 id="up">{{ __('app.competition.title') }}</h3>
    @auth
        <div class="row pt-5">
            <a class="btn btn-success mr-2"
               href="{{ action(\App\Http\Controllers\Competition\ShowCreateFormAction::class, [$selectedYear]) }}"
            >{{ __('app.competition.add_competition') }}</a>
        </div>
    @endauth
    <ul class="nav nav-tabs pt-2">
        @foreach($years as $year)
            <li class="nav-item">
                <a href="{{ action(\App\Http\Controllers\Competition\ShowCompetitionsTableAction::class, [$year]) }}"
                   class="nav-link {{ $year === $selectedYear ? 'active' : '' }}"
                >{{ $year }}</a>
            </li>
        @endforeach
    </ul>
    <div class="tab-content">
        <div class="tab-pane fade show active">
            <table class="table table-bordered" id="table">
                <thead>
                <tr class="table-info">
                    <th>{{ __('app.common.title') }}</th>
                    <th>{{ __('app.common.dates') }}</th>
                    <th>{{ __('app.common.description') }}</th>
                    @auth<th scope="col"></th>@endauth
                </tr>
                </thead>
                <tbody>
                @foreach ($competitions as $competition)
                    <tr>
                        <td>
                            <a href="{{ action(\App\Http\Controllers\Competition\ShowCompetitionAction::class, [$competition->id]) }}"
                            >{{ $competition->name }}</a>
                        </td>
                        <td>{{ $competition->from->format('d.m.Y') }} - {{ $competition->to->format('d.m.Y') }}</td>
                        <td><small>{{ Str::limit($competition->description, 100, '...') }}</small></td>
                        @auth
                            <td>
                                <a href="{{ action(\App\Http\Controllers\Competition\DeleteCompetitionAction::class, [$selectedYear, $competition->id]) }}"
                                   class="text-danger"
                                >{{ __('app.common.delete') }}</a>
                            </td>
                        @endauth
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
