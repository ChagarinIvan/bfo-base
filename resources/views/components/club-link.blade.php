@php
    use App\Application\Dto\Club\ViewClubDto;
    /**
     * @var ViewClubDto|null $club
     */
@endphp
@if($club)
    <a href="/app/clubs/{{ $club->id }}">
        {{ $club->name }}
    </a>
@endif
