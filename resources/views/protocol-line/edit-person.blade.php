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
    <h3>{{ $protocolLine->lastname }} {{ $protocolLine->firstname }}</h3>
    <h4>{{ $protocolLine->club }}</h4>
    <h4>{{ $protocolLine->year }}</h4>
    <h4>{{ $protocolLine->rank }}</h4>
    <div class="row pt-3">
        <table class="table table-bordered table-fixed"
               id="table"
               data-toggle="table"
               data-sticky-header="true"
        >
            <thead>
            <tr class="table-info">
                <th>{{ $protocolLine->lastname }}</th>
                <th>{{ $protocolLine->firstname }}</th>
                <th>-</th>
                <th>{{ $protocolLine->year }}</th>
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
                    <td><a href="{{ $link }}">{{ $person->patronymic }}</a></td>
                    <td><a href="{{ $link }}">{{ $person->birthday ? $person->birthday->format('Y-m-d') : '' }}</a></td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
@endsection
