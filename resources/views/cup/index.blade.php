@php
    use App\Bridge\Laravel\Http\Controllers\Cup\DeleteCupAction;
    use App\Bridge\Laravel\Http\Controllers\Cup\ShowCreateCupFormAction;
    use App\Bridge\Laravel\Http\Controllers\Cup\ShowCupAction;
    use App\Bridge\Laravel\Http\Controllers\Cup\ShowCupsListAction;
    use App\Bridge\Laravel\Http\Controllers\Cup\ShowCupTableAction;
    use App\Bridge\Laravel\Http\Controllers\Cup\ShowEditCupFormAction;
    use App\Application\Dto\Cup\ViewCupDto;
    use App\Models\Year;
    /**
     * @var ViewCupDto[] $cups;
     * @var string $selectedYear;
     */
@endphp

@extends('layouts.app')

@section('title', __('app.navbar.cups'))

@section('content')
    @auth
        <div class="row mb-3">
            <div class="col-12 col-md-12 col-lg-6 col-xl-4 col-xxl-3">
                <x-button text="app.common.new"
                          color="success"
                          icon="bi-file-earmark-plus-fill"
                          url="{{ action(ShowCreateCupFormAction::class, [$selectedYear]) }}"
                />
            </div>
        </div>
    @endauth
    <div class="row">
        <ul class="nav nav-tabs">
            @foreach(Year::cases() as $year)
                <li class="nav-item">
                    <a href="{{ action(ShowCupsListAction::class, ['year' => $year->value]) }}"
                       class="text-decoration-none nav-link {{ $year->value === $selectedYear ? 'active' : '' }}"
                    >
                        <b>{{ $year->value }}</b>
                    </a>
                </li>
            @endforeach
        </ul>
        <div class="tab-content">
            <div class="tab-pane fade show active">
                @if (count($cups) > 0)
                    <table id="table"
                           data-cookie="true"
                           data-cookie-id-table="cups-list-{{ $selectedYear }}"
                           data-mobile-responsive="true"
                           data-check-on-init="true"
                           data-min-width="800"
                           data-toggle="table"
                           data-sort-class="table-active"
                           data-page-list="[10,25,50,100,All]"
                           data-resizable="true"
                           data-custom-sort="customSort"
                    >
                        <thead class="table-dark">
                        <tr>
                            <th data-sortable="true">{{ __('app.common.title') }}</th>
                            <th data-sortable="true">{{ __('app.cup.last_date') }}</th>
                            <th>{{ __('app.common.groups') }}</th>
                            @auth
                                <th data-sortable="true">{{ __('app.common.created') }}</th>
                                <th data-sortable="true">{{ __('app.common.updated') }}</th>
                            @endauth
                            <th></th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach ($cups as $cup)
                            <tr>
                                <td>
                                    <a href="{{ action(ShowCupAction::class, [$cup->id]) }}">{{ $cup->name }}</a>
                                </td>
                                <td>{{ $cup->lastEventDate }}</td>
                                <td>
                                    @foreach($cup->groups as $group)
                                        @php
                                        @endphp
                                        <x-badge name="{{ $group->name }}"
                                                 url="{{ action(ShowCupTableAction::class, [$cup->id, $group->id]) }}"
                                        />
                                    @endforeach
                                </td>
                                @auth
                                    <td>
                                        <x-impression :impression="$cup->created"/>
                                    </td>
                                    <td>
                                        <x-impression :impression="$cup->updated"/>
                                    </td>
                                @endauth
                                <td>
                                    <x-button text="app.cup.table" color="secondary" icon="bi-table"
                                              url="{{ action(ShowCupTableAction::class, [$cup->id, $cup->groups[0]->id]) }}"/>
                                    @auth
                                        <x-edit-button
                                                url="{{ action(ShowEditCupFormAction::class, [$cup->id]) }}"/>
                                        <x-modal-button modal-id="deleteModal{{ $cup->id }}"/>
                                    @endauth
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
        </div>
    </div>
    @foreach ($cups as $cup)
        <x-modal modal-id="deleteModal{{ $cup->id }}"
                 url="{{ action(DeleteCupAction::class, [$cup->id]) }}"
        />
    @endforeach
@endsection

@section('table_extracted_columns', '[0]')
@section('table_extracted_columns', '[0]')
