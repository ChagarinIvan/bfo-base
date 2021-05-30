@php
    use App\Models\Cup;
    use App\Models\Group;
    use Carbon\Carbon;
    use Illuminate\Support\Collection;
    /**
     * @var Group[]|Collection $groups;
     * @var Cup $cup;
     */
    $year = Carbon::now()->year;
@endphp

@extends('layouts.app')

@section('title', __('app.cup.edit'))

@section('content')
    <div class="row pr-2">
        <h1>{{ __('app.cup.edit') }}</h1>
    </div>
    <form class="pt-5" method="POST" action="/cups/{{ $cup->id }}/update">
        @csrf
        <div class="form-group">
            <label for="name">{{ __('app.cup.name') }}</label>
            <input class="form-control" id="name" name="name" value="{{ $cup->name }}">
        </div>
        <div class="form-group">
            <label for="year" class="col-2 col-form-label">{{ __('app.common.year') }}</label>
            <select class="custom-select" id="year" name="year">
                @for($i = $cup->year - 5; $i <= ($cup->year + 1); $i++)
                    <option value="{{ $i }}" {{ $i === $cup->year ? 'selected' : '' }}>{{ $i }}</option>
                @endfor
            </select>
        </div>
        <div class="form-group">
            <label for=groups" class="col-2 col-form-label">{{ __('app.common.groups') }}</label>
            <select class="selectpicker form-control" multiple data-live-search="true" id="groups" name="groups[]'">
                @php
                    $selectedGroups = $cup->groups->pluck('id')->toArray();
                @endphp
                @foreach($groups as $group)
                    <option value="{{ $group->id }}"
                            {{ in_array($group->id, $selectedGroups, true) ? 'selected' : '' }}
                            data-content="<span class='badge' style='background: {{ \Color::getColor($group->name) }}'
                    >{{ $group->name }}</span>">{{ $group->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="row">
            <input type="submit" class="btn btn-primary" value="{{ __('app.common.update') }}">
            <a href="{{ url()->previous() }}" class="btn btn-danger ml-1">{{ __('app.common.cancel') }}</a>
        </div>
    </form>
@endsection

@section('script')
    <script>
        $('#groups').selectpicker();
    </script>
@endsection
