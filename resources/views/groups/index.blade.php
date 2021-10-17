@php
    use App\Models\Group;
    use Illuminate\Support\Collection;
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
               data-sticky-header="true"
               data-sticky-header-offset-y="56"
               data-custom-sort="customSort"
               data-pagination-next-text="{{ __('pagination.next') }}"
               data-pagination-pre-text="{{ __('pagination.previous') }}"
        >
            <thead class="table-dark">
                <tr>
                    <th data-sortable="true">{{ __('app.common.title') }}</th>
                    <th data-sortable="true">{{ __('app.groups.events_count') }}</th>
                    @auth<th></th>@endauth
                </tr>
            </thead>
            <tbody>
                @foreach ($groups as $group)
                    <tr>
                        <td>
                            <x-badge color="{{ \App\Facades\Color::getColor($group->name) }}"
                                     name="{{ $group->name }}"
                                     url="{{ action(\App\Http\Controllers\Groups\ShowGroupAction::class, [$group->id]) }}"
                            />
                        </td>
                        <td>{{ $group->distances->count() }}</td>
                        <td>
                            <x-button text="app.common.sum" color="info" icon="bi-stickies"/>
                            <x-edit-button/>
                            <x-delete-button modal-id="deleteModal{{ $group->id }}"/>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @foreach ($groups as $group)
        <x-modal modal-id="deleteModal{{ $group->id }}"
                 url="{{ action(\App\Http\Controllers\Groups\DeleteGroupAction::class, [$group->id]) }}"
        />
    @endforeach
@endsection

@section('table_extracted_columns', '[0]')
