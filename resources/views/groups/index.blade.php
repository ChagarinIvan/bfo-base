@php
    use App\Models\Group;
    use Illuminate\Support\Collection;
    /**
     * @var Collection|Group[] $groups
     */
@endphp

@extends('layouts.app')

@section('title', __('app.common.groups'))

@section('content')
    <h3>{{ __('app.common.groups') }}</h3>
    <div class="row pt-3">
        <table class="table table-bordered" id="table">
            <thead>
            <tr class="table-info">
                <th>{{ __('app.common.title') }}</th>
                <th>{{ __('app.groups.events_count') }}</th>
                <th></th>
            </tr>
            </thead>
            <tbody>
            @foreach ($groups as $group)
                <tr>
                    <td>
                        <span class="badge" style="background: {{ \App\Facades\Color::getColor($group->name) }}">
                            <a href="{{ action(\App\Http\Controllers\Groups\ShowGroupAction::class, [$group->id]) }}">{{ $group->name }}</a>
                        </span>
                    </td>
                    <td>{{ $group->distances->count() }}</td>
                    <td>
                        <a href="#"
                           class="btn btn-info mr-2"
                        >{{ __('app.common.sum') }}</a>
                        <a href="#"
                           class="btn btn-primary mr-2"
                        >{{ __('app.common.edit') }}</a>
                        <a href="{{ action(\App\Http\Controllers\Groups\DeleteGroupAction::class, [$group->id]) }}"
                           class="btn btn-danger mr-2"
                        >{{ __('app.common.delete') }}</a>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
@endsection
