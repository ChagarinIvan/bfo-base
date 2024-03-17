@php
    use App\Bridge\Laravel\Http\Controllers\PersonPrompt\UpdatePromptAction;
    use App\Application\Dto\PersonPrompt\ViewPersonPromptDto;
    /**
     * @var ViewPersonPromptDto $prompt
     */
@endphp

@extends('layouts.app')

@section('title', __('app.prompt.edit'))

@section('content')
    <div class="row">
        <form method="POST"
              action="{{ action(UpdatePromptAction::class, [$prompt->id]) }}"
        >
            @csrf
            <div class="form-floating mb-3">
                <input class="form-control @error('prompt') is-invalid @enderror" id="prompt" name="prompt" value="{{ $prompt->prompt }}" />
                <label for="prompt">{{ __('app.common.prompt') }}</label>
            </div>

            <div class="col-12 col-sm-12 col-md-6 col-lg-4 col-xl-3 col-xxl-2">
                <input type="submit" class="btn btn-outline-primary btn-sm" value="{{ __('app.common.save') }}">
                <x-back-button/>
            </div>
        </form>
    </div>
@endsection
