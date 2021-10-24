@php
    use App\Models\ProtocolLine;
    use Illuminate\Support\Collection;
    /**
     * @var Collection $lines;
     * @var ProtocolLine $line;
     * @var bool $withPoints;
     */
@endphp

@extends('events.show')

@section('groups')
    @foreach($lines as $line)
        <tr id="{{ $line->id }}">
            @php
                $hasPerson = $line->person_id !== null;
            @endphp
            <td>{{ $line->serial_number }}</td>
            @if($hasPerson)
                @php
                    $link = action(\App\Http\Controllers\Person\ShowPersonAction::class, [$line->person_id]);
                @endphp
                <td><a href="{{ $link }}">{{ $line->lastname }}</a>&nbsp;
                    @auth
                        <a href="{{ action(\App\Http\Controllers\Person\ShowSetPersonToProtocolLineAction::class, [$line]) }}">
                            <span class="badge rounded-pill bg-warning">{{ __('app.common.edit') }}</span>
                        </a>
                    @endauth
                </td>
                <td><a href="{{ $link }}">{{ $line->firstname }}</a></td>
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
                        {{ ($line->club) }}
                    </a>
                </td>
            @else
                <td><span class="">{{ ($line->club) }}</span></td>
            @endif
            <td>{{ $line->year }}</td>
            <td>{{ $line->rank }}</td>
            <td>{{ $line->time ? $line->time->format('H:i:s') : '-' }}</td>
            <td>{{ $line->place ?: '-' }}</td>
            <td>{{ $line->complete_rank }}</td>
            @if($withPoints)<td>{{ $line->points ?: '-'}}</td>@endif
        </tr>
    @endforeach
@endsection
