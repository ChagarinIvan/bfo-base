@php
    use \App\Models\Event;
    use \App\Models\Flag;
    use Illuminate\Support\Collection;
    /**
     * @var Event $event;
     * @var Flag[]|Collection $flags;
     */
@endphp

@extends('layouts.app')

@section('title', 'Редактирование этапа')

@section('content')
    <div class="row">
        <h1>{{ __('app.common.edit_event') }}</h1>
    </div>
    <form class="pt-5" method="POST" action="/competitions/events/{{ $event->id }}/update" enctype="multipart/form-data">
        @csrf
        <div class="form-group">
            <label for="name">Название этапа</label>
            <input class="form-control" id="name" name="name" value="{{ $event->name }}"/>
        </div>
        <div class="form-group">
            <label for="description">Описание</label>
            <input class="form-control" id="description" name="description" value="{{ $event->description }}">
        </div>
        <div class="form-group row">
            <label for="date" class="col-2 col-form-label">Дата проведения</label>
            <div class="col-10">
                <input class="form-control" type="date" id="date" name="date" value="{{ $event->date->format('Y-m-d') }}">
            </div>
        </div>
        <div class="form-group">
            <label for="protocol" class="col-2 col-form-label">{{ __('app.protocol') }}</label>
            <p>{{ __('app.protocol-hint') }}</p>
            <input class="form-control" type="file" name="protocol" id="protocol"/>
        </div>
        <div class="row">
            <input type="submit" class="btn btn-primary" value="Сохранить">
            <a href="/competitions/events/{{ $event->id }}/show" class="btn btn-danger ml-1">{{ __('app.common.cancel') }}</a>
        </div>
    </form>
@endsection
