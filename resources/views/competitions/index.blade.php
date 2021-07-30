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
    <div class="row pt-5">
        <a class="btn btn-success mr-2" href="/competitions/y{{ $selectedYear }}/create">{{ __('app.competition.add_competition') }}</a>
    </div>
    <ul class="nav nav-tabs pt-2">
        @foreach($years as $year)
            <li class="nav-item">
                <a href="/competitions/y{{ $year }}"
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
                    <th scope="col"></th>
                </tr>
                </thead>
                <tbody>
                @foreach ($competitions as $competition)
                    <tr>
                        <td>
                            <a href="/competitions/{{$competition->id}}/show">{{ $competition->name }}</a>
                        </td>
                        <td>{{ $competition->from->format('d.m.Y') }} - {{ $competition->to->format('d.m.Y') }}</td>
                        <td><small>{{ Str::limit($competition->description, 100, '...') }}</small></td>
                        <td>
                            <a href="/competitions/y{{ $selectedYear }}/delete/{{ $competition->id }}" class="text-danger">{{ __('app.common.delete') }}</a>
                        </td>
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
