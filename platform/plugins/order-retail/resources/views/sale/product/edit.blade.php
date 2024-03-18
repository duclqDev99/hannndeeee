@php
    $sizes = [
        's' => 'S',
        'm' => 'M',
        'l' => 'L',
        'xl' => 'XL',
        '2xl' => '2xL',
        'freesize' => 'FreeSize',
    ];

@endphp
<div>
    <div id="form-wrapper">
        <form id="edit-product-form">
            <div class="row price-group">
                <input class="detect-schedule d-none" name="sale_type" type="hidden" value="0">
                <div class="col-md-4">
                    <div class="mb-3 position-relative">
                        <label class="form-label required" for="product_sku">
                            Mã sản phẩm
                        </label>
                        <input class="form-control" type="text" name="product_sku" id="product_sku"
                            value="{{ $product->sku }}">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="mb-3 position-relative">
                        <label class="form-label required" for="product_name">
                            Tên thành phẩm
                        </label>
                        <input class="form-control" type="text" name="product_name" id="product_name"
                            value="{{ $product->product_name }}">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="mb-3 position-relative">
                        <label class="form-label required" for="sale_price">
                            Thành phần
                        </label>
                        <div class="input-group input-group-flat">
                            <input class="form-control" type="text" name="ingredient" id="ingredient"
                                value="{{ $product?->ingredient }}">
                        </div>
                    </div>
                </div>

                <div class="col-12 mb-3">
                    <div class="card">
                        <div class="card-header">
                            <label class="form-label required" for="">
                                Sizes
                            </label>
                        </div>
                        <div class="card-body">
                            <div id="form-sizes">
                                @php
                                    $selectedSizes = [];
                                @endphp
                                @foreach ($product->sizes as $i => $size)
                                    @php
                                        $selectedSizes[] = $size;
                                    @endphp
                                    {{-- <div class="mb-3 row align-items-center">
                                        <div class="col-md-3">
                                            <div class="position-relative">
                                                <select class="form-select form-control size-value"
                                                    name="size[{{ $i }}]value" data-id="{{ $size->id }}">
                                                    <option value="">Chọn Size</option>
                                                    <option value="{{ $size->value }}">{{ $sizes[$size->value] }}</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="input-group input-group-flat">
                                                <input class="form-control size-quantity" type="number" min="1"
                                                    name="size[{{ $i }}]quantity" data-id="${size.id}"
                                                    id="product_quantity" value="{{ $size->quantity }}"
                                                    placeholder="Nhập số lượng">
                                            </div>
                                        </div>
                                        @if (count($product->sizes) > 1)
                                            <div class="col-md-3">
                                                <button class="btn btn-light remove-size-btn" type="button"
                                                    data-id="{{$size->id}}">
                                                    <i class="fa-solid fa-trash"></i>
                                                </button>
                                            </div>
                                        @endif
                                    </div> --}}
                                @endforeach
                                <script>
                                    window.selectedSizes = @json($selectedSizes)
                                </script>
                            </div>
                            <div>
                                <button class="btn btn-primary p-3 py-1 btn-add-size" type="button">
                                    <span class="">
                                        <i class="fa-solid fa-plus"></i>
                                    </span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="mb-3 position-relative">
                        <label class="form-label required" for="price">
                            Đơn vị tính
                        </label>
                        <div class="input-group input-group-flat">
                            <input class="form-control" type="text" name="product_cal" id="product_cal"
                                value="{{ $product->cal }}">
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="mb-3 position-relative">
                        <label class="form-label required" for="price">
                            Giá dự kiến
                        </label>
                        <div class="input-group input-group-flat">
                            <input class="form-control" type="number" name="product_price" id="product_price"
                                value="{{ $product->price }}">
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="mb-3 position-relative">
                        <label class="form-label" for="sale_price">
                            Thành tiền
                        </label>
                        <div class="input-group input-group-flat">
                            <input class="form-control" type="text" name="product_total_price"
                                id="product_total_price" value="{{ number_format($product->qty * $product->price) }}"
                                readonly>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3 position-relative">
                        <label class="form-label required" for="sale_price">
                            Địa chỉ nhận hàng
                        </label>
                        <div class="input-group input-group-flat">
                            <input class="form-control" type="text" name="product_address" id="product_address"
                                value="{{ $product->address }}">
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3 position-relative">
                        <label class="form-label required" for="price">
                            Hình thức giao hàng
                        </label>
                        <select class="form-select form-control" name="product_shipping_method"
                            id="product_shipping_method">
                            <option value="">Chọn hình thức</option>
                            <option value="hub" @if ($product->shipping_method == 'hub') selected @endif>Giao qua Hub
                            </option>
                            <option value="delivery" @if ($product->shipping_method == 'delivery') selected @endif>Giao hàng tận
                                nơi</option>
                        </select>
                    </div>
                </div>
                <div class="col-12">
                    <div class="position-relative mb-3">
                        <label for="note" class="control-label required">Mô tả chi tiết</label>
                        <textarea class="form-control" placeholder="" data-counter="500" rows="3" name="product_note" cols="50"
                            id="product_note">{!! $product?->description !!}</textarea>
                    </div>
                </div>

                <div class="col-12">
                    <div class="mb-3 position-relative">
                        <label class="form-label required" for="sale_price">
                            File thiết kế đính kèm
                        </label>
                        <div class="input-group input-group-flat">
                            <input class="form-control" type="file" name="product_design_file"
                                id="product_design" value="">
                        </div>
                    </div>
                </div>
            </div>

            <div class="card image-preview" data-name="images" id="box-image">
                <div class="card-header">
                    <div class="w-100 d-flex align-items-center justify-content-between">
                        <div class="d-inline-flex align-items-center gap-3">
                            <h3 class="card-title">Hình ảnh</h3>
                            <p class="mb-0 text-danger" id="images-error-message" style="display: none">Vui lòng
                                tải ít nhất
                                1 ảnh!</p>
                        </div>
                    </div>
                </div>
                <div class="card-body" style="display: block">
                    <label style="display: none" for="images"
                        class="w-100 mb-0 gallery-images-wrapper list-images form-fieldset" style="cursor: pointer">
                        <div class="images-wrapper">
                            <div class="text-center cursor-pointer default-placeholder-gallery-image">
                                <div class="mb-3">
                                    <span class="icon-tabler-wrapper icon-md text-secondary">
                                        <svg xmlns="http://www.w3.org/2000/svg"
                                            class="icon icon-tabler icon-tabler-photo-plus" width="24"
                                            height="24" viewBox="0 0 24 24" stroke-width="2"
                                            stroke="currentColor" fill="none" stroke-linecap="round"
                                            stroke-linejoin="round">
                                            <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                            <path d="M15 8h.01"></path>
                                            <path
                                                d="M12.5 21h-6.5a3 3 0 0 1 -3 -3v-12a3 3 0 0 1 3 -3h12a3 3 0 0 1 3 3v6.5">
                                            </path>
                                            <path d="M3 16l5 -5c.928 -.893 2.072 -.893 3 0l4 4"></path>
                                            <path d="M14 14l1 -1c.67 -.644 1.45 -.824 2.182 -.54"></path>
                                            <path d="M16 19h6"></path>
                                            <path d="M19 16v6"></path>
                                        </svg>
                                    </span>
                                </div>
                                <p class="mb-0 text-body">
                                    Nhấn để thêm ảnh
                                </p>
                                <small>Tối đa 10 ảnh</small>
                            </div>
                            <div class="row w-100 list-gallery-media-images hidden ui-sortable" style="">
                            </div>
                        </div>
                        <div style="display: none;" class="footer-action">
                            <a data-bb-toggle="gallery-add" class="me-2 cursor-pointer">Add Images</a>
                            <button class="text-danger cursor-pointer btn-link" data-bb-toggle="gallery-reset">
                                Reset
                            </button>
                        </div>
                    </label>
                    <input class="sr-only" type="file" name="images[]" multiple id="images[]">
                    <input class="sr-only" type="file" name="pick-images[]" multiple accept="image/*"
                        id="images">
                    <div id="image-preview-container" class="d-flex gap-3 flex-wrap">
                        @foreach ($product->images as $image)
                            <div class="position-relative overflow-hidden img-wrapper"
                                style='width: 160px;height: 160px;'>
                                <img src="{{ Botble\Media\Facades\RvMedia::getImageUrl($image->url) }}"
                                    class= 'img-thumbnail img-fluid object-fit-cover w-100 h-100'>
                                <button data-type="old" data-name="{{ $image->url }}" type="button"
                                    class="btn btn-light rounded-circle position-absolute delete-image-btn"
                                    style="width:18px;height:32px; top:8px; right:8px;">
                                    <i class="fa-solid fa-xmark"></i>
                                </button>
                            </div>
                        @endforeach
                    </div>
                    <div class="change-images-btn-list mt-3" style="display: block">
                        <button type="button" id="add-image-btn" class="btn btn-primary p-0">
                            <label for="images" class="w-100 h-100 d-block p-3 py-1" style="cursor: pointer">
                                <i class="fa-solid fa-plus"></i>
                            </label>
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
