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
    <div class="row">
        <form method="POST"
              action="{{ action(\App\Http\Controllers\Cups\UpdateCupAction::class, [$cup]) }}"
        >
            @csrf
            <div class="form-floating mb-3">
                <input class="form-control" id="name" name="name" value="{{ $cup->name }}">
                <label for="name">{{ __('app.cup.name') }}</label>
            </div>
            <div class="form-floating mb-3">
                <select class="form-select" id="type" name="type">
                    @foreach(\App\Models\Cups\CupType::getCupTypes() as $cupType)
                        <option value="{{ $cupType->getId() }}" {{ $cup->type === $cupType->getId() ? 'selected' : '' }}>{{ __($cupType->getNameKey()) }}</option>
                    @endforeach
                </select>
                <label for="type">{{ __('app.common.type') }}</label>
            </div>
            <div class="form-floating mb-3">
                <input class="form-control" id="events_count" name="events_count" value="{{ $cup->events_count }}">
                <label for="events_count">{{ __('app.cup.events_count') }}</label>
            </div>
            <div class="form-floating mb-3">
                <select class="form-select" id="year" name="year">
                    @for($i = $cup->year - 5; $i <= ($cup->year + 1); $i++)
                        <option value="{{ $i }}" {{ $i === $cup->year ? 'selected' : '' }}>{{ $i }}</option>
                    @endfor
                </select>
                <label for="year">{{ __('app.common.year') }}</label>
            </div>
            <div class="form-group mb-3">
                <select class="selectpicker form-control" multiple data-live-search="true" id="groups" name="groups[]'" title="{{ __('app.common.groups') }}">
                    @php
                        $selectedGroups = $cup->getCupType()->getGroups()->pluck('id')->toArray();
                    @endphp
                    @foreach($groups as $group)
                        <option value="{{ $group->id }}"
                                {{ in_array($group->id, $selectedGroups, true) ? 'selected' : '' }}
                                data-content="<span class='badge' style='background: {{ \App\Facades\Color::getColor($group->name) }}'
                    >{{ $group->name }}</span>">{{ $group->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-12 col-sm-12 col-md-6 col-lg-4 col-xl-3 col-xxl-2">
                <input type="submit" class="btn btn-outline-primary btn-sm" value="{{ __('app.common.update') }}">
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
