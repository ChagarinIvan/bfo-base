@php
    /**
     * @var string $text
     * @var string $color
     * @var string $icon
     * @var string $modalId
     */
@endphp
<button type="button"
        class="btn btn-outline-{{ $color }} btn-sm me-1"
        data-bs-toggle="modal"
        data-bs-target="#{{ $modalId }}"
>
    <i class="bi {{ $icon }}"
       data-bs-toggle="tooltip"
       data-bs-placement="top"
       title="{{ $text }}"
    ></i>
    <span class="d-none d-xl-inline">{{ $text }}</span>
</button>
