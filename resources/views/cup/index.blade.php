@php
    use Illuminate\Support\Collection;
    /**
     * @var Collection $cups;
     * @var int $selectedYear;
     */
@endphp

@extends('layouts.app')

@section('title', __('app.navbar.cups'))

@section('content')
    <h3 id="up">{{ __('app.cups') }}</h3>
    <div class="row pt-5">
        <a class="btn btn-success mr-2" href="/cups/y{{ $selectedYear }}/create">{{ __('app.common.new') }}</a>
    </div>
    <ul class="nav nav-tabs pt-2">
        @foreach(\App\Models\Year::YEARS as $year)
            <li class="nav-item">
                <a href="/cups/y{{ $year }}"
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
                        <th>{{ __('app.common.year') }}</th>
                        <th>{{ __('app.common.groups') }}</th>
                        <th>{{ __('app.common.competitors') }}</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($cups as $cup)
                        @php
                            /** @var \App\Models\Cup $cup */
                        @endphp
                        <tr>
                            <td>
                                <a href="/cups/{{ $cup->id }}/show">{{ $cup->name }}</a>
                            </td>
                            <td>{{ $cup->year }}</td>
                            <td>
                                @foreach($cup->groups as $group)
                                    <span class="badge" style="background: {{ Color::getColor($group->name) }}"><a href="/cups/{{ $cup->id }}/table/{{ $group->id }}">{{ $group->name }}</a></span>
                                @endforeach
                            </td>
                            <td>{{ \App\Models\ProtocolLine::join('distances', 'distances.id', '=', 'protocol_lines.distance_id')
                                ->whereIn('distances.group_id', $cup->groups->pluck('id'))
                                ->whereNotNull('person_id')
                                ->count() }}</td>
                            <td>
                                <a class="btn btn-secondary mr-2" href="/cups/{{ $cup->id }}/table/0">{{ __('app.cup.table') }}</a>
                                <a class="btn btn-info mr-2" href="/cups/{{ $cup->id }}/edit">{{ __('app.common.edit') }}</a>
                                <a class="btn btn-danger mr-2" href="/cups/{{ $cup->id }}/delete">{{ __('app.common.delete') }}</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
