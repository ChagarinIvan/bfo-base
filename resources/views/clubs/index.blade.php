@php
    use App\Models\Club;
    /**
     * @var Club[] $clubs;
     */
@endphp

@extends('layouts.app')

@section('title', __('app.navbar.clubs'))

@section('content')
    <div class="row">
        <table id="table"
               data-cookie="true"
               data-cookie-id-table="clubs-list"
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
               data-pagination-next-text="{{ __('pagination.next') }}"
               data-pagination-pre-text="{{ __('pagination.previous') }}"
        >
            <thead class="table-dark">
                <tr>
                    <th data-sortable="true">{{ __('app.common.title') }}</th>
                    <th data-sortable="true">{{ __('app.club.persons_count') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($clubs as $club)
                    <tr>
                        <td>
                            <a href="{{ action(\App\Http\Controllers\Club\ShowClubAction::class, [$club->id]) }}">
                                {{ $club->name }}
                            </a>
                        </td>
                        <td>{{ $club->persons->count() }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection

@section('table_extracted_columns', '[0]')
