@php
    use App\Models\Flag;
    /**
     * @var Flag $flag;
     *
     */
@endphp
@extends('layouts.app')

@section('title', __('app.flags.edit.title'))

@section('content')
    <div class="row">
        <h1>{{ __('app.flags.edit.title') }}</h1>
    </div>
    <form class="pt-5" method="PATCH" action="/flags/{{ $flag->id }}/update" enctype="multipart/form-data">
        @method('PATCH')
        @csrf
        <div class="form-group">
            <label for="name">{{ __('app.flags.name') }}</label>
            <input class="form-control" id="name" name="name" value="{{ $flag->name }}">
        </div>
        <div class="form-group">
            <label for="type">{{ __('app.flags.color') }}</label>
            <input class="form-control" id="color" name="color" type="color" value="{{ $flag->color }}">
        </div>
        <div class="row">
            <input type="submit" class="btn btn-primary" value="{{ __('app.common.update') }}">
        </div>
    </form>
@endsection
