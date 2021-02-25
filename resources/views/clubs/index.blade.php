@php
    use App\Models\Club;
    /**
     * @var Club[] $clubs;
     */
@endphp

@extends('layouts.app')

@section('title', 'Клубы')

@section('content')
    <h3>Клубы</h3>
    <table class="table table-bordered" id="table">
        <thead>
        <tr class="table-info">
            <th>Название</th>
            <th>Чиcло членов</th>
        </tr>
        </thead>
        <tbody>
        @foreach ($clubs as $club)
            <tr>
                <td><a href="/club/{{ $club->id }}/show">{{ $club->name }}</a></td>
                <td>{{ $club->persons->count() }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
    {{ $clubs->links() }}
@endsection
