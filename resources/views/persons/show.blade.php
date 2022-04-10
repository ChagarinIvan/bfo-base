@php
    use App\Models\Person;
    use App\Models\PersonPayment;
    use App\Models\Rank;
    use Illuminate\Support\Collection;
    /**
     * @var Person $person
     * @var Collection $groupedProtocolLines
     * @var Rank $rank
     * @var ?PersonPayment $personPayment
     */
@endphp

@extends('layouts.app')

@section('title', $person->lastname.' '.$person->firstname)

@section('content')
    @if ($rank)
        <div class="row mb-3">
            <div class="col-12">
                <h4>{{ $rank->rank }} {{ __('app.common.do') }} {{ $rank->finish_date->format('Y-m-d') }}</h4>
            </div>
            @if ($personPayment)
                <div class="col-12">
                    <h4>{{ __('app.common.last_payment') }}: {{ $personPayment->date->format('Y-m-d') }}</h4>
                </div>
            @endif
        </div>
    @endif
    <div class="row mb-3">
        <div class="col-12">
            <h4>{{ $person->birthday ? $person->birthday->format('Y') : '' }}</h4>
        </div>
    </div>
    @auth
        <div class="row mb-3">
            <div class="col-12">
                <h4>{{ __('app.common.prompt') }}</h4>
                @foreach($person->prompts as $prompt)
                    <p>{{ $prompt->prompt }}</p>
                @endforeach
            </div>
        </div>
    @endauth
    <div class="row mb-3">
        <div class="col-12">
            @auth
                <x-edit-button url="{{ action(\App\Http\Controllers\Person\ShowEditPersonAction::class, [$personId]) }}"/>
            @endauth
            <x-button text="app.ranks"
                      color="info"
                      icon="bi-smartwatch"
                      url="{{ action(\App\Http\Controllers\Rank\ShowPersonRanksAction::class, [$person->id]) }}"
            />
            <x-back-button/>
        </div>
    </div>
    @if($person->protocolLines->count() > 0)
        <table id="table"
               data-cookie="true"
               data-cookie-id-table="persons-show"
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
                    <th data-sortable="true">{{ __('app.competition.title') }}</th>
                    <th data-sortable="true">{{ __('app.event.title') }}</th>
                    <th data-sortable="true">{{ __('app.common.lastname') }} {{ __('app.common.name') }}</th>
                    <th data-sortable="true">{{ __('app.common.date') }}</th>
                    <th data-sortable="true">{{ __('app.common.group') }}</th>
                    <th data-sortable="true">{{ __('app.common.birthday') }}</th>
                    <th data-sortable="true">{{ __('app.common.result') }}</th>
                    <th data-sortable="true">{{ __('app.common.place') }}</th>
                    <th data-sortable="true">{{ __('app.common.points') }}</th>
                    <th data-sortable="true">{{ __('app.common.complete_rank') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($groupedProtocolLines as $year => $lines)
                    <tr>
                        <td class="text-center" colspan="9"><b id="{{ $year }}">{{ $year }}</b></td>
                    </tr>
                    @foreach($lines as $line)
                        @php
                            /** @var App\Models\ProtocolLine $line */
                        @endphp
                        <tr>
                            <td>
                                <a href="{{ action(\App\Http\Controllers\Competition\ShowCompetitionAction::class, [$line->distance->event->competition_id]) }}">
                                    {{ \Illuminate\Support\Str::limit($line->distance->event->competition->name, 20, '...') }}
                                </a>
                            </td>
                            <td>
                                <a href="{{ action(\App\Http\Controllers\Event\ShowEventAction::class, [$line->distance->event_id, $line->distance_id]) }}#{{ $line->id }}">
                                    {{ \Illuminate\Support\Str::limit($line->distance->event->name, 20, '...') }}
                                </a>
                            </td>
                            <td>{{ $line->lastname }} {{ $line->firstname }}</td>
                            <td>{{ $line->distance->event->date->format('Y-m-d') }}</td>
                            <td>{{ $line->distance->group ? $line->distance->group->name : '' }}</td>
                            <td>{{ $line->year ?: '' }}</td>
                            <td>{{ $line->time ? $line->time->format('H:i:s') : '-' }}</td>
                            <td>{{ $line->place }}</td>
                            <td>{{ $line->points }}</td>
                            <td>{{ $line->complete_rank }}</td>
                        </tr>
                    @endforeach
                @endforeach
            </tbody>
        </table>
    @endif
@endsection

@section('table_extracted_columns', '[0,1,2]')
@section('table_extracted_dates_columns', '[3]')
