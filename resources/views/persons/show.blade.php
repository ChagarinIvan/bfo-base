@php
    use App\Models\Person;
    use Illuminate\Support\Collection;
    /**
     * @var Person $person
     * @var Collection $groupedProtocolLines
     */
@endphp

@extends('layouts.app')

@section('title', $person->lastname)

@section('content')
    <h3>{{ $person->lastname }} {{ $person->firstname }}</h3>
    <h4>{{ $person->birthday ? $person->birthday->format('Y') : '' }}</h4>
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
                    /** @var ProtocolLine[]|Collection $lines */;
                @endphp
                <tr>
                    <td class="text-center" colspan="9"><b id="{{ $year }}">{{ $year }}</b></td>
                </tr>
                @foreach($lines as $line)
                    <tr>
                        <td><a href="/competitions/{{ $line->event->competition_id }}/show"><u>{{ Str::limit($line->event->competition->name, 20, '...') }}</u></a></td>
                        <td>
                            <a href="/competitions/events/{{ $line->event_id }}/show#{{ $line->id }}"><u>{{ Str::limit($line->event->name, 20, '...') }}</u></a>
                            @foreach($line->event->flags as $flag)
                                <span class="badge" style="background: {{ $flag->color }}"><a href="/flags/{{ $flag->id }}/show-events">{{ $flag->name }}</a></span>
                            @endforeach
                        </td>
                        <td>{{ $line->lastname }} {{ $line->firstname }}</td>
                        <td>{{ $line->event->date->format('Y-m-d') }}</td>
                        <td>{{ $line->group->name }}</td>
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
