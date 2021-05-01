@php
    use App\Models\ProtocolLine;
    use App\Models\Person;
    /**
     * @var ProtocolLine $protocolLine
     * @var Person[] $persons
     * @var string $search
     */
@endphp
@extends('layouts.app')

@section('title', 'Редактирование привязки спортсмена')

@section('content')
    <h2>{{ __('app.ident.edit.title') }}</h2>
    <h3>{{ $protocolLine->lastname }} {{ $protocolLine->firstname }}</h3>
    <h4>{{ $protocolLine->club }}</h4>
    <h4>{{ $protocolLine->year }}</h4>
    <h4>{{ $protocolLine->rank }}</h4>
    <div class="row">
        <div class="col-sm-10">
            <form class="form-inline" action="/protocol-lines/{{ $protocolLine->id }}/edit-person">
                <div class="form-group mr-1">
                    <input type="text" class="form-control" id="search" name="search" value="{{ $search }}">
                </div>
                <button type="submit" class="btn btn-primary mr-1">{{ __('app.common.search') }}</button>
                <a type="submit" href="/protocol-lines/{{ $protocolLine->id }}/edit-person" class="btn btn-danger">{{ __('app.common.cancel') }}</a>
            </form>
        </div>
    </div>
    <div class="row pt-3">
        <table class="table table-bordered table-fixed" id="table">
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
                    $link = "/protocol-lines/{$protocolLine->id}/set-person/{$person->id}";
                @endphp
                <tr>
                    <td><a href="{{ $link }}">{{ $person->lastname }}</a></td>
                    <td><a href="{{ $link }}">{{ $person->firstname }}</a></td>
                    <td><a href="{{ $link }}">{{ $person->club->name ?? ''}}</a></td>
                    <td><a href="{{ $link }}">{{ $person->birthday ? $person->birthday->format('Y') : '' }}</a></td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
    @if($persons->hasPages())
        <nav aria-label="Page navigation example">
            <ul class="pagination">
                @if(!$persons->onFirstPage())
                    <li class="page-item"><a class="page-link" href="/protocol-lines/{{ $protocolLine->id }}/edit-person?search={{ $search }}">1</a></li>
                @endif
                @if($persons->previousPageUrl() !== null)
                    <li class="page-item"><a class="page-link" href="{{ $persons->previousPageUrl() }}&search={{ $search }}">{{ __('pagination.previous') }}</a></li>
                @endif
                <li class="page-item active"><a class="page-link" href="#">{{ $persons->currentPage() }} <span class="sr-only">(current)</span></a></li>
                @if($persons->nextPageUrl() !== null)
                    <li class="page-item"><a class="page-link" href="{{ $persons->nextPageUrl() }}&search={{ $search }}">{{ __('pagination.next') }}</a></li>
                @endif
                @if($persons->lastPage() !== $persons->currentPage())
                    <li class="page-item"><a class="page-link" href="/protocol-lines/{{ $protocolLine->id }}/edit-person?page={{ $persons->lastPage() }}&search={{ $search }}">{{ $persons->lastPage() }}</a></li>
                @endif
            </ul>
        </nav>
    @endif
@endsection
@section('footer')
    <footer class="footer bg-dark">
        <div class="container-relative">
            <span class="text-danger">{{ $protocolLine->lastname }}</span>&nbsp;&nbsp;
            <span class="text-danger">{{ $protocolLine->firstname }}</span>&nbsp;&nbsp;
            <span class="text-danger">{{ $protocolLine->year }}</span>&nbsp;&nbsp;
        </div>
    </footer>
@endsection
