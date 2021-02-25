@php
    use App\Models\Person;
    use App\Models\Club;
    /**
     * @var Club $club;
     * @var Person[] $persons;
     */
@endphp

@extends('layouts.app')

@section('title', __('app.navbar.persons'))

@section('content')
    <h3>{{ __('app.club.name').' '.$club->name }}</h3>
    <table class="table table-bordered" id="table">
        <thead>
        <tr class="table-info">
            <th>{{ __('app.common.lastname') }}</th>
            <th>{{ __('app.common.name') }}</th>
            <th>{{ __('app.common.birthday') }}</th>
        </tr>
        </thead>
        <tbody>
        @foreach ($persons as $person)
            @php
                $hide = $person->protocolLines->count() === 0;
                $link = "/persons/{$person->id}/show";
            @endphp
            <tr>
                @if($hide)
                    <td>{{ $person->lastname }}</td>
                    <td>{{ $person->firstname }}</td>
                    <td>{{ $person->birthday ? $person->birthday->format('Y') : '' }}</td>
                @else
                    <td><a href="{{ $link }}"><u>{{ $person->lastname }}</u></a></td>
                    <td><a href="{{ $link }}"><u>{{ $person->firstname }}</u></a></td>
                    <td><a href="{{ $link }}"><u>{{ $person->birthday ? $person->birthday->format('Y') : '' }}</u></a></td>
                @endif
            </tr>
        @endforeach
        </tbody>
    </table>
    {{ $persons->links() }}
@endsection
