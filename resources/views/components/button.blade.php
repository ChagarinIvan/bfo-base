@php
    /**
     * @var string $text
     * @var string $color
     * @var string $icon
     * @var string $url
     */
@endphp
<a href="{{ $url }}"
   type="button"
   class="btn btn-sm btn-outline-{{ $color }} me-1"
>
    <i class="bi {{ $icon }}"
       data-bs-toggle="tooltip"
       data-bs-placement="top"
       title="{{ $text }}"
    ></i>
    <span class="d-none d-xl-inline">{{ $text }}</span>
</a>
