@php
    use App\Models\Person;
    use Illuminate\Support\Collection;
    /**
     * @var Collection|Person[] $persons;
     * @var array $actualRanks;
     */
@endphp

@extends('layouts.app')

@section('title', __('app.navbar.persons'))

@section('content')
    @auth
        <div class="row mb-3">
            <div class="col-12">
                <x-button text="app.person.create_button"
                          color="success"
                          icon="bi-file-earmark-plus-fill"
                          url="{{ action(\App\Http\Controllers\Person\ShowCreatePersonFormAction::class) }}"
                />
            </div>
        </div>
    @endauth
    <div class="row">
        <table id="table"
               data-cookie="true"
               data-cookie-id-table="persons-list"
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
                    <th data-sortable="true">{{ __('app.common.fio') }}</th>
                    <th data-sortable="true">{{ __('app.common.rank') }}</th>
                    <th data-sortable="true">{{ __('app.common.events_count') }}</th>
                    <th data-sortable="true">{{ __('app.club.name') }}</th>
                    <th data-sortable="true">{{ __('app.common.birthday') }}</th>
                    @auth<th></th>@endauth
                </tr>
            </thead>
            <tbody>
                @foreach ($persons as $person)
                    @php
                        /** @var \App\Models\Person $person */
                        $count = $person->protocolLines->count();
                    @endphp
                    <tr>
                        <td><a href="{{ action(\App\Http\Controllers\Person\ShowPersonAction::class, [$person]) }}">{{ $person->lastname }} {{ $person->firstname }}</a></td>
                        <td>
                            <a href="{{ action(\App\Http\Controllers\Rank\ShowPersonRanksAction::class, [$person->id]) }}">
                                {{ isset($actualRanks[$person->id]) ? $actualRanks[$person->id]->rank : '' }}
                            </a>
                        </td>
                        <td>{{ $count }}</td>
                        @if($person->club === null)
                            <td><span class=""></span></td>
                        @else
                            <td>
                                <a href="{{ action(\App\Http\Controllers\Club\ShowClubAction::class, [$person->club_id]) }}">
                                    {{ $person->club->name }}
                                </a>
                            </td>
                        @endif
                        <td>{{ $person->birthday ? $person->birthday->format('Y') : '' }}</td>
                        @auth
                            <td>
                                <x-edit-button url="{{ action(\App\Http\Controllers\Person\ShowEditPersonFormAction::class, [$person->id]) }}"/>
                                <x-delete-button modal-id="deleteModal{{ $person->id }}"/>
                            </td>
                        @endauth
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @foreach ($persons as $person)
        <x-modal modal-id="deleteModal{{ $person->id }}"
                 url="{{ action(\App\Http\Controllers\Person\DeletePersonAction::class, [$person->id]) }}"
        />
    @endforeach
@endsection

@section('table_extracted_columns', '[1,2]')
