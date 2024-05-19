@php
    use App\Bridge\Laravel\Http\Controllers\Person\SetProtocolLinePersonAction;
    use App\Domain\Person\Person;
    use App\Domain\ProtocolLine\ProtocolLine;
    /**
     * @var ProtocolLine $protocolLine
     * @var Person[] $persons
     * @var string $search
     */
@endphp

@extends('layouts.app')

@section('title', __('app.ident.edit.title'))

@section('content')
    <div class="row">
        <h4>{{ $protocolLine->lastname }} {{ $protocolLine->firstname }}</h4>
    </div>
    <div class="row"><h4>{{ $protocolLine->club }}</h4></div>
    <div class="row"><h4>{{ $protocolLine->year }}</h4></div>
    <div class="row"><h4>{{ $protocolLine->rank }}</h4></div>
    <div class="row mb-3">
        <div class="col-12">
            <x-back-button/>
        </div>
    </div>
    <div class="row">
        <table id="table"
               data-cookie="true"
               data-cookie-id-table="edit-protocol-line-person"
               data-mobile-responsive="true"
               data-check-on-init="true"
               data-min-width="800"
               data-toggle="table"
               data-sort-class="table-active"
               data-search="true"
               data-search-highlight="true"
               data-resizable="true"
               data-pagination="true"
               data-page-list="[10,25,50,100,All]"
               data-custom-sort="customSort"
               data-pagination-next-text="{{ __('app.pagination.next') }}"
               data-pagination-pre-text="{{ __('app.pagination.previous') }}"
        >
            <thead class="table-dark">
            <tr>
                <th data-sortable="true">{{ __('app.common.lastname') }}</th>
                <th data-sortable="true">{{ __('app.common.name') }}</th>
                <th data-sortable="true">{{ __('app.club.name') }}</th>
                <th data-sortable="true">{{ __('app.common.birthday') }}</th>
            </tr>
            </thead>
            <tbody>
            @foreach ($persons as $person)
                @php
                    $link = action(SetProtocolLinePersonAction::class, [$person, $protocolLine->id]);
                @endphp
                <tr>
                    <td><a href="{{ $link }}">{{ $person->lastname }}</a></td>
                    <td><a href="{{ $link }}">{{ $person->firstname }}</a></td>
                    <td><a href="{{ $link }}">{{ $person->club->name ?? ''}}</a></td>
                    <td><a href="{{ $link }}">{{ $person->birthday ? $person->birthday->format('Y') : '' }}</a></td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
@endsection

@section('table_extracted_columns', '[0,1,2,3]')
