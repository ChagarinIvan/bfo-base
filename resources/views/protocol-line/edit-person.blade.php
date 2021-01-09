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
    <table class="table table-bordered table-fixed" id="table"
    >
        <thead>
        <tr class="table-info">
            <th>Фамилия</th>
            <th>Имя</th>
            <th>Отчество</th>
            <th>Дата рождения</th>
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
                <td><a href="{{ $link }}">{{ $person->patronymic }}</a></td>
                <td><a href="{{ $link }}">{{ $person->birthday ? $person->birthday->format('Y-m-d') : '' }}</a></td>
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
