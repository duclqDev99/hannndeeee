<template>
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body">
                    <h2 class="form-label">{{ __('Thông tin chính sách') }}</h2>
                    <div class="row">

                    <div class="mb-3 position-relative col-6">
                        <label class="form-label">{{ __('Tên chính sách: ') }}</label>
                        <input
                                type="text"
                                class="form-control coupon-code-input"
                                name="name"
                            v-model="name"

                            />

                    </div>
                    <div class="mb-3 position-relative col-6">
                        <label class="form-label">{{ __('Mã chính sách: ') }}</label>
                        <input
                                type="text"
                                class="form-control"
                                name="code"
                            v-model="code"

                            />

                    </div>
                    <div class="row">
                        <div class="col-9">
                            <label class="form-label">{{ __('Đối tượng áp dụng: ') }}</label>
                            <select
                                class="form-select"
                                id="apply_for"
                                name="apply_for"
                                v-model="apply_for"
                            >
                            <option v-for="option in apply_list" :value="option.value">{{ option.label }}</option>
                            </select>
                        </div>
                        <div class="col-3">
                            <div v-if="apply_for === 'KHTT'">
                            <label class="form-label">Khách hàng:</label>

                                <select class="form-select" id="select-con" name="customer_class_type" v-model="customer_class_type" >
                                    <option v-for="option in custom_apply" :value="option.value">{{ option.descript }}</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>



                    <div class="mb-3 position-relative">
                        <div class="row">
                            <div class="col-3">
                        <div class="mb-3 position-relative">
                                <label class="form-label">{{ __('Kho áp dụng: ') }}</label>
                        <select
                            class="form-select"
                            id="select-promotion"
                            name="type_warehouse"
                            v-model="type_warehouse"
                        >
                            <option value="sale">{{ __('Kho sale') }}</option>
                            <option value="showroom">{{ __('Showroom') }}</option>
                        </select>
                            </div>
                    </div>

                        <div class="col-3" v-if="type_warehouse == 'sale'">
                                <label class="form-label">{{ __('Sau: ') }}</label>
                                <input type="number" name="time_active"  class="form-control" v-model="time_active" id="time_active" >
                            </div>
                            <div class="col-2"  v-if="type_warehouse == 'sale'">
                                <label class="form-label" >{{ __('Thời gian: ') }}</label>
                                <select
                                    class="form-select"
                                    id="select-date"
                                    name="type_date_active"
                                    v-model="type_date_active"

                                >
                                    <option value="date" >{{ __('Ngày') }}</option>
                                    <option value="month">{{ __('Tháng') }}</option>
                                    <option value="year">{{ __('Năm') }}</option>
                                </select>
                            </div>
                            <div class="col-4"  v-if="type_warehouse == 'sale'">
                                <label class="form-label">{{ __('Bắt đầu từ: ') }}</label>
                                <select
                                class="form-select "
                                id="select-time"
                                name="type_time"
                                v-model="type_time"
                                >
                                <option value="date_production" >{{ __('Ngày sản xuất') }}</option>
                                <option value="date_in">{{ __('Ngày nhập kho') }}</option>
                            </select>
                        </div>
                        </div>

                    </div>
                    <!-- <div class="mb-3 position-relative">
                            <label class="form-check">
                                <input class="form-check-input" type="checkbox" name="is_unlimited" v-model="is_unlimited" value="1">
                                <span class="form-check-label">
                                    {{ __('Số lượng không giới hạn') }}
                                </span>
                            </label>
                        </div>

                        <div class="mb-3 position-relative" v-show="!is_unlimited">
                            <label class="form-label">{{ __('Số lượng:') }}</label>
                            <input type="number" name="quantity" v-model="quantity" value="1"  class="form-control" id="quantity" >
                        </div> -->

                        <div class="border-top">
                            <h4 class="mt-3 mb-2">{{ __('Loại giảm giá') }}</h4>
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <select id="discount-type-option" name="type_option" class="form-select" v-model="type_option"
                                        @change="handleChangeTypeOption()">
                                        <option value="amount">{{ currency }}</option>
                                        <option value="percentage">{{ __('% Giảm giá') }}</option>
                                    </select>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <div class="input-group input-group-flat">
                                        <span class="input-group-text">{{ value_label }}</span>
                                        <input type="number" class="form-control" name="value" v-model="discount_value" autocomplete="off"
                                            placeholder="0" />
                                        <span class="input-group-text"> {{ discountUnit }} </span>
                                    </div>
                                </div>
                                <div class="col-md-4 mb-3">
                                <div class="input-group input-group-flat" @change="handleChangeTarget()">
                                    <span class="input-group-text" v-show="type_option !== 'shipping' && type_option">
                                        {{ __('Áp dụng cho') }}
                                    </span>

                                    <select id="select-offers" class="form-control form-select" name="target" v-model="target">
                                        <option value="all-orders" v-if="type_option !== 'same-price'">
                                            {{ __('Tất cả sản phẩm') }}
                                        </option>
                                        <option value="products-by-category">{{ __('Danh mục sản phẩm') }}</option>
                                        <option value="specific-product">{{ __('Sản phẩm cụ thể') }}</option>

                                    </select>
                                </div>
                            </div>

                        </div>
                        <div class="col-md-4 mb-3"  v-if="target === 'specific-product'">
                            <div class="position-relative box-search-advance product">
                            <input
                                type="text"
                                class="form-control textbox-advancesearch"
                                @click="loadListProductsForSearch(0)"
                                @keyup="handleSearchProduct(0, $event.target.value)"
                                :placeholder="__('Tìm kiếm sản phẩm')"
                            />

                            <div
                                class="card position-absolute w-100 z-1"
                                :class="{ active: products, hidden: hidden_product_search_panel }"
                                :style="[loading ? { minHeight: '10rem' } : {}]"
                            >
                                <div v-if="loading" class="loading-spinner"></div>
                                <div v-else class="list-group list-group-flush overflow-auto" style="max-height: 25rem">
                                    <a
                                        class="list-group-item list-group-item-action"
                                        v-for="product in products.data"
                                        @click="handleSelectProducts(product)"
                                        href="javascript:void(0)"
                                    >
                                        <div class="row align-items-center">
                                            <div class="col-auto">
                                                <span class="avatar" :style="{ backgroundImage: 'url(' + product.image_url + ')' }"></span>
                                            </div>
                                            <div class="col text-truncate">
                                                <div class="text-body d-block">{{ product.name }}</div>
                                            </div>
                                        </div>
                                    </a>
                                    <div class="p-3" v-if="products.data.length === 0">
                                        <p class="text-muted text-center mb-0">{{ __('discount.no_products_found') }}</p>
                                    </div>
                                </div>
                                <div
                                    class="card-footer"
                                    v-if="(products.next_page_url || products.prev_page_url) && !loading"
                                >
                                    <discount-search-box-pagination
                                        :resource="products"
                                        @on-prev="loadListProductsForSearch(
                                            0,
                                            products.prev_page_url ? products.current_page - 1 : products.current_page,
                                            true
                                        )"
                                        @on-next="loadListProductsForSearch(
                                            0,
                                            products.next_page_url ? products.current_page + 1 : products.current_page,
                                            true
                                        )"
                                    />
                                </div>
                            </div>
                            </div>
                        </div>
       <div
                            v-if="selected_products.length  && target === 'specific-product'"
                            class="list-group list-group-flush list-group-hoverable"
                        >
                            <input type="hidden" v-model="selected_product_ids" name="products" />

                            <h4>{{ __('Danh sách sản phẩm') }}</h4>

                            <div class="list-group-item" v-for="product in selected_products">
                                <div class="row align-items-center">
                                    <div class="col-auto">
                                        <span class="avatar" :style="{ backgroundImage: 'url(' + product.image_url + ')' }"></span>
                                    </div>
                                    <div class="col text-truncate">
                                        <a :href="product.product_link" class="text-body d-block" target="_blank">{{ product.name }}</a>
                                    </div>
                                    <div class="col-auto">
                                        <discount-list-item-remove-icon-button @click="handleRemoveProduct($event, product)" />
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3" v-if="target === 'products-by-category'">
                                <select name="product_categories" class="form-select" v-model="product_category_id">
                                    <option
                                        v-for="productCategory in product_categories"
                                        :value="productCategory.id"
                                        v-html="productCategory.name"
                                    >
                                    </option>
                                </select>
                            </div>
                        </div>
                        <div class="row">

                            <div class="col-6 mb-3 position-relative">
                                <label for="">Chứng từ đính kèm</label>
                                <div v-if="document.file">
                                    <a :href="document.file" target="_blank">Xem tệp đã tải lên</a>
                                </div>
                                <input name="document" class="form-control" type="file"  />

                            </div>
                            <div class="col-6 mb-3 position-relative">
                                            <div class="gallery-images-wrapper list-images form-fieldset">
                                                <div class="images-wrapper mb-2">


                                                    <div v-show="imageUrls.length == 0" data-bb-toggle="gallery-add"
                                                        class="text-center cursor-pointer default-placeholder-gallery-image"
                                                        data-name="images[]">
                                                        <div class="mb-3">
                                                            <span class="icon-tabler-wrapper">
                                                                <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-photo-plus"
                                                                width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"
                                                                fill="none" stroke-linecap="round" stroke-linejoin="round">
                                                                    <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                                                    <path d="M15 8h.01"></path>
                                                                    <path d="M12.5 21h-6.5a3 3 0 0 1 -3 -3v-12a3 3 0 0 1 3 -3h12a3 3 0 0 1 3 3v6.5"></path>
                                                                    <path d="M3 16l5 -5c.928 -.893 2.072 -.893 3 0l4 4"></path>
                                                                    <path d="M14 14l1 -1c.67 -.644 1.45 -.824 2.182 -.54"></path>
                                                                    <path d="M16 19h6"></path>
                                                                    <path d="M19 16v6"></path>
                                                                </svg>


                                                            </span>
                                                        </div>
                                                        <p class="mb-0 text-body">
                                                            Chọn hình ảnh đính kèm
                                                        </p>
                                                    </div>
                                                    <div class="row w-100 list-gallery-media-images ui-sortable" data-name="images[]" data-allow-thumb="1">
                                                        <!-- Sử dụng v-for để lặp qua mỗi URL hình ảnh trong mảng imageUrls -->
                                                        <div v-for="(imageUrl, index) in imageUrls" :key="index" class="col-lg-2 col-md-3 col-4 gallery-image-item-handler mb-2">
                                                            <div class="custom-image-box image-box">
                                                            <input type="hidden" name="images[]" :value="imageUrl.value" class="image-data">
                                                            <div class="preview-image-wrapper w-100">
                                                                <div class="preview-image-inner">
                                                                <img :src="imageUrl.file" :alt="'Image ' + index" class="preview-image">
                                                                <div class="image-picker-backdrop"></div>
                                                                <span class="image-picker-remove-button">
                                                                    <button  @click.prevent="removeImage(index)" class="btn btn-sm btn-icon">
                                                                        <span class="icon-tabler-wrapper icon-sm icon-left">
                                                                            <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-x" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                                                                <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                                                                <path d="M18 6l-12 12"></path>
                                                                                <path d="M6 6l12 12"></path>
                                                                            </svg>
                                                                        </span>
                                                                    </button>
                                                                </span>
                                                                <div data-bb-toggle="image-picker-edit" class="image-box-actions cursor-pointer"></div>
                                                                </div>
                                                            </div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                </div>
                                                <div class="footer-action" v-show="imageUrls && imageUrls.length > 0" style="display: none;">
                                                    <a data-bb-toggle="gallery-add" class="me-2 cursor-pointer">Thêm</a>
                                                    <button class="text-danger cursor-pointer btn-link" @click="removeAllimageUrls()" data-bb-toggle="gallery-reset">Reset</button>
                                                </div>

                                            </div>

                            </div>
                        </div>
</div>
</div>
</div>
<div class="col-md-4">
    <div class="meta-boxes card mb-3">
        <div class="card-header">
            <h4 class="card-title">{{ __('Thời gian thực hiện chính sách') }}</h4>
        </div>
        <div class="card-body">
            <div class="mb-3 position-relative">
                <label class="form-label">{{ __('Ngày bắt đầu') }}</label>
                <div class="d-flex">
                    <div class="input-icon datepicker">
                        <input type="text" :placeholder="dateFormat" :data-date-format="dateFormat" name="start_date"
                            v-model="start_date" class="form-control rounded-end-0" readonly data-input />
                        <span class="input-icon-addon">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24"
                                viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"
                                stroke-linecap="round" stroke-linejoin="round">
                                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                <path
                                    d="M4 7a2 2 0 0 1 2 -2h12a2 2 0 0 1 2 2v12a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2v-12z" />
                                <path d="M16 3v4" />
                                <path d="M8 3v4" />
                                <path d="M4 11h16" />
                                <path d="M11 15h1" />
                                <path d="M12 15v3" />
                            </svg>
                        </span>
                    </div>
                    <div class="input-icon">
                        <input type="text" placeholder="hh:mm" name="start_time" v-model="start_time"
                            class="form-control rounded-start-0 timepicker timepicker-24" />
                        <span class="input-icon-addon">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-clock"
                                width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"
                                fill="none" stroke-linecap="round" stroke-linejoin="round">
                                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                <path d="M3 12a9 9 0 1 0 18 0a9 9 0 0 0 -18 0" />
                                <path d="M12 7v5l3 3" />
                            </svg>
                        </span>
                    </div>
                </div>
            </div>
            <div class="mb-3 position-relative">
                <label class="form-label">{{ __('Ngày kết thúc') }}</label>
                <div class="d-flex">
                    <div class="input-icon datepicker">
                        <input type="text" :placeholder="dateFormat" :data-date-format="dateFormat" name="end_date"
                            v-model="end_date" class="form-control rounded-end-0" :disabled="unlimited_time" readonly
                            data-input />
                        <span class="input-icon-addon">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24"
                                viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"
                                stroke-linecap="round" stroke-linejoin="round">
                                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                <path
                                    d="M4 7a2 2 0 0 1 2 -2h12a2 2 0 0 1 2 2v12a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2v-12z" />
                                <path d="M16 3v4" />
                                <path d="M8 3v4" />
                                <path d="M4 11h16" />
                                <path d="M11 15h1" />
                                <path d="M12 15v3" />
                            </svg>
                        </span>
                    </div>
                    <div class="input-icon">
                        <input type="text" placeholder="hh:mm" name="end_time" v-model="end_time"
                            class="form-control rounded-start-0 timepicker timepicker-24" :disabled="unlimited_time" />
                        <span class="input-icon-addon">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-clock"
                                width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"
                                fill="none" stroke-linecap="round" stroke-linejoin="round">
                                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                <path d="M3 12a9 9 0 1 0 18 0a9 9 0 0 0 -18 0" />
                                <path d="M12 7v5l3 3" />
                            </svg>
                        </span>
                    </div>
                </div>
            </div>
            <div class="mb-3 position-relative">
                <label class="form-check">
                    <input class="form-check-input" type="checkbox" name="unlimited_time" v-model="unlimited_time"
                        value="1">
                    <span class="form-check-label">{{ __('Không giới hạn') }}</span>
                </label>
            </div>
        </div>
    </div>
    <div class="card meta-boxes">
    <div class="card-header">
    <h4 class="card-title">
        <label for="status" class="control-label required" aria-required="true">Trạng thái</label>
    </h4>
    </div>
        <div class=" card-body">
            <select class="form-control select-full form-select select2-hidden-accessible"
            v-model="status"
            data-placeholder="Select an option" id="status" name="status" data-select2-id="select2-data-status" tabindex="-1" aria-hidden="true">
                <option value="active" data-select2-id="select2-data-5-s1tm">Kích hoạt</option>
                <option value="inactive" data-select2-id="select2-data-7-rog3">Ngưng kích hoạt</option>
            </select>
        </div>
    </div>
    <div class="card">
        <div class="card-body">
            <button type="submit" class="btn btn-primary">{{ __('Lưu thông tin') }}</button>
        </div>

    </div>
</div>
</div>
</template>
<style lang="scss">
.date-time-group {
    .invalid-feedback {
        position: absolute;
        bottom: -15px;
    }
}
</style>
<script>

const moment = require('moment')
import DiscountSearchBoxPagination from "../../../../../ecommerce/resources/assets/js/components/partials/DiscountSearchBoxPagination.vue";

export default {
    data: () => {
        return {
            status: 'active',
            type_warehouse: 'sale',
            type_date_active: 'date',
            type_time: 'date_production',
            name: null,
            is_unlimited: true,
            apply_via_url: false,
            display_at_checkout: false,
            description: null,
            time_active: 0,
            quantity: 0,
            unlimited_time: true,
            start_date: moment().format('Y-MM-DD'),
            start_time: '00:00',
            end_date: moment().format('Y-MM-DD'),
            end_time: '23:59',
            type_option: 'amount',
            discount_value: null,
            target: 'all-orders',
            can_use_with_promotion: false,
            value_label: 'Giá trị',
            variants: {
                data: [],
            },
            selected_variants: [],
            selected_variant_ids: [],
            hidden_product_search_panel: true,
            product_collection_id: null,
            product_collections: [],
            product_category_id: null,
            product_categories: [],
            discount_on: 'per-order',
            min_order_price: 0,
            product_quantity: 1,
            products: {
                data: [],
            },
            selected_products: [],
            selected_product_ids: [],
            product_keyword: null,
            customers: {
                data: [],
            },
            selected_customers: [],
            selected_customer_ids: [],
            customer_keyword: null,
            hidden_customer_search_panel: true,
            loading: false,
            discountUnit: '$',
            apply_for: 'CBNV',
            imageUrls: [],
            document: {},
        }
    },
    props: {
        currency: {
            type: String,
            default: () => null,
            required: true,
        },
        dateFormat: {
            type: String,
            default: () => 'Y-m-d',
            required: true,
        },
        discount: {
            type: Object,
            default: () => null,
        },
        apply_list: {
            type: Object,
            default: [
                { value: 'CBNV', label: 'CBNV' },
                { value: 'partner', label: 'Đối tác' },
                { value: 'KHTT', label: 'Khách hàng thân thiết' }
            ]
        },
        custom_apply: {
            type: Object,
        },
    },
    watch: {
        quantity(value) {
            if (value < 1) {
                Botble.showError('Số lượng phải lớn hơn 0')

            }
        }
    },
    mounted: async function () {
        this.discountUnit = this.currency

        if (this.discount) {
            console.log(this.discount);
            this.name = this.discount.name
            this.code = this.discount.code
            this.time_active = this.discount.time_active
            this.quantity = this.discount.quantity
            this.start_date = moment(this.discount.start_date).utcOffset('+0700').format('Y-MM-DD')
            this.start_time = moment(this.discount.start_date).utcOffset('+0700').format('HH:mm')
            this.target = this.discount.target
            if (this.discount.end_date) {
                this.end_date = moment(this.discount.end_date).utcOffset('+0700').format('Y-MM-DD')
                this.end_time = moment(this.discount.end_date).utcOffset('+0700').format('HH:mm')
            }
            let type_warehouse = this.discount.type_warehouse

            this.unlimited_time = !this.discount.end_date
            this.is_unlimited = !this.discount.quantity
            this.type_warehouse = type_warehouse == "Botble\\SaleWarehouse\\Models\\SaleWarehouse" ? 'sale' : 'showroom'
            this.type_date_active = this.discount.type_date_active
            this.description = this.discount.description
            this.type_option = this.discount.type_option
            this.type_time = this.discount.type_time
            this.status = this.discount.status.value
            this.discount_value = this.discount.value || 0
            this.apply_for = this.discount.apply_for
            this.handleChangeTypeOption()
            this.discount.listProduct.map(item => {
                this.selected_products.push(item)
                this.selected_product_ids.push(item.id)
            })
            this.document = this.discount.document
            this.customer_class_type = this.discount.customer_class_type
            this.discount.url.map(item => {
                this.imageUrls.push(item)
            })

        }
    },
    methods: {

        handleChangeTypeOption: function () {
            let context = this

            context.discountUnit = context.currency
            context.value_label = context.__('Giá trị')

            switch (context.type_option) {
                case 'amount':
                    break
                case 'percentage':
                    context.discountUnit = '%'
                    break
            }
        },
        loadListProductsForSearch: function (include_variation = 1, page = 1, force = false) {
            let context = this
            context.hidden_product_search_panel = false
            $('.textbox-advancesearch').closest('.box-search-advance').find('.panel').removeClass('hidden')
            if (_.isEmpty(context.variants.data) || _.isEmpty(context.products.data) || force) {
                context.loading = true
                axios
                    .get(
                        route('products.get-list-products-for-select', {
                            keyword: context.product_keyword,
                            include_variation: include_variation,
                            page: page,
                        })
                    )
                    .then((res) => {
                        if (include_variation) {
                            context.variants = res.data.data
                        } else {
                            context.products = res.data.data
                        }

                        context.loading = false
                    })
                    .catch((res) => {
                        Botble.handleError(res.response.data)
                    })
            }
        },
        handleSelectProducts: function (item) {
            if (!_.includes(this.selected_product_ids, item.id)) {
                this.selected_products.push(item)
                this.selected_product_ids.push(item.id)
            }
            this.hidden_product_search_panel = true
        },
        handleChangeTarget: function () {
            let context = this
            switch (context.target) {
                case 'products-by-category':
                    context.getListProductCategories()
                    break
                case 'specific-product':
                    context.selected_variant_ids = []
                    context.selected_customers = []
                    context.selected_customer_ids = []
                    break
            }
        },
        getListProductCollections: async function () {
            let context = this
            if (_.isEmpty(context.product_collections)) {
                context.loading = true
                await axios
                    .get(route('product-collections.get-list-product-collections-for-select'))
                    .then((res) => {
                        context.product_collections = res.data.data
                        if (!_.isEmpty(res.data.data)) {
                            context.product_collection_id = _.first(res.data.data).id
                        }
                        context.loading = false
                    })
                    .catch((res) => {
                        Botble.handleError(res.response.data)
                    })
            }
        },
        getListProductCategories: async function () {
            let context = this

            if (context.product_categories.length < 1) {
                context.loading = true

                await $httpClient
                    .make()
                    .get(route('product-categories.get-list-product-categories-for-select'))
                    .then(({ data }) => {
                        context.product_categories = data.data
                        if (data.data.length > 0) {
                            context.product_category_id = data.data[0].id
                        }
                    })
                    .catch(({ response }) => Botble.handleError(response.data))
                    .finally(() => (context.loading = false))
            }
        },
        handleSearchProduct: function (include_variation = 1, value) {
            if (value !== this.product_keyword) {
                let context = this
                this.product_keyword = value
                setTimeout(() => {
                    context.loadListProductsForSearch(include_variation, 1, true)
                }, 500)
            }
        },
        removeImage(index) {
            this.imageUrls.splice(index, 1);
        },
        removeAllimageUrls() {
            this.imageUrls= [];
        }



    },
    components: { DiscountSearchBoxPagination},

}
</script>
