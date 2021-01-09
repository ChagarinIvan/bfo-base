@extends('layouts.app')

@section('title', 'Соревнования')

@section('content')
    <h3>Соревнования</h3>
    <div class="row pt-5">
        <a class="btn btn-success mr-2" href="/competitions/create">Добавить соревнование</a>
    </div>
    <div class="row pt-3">
        <table class="table table-bordered table-fixed"
               id="table"
               data-toggle="table"
               data-sticky-header="true"
        >
            <thead>
            <tr class="table-info">
                <th>Название</th>
                <th>Даты</th>
                <th>Описание</th>
                <th>Действия</th>
            </tr>
            </thead>
            <tbody>
            @foreach ($competitions as $competition)
                <tr>
                    <td>
                        <a href="/competitions/{{$competition->id}}/show">{{ $competition->name }}</a>
                    </td>
                    <td>{{ $competition->from->format('d.m.Y') }} - {{ $competition->to->format('d.m.Y') }}</td>
                    <td><small>{{ Str::limit($competition->description, 100, '...') }}</small></td>
                    <td><a href="/competitions/show-edit" class="text-primary">Edit</a></td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
@endsection
