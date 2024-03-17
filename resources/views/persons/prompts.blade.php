@php
    use App\Bridge\Laravel\Http\Controllers\PersonPrompt\DeletePromptAction;
    use App\Bridge\Laravel\Http\Controllers\PersonPrompt\ShowCreatePromptAction;
    use App\Bridge\Laravel\Http\Controllers\PersonPrompt\ShowEditPromptAction;
    use App\Application\Dto\PersonPrompt\ViewPersonPromptDto;
    /**
     * @var string $personId
     * @var ViewPersonPromptDto[] $prompts
     */
@endphp

@extends('layouts.app')

@section('title', __('app.common.prompts'))

@section('content')
    <div class="row mb-3">
        <div class="col-12">
            @auth
                <x-button text="app.common.new"
                          color="success"
                          icon="bi-file-earmark-plus-fill"
                          url="{{ action(ShowCreatePromptAction::class, [$personId]) }}"
                />
            @endauth
            <x-back-button/>
        </div>
    </div>
    @if(count($prompts) > 0)
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
                <th data-sortable="true">{{ __('app.common.metaphone') }}</th>
                @auth
                    <th></th>
                @endauth
            </tr>
            </thead>
            <tbody>
            @foreach ($prompts as $prompt)
                <tr>
                    <td>{{ $prompt->prompt }}</td>
                    <td>{{ $prompt->metaphone }}</td>
                    @auth
                        <td>
                            <x-edit-button url="{{ action(ShowEditPromptAction::class, [$prompt->id]) }}"/>
                            <x-modal-button modal-id="deleteModal{{ $prompt->id }}"/>
                        </td>
                    @endauth
                </tr>
            @endforeach
            </tbody>
        </table>
        @foreach ($prompts as $prompt)
            <x-modal modal-id="deleteModal{{ $prompt->id }}"
                     url="{{ action(DeletePromptAction::class, [$prompt->id]) }}"
            />
        @endforeach
    @endif
@endsection
