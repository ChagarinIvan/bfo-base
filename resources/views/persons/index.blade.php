@php
    use App\Models\Person;
    /**
     * @var Person[] $persons;
     */
@endphp

@extends('layouts.app')

@section('title', __('app.navbar.persons'))

@section('content')
    <h3 id="up">{{ __('app.person.title') }}</h3>
    <div class="row pt-5">
        <a class="btn btn-success mr-2" href="/persons/create">{{ __('app.person.create_button') }}</a>
    </div>
    <div class="row pt-3">
        <table class="table table-bordered" id="table">
            <thead>
            <tr class="table-info">
                <th>{{ __('app.common.lastname') }}</th>
                <th>{{ __('app.common.name') }}</th>
                <th>{{ __('app.club.name') }}</th>
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
                        @if($person->club === null)<td></td>@else<td><a href="/club/{{ $person->club_id }}/show"><u>{{ $person->club->name }}</u></a></td>@endif
                        <td>{{ $person->birthday ? $person->birthday->format('Y') : '' }}</td>
                    @else
                        <td><a href="{{ $link }}"><u>{{ $person->lastname }}</u></a></td>
                        <td><a href="{{ $link }}"><u>{{ $person->firstname }}</u></a></td>
                        @if($person->club === null)<td></td>@else<td><a href="/club/{{ $person->club_id }}/show"><u>{{ $person->club->name }}</u></a></td>@endif
                        <td><a href="{{ $link }}"><u>{{ $person->birthday ? $person->birthday->format('Y') : '' }}</u></a></td>
                    @endif
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
    {{ $persons->links() }}
@endsection
