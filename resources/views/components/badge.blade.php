@php
    /**
     * @var string $color
     * @var string $name
     * @var string $url
     */
@endphp
<span class="badge" style="background: {{ $color }}">
    @if ($url === '')
        <b class="text-decoration-none text-dark">{{ $name }}</b>
    @else
        <a href="{{ $url }}" class="text-decoration-none">
            <b class="text-decoration-none text-dark">{{ $name }}</b>
        </a>
    @endif
</span>
