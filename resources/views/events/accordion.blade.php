<div class="accordion mb-3" id="accordion">
    <div class="accordion-item">
        <h5 class="accordion-header" id="headingOne">
            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                {{ __('app.protocol') }}
            </button>
        </h5>
        <div id="collapseOne" class="accordion-collapse collapse show" aria-labelledby="headingOne" data-bs-parent="#accordion">
            <div class="form-control mb-3">
                <input class="form-control" type="file" name="protocol" id="protocol">
            </div>
        </div>
    </div>
    <div class="accordion-item">
        <h5 class="accordion-header" id="headingTwo">
            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                OBelarus.net
            </button>
        </h5>
        <div id="collapseTwo" class="accordion-collapse collapse" aria-labelledby="headingTwo" data-bs-parent="#accordion">
            <div class="form-floating mb-3">
                <input class="form-control" id="url" name="url">
                <label for="obelarus-url">{{ __('app.common.url') }}</label>
            </div>
        </div>
    </div>
</div>
