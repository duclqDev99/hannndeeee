<div class="form-group">
    <label class="control-label">{{ __('Tiêu đề') }}</label>
    <input type="text" name="title" value="{{ Arr::get($attributes, 'title') }}" class="form-control" placeholder="Title">
</div>
<div class="form-group">
    <label class="control-label">{{ __('Hình ảnh shape cho nền') }}</label>
    <div class="image-box image-box-image-banner" action="select-image">
        <input class="image-data" name="imageBanner" type="hidden" value="{{ Arr::get($attributes, 'imagebanner') }}">
        <div style="width: 8rem" class="preview-image-wrapper mb-1">
            <div class="preview-image-inner">
                <a data-bb-toggle="image-picker-choose" data-target="popup" class="image-box-actions"
                    data-result="imageBanner" data-action="select-image" data-allow-thumb="1" href="#">
                    <img class="preview-image default-image"
                        data-default="http://127.0.0.1:8000/vendor/core/core/base/images/placeholder.png"
                        src="http://127.0.0.1:8000/vendor/core/core/base/images/placeholder.png" alt="Preview image">
                    <span class="image-picker-backdrop"></span>
                </a>
                <button class="btn btn-pill btn-icon  btn-sm image-picker-remove-button p-0"
                    style="display: none; --bb-btn-font-size: 0.5rem;" type="button"
                    data-bb-toggle="image-picker-remove" data-bs-toggle="tooltip" data-bs-placement="top"
                    aria-label="Remove image" data-bs-original-title="Remove image">
                    <span class="icon-tabler-wrapper icon-sm icon-left">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-x" width="24"
                            height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"
                            stroke-linecap="round" stroke-linejoin="round">
                            <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                            <path d="M18 6l-12 12"></path>
                            <path d="M6 6l12 12"></path>
                        </svg>
                    </span>
                </button>
            </div>
        </div>

        <a data-bb-toggle="image-picker-choose" data-target="popup" data-result="imageBanner" data-action="select-image"
            data-allow-thumb="1" href="#">
            Choose image
        </a>

        <div data-bb-toggle="upload-from-url">
            <span class="text-muted">or</span>
            <a href="javascript:void(0)" class="mt-1" data-bs-toggle="modal"
                data-bs-target="#rv_media_modal" data-bb-target=".image-box-image-banner">
                Add from URL
            </a>
        </div>
    </div>
</div>