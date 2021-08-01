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
        @foreach($groupedLines->get($distance->id) as $linesGroup)
            @foreach($linesGroup as $line)
                <tr id="{{ $line->id }}" {!! $loop->parent->odd ? 'class="table-secondary"' : '' !!}>
                    @php
                        $hasPerson = $line->person_id !== null;
                    @endphp
                    @if($hasPerson)
                        @php
                            $link = "/persons/{$line->person_id}/show";
                        @endphp
                        <td><a href="{{ $link }}"><u>{{ $line->lastname }}</u></a>&nbsp;
                            @auth
                                <a href="/protocol-lines/{{ $line->id }}/edit-person">
                                    <span class="badge rounded-pill bg-warning">{{ __('app.common.edit') }}</span>
                                </a>
                            @endauth
                        </td>
                        <td><a href="{{ $link }}"><u>{{ $line->firstname }}</u></a></td>
                    @else
                        <td>{{ $line->lastname }}&nbsp;
                            @auth
                                <a href="/protocol-lines/{{ $line->id }}/edit-person">
                                    <span class="badge rounded-pill bg-danger">{{ __('app.common.new') }}</span>
                                </a>
                            @endauth
                        </td>
                        <td>{{ $line->firstname }}</td>
                    @endif
                    @if(
                        $hasPerson &&
                        ($line->club === ($line->person->club->name ?? '') ||
                        (preg_match("#^[A-Z]{3}\s.*#", $line->club) && substr($line->club, 3) === ($line->person->club->name ?? '')))
                    )
                        <td><a href="/club/{{ $line->person->club_id }}/show"><u>{{ ($line->club) }}</u></a></td>
                    @else
                        <td>{{ ($line->club) }}</td>
                    @endif
                    <td>{{ $line->year }}</td>
                    <td>{{ $line->rank }}</td>
                    <td>{{ $line->time ? $line->time->format('H:i:s') : '-' }}</td>
                    @if($loop->first)<td {!! $loop->first ? 'rowspan="'.count($linesGroup).'"' : '' !!}><b>{{ $line->place ?: '-' }}</b></td>@endif
                    @if($loop->first)<td {!! $loop->first ? 'rowspan="'.count($linesGroup).'"' : '' !!}><b>{{ $line->complete_rank }}</b></td>@endif
                    @if($loop->first) @if($withPoints)<td {!! $loop->first ? 'rowspan="'.count($linesGroup).'"' : '' !!}><b>{{ $line->points ?: '-' }}</b></td>@endif @endif
                </tr>
            @endforeach
        @endforeach
    @endforeach
@endsection
