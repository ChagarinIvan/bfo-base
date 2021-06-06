@php
    use App\Models\Club;
    /**
     * @var string $search
     * @var Club[] $clubs;
     */
@endphp

@extends('layouts.app')

@section('title', __('app.navbar.clubs'))

@section('content')
    <h3>{{ __('app.navbar.clubs') }}</h3>
    <div class="row pt-5">
        <div class="col-sm-10">
            <form class="form-inline" action="/club">
                <div class="form-group mr-1">
                    <input type="text" class="form-control" id="search" name="search" value="{{ $search }}">
                </div>
                <button type="submit" class="btn btn-primary mr-1">{{ __('app.common.search') }}</button>
                @if($search !== '')
                    <a type="submit" href="/club" class="btn btn-danger">{{ __('app.common.cancel') }}</a>
                @endif
            </form>
        </div>
    </div>
    <div class="row pt-3">
        <table class="table table-bordered" id="table">
            <thead>
            <tr class="table-info">
                <th>{{ __('app.common.title') }}</th>
                <th>{{ __('app.club.persons_count') }}</th>
            </tr>
            </thead>
            <tbody>
            @foreach ($clubs as $club)
                <tr>
                    <td><a href="/club/{{ $club->id }}/show">{{ $club->name }}</a></td>
                    <td>{{ $club->persons->count() }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
    @if($clubs->hasPages())
        <nav aria-label="Page navigation example">
            <ul class="pagination">
                @if(!$clubs->onFirstPage())
                    <li class="page-item"><a class="page-link" href="/club?search={{ $search }}">1</a></li>
                @endif
                @if($clubs->previousPageUrl() !== null)
                    <li class="page-item"><a class="page-link" href="{{ $clubs->previousPageUrl() }}&search={{ $search }}">{{ __('pagination.previous') }}</a></li>
                @endif
                <li class="page-item active"><a class="page-link" href="#">{{ $clubs->currentPage() }} <span class="sr-only">(current)</span></a></li>
                @if($clubs->nextPageUrl() !== null)
                    <li class="page-item"><a class="page-link" href="{{ $clubs->nextPageUrl() }}&search={{ $search }}">{{ __('pagination.next') }}</a></li>
                @endif
                @if($clubs->lastPage() !== $clubs->currentPage())
                    <li class="page-item"><a class="page-link" href="/club?page={{ $clubs->lastPage() }}&search={{ $search }}">{{ $clubs->lastPage() }}</a></li>
                @endif
            </ul>
        </nav>
    @endif
@endsection
