@php
    use App\Bridge\Laravel\Facades\Color;
    use App\Bridge\Laravel\Http\Controllers\Groups\UnitGroupsAction;
    use App\Models\Group;
    use Illuminate\Support\Collection;
    /**
     * @var Group $unitedGroup;
     * @var Collection|Group[] $groups;
     */
@endphp

@extends('layouts.app')

@section('title', __('app.common.sum').' '.$unitedGroup->name)

@section('content')
    <div class="row">
        <form method="POST"
              action="{{ action(UnitGroupsAction::class, [$unitedGroup->id]) }}"
              enctype="multipart/form-data"
        >
            @csrf
            <div class="form-group mb-3">
                <select class="selectpicker form-control" data-live-search="true" id="group_id" name="group_id"
                        title="{{ __('app.common.group') }}">
                    @foreach($groups as $group)
                        <option value="{{ $group->id }}"
                                data-content="<span class='badge' style='background: {{ Color::getColor($group->name) }}'><b class='text-decoration-none text-dark'>{{ $group->name }} ({{ $group->distances->count() }})</b></span>"
                        >{{ $group->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-12">
                <input type="submit" class="btn btn-outline-primary btn-sm" value="{{ __('app.common.sum') }}">
                <x-back-button/>
            </div>
        </form>
    </div>
@endsection
