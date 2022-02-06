@php
    use App\Models\Flag;
    use App\Models\Event;
    use Illuminate\Support\Collection;
    /**
     * @var Flag[]|Collection $flags;
     * @var Event $event;
     */
    $eventFlags = $event->flags;
    $eventFlags = $eventFlags->keyBy('id');
@endphp

@extends('layouts.app')

@section('title', __('app.flags.add_flags_title').' - '.$event->name)

@section('content')
    <div class="row mb-3">
        <div class="col-12 col-md-12 col-lg-6 col-xl-4 col-xxl-3">
            <x-back-button/>
        </div>
    </div>
    <div class="row mb-3">
        <div class="col-12">
            @foreach($eventFlags as $flag)
                <x-badge color="{{ $flag->color }}"
                         name="{{ $flag->name }}"
                         url="{{ action(\App\Http\Controllers\Flags\ShowFlagEventsAction::class, [$flag]) }}"
                />
            @endforeach
        </div>
    </div>
    <div class="row mb-3">
        <table id="table"
               data-cookie="true"
               data-cookie-id-table="add-flags"
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
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($flags as $flag)
                    <tr>
                        <td>
                            <x-badge color="{{ $flag->color }}"
                                     name="{{ $flag->name }}"
                            />
                        </td>
                        <td>
                            @if(!$eventFlags->has($flag->id))
                                <x-button text="app.common.new"
                                          color="info"
                                          icon="bi-file-earmark-plus-fill"
                                          url="{{ action(\App\Http\Controllers\Event\AddFlagToEventAction::class, [$event, $flag->id]) }}"
                                />
                            @else
                                <x-button text="app.common.delete"
                                          color="danger"
                                          icon="bi-trash-fill"
                                          url="{{ action(\App\Http\Controllers\Event\ShowAddFlagToEventFormAction::class, [$event]) }}"
                                />
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection

@section('table_extracted_columns', '[0]')
