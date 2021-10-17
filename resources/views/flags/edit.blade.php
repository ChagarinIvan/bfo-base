@php
    use App\Models\Flag;
    /**
     * @var Flag $flag;
     *
     */
@endphp
@extends('layouts.app')

@section('title', __('app.flags.edit.title').' - '.$flag->name)

@section('content')
    <div class="row">
        <form method="POST"
              action="{{ action(\App\Http\Controllers\Flags\UpdateFlagAction::class, [$flag]) }}"
              enctype="multipart/form-data"
        >
            @csrf
            <div class="form-floating mb-3">
                <input class="form-control" id="name" name="name" value="{{ $flag->name }}">
                <label for="name">{{ __('app.flags.name') }}</label>
            </div>
            <div class="form-floating mb-3">
                <input class="form-control" id="color" name="color" type="color" value="{{ $flag->color }}">
                <label for="type">{{ __('app.flags.color') }}</label>
            </div>
            <div class="col-12">
                <input type="submit" class="btn btn-outline-primary btn-sm" value="{{ __('app.common.update') }}">
                <x-back-button/>
            </div>
        </form>
    </div>
@endsection
