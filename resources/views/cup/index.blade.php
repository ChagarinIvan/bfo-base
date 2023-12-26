@php
    use App\Http\Controllers\Cups\DeleteCupAction;
    use App\Http\Controllers\Cups\ShowCreateCupFormAction;
    use App\Http\Controllers\Cups\ShowCupAction;
    use App\Http\Controllers\Cups\ShowCupsListAction;
    use App\Http\Controllers\Cups\ShowCupTableAction;
    use App\Http\Controllers\Cups\ShowEditCupFormAction;
    use App\Models\Cup;
    use App\Models\Year;
    use Illuminate\Support\Collection;
    /**
     * @var Collection|Cup[] $cups;
     * @var Year $selectedYear;
     */
@endphp

@extends('layouts.app')

@section('title', __('app.navbar.cups'))

@section('content')
    @auth
        <div class="row mb-3">
            <div class="col-12 col-md-12 col-lg-6 col-xl-4 col-xxl-3">
                <x-button text="app.common.new"
                          color="success"
                          icon="bi-file-earmark-plus-fill"
                          url="{{ action(ShowCreateCupFormAction::class, [$selectedYear->value]) }}"
                />
            </div>
        </div>
    @endauth
    <div class="row">
        <ul class="nav nav-tabs">
            @foreach(Year::cases() as $year)
                <li class="nav-item">
                    <a href="{{ action(ShowCupsListAction::class, [$year->value]) }}"
                       class="text-decoration-none nav-link {{ $year === $selectedYear ? 'active' : '' }}"
                    >
                        <b>{{ $year->value }}</b>
                    </a>
                </li>
            @endforeach
        </ul>
        <div class="tab-content">
            <div class="tab-pane fade show active">
                @if ($cups->count() > 0)
                    <table id="table"
                           data-cookie="true"
                           data-cookie-id-table="cups-list-{{ $selectedYear->value }}"
                           data-mobile-responsive="true"
                           data-check-on-init="true"
                           data-min-width="800"
                           data-toggle="table"
                           data-sort-class="table-active"
                           data-page-list="[10,25,50,100,All]"
                           data-resizable="true"
                           data-custom-sort="customSort"
                    >
                        <thead class="table-dark">
                        <tr>
                            <th data-sortable="true">{{ __('app.common.title') }}</th>
                            <th data-sortable="true">{{ __('app.cup.last_date') }}</th>
                            <th>{{ __('app.common.groups') }}</th>
                            <th></th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach ($cups as $cup)
                            <tr>
                                <td>
                                    <a href="{{ action(ShowCupAction::class, [$cup]) }}">{{ $cup->name }}</a>
                                </td>
                                <td>{{ $cup->events->sortByDesc('cup_event.event.date')->count() > 0
                                        ? $cup->events->sortByDesc('cup_event.event.date')->last()->event->date->format('Y-m-d')
                                        : '' }}</td>
                                <td>
                                    @foreach($cup->getCupType()->getGroups() as $group)
                                        @php
                                            /** @var \App\Models\Group\CupGroup $group */
                                        @endphp
                                        <x-badge name="{{ $group->name() }}"
                                                 url="{{ action(ShowCupTableAction::class, [$cup, $group->id()]) }}"
                                        />
                                    @endforeach
                                </td>
                                <td>
                                    <x-button text="app.cup.table" color="secondary" icon="bi-table"
                                              url="{{ action(ShowCupTableAction::class, [$cup, $cup->getCupType()->getGroups()->first()->id()]) }}"/>
                                    @auth
                                        <x-edit-button
                                                url="{{ action(ShowEditCupFormAction::class, [$cup]) }}"/>
                                        <x-modal-button modal-id="deleteModal{{ $cup->id }}"/>
                                    @endauth
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
        </div>
    </div>
    @foreach ($cups as $cup)
        <x-modal modal-id="deleteModal{{ $cup->id }}"
                 url="{{ action(DeleteCupAction::class, [$cup]) }}"
        />
    @endforeach
@endsection

@section('table_extracted_columns', '[0]')
@section('table_extracted_columns', '[0]')
