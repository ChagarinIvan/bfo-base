@php
    use Illuminate\Support\Collection;
    /**
     * @var Collection $groupedCompetitions;
     * @var string[]|Collection $years;
     */
@endphp

@extends('layouts.app')

@section('title', 'Соревнования')

@section('content')
    <h3 id="up">{{ __('app.competition') }}</h3>
    <div class="row pt-5">
        <a class="btn btn-success mr-2" href="/competitions/create">{{ __('app.add_competition') }}</a>
    </div>
    <div class="row pt-3">
        <table class="table table-bordered" id="table">
            <thead>
            <tr class="table-info">
                <th>{{ __('app.common.title') }}</th>
                <th>{{ __('app.common.dates') }}</th>
                <th>{{ __('app.common.description') }}</th>
            </tr>
            </thead>
            <tbody>
            @foreach ($groupedCompetitions as $year => $competitions)
                @php
                    /** @var Competition|Collection $competitions */;
                @endphp
                <tr>
                    <td class="text-center" colspan="4"><b id="{{$year }}">{{ $year }}</b></td>
                </tr>
                @foreach ($competitions as $competition)
                    <tr>
                        <td>
                            <a href="/competitions/{{$competition->id}}/show">{{ $competition->name }}</a>
                        </td>
                        <td>{{ $competition->from->format('d.m.Y') }} - {{ $competition->to->format('d.m.Y') }}</td>
                        <td><small>{{ Str::limit($competition->description, 100, '...') }}</small></td>
                    </tr>
                @endforeach
            @endforeach
            </tbody>
        </table>
    </div>
@endsection

@section('footer')
    <footer class="footer bg-dark">
        <div class="container-relative">
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            @foreach($years as $year)
                <a class="text-danger" href="#{{ $year }}">{{ $year }}</a>&nbsp;&nbsp;
            @endforeach
            <a class="text-success" href="#up">Вверх</a>
        </div>
    </footer>
@endsection
