@php
    use App\Models\Group;
    use Carbon\Carbon;
    use Illuminate\Support\Collection;
    /**
     * @var Group[]|Collection $groups;
     */
    $year = Carbon::now()->year;
@endphp

@extends('layouts.app')

@section('title', __('app.cup.add'))

@section('content')
    <div class="row">
        <h1>{{ __('app.cup.add') }}</h1>
    </div>
    <form class="pt-5" method="POST" action="/cups/store">
        @csrf
        <div class="form-group">
            <label for="name">{{ __('app.cup.name') }}</label>
            <input class="form-control" id="name" name="name">
        </div>
        <div class="form-group">
            <label for="year" class="col-2 col-form-label">{{ __('app.common.year') }}</label>
            <select class="custom-select" id="year" name="year">
                @php
                    $year = \Illuminate\Support\Carbon::now()->year;
                @endphp
                @for($i = $year - 5; $i <= ($year + 1); $i++)
                    <option value="{{ $i }}" {{ $i === $year ? 'selected' : '' }}>{{ $i }}</option>
                @endfor
            </select>
        </div>
        <div class="form-group">
            <label for=groups" class="col-2 col-form-label">{{ __('app.common.groups') }}</label>
            <select class="selectpicker form-control" multiple data-live-search="true" id="groups" name="groups[]'">
                @foreach($groups as $group)
                    <option value="{{ $group->id }}"
                            data-content="<span class='badge' style='background: {{ \Color::getColor($group->name) }}'
                    >{{ $group->name }}</span>">{{ $group->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="row">
            <input type="submit" class="btn btn-primary" value="{{ __('app.common.create') }}">
            <a href="/cups" class="btn btn-danger ml-1">{{ __('app.common.cancel') }}</a>
        </div>
    </form>
@endsection

@section('script')
    <script>
        $('#groups').selectpicker();
    </script>
@endsection
