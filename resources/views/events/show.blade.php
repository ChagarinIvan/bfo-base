@php
    use App\Models\Distance;
    use App\Models\Event;
    use App\Models\Group;
    use App\Models\Club;
    use Illuminate\Support\Collection;
    /**
     * @var Event $event
     * @var Collection|Group[] $groupAnchors
     * @var Distance $selectedDistance
     * @var Collection $lines;
     * @var bool $withPoints;
     * @var bool $withVk;
     * @var Collection|Club[] $clubs;
     */
@endphp

@extends('layouts.app')

@section('title', $event->name.' ('.$event->date->format('d.m.Y').')')

@section('content')
    <div class="row mb-3">
        <div class="col-12"><h4>{{ $event->competition->name }}</h4></div>
    </div>
    <div class="row mb-3">
        <div class="col-12">
            @auth
                <x-edit-button url="{{ action(\App\Http\Controllers\Event\ShowEditEventFormAction::class, [$event]) }}"/>
            @endauth
            <x-back-button/>
        </div>
    </div>
    <div class="row">
        <ul class="nav nav-tabs">
            @foreach($event->distances as $distance)
                <li class="nav-item">
                    <a href="{{ action(\App\Http\Controllers\Event\ShowEventAction::class, [$event, $distance]) }}"
                       class="text-decoration-none nav-link {{ $distance->id === $selectedDistance->id ? 'active' : '' }}"
                    >
                        <b>{{ $distance->group->name }}</b>
                    </a>
                </li>
            @endforeach
        </ul>
        @if ($selectedDistance->length > 0 || $selectedDistance->points > 0)
            <div><p><b>{{ $selectedDistance->length }}</b> м, <b>{{ $selectedDistance->points }}</b> КП.</p></div>
        @endif
        <div class="tab-content">
            <div class="tab-pane fade show active">
                <table id="table"
                       data-cookie="true"
                       data-cookie-id-table="show-event"
                       data-mobile-responsive="true"
                       data-check-on-init="true"
                       data-min-width="800"
                       data-toggle="table"
                       data-sort-class="table-active"
                       data-search="true"
                       data-search-highlight="true"
                       data-resizable="true"
                       data-pagination="true"
                       data-page-list="[10,25,50,100,All]"
                       data-custom-sort="customSort"
                       data-pagination-next-text="{{ __('pagination.next') }}"
                       data-pagination-pre-text="{{ __('pagination.previous') }}"
                >
                    <thead class="table-dark">
                        <tr>
                            <th data-sortable="true">#</th>
                            <th data-sortable="true">{{ __('app.common.lastname') }}</th>
                            <th data-sortable="true">{{ __('app.common.name') }}</th>
                            <th data-sortable="true">{{ __('app.club.name') }}</th>
                            <th data-sortable="true">{{ __('app.common.year') }}</th>
                            <th data-sortable="true">{{ __('app.common.rank') }}</th>
                            <th data-sortable="true">{{ __('app.common.time') }}</th>
                            <th data-sortable="true">{{ __('app.common.place') }}</th>
                            <th data-sortable="true">{{ __('app.common.complete') }}</th>
                            @if($withPoints)<th data-sortable="true">{{ __('app.common.points') }}</th>@endif
                            @if($withVk)<th data-sortable="true">{{ __('app.common.vk') }}</th>@endif
                        </tr>
                    </thead>
                    <tbody>
                    @foreach($lines as $line)
                        @php
                            /** @var \App\Models\ProtocolLine $line */
                         @endphp
                        <tr id="{{ $line->id }}">
                            @php
                                $hasPerson = $line->person_id !== null;
                            @endphp
                            <td>{{ $line->serial_number }}</td>
                            @if($hasPerson)
                                @php
                                    $link = action(\App\Http\Controllers\Person\ShowPersonAction::class, [$line->person_id]);
                                @endphp
                                <td><a href="{{ $link }}">{{ $line->lastname }}</a>&nbsp;
                                    @auth
                                        <a href="{{ action(\App\Http\Controllers\Person\ShowSetPersonToProtocolLineAction::class, [$line]) }}">
                                            <span class="badge rounded-pill bg-warning">{{ __('app.common.edit') }}</span>
                                        </a>
                                    @endauth
                                </td>
                                <td><a href="{{ $link }}">{{ $line->firstname }}</a></td>
                            @else
                                <td>{{ $line->lastname }}&nbsp;
                                    @auth
                                        <a href="{{ action(\App\Http\Controllers\Person\ShowSetPersonToProtocolLineAction::class, [$line]) }}">
                                            <span class="badge rounded-pill bg-danger">{{ __('app.common.new') }}</span>
                                        </a>
                                    @endauth
                                </td>
                                <td>{{ $line->firstname }}</td>
                            @endif
                            @if($club = $clubs->get(\App\Services\ClubsService::normalizeName($line->club)))
                                <td>
                                    <a href="{{ action(\App\Http\Controllers\Club\ShowClubAction::class, [$club]) }}">
                                        {{ ($line->club) }}
                                    </a>
                                </td>
                            @else
                                <td><span class="">{{ ($line->club) }}</span></td>
                            @endif
                            <td>{{ $line->year }}</td>
                            <td>{{ $line->rank }}</td>
                            <td>{{ $line->time ? $line->time->format('H:i:s') : '-' }}</td>
                            <td>{{ $line->place ?: '-' }}</td>
                            <td>{{ $line->complete_rank }}</td>
                            @if($withPoints)<td>{{ $line->points ?: '-'}}</td>@endif
                            @if($withVk)<td>{{ $line->vk ? 'в/к' : '-' }}</td>@endif
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@section('table_extracted_columns', '[1,2,3]')
