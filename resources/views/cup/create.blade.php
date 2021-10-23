@php
    /**
     * @var int $selectedYear;
     */
@endphp

@extends('layouts.app')

@section('title', __('app.cup.add'))

@section('content')
    <div class="row">
        <form method="POST" action="{{ action(\App\Http\Controllers\Cups\StoreCupAction::class) }}">
            @csrf
            <div class="form-floating mb-3">
                <input class="form-control" id="name" name="name">
                <label for="name">{{ __('app.cup.name') }}</label>
            </div>
            <div class="form-floating mb-3">
                <select class="form-select" id="type" name="type">
                    @foreach(\App\Models\Cups\CupType::getCupTypes() as $cupType)
                        <option value="{{ $cupType->getId() }}" {{ $loop->first ? 'selected' : '' }}>{{ __($cupType->getNameKey()) }}</option>
                    @endforeach
                </select>
                <label for="type">{{ __('app.common.type') }}</label>
            </div>
            <div class="form-floating mb-3">
                <input class="form-control" id="events_count" name="events_count">
                <label for="events_count">{{ __('app.cup.events_count') }}</label>
            </div>
            <div class="form-floating mb-3">
                <select class="form-select" id="year" name="year">
                    @foreach(App\Models\Year::YEARS as $year)
                        <option value="{{ $year }}" {{ $year === $selectedYear ? 'selected' : '' }}>{{ $year }}</option>
                    @endforeach
                </select>
                <label for="year">{{ __('app.common.year') }}</label>
            </div>
            <div class="col-12 col-sm-12 col-md-6 col-lg-4 col-xl-3 col-xxl-2">
                <input type="submit" class="btn btn-outline-primary btn-sm" value="{{ __('app.common.create') }}">
                <x-back-button/>
            </div>
        </form>
    </div>
@endsection

@section('script')
    <script>
        $('#groups').selectpicker();
    </script>
@endsection
