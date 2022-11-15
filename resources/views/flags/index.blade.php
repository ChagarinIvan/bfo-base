@php
    use App\Http\Controllers\Flags\DeleteFlagAction;
    use App\Http\Controllers\Flags\ShowCreateFlagFormAction;
    use App\Http\Controllers\Flags\ShowEditFlagFormAction;
    use App\Http\Controllers\Flags\ShowFlagEventsAction;
    use App\Models\Flag;
    /**
     * @var Flag[] $flags;
     */
@endphp

@extends('layouts.app')

@section('title', __('app.navbar.flags'))

@section('content')
    @auth
        <div class="row mb-3">
            <div class="col-12">
                <x-button text="app.common.new"
                          color="success"
                          icon="bi-file-earmark-plus-fill"
                          url="{{ action(ShowCreateFlagFormAction::class) }}"
                />
            </div>
        </div>
    @endauth
    <div class="row">
        <table id="table"
               data-cookie="true"
               data-cookie-id-table="flags-list"
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
                <th data-sortable="true">{{ __('app.flags.name') }}</th>
                <th>{{ __('app.flags.color') }}</th>
                @auth
                    <th></th>
                @endauth
            </tr>
            </thead>
            <tbody>
            @foreach ($flags as $flag)
                <tr>
                    <td>
                        <a href="{{ action(ShowFlagEventsAction::class, [$flag]) }}">
                            {{ $flag->name }}
                        </a>
                    </td>
                    <td style="background: {{ $flag->color }}">{{ $flag->color }}</td>
                    @auth
                        <td>
                            <x-edit-button
                                    url="{{ action(ShowEditFlagFormAction::class, [$flag]) }}"/>
                            <x-delete-button modal-id="deleteModal{{ $flag->id }}"/>
                        </td>
                    @endauth
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
    @foreach ($flags as $flag)
        <x-modal modal-id="deleteModal{{ $flag->id }}"
                 url="{{ action(DeleteFlagAction::class, [$flag]) }}"
        />
    @endforeach
@endsection

@section('table_extracted_columns', '[0,1]')
