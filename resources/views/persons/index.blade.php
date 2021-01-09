@php
    use App\Models\Person;
    /**
     * @var Person[] $persons;
     */
@endphp

@extends('layouts.app')

@section('title', 'Люди')

@section('content')
    <h3>Члены федерации</h3>
    <table class="table table-bordered  id="table""
           id="table"
    >
        <thead>
        <tr class="table-info">
            <th>Фамилия</th>
            <th>Имя</th>
            <th>Отчество</th>
            <th>Дата рожедния</th>
        </tr>
        </thead>
        <tbody>
        @foreach ($persons as $person)
            @php
                $link = "/persons/{$person->id}/show";
            @endphp
            <tr>
                <td><a href="{{ $link }}"><u>{{ $person->lastname }}</u></a></td>
                <td><a href="{{ $link }}"><u>{{ $person->firstname }}</u></a></td>
                <td><a href="{{ $link }}"><u>{{ $person->patronymic }}</u></a></td>
                <td><a href="{{ $link }}"><u>{{ $person->birthday ? $person->birthday->format('Y-m-d') : '' }}</u></a></td>
            </tr>
        @endforeach
        </tbody>
    </table>
@endsection
