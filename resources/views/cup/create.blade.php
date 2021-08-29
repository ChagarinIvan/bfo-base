@php
    use App\Models\Group;
    use Illuminate\Support\Collection;
    /**
     * @var Group[]|Collection $groups;
     * @var int $selectedYear;
     */
@endphp

@extends('layouts.app')

@section('title', __('app.cup.add'))

@section('content')
    <div class="row">
        <h1>{{ __('app.cup.add') }}</h1>
    </div>
    <form class="pt-5" method="POST" action="{{ \App\Http\Controllers\Cups\StoreCupAction::class }}">
        @csrf
        <div class="form-group">
            <label for="name">{{ __('app.cup.name') }}</label>
            <input class="form-control" id="name" name="name">
        </div>
        <div class="form-group">
            <label for="type" class="col-2 col-form-label">{{ __('app.common.type') }}</label>
            <select class="custom-select" id="type" name="type">
                @foreach(\App\Models\Cups\CupType::getCupTypes() as $cupType)
                    <option value="{{ $cupType->getId() }}" {{ $loop->first ? 'selected' : '' }}>{{ $cupType->getName() }}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group">
            <label for="events_count">{{ __('app.cup.events_count') }}</label>
            <input class="form-control" id="events_count" name="events_count">
        </div>
        <div class="form-group">
            <label for="year" class="col-2 col-form-label">{{ __('app.common.year') }}</label>
            <select class="custom-select" id="year" name="year">
                @foreach(App\Models\Year::YEARS as $year)
                    <option value="{{ $year }}" {{ $year === $selectedYear ? 'selected' : '' }}>{{ $year }}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group">
            <label for=groups" class="col-2 col-form-label">{{ __('app.common.groups') }}</label>
            <select class="selectpicker form-control" multiple data-live-search="true" id="groups" name="groups[]'">
                @foreach($groups as $group)
                    <option value="{{ $group->id }}"
                            data-content="<span class='badge' style='background: {{ \App\Facades\Color::getColor($group->name) }}'
                    >{{ $group->name }}</span>">{{ $group->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="row">
            <input type="submit" class="btn btn-primary" value="{{ __('app.common.create') }}">
            <a href="{{ action(\App\Http\Controllers\Cups\ShowCupsListAction::class, [$selectedYear]) }}"
               class="btn btn-danger ml-1"
            >{{ __('app.common.cancel') }}</a>
        </div>
    </form>
@endsection

@section('script')
    <script>
        $('#groups').selectpicker();
    </script>
@endsection
