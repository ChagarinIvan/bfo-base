@php
    use App\Bridge\Laravel\Http\Controllers\Club\ShowCreateClubFormAction;
    use App\Application\Dto\Club\ViewClubDto;
    /**
     * @var ViewClubDto[] $clubs;
     */
@endphp

@extends('layouts.app')

@section('title', __('app.navbar.clubs'))

@section('content')
    @auth
        <div class="row mb-3">
            <div class="col-12">
                <x-button text="app.common.new"
                          color="success"
                          icon="bi-file-earmark-plus-fill"
                          url="{{ action(ShowCreateClubFormAction::class) }}"
                />
            </div>
        </div>
    @endauth
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
               data-pagination-next-text="{{ __('app.pagination.next') }}"
               data-pagination-pre-text="{{ __('app.pagination.previous') }}"
        >
            <thead class="table-dark">
            <tr>
                <th data-sortable="true">{{ __('app.common.title') }}</th>
                <th data-sortable="true">{{ __('app.club.persons_count') }}</th>
                @auth
                    <th data-sortable="true">{{ __('app.common.created') }}</th>
                    <th data-sortable="true">{{ __('app.common.updated') }}</th>
                @endauth
            </tr>
            </thead>
            <tbody>
            @foreach ($clubs as $club)
                <tr>
                    <td>
                        <x-club-link :clubId="$club->id"></x-club-link>
                    </td>
                    <td>{{ $club->personsCount }}</td>
                    @auth
                        <td><x-impression :impression="$club->created"/></td>
                        <td><x-impression :impression="$club->updated"/></td>
                    @endauth
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
@endsection

@section('table_extracted_columns', '[0]')
