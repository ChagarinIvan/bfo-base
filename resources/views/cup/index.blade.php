@php
    use Illuminate\Support\Collection;
    /**
     * @var Collection $groupedCups;
     * @var int[]|Collection $years;
     */
@endphp

@extends('layouts.app')

@section('title', __('app.navbar.cups'))

@section('content')
    <h3 id="up">{{ __('app.cups') }}</h3>
    <div class="row pt-5">
        <a class="btn btn-success mr-2" href="/cups/create">{{ __('app.common.new') }}</a>
    </div>
    <div class="row pt-3">
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
            @foreach ($groupedCups as $year => $cups)
                @php
                    /** @var Cup[]|Collection $cups */
                @endphp
                <tr>
                    <td class="text-center" colspan="5"><b id="{{ $year }}">{{ $year }}</b></td>
                </tr>
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
                        <td>{{ \App\Models\ProtocolLine::whereIn('event_id', $cup->events->pluck('event_id'))
                            ->whereIn('group_id', $cup->groups->pluck('id'))
                            ->whereNotNull('person_id')
                            ->count() }}</td>
                        <td>
                            <a class="btn btn-info mr-2" href="/cups/{{ $cup->id }}/edit">{{ __('app.common.edit') }}</a>
                            <a class="btn btn-danger mr-2" href="/cups/{{ $cup->id }}/delete">{{ __('app.common.delete') }}</a>
                        </td>
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
