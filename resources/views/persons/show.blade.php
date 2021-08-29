@php
    use App\Models\Person;
    use Illuminate\Support\Collection;
    /**
     * @var Person $person
     * @var Collection $groupedProtocolLines
     * @var string $backUrl
     */
@endphp

@extends('layouts.app')

@section('title', $person->lastname)

@section('content')
    <h3>{{ $person->lastname }} {{ $person->firstname }}</h3>
    <h4>{{ $person->birthday ? $person->birthday->format('Y') : '' }}</h4>
    <div class="pb-3">
        <a class="btn btn-success mr-2"
           href="{{ action(\App\Http\Controllers\Person\ShowPersonRanksAction::class, [$person->id]) }}"
        >{{ __('app.ranks') }}</a>
        <a class="btn btn-danger mr-2" href="{{ $backUrl }}">{{ __('app.common.back') }}</a>
    </div>
    @if($person->protocolLines->count() > 0)
        <table class="table table-bordered table-fixed"
               id="table"
               data-toggle="table"
               data-sticky-header="true"
        >
            <thead>
            <tr class="table-info">
                <th scope="col">{{ __('app.competition.title') }}</th>
                <th scope="col">{{ __('app.event.title') }}</th>
                <th scope="col">{{ __('app.common.lastname') }} {{ __('app.common.name') }}</th>
                <th scope="col">{{ __('app.common.date') }}</th>
                <th scope="col">{{ __('app.common.group') }}</th>
                <th scope="col">{{ __('app.common.result') }}</th>
                <th scope="col">{{ __('app.common.place') }}</th>
                <th scope="col">{{ __('app.common.points') }}</th>
                <th scope="col">{{ __('app.common.complete_rank') }}</th>
            </tr>
            </thead>
            <tbody>
            @foreach ($groupedProtocolLines as $year => $lines)
                @php
                    use App\Models\ProtocolLine;
                    use Illuminate\Support\Collection;
                    /** @var ProtocolLine[]|Collection $lines */;
                @endphp
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
                                <u>{{ \Illuminate\Support\Str::limit($line->distance->event->competition->name, 20, '...') }}</u>
                            </a>
                        </td>
                        <td>
                            <a href="{{ action(\App\Http\Controllers\Event\ShowEventAction::class, [$line->distance->event->id]) }}#{{ $line->id }}">
                                <u>{{ \Illuminate\Support\Str::limit($line->distance->event->name, 20, '...') }}</u>
                            </a>
                            @foreach($line->distance->event->flags as $flag)
                                <span class="badge" style="background: {{ $flag->color }}">
                                    <a href="{{ action(\App\Http\Controllers\Flags\ShowFlagEventsAction::class, [$flag]) }}">{{ $flag->name }}</a>
                                </span>
                            @endforeach
                        </td>
                        <td>{{ $line->lastname }} {{ $line->firstname }}</td>
                        <td>{{ $line->distance->event->date->format('Y-m-d') }}</td>
                        <td>{{ $line->distance->group->name }}</td>
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
