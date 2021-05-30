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

@section('title', __('app.cup.table'))

@section('content')
    <div class="row">
        <h1>Тут будет таблічка</h1>
    </div>
@endsection
