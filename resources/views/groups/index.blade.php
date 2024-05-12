@php
    use App\Bridge\Laravel\Http\Controllers\Groups\DeleteGroupAction;use App\Bridge\Laravel\Http\Controllers\Groups\ShowGroupAction;use App\Bridge\Laravel\Http\Controllers\Groups\ShowUnitGroupsAction;use App\Domain\Group\Group;use Illuminate\Support\Collection;
    /**
     * @var Collection|Group[] $groups
     */
@endphp

@extends('layouts.app')

@section('title', __('app.common.groups'))

@section('content')
    <div class="row">
        <table id="table"
               data-cookie="true"
               data-cookie-id-table="group-list"
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
                <th data-sortable="true">{{ __('app.groups.events_count') }}</th>
                @auth
                    <th></th>
                @endauth
            </tr>
            </thead>
            <tbody>
            @foreach ($groups as $group)
                <tr>
                    <td>
                        <x-badge name="{{ $group->name }}"
                                 url="{{ action(ShowGroupAction::class, [$group->id]) }}"
                        />
                    </td>
                    <td>{{ $group->distances->count() }}</td>
                    <td>
                        <x-button
                                url="{{ action(ShowUnitGroupsAction::class, [$group->id]) }}"
                                text="app.common.sum"
                                color="info"
                                icon="bi-stickies"
                        />
                        <x-edit-button/>
                        <x-modal-button modal-id="deleteModal{{ $group->id }}"/>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
    @foreach ($groups as $group)
        <x-modal modal-id="deleteModal{{ $group->id }}"
                 url="{{ action(DeleteGroupAction::class, [$group->id]) }}"
        />
    @endforeach
@endsection

@section('table_extracted_columns', '[0]')
