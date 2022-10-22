@php
    use App\Http\Controllers\Rank\CheckPersonsRanksAction;
@endphp
@extends('layouts.app')

@section('title', __('app.rank.check'))

@section('content')
    <div class="row">
        <form method="POST"
              action="{{ action(CheckPersonsRanksAction::class) }}"
              enctype="multipart/form-data"
        >
            @csrf
            <div class="mb-3">
                <label for="list" class="form-label">{{ __('app.rank.list') }}</label>
                <input class="form-control" type="file" id="list" name="list">
            </div>
            <div class="col-12">
                <input type="submit" class="btn btn-outline-primary btn-sm" value="{{ __('app.rank.check_button') }}">
                <x-back-button/>
            </div>
        </form>
    </div>
@endsection
