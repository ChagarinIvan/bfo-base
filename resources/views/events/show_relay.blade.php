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
        @foreach($groupedLines->get($group->id) as $linesGroup)
            @foreach($linesGroup as $line)
                <tr {!! $loop->parent->odd ? 'class="table-secondary"' : '' !!}>
                    @if($line->person_id === null)
                        <td>{{ $line->lastname }}&nbsp;
                            <a href="/protocol-lines/{{ $line->id }}/edit-person">
                                <span class="badge rounded-pill bg-danger">add</span>
                            </a>
                        </td>
                        <td>{{ $line->firstname }}</td>
                    @else
                        @php
                            $link = "/persons/{$line->person_id}/show";
                        @endphp
                        <td><a href="{{ $link }}"><u>{{ $line->lastname }}</u></a>&nbsp;
                            <a href="/protocol-lines/{{ $line->id }}/edit-person">
                                <span class="badge rounded-pill bg-warning">edit</span>
                            </a>
                        </td>
                        <td><a href="{{ $link }}"><u>{{ $line->firstname }}</u></a></td>
                    @endif
                    <td>{{ $line->club }}</td>
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
