@extends('events.show')

@section('groups')
    @foreach ($groups as $group)
        <tr>
            <td class="text-center" colspan="{{ $withPoints ? 9 : 8 }}"><b id="{{ $group->name }}">{{ $group->name }}</b></td>
        </tr>
        @foreach($lines->get($group->id) as $line)
            <tr>
                <td>{{ $line->lastname }}</td>
                <td>{{ $line->firstname }}</td>
                <td>{{ $line->club }}</td>
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
