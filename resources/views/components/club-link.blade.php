@php
    use App\Bridge\Laravel\Http\Controllers\Club\ShowClubAction;
    use App\Models\Club;
    /**
     * @var Club|null $club
     */
@endphp
@if($club)
    <a href="{{ action(ShowClubAction::class, [$club]) }}">
        {{ $club->name }}
    </a>
@endif
