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
                <th data-sortable="true">{{ __('app.common.created') }}</th>
                <th data-sortable="true">{{ __('app.common.updated') }}</th>
            </tr>
            </thead>
            <tbody>
            @foreach ($payments as $payment)
                <tr>
                    <td>{{ $payment->year }}</td>
                    <td>{{ $payment->date }}</td>
                    <td data-toggle="tooltip" title="lkjlkjlk asdasd">
                        <span style="margin-right: 5px;">2021-10-22</span>
                        <i class="bi bi-info-circle-fill text-info"
                           data-bs-toggle="tooltip"
                           data-bs-placement="top"
                           title="adasdasd"
                        ></i>
                    </td>
                    <td></td>
                </tr>
            @endforeach
            </tbody>
        </table>
    @endif
@endsection
