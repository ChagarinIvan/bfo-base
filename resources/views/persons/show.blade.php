@php
    use App\Bridge\Laravel\Http\Controllers\Competition\ShowCompetitionAction;use App\Bridge\Laravel\Http\Controllers\Event\ShowEventAction;use App\Bridge\Laravel\Http\Controllers\Person\ExtractPersonAction;use App\Bridge\Laravel\Http\Controllers\Person\ShowEditPersonAction;use App\Bridge\Laravel\Http\Controllers\Person\ShowPersonPaymentsListAction;use App\Bridge\Laravel\Http\Controllers\PersonPrompt\ShowPersonPromptsListAction;use App\Bridge\Laravel\Http\Controllers\Rank\ShowPersonRanksAction;use App\Domain\Person\Person;use App\Domain\PersonPayment\PersonPayment;use App\Models\Rank;use Illuminate\Support\Collection;use Illuminate\Support\Str;

    /**
     * @var Person $person
     * @var Collection $groupedProtocolLines
     * @var Rank $rank
     * @var ?PersonPayment $personPayment
     */

    $personName = "{$person->lastname}_$person->firstname";
@endphp

@extends('layouts.app')

@section('title', $person->lastname.' '.$person->firstname)

@section('content')
    @if ($rank)
        <div class="row mb-3">
            <div class="col-12">
                <h4>{{ $rank->rank }} {{ __('app.common.do') }} {{ $rank->finish_date->format('Y-m-d') }}</h4>
            </div>
        </div>
    @endif
    @if ($personPayment)
        <div class="row mb-3">
            <div class="col-12">
                <h4>{{ __('app.common.last_payment') }}: {{ $personPayment->date->format('Y-m-d') }}</h4>
            </div>
        </div>
    @endif
    <div class="row mb-3">
        <div class="col-12">
            <h4>{{ $person->birthday ? $person->birthday->format('Y') : '' }}</h4>
        </div>
    </div>
    <div class="row mb-3">
        <div class="col-12">
            @auth
                <x-edit-button url="{{ action(ShowEditPersonAction::class, [$person->id]) }}"/>
                <x-button text="app.common.prompts"
                          color="success"
                          icon="bi-terminal"
                          url="{{ action(ShowPersonPromptsListAction::class, [$person->id]) }}"
                />
                <x-button text="app.common.payments"
                          color="warning"
                          icon="bi-currency-dollar"
                          url="{{ action(ShowPersonPaymentsListAction::class, [$person->id]) }}"
                />
            @endauth
            <x-button text="app.ranks"
                      color="info"
                      icon="bi-smartwatch"
                      url="{{ action(ShowPersonRanksAction::class, [$person->id]) }}"
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
                        /** @var \App\Domain\ProtocolLine\ProtocolLine $line */
                        $lineName = "{$line->lastname}_$line->firstname";
                    @endphp
                    <tr>
                        <td>
                            <a href="{{ action(ShowCompetitionAction::class, [$line->distance->event->competition_id]) }}">
                                {{ Str::limit($line->distance->event->competition->name, 20, '...') }}
                            </a>
                        </td>
                        <td>
                            <a href="{{ action(ShowEventAction::class, [$line->distance->event_id, $line->distance_id]) }}#{{ $line->id }}">
                                {{ Str::limit($line->distance->event->name, 20, '...') }}
                            </a>
                        </td>
                        <td>
                            {{ $line->lastname }} {{ $line->firstname }}
                            @if($lineName !== $personName)
                                @auth
                                    <a href="{{ action(ExtractPersonAction::class, [$line->id]) }}">
                                        <span class="badge rounded-pill bg-warning">{{ __('app.common.extract') }}</span>
                                    </a>
                                @endauth
                            @endif
                        </td>
                        <td>{{ $line->distance->event->date->format('Y-m-d') }}</td>
                        <td>{{ $line->distance->group->name ?? '' }}</td>
                        <td>{{ $line->year ?: '' }}</td>
                        <td>{{ $line->time ? $line->time->format('H:i:s') : '-' }}</td>
                        <td>{{ $line->place }}</td>
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
