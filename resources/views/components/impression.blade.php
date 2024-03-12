@php
    /**
     * @var string $date
     * @var string $email
     */
@endphp
<td data-toggle="tooltip" title="{{ $email }}">
    <span style="margin-right: 5px;">{{ $date }}</span>
    <i class="bi bi-info-circle-fill text-info"
       data-bs-toggle="tooltip"
       data-bs-placement="top"
       title="{{ $email }}"
    ></i>
</td>
