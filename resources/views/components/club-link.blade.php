@php
    use App\Bridge\Laravel\Http\Controllers\Club\ShowClubAction;
    use App\Application\Dto\Club\ViewClubDto;
    /**
     * @var ViewClubDto|null $club
     */
@endphp
@if($club)
    <a href="{{ action(ShowClubAction::class, [$club->id]) }}">
        {{ $club->name }}
    </a>
@endif
