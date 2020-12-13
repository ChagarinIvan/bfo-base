@extends('layouts.app')

@section('title', 'Добавление этапа')

@section('content')
    <form class="pt-5" method="POST" action="/competitions/{{ $competitionId }}/events/store" enctype="multipart/form-data">
        @csrf
        <div class="form-group">
            <label for="name">Название этапа</label>
            <input class="form-control" id="name" name="name">
        </div>
        <div class="form-group">
            <label for="type">Тип</label>
            <input class="form-control" id="type" name="type">
        </div>
        <div class="form-group">
            <label for="description">Описание</label>
            <input class="form-control" id="description" name="description">
        </div>
        <div class="form-group row">
            <label for="date" class="col-2 col-form-label">Дата проведения</label>
            <div class="col-10">
                <input class="form-control" type="date" id="date" name="date">
            </div>
        </div>
        <div class="form-group">
            <input class="form-control" type="file" name="protocol"/>
        </div>
        <div class="row">
            <input type="submit" class="btn btn-primary" value="Создать">
            <a href="/competitions/{{ $competitionId }}/show" class="btn btn-danger ml-1">Отмена</a>
        </div>
    </form>
@endsection
