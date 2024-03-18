<x-core::modal id="searchProductModal" :title="'Tìm kiếm YCSX'"
        button-id="submit-pick-purchase-order-btn"
        data-load-form-url="{{ route('retail.sale.purchase-order.get-add-product-form') }}" :button-label="'Lưu'"
        :size="'xl'">
        <div class="add-product-form-wrapper">
            <form id="searchProductForm" action="">
                <div class="row price-group">
                    <div class="col-12">
                        <div class="mb-3 position-relative">
                            <label class="form-label required" for="price">
                                Nhập mã YCSX
                            </label>
                            <div class="input-group input-group-flat">
                                <input class="form-control" type="text" name="search_order" id="search_order"
                                    placeholder="Tìm kiếm..."
                                    value="">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card order-preview-wrapper" data-name="images" id="box-image" style="display: none">
                    <div class="card-header">
                        <div class="w-100 d-flex align-items-center justify-content-between">
                            <div class="d-inline-flex align-items-center gap-3">
                                <span >Kết quả tìm kiếm <span style="display: none" class="ml-2" id="count-result">(3)</span></span>
                            </div>
                        </div>
                    </div>
                    <div class="card-body d-flex align-items-center justify-content-center flex-column" id="products-response"> </div>
                </div>
            </form>
        </div>
</x-core::modal>