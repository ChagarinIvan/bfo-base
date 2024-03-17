@php
    use App\Bridge\Laravel\Http\Controllers\Person\ShowPersonAction;
    use App\Application\Dto\Club\ViewClubDto;
    use App\Application\Dto\Person\ViewPersonDto;
    /**
     * @var ViewClubDto $club;
     * @var ViewPersonDto[] $persons;
     */
@endphp

@extends('layouts.app')

@section('title', $club->name.' - '.__('app.navbar.persons'))

@section('content')
    <div class="row mb-2">
        <div class="col-12">
            <x-back-button/>
        </div>
    </div>
    <div class="row">
        <table id="table"
               data-cookie="true"
               data-cookie-id-table="club-show"
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
                <th data-sortable="true">{{ __('app.common.lastname') }}</th>
                <th data-sortable="true">{{ __('app.common.name') }}</th>
                <th data-sortable="true">{{ __('app.common.birthday') }}</th>
            </tr>
            </thead>
            <tbody>
            @foreach ($persons as $person)
                @php
                    $link = action(ShowPersonAction::class, [$person->id]);
                @endphp
                <tr>
                    <td><a href="{{ $link }}">{{ $person->lastname }}</a></td>
                    <td><a href="{{ $link }}">{{ $person->firstname }}</a></td>
                    <td>{{ $person->birthday ?: '' }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
@endsection

@section('table_extracted_columns', '[0, 1]')
