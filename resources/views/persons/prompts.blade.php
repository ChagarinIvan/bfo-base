@php
    use App\Models\Person;
    /**
     * @var Person $person
     */
@endphp

@extends('layouts.app')

@section('title', __('app.common.prompts'))

@section('content')
    <div class="row mb-3">
        <div class="col-12">
            @auth
                <x-edit-button url="{{ action(\App\Http\Controllers\PersonPrompt\ShowCreatePromptAction::class, [$person->id]) }}"/>
            @endauth
            <x-back-button/>
        </div>
    </div>
    @if($person->prompts->count() > 0)
        <table id="table"
               data-cookie="true"
               data-cookie-id-table="person-prompts-show"
               data-mobile-responsive="true"
               data-check-on-init="true"
               data-min-width="800"
               data-toggle="table"
               data-search="true"
               data-search-highlight="true"
               data-sort-class="table-active"
               data-resizable="true"
        >
            <thead class="table-dark">
                <tr>
                    <th data-sortable="true">{{ __('app.common.prompts') }}</th>
                    @auth<th></th>@endauth
                </tr>
            </thead>
            <tbody>
                @foreach ($person->prompts as $prompt)
                    <tr>
                        <td>{{ $prompt->prompt }}</td>
                    </tr>
                    @auth
                        <td>
                            <x-edit-button url="{{ action(\App\Http\Controllers\Competition\ShowEditCompetitionFormAction::class, [$year->value, $competition->id]) }}"/>
                            <x-delete-button modal-id="deleteModal{{ $prompt->id }}"/>
                        </td>
                    @endauth
                @endforeach
            </tbody>
        </table>
        @foreach ($person->prompts as $prompt)
            <x-modal modal-id="deleteModal{{ $prompt->id }}"
                     url="{{ action(\App\Http\Controllers\PersonPrompt\DeletePromptAction::class, [$prompt->id]) }}"
            />
        @endforeach
    @endif
@endsection
