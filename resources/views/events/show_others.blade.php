@php
    use App\Models\Group;
    use App\Models\ProtocolLine;
    use Illuminate\Support\Collection;
    /**
     * @var Group[] $groups;
     * @var Collection $lines;
     * @var ProtocolLine $line;
     * @var bool $withPoints;
     */
@endphp

@extends('events.show')

@section('groups')
    @foreach ($groups as $group)
        <tr>
            <td class="text-center" colspan="{{ $withPoints ? 9 : 8 }}"><b id="{{ $group->name }}">{{ $group->name }}</b></td>
        </tr>
        @foreach($lines->get($group->id) as $line)
            <tr id="{{ $line->id }}">
                @php
                    $hasPerson = $line->person_id !== null;
                @endphp
                @if($hasPerson)
                    @php
                        $link = "/persons/{$line->person_id}/show";
                    @endphp
                    <td><a href="{{ $link }}"><u>{{ $line->lastname }}</u></a>&nbsp;
                        <a href="/protocol-lines/{{ $line->id }}/edit-person">
                            <span class="badge rounded-pill bg-warning">edit</span>
                        </a>
                    </td>
                    <td><a href="{{ $link }}"><u>{{ $line->firstname }}</u></a></td>
                @else
                    <td>{{ $line->lastname }}&nbsp;
                        <a href="/protocol-lines/{{ $line->id }}/edit-person">
                            <span class="badge rounded-pill bg-danger">add</span>
                        </a>
                    </td>
                    <td>{{ $line->firstname }}</td>
                @endif
                @if($hasPerson && $line->club === ($line->person->club->name ?? ''))
                    <td><a href="/club/{{ $line->person->club_id }}/show"><u>{{ ($line->club) }}</u></a></td>
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
