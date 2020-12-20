@extends('events.show')

@section('groups')
    @foreach ($groups as $group)
        <tr>
            <td class="text-center" colspan="{{ $withPoints ? 9 : 8 }}"><b id="{{ $group->name }}">{{ $group->name }}</b></td>
        </tr>
        @foreach($groupedLines->get($group->id) as $linesGroup)
            @foreach($linesGroup as $line)
                <tr {!! $loop->parent->odd ? 'class="table-secondary"' : '' !!}>
                    <td>{{ $line->lastname }}</td>
                    <td>{{ $line->firstname }}</td>
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
