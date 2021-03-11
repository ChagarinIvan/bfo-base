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
    <div class="row pt-5">
        <div class="col-sm-10">
            <form class="form-inline" action="/club/{{ $club->id }}/show">
                <div class="form-group mr-1">
                    <input type="text" class="form-control" id="search" name="search" value="{{ $search }}">
                </div>
                <button type="submit" class="btn btn-primary mr-1">{{ __('app.common.search') }}</button>
                <a type="submit" href="/club/{{ $club->id }}/show" class="btn btn-danger">{{ __('app.common.cancel') }}</a>
            </form>
        </div>
    </div>
    <div class="row pt-3">
        <table class="table table-bordered" id="table">
            <thead>
            <tr class="table-info">
                <th>{{ __('app.common.lastname') }}</th>
                <th>{{ __('app.common.name') }}</th>
                <th>{{ __('app.common.birthday') }}</th>
                <th></th>
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
                    <td>
                        <a href="/persons/{{ $person->id }}/edit" class="text-primary">Edit</a>
                        <a href="/persons/{{ $person->id }}/delete" class="text-danger">Delete</a>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
    @if($persons->hasPages())
        <nav aria-label="Page navigation example">
            <ul class="pagination">
                @if(!$persons->onFirstPage())
                    <li class="page-item"><a class="page-link" href="/club/{{ $club->id }}/show?search={{ $search }}">1</a></li>
                @endif
                @if($persons->previousPageUrl() !== null)
                    <li class="page-item"><a class="page-link" href="{{ $persons->previousPageUrl() }}&search={{ $search }}">{{ __('pagination.previous') }}</a></li>
                @endif
                <li class="page-item active"><a class="page-link" href="#">{{ $persons->currentPage() }} <span class="sr-only">(current)</span></a></li>
                @if($persons->nextPageUrl() !== null)
                    <li class="page-item"><a class="page-link" href="{{ $persons->nextPageUrl() }}&search={{ $search }}">{{ __('pagination.next') }}</a></li>
                @endif
                @if($persons->lastPage() !== $persons->currentPage())
                    <li class="page-item"><a class="page-link" href="/club/{{ $club->id }}/show?page={{ $persons->lastPage() }}&search={{ $search }}">{{ $persons->lastPage() }}</a></li>
                @endif
            </ul>
        </nav>
    @endif
@endsection
