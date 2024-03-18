<div class="form-group">
    <label for="title">Tiêu đề</label>
    <input type="text" id="title" name="title" class="form-control" value="{{Arr::get($attributes, 'title')}}" placeholder="Nhập tiêu đề">
</div>
<div class="form-group">
    <label for="title">Tiêu đề sản phẩm nổi bật</label>
    <input type="text" id="titleFeature" name="titleFeature" class="form-control" value="{{Arr::get($attributes, 'titlefeature')}}" placeholder="Nhập tên">
</div>
<div class="form-group">
    <label for="url">{{__('Url')}}</label>
    <input type="text" id="url" name="url" class="form-control" value="{{Arr::get($attributes, 'url')}}" placeholder="">
</div>
<div class="form-group">
    <label class="control-label">{{ __('Hình ảnh banner') }}</label>
    <div class="image-box image-box-image-banner" action="select-image">
        <input class="image-data" name="imageBanner" type="hidden" value="{{ Arr::get($attributes, 'imagebanner') }}">
        <div style="width: 8rem" class="preview-image-wrapper mb-1">
            <div class="preview-image-inner">
                <a data-bb-toggle="image-picker-choose" data-target="popup" class="image-box-actions"
                    data-result="imageBanner" data-action="select-image" data-allow-thumb="1" href="#">
                    <img class="preview-image default-image"
                        data-default="{{RvMedia::getDefaultImage()}}"
                        src="{{RvMedia::getImageUrl(Arr::get($attributes, 'imagebanner'), null, false, RvMedia::getDefaultImage())}}" alt="Preview image">
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
<div class="form-group">
    <label class="control-label">{{ __('Hình ảnh sản phẩm nổi bật') }}</label>
    <div class="image-box image-box-image-feature" action="select-image">
        <input class="image-data" name="imagefeature" type="hidden" value="{{ Arr::get($attributes, 'imagefeature') }}">
        <div style="width: 8rem" class="preview-image-wrapper mb-1">
            <div class="preview-image-inner">
                <a data-bb-toggle="image-picker-choose" data-target="popup" class="image-box-actions"
                    data-result="imagefeature" data-action="select-image" data-allow-thumb="1" href="#">
                    <img class="preview-image default-image"
                        data-default="{{RvMedia::getDefaultImage()}}"
                        src="{{RvMedia::getImageUrl(Arr::get($attributes, 'imagefeature'), null, false, RvMedia::getDefaultImage())}}" alt="Preview image">
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

        <a data-bb-toggle="image-picker-choose" data-target="popup" data-result="imagefeature" data-action="select-image"
            data-allow-thumb="1" href="#">
            Choose image
        </a>

        <div data-bb-toggle="upload-from-url">
            <span class="text-muted">or</span>
            <a href="javascript:void(0)" class="mt-1" data-bs-toggle="modal"
                data-bs-target="#rv_media_modal" data-bb-target=".image-box-image-feature">
                Add from URL
            </a>
        </div>
    </div>
</div>
