@php
    use App\Models\Year;
    use Illuminate\Support\Collection;
    /**
     * @var Collection $competitions;
     * @var Year $selectedYear;
     */
@endphp

@extends('layouts.app')

@section('title', __('app.competition.title'))

@section('content')
    @auth
        <div class="row mb-3">
            <div class="col-12 col-sm-12 col-md-6 col-lg-4 col-xl-3 col-xxl-2">
                <x-button text="app.competition.add_competition"
                          color="success"
                          icon="bi-file-earmark-plus-fill"
                          url="{{ action(\App\Http\Controllers\Competition\ShowCreateCompetitionFormAction::class, [$selectedYear->value]) }}"
                />
            </div>
        </div>
    @endauth
    <div class="row">
        <ul class="nav nav-tabs">
            @foreach(\App\Models\Year::cases() as $year)
                <li class="nav-item">
                    <a href="{{ action(\App\Http\Controllers\Competition\ShowCompetitionsListAction::class, [$year->value]) }}"
                       class="text-decoration-none nav-link {{ $year === $selectedYear ? 'active' : '' }}"
                    >
                        <b>{{ $year->value }}</b>
                    </a>
                </li>
            @endforeach
        </ul>
        <div class="tab-content">
            <div class="tab-pane fade show active">
                <table id="table"
                       data-cookie="true"
                       data-cookie-id-table="competition-list-{{ $selectedYear->value }}"
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
                            <th data-sortable="true">{{ __('app.common.title') }}</th>
                            <th data-sortable="true">{{ __('app.common.dates') }}</th>
                            <th data-sortable="true">{{ __('app.common.description') }}</th>
                            @auth<th></th>@endauth
                        </tr>
                    </thead>
                    <tbody>
                    @foreach ($competitions as $competition)
                        <tr>
                            <td>
                                <a href="{{ action(\App\Http\Controllers\Competition\ShowCompetitionAction::class, [$competition->id]) }}"
                                >{{ \Illuminate\Support\Str::limit($competition->name, 50) }}</a>
                            </td>
                            <td>{{ $competition->from->format('Y-m-d') }} / {{ $competition->to->format('Y-m-d') }}</td>
                            <td><small>{{ \Illuminate\Support\Str::limit($competition->description) }}</small></td>
                            @auth
                                <td>
                                    <x-edit-button url="{{ action(\App\Http\Controllers\Competition\ShowEditCompetitionFormAction::class, [$year->value, $competition->id]) }}"/>
                                    <x-delete-button modal-id="deleteModal{{ $competition->id }}"/>
                                </td>
                            @endauth
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @foreach ($competitions as $competition)
        <x-modal modal-id="deleteModal{{ $competition->id }}"
                 url="{{ action(\App\Http\Controllers\Competition\DeleteCompetitionAction::class, [$selectedYear->value    , $competition->id]) }}"
        />
    @endforeach
@endsection

@section('table_extracted_columns', '[0]')
@section('table_extracted_dates_columns', '[1]')
