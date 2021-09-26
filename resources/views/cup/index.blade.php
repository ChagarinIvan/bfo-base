@php
    use App\Models\Cup;
    use Illuminate\Support\Collection;
    /**
     * @var Collection|Cup[] $cups;
     * @var int $selectedYear;
     */
@endphp

@extends('layouts.app')

@section('title', __('app.navbar.cups'))

@section('content')
    <h3 id="up">{{ __('app.cups') }}</h3>
    @auth
        <div class="row pt-5">
            <a class="btn btn-success mr-2"
               href="{{ action(\App\Http\Controllers\Cups\ShowCreateCupFormAction::class, [$selectedYear]) }}"
            >{{ __('app.common.new') }}</a>
        </div>
    @endauth
    <ul class="nav nav-tabs pt-2">
        @foreach(\App\Models\Year::YEARS as $year)
            <li class="nav-item">
                <a href="{{ action(\App\Http\Controllers\Cups\ShowCupsListAction::class, [$year]) }}"
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
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($cups as $cup)
                        <tr>
                            <td>
                                <a href="{{ action(\App\Http\Controllers\Cups\ShowCupAction::class, [$cup]) }}">{{ $cup->name }}</a>
                            </td>
                            <td>{{ $cup->year }}</td>
                            <td>
                                @foreach($cup->getGroups() as $group)
                                    @php
                                        /** @var \App\Models\Group $group */
                                    @endphp
                                    <span class="badge" style="background: {{ \App\Facades\Color::getColor($group->name) }}">
                                        <a href="{{ action(\App\Http\Controllers\Cups\ShowCupTableAction::class, [$cup, $group]) }}">{{ $group->name }}</a>
                                    </span>
                                @endforeach
                            </td>
                            <td>
                                <a class="btn btn-secondary mr-2"
                                   href="{{ action(\App\Http\Controllers\Cups\ShowCupTableAction::class, [$cup, $cup->groups->first()]) }}"
                                >{{ __('app.cup.table') }}</a>
                                @auth
                                    <a class="btn btn-info mr-2"
                                       href="{{ action(\App\Http\Controllers\Cups\ShowEditCupFormAction::class, [$cup]) }}"
                                    >{{ __('app.common.edit') }}</a>
                                    <a class="btn btn-danger mr-2"
                                       href="{{ action(\App\Http\Controllers\Cups\DeleteCupAction::class, [$cup]) }}"
                                    >{{ __('app.common.delete') }}</a>
                                @endauth
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
