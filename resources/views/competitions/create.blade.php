@extends('layouts.app')

@section('title', 'Создание соревнования')

@section('content')
    <div class="row">
        <h1>Добавление соревнивания</h1>
    </div>
    <form class="pt-5" method="POST" action="/competitions/store">
        @csrf
        <div class="form-group">
            <label for="name">Название соревнований</label>
            <input class="form-control" id="name" name="name">
        </div>
        <div class="form-group">
            <label for="description">Описание</label>
            <input class="form-control" id="description" name="description">
        </div>
        <div class="form-group row">
            <label for="from" class="col-2 col-form-label">Дата начала</label>
            <div class="col-10">
                <input class="form-control" type="date" id="from" name="from">
            </div>
        </div>
        <div class="form-group row">
            <label for="to" class="col-2 col-form-label">Дата окончания</label>
            <div class="col-10">
                <input class="form-control" type="date" id="to" name="to">
            </div>
        </div>
        <div class="row">
            <input type="submit" class="btn btn-primary" value="Создать">
            <a href="/competitions" class="btn btn-danger ml-1">Отмена</a>
        </div>
    </form>
@endsection
