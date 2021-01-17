@php
    use App\Models\ProtocolLine;
    use App\Models\Person;
    /**
     * @var ProtocolLine $protocolLine
     * @var Person[] $persons
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
    <table class="table table-bordered table-fixed" id="table"
    >
        <thead>
        <tr class="table-info">
            <th>{{ __('app.common.lastname') }}</th>
            <th>{{ __('app.common.name') }}</th>
            <th>{{ __('app.common.club') }}</th>
            <th>{{ __('app.common.birthday') }}</th>
        </tr>
        </thead>
        <tbody>
        @foreach ($persons as $person)
            @php
                $link = "/protocol-lines/{$protocolLine->id}/set-person/{$person->id}?url=".url()->previous();
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
