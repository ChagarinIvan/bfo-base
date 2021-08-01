@php
    use App\Models\Person;
    use Illuminate\Pagination\Paginator;
    use Illuminate\Support\Collection;
    /**
     * @var Paginator $paginator;
     * @var Collection|Person[] $persons;
     * @var string $search;
     */
@endphp

@extends('layouts.app')

@section('title', __('app.navbar.persons'))

@section('content')
    <h3 id="up">{{ __('app.navbar.persons') }}</h3>
    <div class="row pt-5">
        @auth
            <div class="col-sm-2">
                <a class="btn btn-success mr-2" href="/persons/create">{{ __('app.person.create_button') }}</a>
            </div>
        @endauth
        <div class="col-sm-10">
            <form class="form-inline" action="/persons">
                <div class="form-group mr-1">
                    <input type="text" class="form-control" id="search" name="search" value="{{ $search }}">
                </div>
                <button type="submit" class="btn btn-primary mr-1">{{ __('app.common.search') }}</button>
                @if ($search !== '')
                    <a type="submit" href="/persons" class="btn btn-danger">{{ __('app.common.cancel') }}</a>
                @endif
            </form>
        </div>
    </div>
    <div class="row pt-3">
        <table class="table table-bordered" id="table">
            <thead>
            <tr class="table-info">
                <th>{{ __('app.common.fio') }}</th>
                <th>{{ __('app.common.events_count') }}</th>
                <th>{{ __('app.club.name') }}</th>
                <th>{{ __('app.common.birthday') }}</th>
                @auth<th></th>@endauth
            </tr>
            </thead>
            <tbody>
            @foreach ($persons as $person)
                @php
                    /** @var \App\Models\Person $person */
                    $count = $person->protocolLines->count();
                    $hide = $count === 0;
                    $link = "/persons/{$person->id}/show";
                @endphp
                <tr>
                    @if($hide)
                        <td>{{ $person->lastname }} {{ $person->firstname }}</td>
                        <td><span class="badge" style="background: {{ \App\Facades\Color::getColor($count) }}">{{ $count }}</span></td>
                        @if($person->club === null)<td></td>@else<td><a href="/club/{{ $person->club_id }}/show"><u>{{ $person->club->name }}</u></a></td>@endif
                        <td>{{ $person->birthday ? $person->birthday->format('Y') : '' }}</td>
                    @else
                        <td><a href="{{ $link }}"><u>{{ $person->lastname }} {{ $person->firstname }}</u></a></td>
                        <td><span class="badge" style="background: {{ \App\Facades\Color::getColor($count) }}">{{ $count }}</span></td>
                        @if($person->club === null)<td></td>@else<td><a href="/club/{{ $person->club_id }}/show"><u>{{ $person->club->name }}</u></a></td>@endif
                        <td><a href="{{ $link }}"><u>{{ $person->birthday ? $person->birthday->format('Y') : '' }}</u></a></td>
                    @endif
                    @auth
                        <td>
                            <a href="/persons/{{ $person->id }}/edit" class="text-primary">{{ __('app.common.edit') }}</a>
                            <a href="/persons/{{ $person->id }}/delete" class="text-danger">{{ __('app.common.delete') }}</a>
                        </td>
                    @endauth
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
    @if($paginator->hasPages())
        <nav aria-label="Page navigation example">
            <ul class="pagination">
                @if(!$paginator->onFirstPage())
                    <li class="page-item"><a class="page-link" href="/persons?search={{ $search }}">1</a></li>
                @endif
                @if($paginator->previousPageUrl() !== null)
                    <li class="page-item"><a class="page-link" href="{{ $paginator->previousPageUrl() }}&search={{ $search }}">{{ __('pagination.previous') }}</a></li>
                @endif
                <li class="page-item active"><a class="page-link" href="#">{{ $paginator->currentPage() }} <span class="sr-only">(current)</span></a></li>
                @if($paginator->nextPageUrl() !== null)
                    <li class="page-item"><a class="page-link" href="{{ $paginator->nextPageUrl() }}&search={{ $search }}">{{ __('pagination.next') }}</a></li>
                @endif
                @if($paginator->lastPage() !== $paginator->currentPage())
                    <li class="page-item"><a class="page-link" href="/persons?page={{ $paginator->lastPage() }}&search={{ $search }}">{{ $paginator->lastPage() }}</a></li>
                @endif
            </ul>
        </nav>
    @endif
@endsection
