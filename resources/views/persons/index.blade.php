@extends('layouts.app')

@section('title', 'Люди')

@section('content')
    <div class="row pt-3">
        <table class="table table-bordered table-fixed"
               id="table"
               data-toggle="table"
               data-sticky-header="true"
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
                <tr>
                    <td>{{ $person->lastname }}</td>
                    <td>{{ $person->firstname }}</td>
                    <td>{{ $person->patronymic }}</td>
                    <td>{{ $person->birthday ? $person->birthday->format('Y-m-d') : '' }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
@endsection
