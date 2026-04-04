@php
    use App\Bridge\Laravel\Http\Controllers\PersonPayment\StorePersonPaymentAction;
    use App\Application\Dto\Person\ViewPersonDto;
    /**
     * @var ViewPersonDto $person
     */
@endphp

@extends('layouts.app')

@section('title', __('app.person.create_person_title'))

@section('content')
    <div class="row">
        <form method="POST"
              action="{{ action(StorePersonPaymentAction::class, [$person->id]) }}"
        >
            @csrf
            <div class="form-floating mb-3 hidden" hidden>
                <input class="form-control" id="personId" type="hidden" name="personId" value="{{ $person->id }}" hidden/>
            </div>
            <div class="form-floating mb-3">
                <input class="form-control @error('date') is-invalid @enderror" type="date" id="date" name="date"/>
                <label for="date">{{ __('app.common.payments.add.date') }}</label>
            </div>
            <div class="col-12 col-sm-12 col-md-6 col-lg-4 col-xl-3 col-xxl-2">
                <button type="submit"
                        class="btn btn-sm btn-outline-primary me-1"
                >
                    <i class="bi bi-clipboard-check me-1"
                       data-bs-toggle="tooltip"
                       data-bs-placement="top"
                       title="{{ __('app.common.create') }}"
                    ></i>
                    <span class="d-none d-xl-inline">{{ __('app.common.create') }}</span>
                </button>
                <x-back-button/>
            </div>
        </form>
    </div>
@endsection
