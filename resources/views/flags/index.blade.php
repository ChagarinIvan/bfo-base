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
    <div class="row pt-5">
        <a class="btn btn-success mr-2" href="/flags/create">{{ __('app.common.new') }}</a>
    </div>
    <div class="row pt-3">
        <table class="table table-bordered pt-3" id="table">
            <thead>
            <tr class="table-info">
                <th scope="col">{{ __('app.flags.name') }}</th>
                <th scope="col">{{ __('app.flags.color') }}</th>
                <th scope="col"></th>
            </tr>
            </thead>
            <tbody>
            @foreach ($flags as $flag)
                <tr>
                    <td><a href="/flags/{{ $flag->id }}/show-events"><u>{{ $flag->name }}</u></a></td>
                    <td style="background: {{ $flag->color }}">{{ $flag->color }}</td>
                    <td>
                        <a href="/flags/{{ $flag->id }}/edit" class="text-primary">{{ __('app.common.edit') }}</a>
                        <a href="/flags/{{ $flag->id }}/delete" class="text-danger">{{ __('app.common.delete') }}</a>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
@endsection
