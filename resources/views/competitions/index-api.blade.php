@php
    use App\Bridge\Laravel\Http\Controllers\Competition\ShowCreateCompetitionFormAction;
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
                          url="{{ action(ShowCreateCompetitionFormAction::class) }}"
                />
            </div>
        </div>
    @endauth
    <div class="row">
        <table id="table"
               data-cookie="true"
               data-url="/api/competitions-json"
               data-cookie-id-table="competition-list-{{ $selectedYear }}"
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
                <th data-sortable="true" data-field="name">{{ __('app.common.title') }}</th>
            </tr>
            </thead>
        </table>
    </div>
@endsection

@section('table_extracted_columns', '[0]')
@section('table_extracted_dates_columns', '[1]')
