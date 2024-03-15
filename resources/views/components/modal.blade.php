@php
    use App\View\Components\EditButton;
    /**
     * @var string $header
     * @var string $content
     * @var string $modalId
     * @var EditButton $button
     */
@endphp
<div class="modal modal-dark fade" id="{{ $modalId }}" tabindex="-1" aria-labelledby="{{ $modalId }}Label" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="{{ $modalId }}Label">{{ $header }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('app.common.close') }}"></button>
            </div>
            <div class="modal-body">{{ $content }}</div>
            <div class="modal-footer">
                <x-button
                        text="{{ $button->text }}"
                        color="{{ $button->color }}"
                        icon="{{ $button->icon }}"
                        url="{{ $button->url }}"
                />
            </div>
        </div>
    </div>
</div>
