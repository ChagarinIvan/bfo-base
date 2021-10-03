@php
    use App\Models\Flag;
    /**
     * @var Flag[] $flags;
     */
@endphp

@extends('layouts.app')

@section('title', __('app.navbar.flags'))

@section('content')
    <h3>{{ __('app.navbar.flags') }}</h3>
    @auth
        <div class="row pt-5">
            <a class="btn btn-success mr-2" href="{{ action(\App\Http\Controllers\Flags\ShowCreateFlagFormAction::class) }}">{{ __('app.common.new') }}</a>
        </div>
    @endauth
    <div class="row pt-3">
        <table class="table table-bordered pt-3" id="table">
            <thead>
            <tr class="table-info">
                <th scope="col">{{ __('app.flags.name') }}</th>
                <th scope="col">{{ __('app.flags.color') }}</th>
                @auth<th scope="col"></th>@endauth
            </tr>
            </thead>
            <tbody>
            @foreach ($flags as $flag)
                <tr>
                    <td>
                        <a href="{{ action(\App\Http\Controllers\Flags\ShowFlagEventsAction::class, [$flag]) }}">
                            <u>{{ $flag->name }}</u>
                        </a>
                    </td>
                    <td style="background: {{ $flag->color }}">{{ $flag->color }}</td>
                    @auth
                        <td>
                            <a href="{{ action(\App\Http\Controllers\Flags\ShowEditFlagFormAction::class, [$flag]) }}"
                               class="btn btn-primary mr-2"
                            >{{ __('app.common.edit') }}</a>
                            <a href="{{ action(\App\Http\Controllers\Flags\DeleteFlagAction::class, [$flag]) }}"
                               class="btn btn-danger"
                            >{{ __('app.common.delete') }}</a>
                        </td>
                    @endauth
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
@endsection
