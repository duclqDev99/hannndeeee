<template>
    <div class="row row-cards" style="justify-content: center;">
        <div class="col-md-9">
            <div class="card">
                <div class="card-header">
                    <div class="col-lg-6" style="margin : 0; padding : 0">
                        <h4 class="card-title">Thêm sản phẩm</h4>
                    </div>
                    <div class="col-lg-6" style="margin : 0; padding : 0">
                        <button id="btn-add-qrcode" class="btn btn-success" style="float: inline-end; " @click="insertProductAgent"  v-if="child_products.length > 0" >
                            <div id="load-button" class="spinner-border" role="status" style="display: none">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            Thêm sản phẩm
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="title" class="form-label required">Kho </label>
                        <select class="form-select" aria-label="Default select example">
                            <option value="{{this.agentWarehouse['id']}}" selected>{{this.agentWarehouse['name']}}</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="title" class="form-label required">Chọn sản phẩm </label>
                        <div class="position-relative box-search-advance product" style="margin-bottom: 10px">
                            <input
                                type="text"
                                class="form-control textbox-advancesearch product"
                                :placeholder="__('order.search_or_create_new_product')"
                                @click="loadListProductsAndVariations()"
                                @keyup="handleSearchProduct($event.target.value)"
                            />

                            <div
                                class="card position-absolute z-1 w-100"
                                :class="{ active: list_products, hidden: hidden_product_search_panel }"
                                :style="[loading ? { minHeight: '10rem' } : {}]"
                            >
                                <div v-if="loading" class="loading-spinner"></div>
                                <div v-else class="list-group list-group-flush overflow-auto" style="max-height: 25rem">
                                    <a
                                        v-for="product_item in list_products.data"
                                        :class="{
                                            'list-group-item list-group-item-action': true,
                                            'item-selectable': !product_item.variations.length,
                                            'item-not-selectable': product_item.variations.length,
                                        }"
                                        v-bind:key="product_item.id"

                                    >
                                        <div class="row align-items-start">
                                            <div class="col-auto">
                                                <span class="avatar" :style="{ backgroundImage: 'url(' + product_item.image_url + ')' }"></span>
                                            </div>
                                            <div class="col text-truncate" >
                                                <ProductAction
                                                    :ref="'product_actions_' + product_item.id"
                                                    :product="product_item"
                                                    :child_products = "child_products"
                                                    @select-product="selectProductVariant"
                                                />

                                                <div v-if="product_item.variations.length" class="list-group list-group-flush">
                                                    <div
                                                        class="list-group-item p-2"
                                                        v-for="variation in product_item.variations"
                                                        v-bind:key="variation.id"
                                                    >
                                                        <ProductAction
                                                            :product="variation"
                                                            @select-product="selectProductVariant"
                                                            v-if="checkEmptyProduct(variation)"
                                                        />
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </a>
                                    <div class="p-3" v-if="list_products.data && list_products.data.length === 0">
                                        <p class="text-muted text-center mb-0">{{ __('order.no_products_found') }}</p>
                                    </div>
                                </div>
                                <div
                                    class="card-footer"
                                    v-if="
                                        ((list_products.links && list_products.links.next)
                                        || (list_products.links && list_products.links.prev))
                                        && !loading
                                    "
                                >
                                    <ul class="pagination my-0 d-flex justify-content-end">
                                        <li :class="{'page-item': true, disabled: list_products.meta.current_page === 1}">
                                            <span v-if="list_products.meta.current_page === 1" class="page-link" :aria-disabled="list_products.meta.current_page === 1">
                                                <i class="icon ti ti-chevron-left"></i>
                                            </span>
                                            <a
                                                v-else
                                                href="javascript:void(0)"
                                                class="page-link"
                                                @click="loadListProductsAndVariations(
                                                    list_products.links.prev
                                                        ? list_products.meta.current_page - 1
                                                        : list_products.meta.current_page,
                                                    true
                                                )"
                                            >
                                                <i class="icon ti ti-chevron-left"></i>
                                            </a>
                                        </li>
                                        <li :class="{'page-item': true, disabled: !list_products.links.next}">
                                            <span v-if="!list_products.links.next" class="page-link" :aria-disabled="!list_products.links.next">
                                                <i class="icon ti ti-chevron-right"></i>
                                            </span>
                                            <a
                                                v-else
                                                href="javascript:void(0)"
                                                class="page-link"
                                                @click="loadListProductsAndVariations(
                                                    list_products.links.next
                                                        ? list_products.meta.current_page + 1
                                                        : list_products.meta.current_page,
                                                    true
                                                )"
                                            >
                                                <i class="icon ti ti-chevron-right"></i>
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <div
                            :class="{ 'loading-skeleton': checking }"
                            v-if="child_products.length"
                        >
                            <table class="table table-bordered table-vcenter">
                                <thead>
                                <tr class="text-center">
                                    <th>Hình ảnh</th>
                                    <th>Tên sản phẩm</th>
                                    <th width="90">Số lượng tồn</th>
                                    <th width="90">Số lượng nhập</th>
                                    <th>Hành động</th>
                                </tr>
                                </thead>
                                <tbody>
                                <tr v-for="(variant, vKey) in child_products" v-bind:key="`${variant.id}-${vKey}`" class="text-center">
                                    <td>
                                        <img
                                            :src="variant.image_url"
                                            :alt="variant.name"
                                            width="50"
                                        />
                                    </td>
                                    <td>
                                        <a :href="variant.product_link" target="_blank">{{ variant.name }}</a>
                                        <p v-if="variant.variation_attributes">
                                            <small>{{ variant.variation_attributes }}</small>
                                        </p>
                                        <ul v-if="variant.option_values && Object.keys(variant.option_values).length">
                                            <li>
                                                <span>{{ __('order.price') }}:</span>
                                                <span>{{ variant.original_price_label }}</span>
                                            </li>
                                            <li v-for="option in variant.option_values" v-bind:key="option.id">
                                                <span>{{ option.title }}:</span>
                                                <span v-for="value in option.values" v-bind:key="value.id">
                                                    {{ value.value }} <strong>+{{ value.price_label }}</strong>
                                                </span>
                                            </li>
                                        </ul>
                                    </td>
                                    <td>
                                        <!-- {{ variant.quantity }} -->
                                        {{fillterProductQuantity(variant.id)}}
                                    </td>
                                    <td class="text-center">
                                        <input
                                            class="form-control form-control-sm"
                                            v-model="variant.select_qty"
                                            type="number"
                                            min="1"
                                        />
                                    </td>

                                    <td class="text-center">
                                        <!-- <button
                                            class="btn btn-success"
                                        >
                                            Tạo QR
                                        </button>
 -->

                                        <a
                                            href="javascript:void(0)"
                                            @click="handleRemoveVariant($event, variant, vKey)"
                                            class="text-decoration-none"
                                            style="margin-left:10px"
                                        ><button
                                            class="btn btn-danger"
                                        >
                                            Xóa
                                        </button>

                                        </a>

                                    </td>
                                </tr>
                                </tbody>
                            </table>
                        </div>


                    </div>

                </div>

            </div>
        </div>



    </div>
</template>

<script>
import ProductAction from './partials/ProductActionComponent.vue'
import moment from 'moment';

export default {
    props: {
        products: {
            type: Array,
            default: () => [],
        },
        product_ids: {
            type: Array,
            default: () => [],
        },

        total_amount: {
            type: Number,
            default: () => 0,
        },
        total_amount_label: {
            type: String,
            default: () => '',
        },
        description: {
            type: String,
            default: () => '',
        },
        check_permission: {
            type: Number,
            default: () => 0,
        },
        agency_product:{
             type: Array,
            default: () => [],
        },
        agent_warehouse:{
             type: Array,
            default: () => [],
        },




    },
    data: function () {
        return {
            list_products: {
                data: [],
            },
            hidden_product_search_panel: true,
            loading: false,
            checking: false,
            note: null,

            child_products: this.products,
            // descriptionQrcode: this.description,
            child_product_ids: this.product_ids,

            child_total_amount: this.total_amount,
            child_total_amount_label: this.total_amount_label,

            productSearchRequest: null,
            timeoutProductRequest: null,
            searchEmptyProduct : false,
            checkPermission : this.check_permission,
            agencyProduct : this.agency_product,
            agentWarehouse : this.agent_warehouse,
        }
    },
    components: {
        ProductAction,
    },
    mounted: function () {
        let context = this
        $(document).on('click', 'body', (e) => {
            let container = $('.box-search-advance')

            if (!container.is(e.target) && container.has(e.target).length === 0) {
                context.hidden_customer_search_panel = true
                context.hidden_product_search_panel = true
            }
        })
    },
    methods: {

        loadListProductsAndVariations: function (page = 1, force = false, show_panel = true) {
            let context = this
            if (show_panel) {
                context.hidden_product_search_panel = false
                $('.textbox-advancesearch.product')
                    .closest('.box-search-advance.product')
                    .find('.panel')
                    .addClass('active')
            } else {
                context.hidden_product_search_panel = true
            }

            if (_.isEmpty(context.list_products.data) || force) {
                context.loading = true
                if (context.productSearchRequest) {
                    context.productSearchRequest.abort()
                }

                context.productSearchRequest = new AbortController()

                axios
                    .get(
                        route('products.get-all-products-and-variations', {
                            keyword: context.product_keyword,
                            page: page,
                            product_ids: context.child_product_ids,
                        }),
                        { signal: context.productSearchRequest.signal }
                    )
                    .then((res) => {
                        context.list_products = res.data.data
                        context.loading = false
                    })
                    .catch((error) => {
                        if (!axios.isCancel(error)) {
                            Botble.handleError(error.response.data)
                            context.loading = false
                        }
                    })
            }
        },
        handleSearchProduct: function (value) {
            if (value !== this.product_keyword) {
                let context = this
                context.product_keyword = value
                if (context.timeoutProductRequest) {
                    clearTimeout(context.timeoutProductRequest)
                }

                context.timeoutProductRequest = setTimeout(() => {
                    context.loadListProductsAndVariations(1, true)
                }, 1000)
            }
        },
        selectProductVariant: function (product, refOptions) {
            let context = this
            if (_.isEmpty(product) && product.is_out_of_stock) {
                Botble.showError(context.__('order.cant_select_out_of_stock_product'))
                return false
            }
            const requiredOptions = product.product_options.filter((item) => item.required)

            if (product.is_variation || !product.variations.length) {
                let refAction = context.$refs['product_actions_' + product.original_product_id][0]
                refOptions = refAction.$refs['product_options_' + product.original_product_id]
            }

            let productOptions = refOptions.values

            if (requiredOptions.length) {
                let errorMessage = []
                requiredOptions.forEach((item) => {
                    if (!productOptions[item.id]) {
                        errorMessage.push(context.__('order.please_choose_product_option') + ': ' + item.name)
                    }
                })

                if (errorMessage && errorMessage.length) {
                    errorMessage.forEach((message) => {
                        Botble.showError(message)
                    })
                    return
                }
            }

            let options = []

            product.product_options.map((item) => {
                options[item.id] = {
                    option_type: item.option_type,
                    values: productOptions[item.id],
                }
            })
            product.select_qty = 1
            context.child_products.push(product)

            context.hidden_product_search_panel = true
        },
        getDataProduct: function (data = {}, onSuccess = null, onError = null) {

        },
        validateQuantityInput(event, max){
            if (event.target.value > max) {
            event.target.value = max;
            }
            if(event.target.value < 1){
                event.target.value = 1;
            }
        },


        handleRemoveVariant: function (event, variant, vKey) {
            event.preventDefault()
            this.child_product_ids = this.child_product_ids.filter((item, k) => k !== vKey)
            this.child_products = this.child_products.filter((item, k) => k !== vKey)

        },

        checkEmptyProduct: function(value){
            return !this.child_products.some(childProduct => childProduct.id === value.id);
        },

        message : function(type ,message, title){
            toastr.clear()

            toastr.options = {
                closeButton: true,
                positionClass: 'toast-bottom-right',
                showDuration: 1000,
                hideDuration: 1000,
                timeOut: 60000,
                extendedTimeOut: 1000,
                showEasing: 'swing',
                hideEasing: 'linear',
                showMethod: 'fadeIn',
                hideMethod: 'fadeOut',
            }
            toastr[type](message, title);
        },

        async insertProductAgent() {
            try {
                $('#load-button').attr('style', 'display: block');
                $('#btn-add-qrcode').attr('disabled', 'disabled');
                let filteredChildProducts = this.child_products.map(item => {
                    let agencyProduct = '';
                    if(typeof this.agencyProduct == 'object'){
                        agencyProduct = this.agencyProduct.find(product => product.product_id === item.id);
                    }
                    return {
                        warehouse_id: this.agentWarehouse['id'],
                        product_id: item.id,
                        select_qty: item.select_qty,
                        agent_product_id: agencyProduct ? agencyProduct.id : null,
                        status: 'created',
                        name: item.name,
                        variation_attributes: item.variation_attributes,
                    };
                });
                const response = await axios.post(route('warehouse-finished-products.store-product-manual'),{
                    products: filteredChildProducts,
                });
                window.location.href = route('warehouse-finished-products.detail-odd', this.agentWarehouse['id']);
                $('#load-button').attr('style', 'display: none');
                $('#btn-add-qrcode').removeAttr('disabled');
                this.message('success', 'Thêm sản phẩm thành công', 'Hoành thành');
            } catch (error) {
                console.error(error);
                this.message('warning', error['response']['data']['message'], 'Cảnh báo');
                $('#load-button').attr('style', 'display: none');
                $('#btn-add-qrcode').removeAttr('disabled');
            }
        },

        fillterProductQuantity: function(id){
            if(typeof this.agencyProduct == 'object'){
                const item = this.agencyProduct.find(item => item.product_id === id);

                return item ? item.quantity_not_qrcode : 0;
            }
            return 0
        }



    },
    watch: {

    },
}
</script>
