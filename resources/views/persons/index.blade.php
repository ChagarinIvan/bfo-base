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
    <table class="table table-bordered" id="table">
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
                $hide = $person->protocolLines->count() === 0;
                $link = "/persons/{$person->id}/show";
            @endphp
            <tr>
                @if($hide)
                    <td>{{ $person->lastname }}</td>
                    <td>{{ $person->firstname }}</td>
                    <td>{{ $person->patronymic }}</td>
                    <td>{{ $person->birthday ? $person->birthday->format('Y-m-d') : '' }}</td>
                @else
                    <td><a href="{{ $link }}"><u>{{ $person->lastname }}</u></a></td>
                    <td><a href="{{ $link }}"><u>{{ $person->firstname }}</u></a></td>
                    <td><a href="{{ $link }}"><u>{{ $person->patronymic }}</u></a></td>
                    <td><a href="{{ $link }}"><u>{{ $person->birthday ? $person->birthday->format('Y-m-d') : '' }}</u></a></td>
                @endif
            </tr>
        @endforeach
        </tbody>
    </table>
@endsection
