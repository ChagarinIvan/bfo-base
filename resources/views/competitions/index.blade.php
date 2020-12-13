@extends('layouts.app')

@section('title', 'Соревнования')

@section('content')
    <div class="row pt-5">
        <a class="btn btn-success" href="/competitions/create">Добавить соревнование</a>
    </div>
    <div class="row pt-3">
        <table class="table">
            <thead>
            <tr class="d-flex">
                <th class="col-3">Название</th>
                <th class="col-2">Даты</th>
                <th class="col-6">Описание</th>
                <th class="col-1"></th>
            </tr>
            </thead>
            <tbody>
            @foreach ($competitions as $competition)
                <tr class="d-flex">
                    <td class="col-3">
                        <a href="/competitions/{{$competition->id}}/show">{{ $competition->name }}</a>
                    </td>
                    <td class="col-2">{{ $competition->from->format('d.m.Y') }} - {{ $competition->to->format('d.m.Y') }}</td>
                    <td class="col-5"><small>{{ Str::limit($competition->description, 100, '...') }}</small></td>
                    <td class="col-1"><a href="/competitions/show-edit" class="text-primary">Edit</a></td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
@endsection
