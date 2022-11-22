@php
    use App\Models\Club;
    use App\Http\Controllers\Club\ShowClubAction;
    /**
     * @var Club|null $club
     */
@endphp
@if($club)
    <a href="{{ action(ShowClubAction::class, [$club]) }}">
        {{ $club->name }}
    </a>
@endif
