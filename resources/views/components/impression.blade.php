@php
    /**
     * @var string $date
     * @var string $email
     */
@endphp
<div class="tooltip">
    <span style="margin-right: 5px;">{{ $date }}</span>
    <i class="impression-icon bi bi-info-circle-fill"></i>
    <span class="tooltip-text">{{ $email }}</span>
</div>
<style>
    .tooltip {
        position: relative;
        display: inline-block;
    }

    .tooltip .tooltip-text {
        visibility: hidden;
        width: 120px;
        background-color: #555;
        color: #fff;
        text-align: center;
        padding: 5px 0;
        border-radius: 6px;

        position: absolute;
        z-index: 1;
        bottom: 100%;
        left: 50%;
        margin-left: -60px;

        opacity: 0;
        transition: opacity 0.3s;
    }

    .tooltip:hover .tooltip-text {
        visibility: visible;
        opacity: 1;
    }

    ..tooltip {
        position: relative;
        display: inline-block;
    }

    .tooltip .tooltiptext {
        visibility: hidden;
        width: 120px;
        background-color: #555;
        color: #fff;
        text-align: center;
        padding: 5px 0;
        border-radius: 6px;

        position: absolute;
        z-index: 1;
        bottom: 100%;
        left: 50%;
        margin-left: -60px;

        opacity: 0;
        transition: opacity 0.3s;
    }

    .tooltip:hover .tooltip-text {
        visibility: visible;
        opacity: 1;
    }

    .icon {
        background-color: #ccc;
        border-radius: 50%;
        padding: 5px;
        display: inline-block;
        text-align: center;
    } {
        background-color: #ccc;
        border-radius: 50%;
        padding: 5px;
        display: inline-block;
        text-align: center;
    }
</style>
