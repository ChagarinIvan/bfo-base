@php
    use App\Models\Distance;
    use App\Models\ProtocolLine;
    use Illuminate\Support\Collection;
    /**
     * @var Distance[] $distances;
     * @var Collection $lines;
     * @var ProtocolLine $line;
     * @var bool $withPoints;
     */
@endphp

@extends('events.show')

@section('groups')
    @foreach ($distances as $distance)
        <tr>
            <td class="text-center" colspan="{{ $withPoints ? 9 : 8 }}"><b id="{{ $distance->group->id }}">{{ $distance->group->name }} - {{ $distance->points }} {{ __('app.distance.points') }}, {{ round($distance->length/1000, 1) }} {{ __('app.distance.length') }}</b></td>
        </tr>
        @foreach($lines->get($distance->id) as $line)
            <tr id="{{ $line->id }}">
                @php
                    $hasPerson = $line->person_id !== null;
                @endphp
                <td>{{ $line->serial_number }}</td>
                @if($hasPerson)
                    @php
                        $link = action(\App\Http\Controllers\Person\ShowPersonAction::class, [$line->person_id]);
                    @endphp
                    <td><a href="{{ $link }}"><u>{{ $line->lastname }}</u></a>&nbsp;
                        @auth
                            <a href="{{ action(\App\Http\Controllers\Person\ShowSetPersonToProtocolLineAction::class, [$line]) }}">
                                <span class="badge rounded-pill bg-warning">{{ __('app.common.edit') }}</span>
                            </a>
                        @endauth
                    </td>
                    <td><a href="{{ $link }}"><u>{{ $line->firstname }}</u></a></td>
                @else
                    <td>{{ $line->lastname }}&nbsp;
                        @auth
                            <a href="{{ action(\App\Http\Controllers\Person\ShowSetPersonToProtocolLineAction::class, [$line]) }}">
                                <span class="badge rounded-pill bg-danger">{{ __('app.common.new') }}</span>
                            </a>
                        @endauth
                    </td>
                    <td>{{ $line->firstname }}</td>
                @endif
                @if($hasPerson && $line->club === ($line->person->club->name ?? '') && $line->person->club_id !== null)
                    <td>
                        <a href="{{ action(\App\Http\Controllers\Club\ShowClubAction::class, [$line->person->club_id]) }}">
                            <u>{{ ($line->club) }}</u>
                        </a>
                    </td>
                @else
                    <td>{{ ($line->club) }}</td>
                @endif
                <td>{{ $line->year }}</td>
                <td>{{ $line->rank }}</td>
                <td>{{ $line->time ? $line->time->format('H:i:s') : '-' }}</td>
                <td>{{ $line->place ?: '-' }}</td>
                <td>{{ $line->complete_rank }}</td>
                @if($withPoints)<td>{{ $line->points ?: '-'}}</td>@endif
            </tr>
        @endforeach
    @endforeach
@endsection
