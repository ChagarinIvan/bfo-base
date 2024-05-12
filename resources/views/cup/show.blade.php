@php
    use App\Application\Dto\Cup\ViewCupDto;
    use App\Bridge\Laravel\Http\Controllers\Cup\ClearCacheAction;
    use App\Bridge\Laravel\Http\Controllers\Cup\DeleteCupAction;
    use App\Bridge\Laravel\Http\Controllers\Cup\ExportCupTableAction;
    use App\Bridge\Laravel\Http\Controllers\Cup\ShowCupEventGroupAction;
    use App\Bridge\Laravel\Http\Controllers\Cup\ShowCupTableAction;
    use App\Bridge\Laravel\Http\Controllers\Cup\ShowEditCupFormAction;
    use App\Bridge\Laravel\Http\Controllers\CupEvents\DeleteCupEventAction;
    use App\Bridge\Laravel\Http\Controllers\CupEvents\ShowCreateCupEventFormAction;
    use App\Bridge\Laravel\Http\Controllers\CupEvents\ShowEditCupEventFormAction;
    use Illuminate\Support\Str;
    /**
     * @var ViewCupDto $cup;
     */
@endphp

@extends('layouts.app')

@section('title', $cup->name.' - '.$cup->year)

@section('content')
    <div class="row mb-3">
        <div class="col-12">
            @auth
                <x-edit-button url="{{ action(ShowEditCupFormAction::class, [$cup->id]) }}"/>
                <x-button text="app.competition.add_event"
                          color="success"
                          icon="bi-file-earmark-plus-fill"
                          url="{{ action(ShowCreateCupEventFormAction::class, [$cup->id]) }}"
                />
                <x-button text="app.common.cache_clear"
                          color="warning"
                          icon="bi-arrow-clockwise"
                          url="{{ action(ClearCacheAction::class, [$cup->id]) }}"
                />
                <x-button text="app.cup.table.export"
                          color="info"
                          icon="download"
                          url="{{ action(ExportCupTableAction::class, [$cup->id]) }}"
                />
                <x-modal-button modal-id="deleteModalCup{{ $cup->id }}"/>
            @endauth
            <x-button text="app.cup.table"
                      color="secondary"
                      icon="bi-table"
                      url="{{ action(ShowCupTableAction::class, [$cup->id, $cup->groups[0]->id]) }}"
            />
            <x-back-button/>
        </div>
    </div>
    <div class="row mb-3">
        <div class="col-12">
            @foreach($cup->groups as $group)
                <x-badge name="{{ $group->name }}"
                         url="{{ action(ShowCupTableAction::class, [$cup->id, $group->id]) }}"/>
            @endforeach
        </div>
    </div>
    <div class="row">
        <table id="table"
               data-cookie="true"
               data-cookie-id-table="cup-event"
               data-mobile-responsive="true"
               data-check-on-init="true"
               data-min-width="800"
               data-toggle="table"
               data-sort-class="table-active"
               data-resizable="true"
               data-search="true"
               data-search-highlight="true"
               data-custom-sort="customSort"
               data-pagination-next-text="{{ __('app.pagination.next') }}"
               data-pagination-pre-text="{{ __('app.pagination.previous') }}"
        >
            <thead class="table-dark">
            <tr>
                <th data-sortable="true">№</th>
                <th data-sortable="true">{{ __('app.common.title') }}</th>
                <th data-sortable="true">{{ __('app.common.date') }}</th>
                <th data-sortable="true">{{ __('app.common.competitors') }}</th>
                <th data-sortable="true">{{ __('app.common.points') }}</th>
                @auth
                    <th></th>
                @endauth
            </tr>
            </thead>
            <tbody>
            @foreach($cup->cupEvents as $index => $cupEvent)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>
                        <a href="{{ action(ShowCupEventGroupAction::class, [$cup->id, $cupEvent->id, $cup->groups[0]->id]) }}">
                            <u class="">{{ Str::limit($cupEvent->event->competitionName, 30).' - '.Str::limit($cupEvent->event->name, 30) }}</u>
                        </a>
                    </td>
                    <td>{{ $cupEvent->event->date }}</td>
                    <td>{{ $cupEvent->participatesCount }}</td>
                    <td>{{ $cupEvent->points }}</td>
                    @auth
                        <td>
                            <x-edit-button
                                    url="{{ action(ShowEditCupEventFormAction::class, [$cup->id, $cupEvent->id]) }}"/>
                            <x-modal-button modal-id="deleteModal{{ $cupEvent->id }}"/>
                        </td>
                    @endauth
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
    @foreach ($cup->cupEvents as $cupEvent)
        <x-modal modal-id="deleteModal{{ $cupEvent->id }}"
                 url="{{ action(DeleteCupEventAction::class, [$cup->id, $cupEvent->id]) }}"
        />
    @endforeach
    <x-modal modal-id="deleteModalCup{{ $cup->id }}"
             url="{{ action(DeleteCupAction::class, [$cup->id]) }}"
    />
@endsection

@section('table_extracted_columns', '[1]')
@section('table_extracted_dates_columns', '[2]')
