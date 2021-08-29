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
            <form class="form-inline" action="{{ action(\App\Http\Controllers\Club\ShowClubAction::class, [$club->id]) }}">
                <div class="form-group mr-1">
                    <input type="text" class="form-control" id="search" name="search" value="{{ $search }}">
                </div>
                <button type="submit" class="btn btn-primary mr-1">{{ __('app.common.search') }}</button>
                @if ($search !== '')
                    <a type="submit"
                       href="{{ action(\App\Http\Controllers\Club\ShowClubAction::class, [$club->id]) }}"
                       class="btn btn-danger"
                    >{{ __('app.common.cancel') }}</a>
                @else
                    <a type="submit"
                       href="{{ action(\App\Http\Controllers\Club\ShowClubsListAction::class) }}"
                       class="btn btn-danger"
                    >{{ __('app.common.back') }}</a>
                @endif
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
                @auth<th></th>@endauth
            </tr>
            </thead>
            <tbody>
            @foreach ($persons as $person)
                @php
                    $hide = $person->protocolLines->count() === 0;
                    $link = action(\App\Http\Controllers\Person\ShowPersonAction::class, [$person]);
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
                    @auth
                        <td>
                            <a href="{{ action(\App\Http\Controllers\Person\ShowEditPersonFormAction::class, [$person->id]) }}"
                               class="text-primary"
                            >{{ __('app.common.edit') }}</a>
                            <a href="{{ action(\App\Http\Controllers\Person\DeletePersonAction::class, [$person->id]) }}"
                               class="text-danger"
                            >{{ __('app.common.delete') }}</a>
                        </td>
                    @endauth
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
    @if($persons->hasPages())
        <nav aria-label="Page navigation example">
            <ul class="pagination">
                @if(!$persons->onFirstPage())
                    <li class="page-item">
                        <a class="page-link"
                           href="{{ action(\App\Http\Controllers\Club\ShowClubAction::class, [$club->id, 'search' => $search,]) }}"
                        >1</a>
                    </li>
                @endif
                @if($persons->previousPageUrl() !== null)
                    <li class="page-item">
                        <a class="page-link" href="{{ $persons->previousPageUrl() }}&search={{ $search }}">{{ __('pagination.previous') }}</a>
                    </li>
                @endif
                <li class="page-item active"><a class="page-link" href="#">{{ $persons->currentPage() }} <span class="sr-only">(current)</span></a></li>
                @if($persons->nextPageUrl() !== null)
                    <li class="page-item"><a class="page-link" href="{{ $persons->nextPageUrl() }}&search={{ $search }}">{{ __('pagination.next') }}</a></li>
                @endif
                @if($persons->lastPage() !== $persons->currentPage())
                    <li class="page-item">
                        <a class="page-link"
                           href="{{ action(\App\Http\Controllers\Club\ShowClubAction::class, [$club->id, 'page' => $persons->lastPage(), 'search' => $search,]) }}"
                        >{{ $persons->lastPage() }}</a>
                    </li>
                @endif
            </ul>
        </nav>
    @endif
@endsection
