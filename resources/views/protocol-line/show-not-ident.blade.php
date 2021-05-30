@php
    use App\Models\ProtocolLine;
    use Illuminate\Pagination\Paginator;
    use Illuminate\Support\Collection;
    /**
     * @var Paginator $persons
     * @var ProtocolLine[]|Collection $lines
     * @var string $search
     */
@endphp
@extends('layouts.app')

@section('title', __('app.navbar.no-ident'))

@section('content')
    <h4>{{ __('app.no-ident-title') }}</h4>
    <div class="row pt-5">
        <div class="col-sm-10">
            <form class="form-inline" action="/protocol-lines/not-ident/show">
                <div class="form-group mr-1">
                    <input type="text" class="form-control" id="search" name="search" value="{{ $search }}">
                </div>
                <button type="submit" class="btn btn-primary mr-1">{{ __('app.common.search') }}</button>
                <a type="submit" href="/protocol-lines/not-ident/show" class="btn btn-danger">{{ __('app.common.cancel') }}</a>
            </form>
        </div>
    </div>
    <div class="row pt-3">
        <table class="table table-bordered" id="table">
            <thead>
            <tr class="table-info">
                <th scope="col">{{ __('app.common.lastname') }}</th>
                <th scope="col">{{ __('app.common.name') }}</th>
                <th scope="col">{{ __('app.navbar.competitions') }}</th>
                <th scope="col">{{ __('app.events.title') }}</th>
                <th scope="col">{{ __('app.club.name') }}</th>
                <th scope="col">{{ __('app.common.date') }}</th>
                <th scope="col">{{ __('app.common.group') }}</th>
                <th scope="col">{{ __('app.common.result') }}</th>
                <th scope="col">{{ __('app.common.place') }}</th>
            </tr>
            </thead>
            <tbody>
            @foreach($persons as $person)
                @foreach($rows as $line)
                    @php
                        /** @var \App\Models\ProtocolLine $line */
                    @endphp
                    <tr>
                        @if($loop->first)
                            <td rowspan="{{ count($rows) }}"><a href="{{ "/protocol-lines/{$line->id}/edit-person" }}"><u>{{ $line->lastname }}</u></a></td>
                            <td rowspan="{{ count($rows) }}"><a href="{{ "/protocol-lines/{$line->id}/edit-person" }}"><u>{{ $line->firstname }}</u></a></td>
                        @endif
                        <td><a href="/competitions/{{ $line->event->competition_id }}/show"><u>{{ Str::limit($line->event->competition->name, 20, '...') }}</u></a></td>
                        <td><a href="/competitions/events/{{ $line->event_id }}/show"><u>{{ Str::limit($line->event->name, 20, '...') }}</u></a></td>
                        <td>{{ $line->club }}</td>
                        <td>{{ $line->event->date->format('Y-m-d') }}</td>
                        <td>{{ $line->group->name }}</td>
                        <td>{{ $line->time ? $line->time->format('H:i:s') : '-' }}</td>
                        <td>{{ $line->place }}</td>
                    </tr>
                @endforeach
            @endforeach
            </tbody>
        </table>
    </div>
    @if($persons->hasPages())
        <nav aria-label="Page navigation example">
            <ul class="pagination">
                @if(!$persons->onFirstPage())
                    <li class="page-item"><a class="page-link" href="/protocol-lines/not-ident/show?search={{ $search }}">1</a></li>
                @endif
                @if($persons->previousPageUrl() !== null)
                    <li class="page-item"><a class="page-link" href="{{ $persons->previousPageUrl() }}&search={{ $search }}">{{ __('pagination.previous') }}</a></li>
                @endif
                <li class="page-item active"><a class="page-link" href="#">{{ $persons->currentPage() }} <span class="sr-only">(current)</span></a></li>
                @if($persons->nextPageUrl() !== null)
                    <li class="page-item"><a class="page-link" href="{{ $persons->nextPageUrl() }}&search={{ $search }}">{{ __('pagination.next') }}</a></li>
                @endif
                @if($persons->lastPage() !== $persons->currentPage())
                    <li class="page-item"><a class="page-link" href="/protocol-lines/not-ident/show?page={{ $persons->lastPage() }}&search={{ $search }}">{{ $persons->lastPage() }}</a></li>
                @endif
            </ul>
        </nav>
    @endif
@endsection
