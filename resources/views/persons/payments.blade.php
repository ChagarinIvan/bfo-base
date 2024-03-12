@php
    use App\Application\Dto\PersonPayment\ViewPersonPaymentDto;
    use App\Application\Dto\Person\ViewPersonDto;

    /**
     * @var ViewPersonDto $person
     * @var ViewPersonPaymentDto[] $payments
     */
@endphp

@extends('layouts.app')

@section('title', sprintf('%s %s %s', __('app.common.payments'), $person->lastname, $person->firstname))

@section('content')
    <div class="row mb-3">
        <div class="col-12">
            <x-back-button/>
        </div>
    </div>
    @if($payments > 0)
        <table id="table"
               data-cookie="true"
               data-cookie-id-table="person-payments-show"
               data-mobile-responsive="true"
               data-check-on-init="true"
               data-min-width="800"
               data-toggle="table"
               data-sort-class="table-active"
               data-resizable="true"
        >
            <thead class="table-dark">
            <tr>
                <th data-sortable="true">{{ __('app.common.year') }}</th>
                <th data-sortable="true">{{ __('app.common.date') }}</th>
            </tr>
            </thead>
            <tbody>
            @foreach ($payments as $payment)
                <tr>
                    <td>{{ $payment->year }}</td>
                    <td>{{ $payment->date }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    @endif
@endsection
