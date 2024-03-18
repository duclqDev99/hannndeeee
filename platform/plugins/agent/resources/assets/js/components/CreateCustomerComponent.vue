<template>
    <div class="row row-cards">
        <div class="col-md-3">
            <div class="card">
                <div v-if="!child_customer_id || !child_customer">
                    <div class="card-header">
                        <h4 class="card-title">{{ __('Thông tin khách hàng') }}</h4>
                    </div>
                    <div class="card-body">
                        <div class="position-relative box-search-advance customer">
                            <!-- search curtomer -->
                            <input
                                ref="customerSearchInput"
                                type="text"
                                class="form-control textbox-advancesearch customer"
                                @click="loadListCustomersForSearch()"
                                @keyup="handleSearchCustomer($event.target.value)"
                                :placeholder="__('Tìm kiếm sđt hoặc tạo khách hàng mới')"
                            />

                            <div
                                class="card position-absolute w-100 z-1"
                                :class="{ active: customers, hidden: hidden_customer_search_panel }"
                                :style="[loading ? { minHeight: '10rem' } : {}]"
                            >
                                <div v-if="loading" class="loading-spinner"></div>
                                <div v-else class="list-group list-group-flush overflow-auto" style="max-height: 25rem">
                                    <div v-if="this.customer_keyword" class="list-group-item cursor-pointer" @click="openAddCustomerModal">
                                        <div class="row align-items-center">
                                            <div class="col-auto">
                                                <img
                                                    width="28"
                                                    src="/vendor/core/plugins/ecommerce/images/next-create-customer.svg"
                                                    alt="icon"
                                                />
                                            </div>
                                            <div class="col">
                                                <span>{{ __('Tạo khách hàng mới') }}</span>
                                            </div>
                                        </div>
                                    </div>
                                    <a
                                        class="list-group-item list-group-item-action"
                                        href="javascript:void(0)"
                                        v-for="customer in customers.data"
                                        v-bind:key="customer.id"
                                        @click="selectCustomer(customer)"
                                    >
                                        <div class="flexbox-grid-default flexbox-align-items-center">
                                            <div class="row align-items-center">
                                                <div class="col-auto">
                                                    <span class="avatar"
                                                          :style="{ backgroundImage: 'url(' + customer.avatar_url + ')' }"></span>
                                                </div>
                                                <div class="col text-truncate">
                                                    <div class="text-body d-block">{{ customer.name }}</div>
                                                    <div class="text-body d-block">{{ customer.vid }}</div>
                                                    <!-- <a :href="'mailto:' + customer.email"
                                                       class="text-secondary text-truncate mt-n1">{{
                                                            customer.email || '-'
                                                        }}</a> -->
                                                </div>
                                            </div>
                                        </div>
                                    </a>
                                    <div class="list-group-item" v-if="customers.data && customers.data.length === 0">
                                        {{ __('Không tìm thấy khách hàng!') }}
                                    </div>
                                </div>
                                <div class="card-footer"
                                     v-if="(customers.next_page_url || customers.prev_page_url) && !loading">
                                    <ul class="pagination my-0 d-flex justify-content-end">
                                        <li :class="{'page-item': true, disabled: customers.current_page === 1}">
                                            <span v-if="customers.current_page === 1" class="page-link"
                                                  :aria-disabled="customers.current_page === 1">
                                                <i class="icon ti ti-chevron-left"></i>
                                            </span>
                                            <a
                                                v-else
                                                href="javascript:void(0)"
                                                class="page-link"
                                                @click="loadListCustomersForSearch(
                                                    customers.prev_page_url
                                                        ? customers.current_page - 1
                                                        : customers.current_page,
                                                    true
                                                )"
                                            >
                                                <i class="icon ti ti-chevron-left"></i>
                                            </a>
                                        </li>
                                        <li :class="{'page-item': true, disabled: !customers.next_page_url}">
                                            <span v-if="!customers.next_page_url" class="page-link"
                                                  :aria-disabled="!customers.next_page_url">
                                                <i class="icon ti ti-chevron-right"></i>
                                            </span>
                                            <a
                                                v-else
                                                href="javascript:void(0)"
                                                class="page-link"
                                                @click="loadListCustomersForSearch(
                                                    customers.next_page_url
                                                        ? customers.current_page + 1
                                                        : customers.current_page,
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
                    </div>
                </div>
                <div v-if="child_customer_id && child_customer">
                    <div class="card-header">
                        <h4 class="card-title">{{ __('Khách hàng') }}</h4>
                        <div class="card-actions">
                            <button
                                type="button"
                                data-bs-toggle="tooltip"
                                data-placement="top"
                                title="Delete customer"
                                @click="removeCustomer()"
                                class="btn-action"
                            >
                                <span class="icon-tabler-wrapper icon-sm icon-left">
        <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-x" width="24" height="24"
             viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round"
             stroke-linejoin="round">
  <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
  <path d="M18 6l-12 12"/>
  <path d="M6 6l12 12"/>
</svg>
    </span>
                            </button>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="p-3">
                            <div class="mb-3">
                                <span class="avatar avatar-lg avatar-rounded"
                                      :style="[child_customer.avatar_url ? { backgroundImage: 'url(' + child_customer.avatar_url + ')' } : {}]"></span>
                            </div>

                            <div class="mb-1">
                                <i class="icon ti ti-inbox"></i>
                                {{ child_customer_order_numbers }}
                                {{ __('Đơn hàng') }}
                            </div>
                            <div class="d-none" id="customer_id" :data-id="child_customer.id"></div>
                            <div class="mb-n1">{{ child_customer.name }}</div>
                            <div class="mb-n1">{{ child_customer.vid }}</div>

                            <div class="d-flex justify-content-between align-items-center">
                                <a :href="'mailto:' + child_customer.email">
                                    {{ child_customer.email || '-' }}
                                </a>

                                <a
                                    href="javascript:void(0)"
                                    v-ec-modal.edit-email
                                    data-placement="top"
                                    data-bs-toggle="tooltip"
                                    data-bs-original-title="Edit email"
                                    class="btn-action text-decoration-none"
                                >
                                    <i class="icon ti ti-pencil"/>
                                </a>
                            </div>
                        </div>

                        <div class="hr my-1"></div>
                    </div>
                </div>
            </div>
        </div>

        <OrderCustomerAddress
            :child_customer_address="child_customer_address"
            :phone_customer="phone_customer"
            :name_customer="name_customer"
            :vid_customer="vid_customer"
            :zip_code_enabled="zip_code_enabled"
            :use_location_data="use_location_data"
            @update-order-address="updateOrderAddress"
            @update-customer-email="updateCustomerEmail"
            @create-new-customer="createNewCustomer"
        ></OrderCustomerAddress>
    </div>
</template>

<script>
import ProductAction from './partials/ProductActionComponent.vue'
import OrderCustomerAddress from './partials/OrderCustomerAddressComponent.vue'
import AddProductModal from './partials/AddProductModalComponent.vue'
import BaseProductScan from "../../../../../qr-scan/resources/assets/js/base-product-scan.js";
import Botble from "../../../../../sales/resources/assets/js/utils";

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
        customer_id: {
            type: Number,
            default: () => null,
        },
        customer: {
            type: Object,
            default: () => {},
        },
        customer_addresses: {
            type: Array,
            default: () => [],
        },
        customer_address: {
            type: Object,
            default: () => ({
                name: null,
                email: null,
                address: null,
                phone: null,
                country: null,
                state: null,
                city: null,
                zip_code: null,
            }),
        },
        customer_order_numbers: {
            type: Number,
            default: () => 0,
        },
        sub_amount: {
            type: Number,
            default: () => 0,
        },
        sub_amount_label: {
            type: String,
            default: () => '',
        },
        tax_amount: {
            type: Number,
            default: () => 0,
        },
        tax_amount_label: {
            type: String,
            default: () => '',
        },
        total_amount: {
            type: Number,
            default: () => 0,
        },
        total_amount_label: {
            type: String,
            default: () => '',
        },
        coupon_code: {
            type: String,
            default: () => '',
        },
        promotion_amount: {
            type: Number,
            default: () => 0,
        },
        promotion_amount_label: {
            type: String,
            default: () => '',
        },
        discount_amount: {
            type: Number,
            default: () => 0,
        },
        discount_amount_label: {
            type: String,
            default: () => '',
        },
        discount_description: {
            type: String,
            default: () => null,
        },
        shipping_amount: {
            type: Number,
            default: () => 0,
        },
        shipping_amount_label: {
            type: String,
            default: () => '',
        },
        shipping_method: {
            type: String,
            default: () => 'default',
        },
        shipping_option: {
            type: String,
            default: () => '',
        },
        is_selected_shipping: {
            type: Boolean,
            default: () => false,
        },
        shipping_method_name: {
            type: String,
            default: function () {
                return 'order.free_shipping'
            },
        },
        payment_method: {
            type: String,
            default: () => 'bank_transfer',
        },
        currency: {
            type: String,
            default: () => null,
            required: true,
        },
        zip_code_enabled: {
            type: Number,
            default: () => 0,
            required: true,
        },
        use_location_data: {
            type: Number,
            default: () => 0,
        },
        is_tax_enabled: {
            type: Number,
            default: () => true,
        },
        agent_id: {
            type: Number,
            default: () => true,
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
            customers: {
                data: [],
            },
            hidden_customer_search_panel: true,
            customer_keyword: null,
            shipping_type: 'free-shipping',
            shipping_methods: {},
            discount_type_unit: this.currency,
            discount_type: 'amount',
            child_discount_description: this.discount_description,
            has_invalid_coupon: false,
            has_applied_discount: this.discount_amount > 0,
            discount_custom_value: 0,
            child_coupon_code: this.coupon_code,
            child_customer: this.customer,
            child_customer_id: this.customer_id,
            child_customer_order_numbers: this.customer_order_numbers,
            child_customer_addresses: this.customer_addresses,
            child_customer_address: this.customer_address,
            child_products: this.products,
            child_product_ids: this.product_ids,
            child_sub_amount: this.sub_amount,
            child_sub_amount_label: this.sub_amount_label,
            child_tax_amount: this.tax_amount,
            child_tax_amount_label: this.tax_amount_label,
            child_total_amount: this.total_amount,
            child_total_amount_label: this.total_amount_label,
            child_promotion_amount: this.promotion_amount,
            child_promotion_amount_label: this.promotion_amount_label,
            child_discount_amount: this.discount_amount,
            child_discount_amount_label: this.discount_amount_label,
            child_shipping_amount: this.shipping_amount,
            child_shipping_amount_label: this.shipping_amount_label,
            child_shipping_method: this.shipping_method,
            child_shipping_option: this.shipping_option,
            child_shipping_method_name: this.shipping_method_name,
            child_is_selected_shipping: this.is_selected_shipping,
            child_payment_method: this.payment_method,
            agent_id: this.agent_id,
            productSearchRequest: null,
            timeoutProductRequest: null,
            customerSearchRequest: null,
            checkDataOrderRequest: null,
            store: {
                id: 0,
                name: null,
            },
            is_available_shipping: false,
            listShowroom: [],
            showroom_id: null,
            scan: null,
            qr_pro_id: {},
            phone_customer: null,
            name_customer: null,
            vid_customer: null,
            channel_broadcast: new BroadcastChannel('order-customer-channel'),
            has_refund_point: true,

        }
    },
    components: {
        ProductAction,
        OrderCustomerAddress,
        AddProductModal,
    },
    mounted() {
        let dataBroadcast = {
            'create' : true,
        }
        this.channel_broadcast.postMessage(dataBroadcast)
    },
    methods: {
        openAddCustomerModal: function() {
            // Thực hiện cuộc gọi API tại đây
            let modalCustomer = document.querySelector('#add-customer')

            $event.emit('ec-modal:open', 'add-customer')
            window.insertSvgLoading(modalCustomer)
            let loaderFrame = $(modalCustomer).find('.loader-container')

            axios.get(route('showroom.customers.check-user-register-app',{
                    phone_customer: this.phone_customer
                }))
                .then(response => {
                    let data = response.data;
                    if(data.error_code == 0){
                        this.child_customer_address.phone = this.phone_customer;
                        this.name_customer = data.data.fullname;
                        this.child_customer_address.name = data.data.fullname;
                        this.vid_customer = data.data.vga;
                        this.child_customer_address.vid = data.data.vga;
                        if (loaderFrame) {
                            loaderFrame.remove();
                        }
                    }
                    else{
                        if (loaderFrame) {
                            loaderFrame.remove();
                        }
                        $event.emit('ec-modal:close', 'add-customer')
                        Botble.showError(data.msg)
                    }
                    // Xử lý phản hồi
                })
                .catch(error => {
                    if (loaderFrame) {
                            loaderFrame.remove();
                        }
                    $event.emit('ec-modal:close', 'add-customer')
                    Botble.showError(error.response.data.message)
                });

        },

        loadListCustomersForSearch: function (page = 1, force = false) {
            let context = this
            context.hidden_customer_search_panel = false
            $('.textbox-advancesearch.customer')
                .closest('.box-search-advance.customer')
                .find('.panel')
                .addClass('active')
            if (_.isEmpty(context.customers.data) || force) {
                context.loading = true
                if (context.customerSearchRequest) {
                    context.customerSearchRequest.abort()
                }

                context.customerSearchRequest = new AbortController()

                axios
                    .get(
                        route('showroom.customers.get-list-customers-for-search', {
                            keyword: context.customer_keyword,
                            page: page,
                        }),
                        {signal: context.customerSearchRequest.signal}
                    )
                    .then((res) => {
                        context.customers = res.data.data
                        context.loading = false
                    })
                    .catch((error) => {
                        if (!axios.isCancel(error)) {
                            context.loading = false
                            Botble.handleError(error.response.data)
                        }
                    })
            }
        },
        handleSearchCustomer: _.debounce(function (value) {
            if (value !== this.customer_keyword) {
                let context = this
                this.customer_keyword = value
                this.phone_customer = value
                context.loadListCustomersForSearch(1, true)
            }
        },500),

        selectCustomer: function (customer) {
            this.child_customer = customer
            this.child_customer_id = customer.id

            // this.loadCustomerAddress(this.child_customer_id)

            this.getOrderNumbers()
        },
        getOrderFormData: function () {
            let products = []
            _.each(this.child_products, function (item) {
                products.push({
                    id: item.id,
                    quantity: item.select_qty,
                    options: item.options,
                })
            })

            return {
                products,
                payment_method: this.child_payment_method,
                shipping_method: this.child_shipping_method,
                shipping_option: this.child_shipping_option,
                shipping_amount: this.child_shipping_amount,
                discount_amount: this.child_discount_amount,
                discount_description: this.child_discount_description,
                coupon_code: this.child_coupon_code,
                customer_id: this.child_customer_id,
                note: this.note,
                sub_amount: this.child_sub_amount,
                tax_amount: this.child_tax_amount,
                amount: this.child_total_amount,
                customer_address: this.child_customer_address,
                discount_type: this.discount_type,
                discount_custom_value: this.discount_custom_value,
                shipping_type: this.shipping_type,
                showroom_id: this.showroom_id,
                customer: this.child_customer,
                has_refund_point: this.has_refund_point,
            }
        },
        removeCustomer: function () {
            this.child_customer = []
            this.child_customer_id = null
            this.child_customer_addresses = []
            this.child_customer_address = {
                name: null,
                email: null,
                address: null,
                phone: null,
                country: null,
                state: null,
                city: null,
                zip_code: null,
                full_address: null,
            }
            this.child_customer_order_numbers = 0

        },
        handleRemoveVariant: function (event, variant, vKey) {
            event.preventDefault()
            let id_remote = this.child_product_ids.find((item, k) => k === vKey)
            this.child_product_ids = this.child_product_ids.filter((item, k) => k !== vKey)
            this.child_products = this.child_products.filter((item, k) => k !== vKey)
            this.removeQrCode(id_remote);
        },

        updateCustomerEmail: function (event) {
            event.preventDefault()
            $(event.target).addClass('button-loading')

            let context = this

            axios
                .post(route('customers.update-email', context.child_customer_address.id), {
                    email: context.child_customer_address.email,
                })
                .then((res) => {
                    if (res.data.error) {
                        Botble.showError(res.data.message)
                    } else {
                        Botble.showSuccess(res.data.message)

                        $event.emit('ec-modal:close', 'edit-email')
                    }
                })
                .catch((res) => {
                    // Botble.handleError(res.response.data)
                })
                .then(() => {
                    $(event.target).removeClass('button-loading')
                })
        },
        updateOrderAddress: function (event) {
            event.preventDefault()
            if (this.customer) {
                $(event.target).addClass('button-loading')
            }
        },
        createNewCustomer: function (event) {
            event.preventDefault()
            let context = this

            $(event.target).addClass('button-loading')
            axios
                .post(route('agent.customers.create.store'), {

                    name: context.child_customer_address.name,
                    phone: context.child_customer_address.phone,
                    vid: context.child_customer_address.vid,
                    agent_id: context.agent_id,
                })
                .then((res) => {
                    if (res.data.error) {
                        Botble.showError(res.data.message)
                    } else {
                        context.child_customer = res.data.data.customer
                        context.child_customer_id = context.child_customer.id

                        context.customers = {
                            data: [],
                        }

                        Botble.showSuccess(res.data.message)

                        $event.emit('ec-modal:close', 'add-customer')
                    }
                })
                .catch((res) => {
                    // Botble.showError(res.response.data.message);
                })
                .then(() => {
                    $(event.target).removeClass('button-loading')
                })
        },
        selectCustomerAddress: function (event) {
            let context = this
            _.each(this.child_customer_addresses, (item) => {
                if (parseInt(item.id) === parseInt(event.target.value)) {
                    context.child_customer_address = item
                }
            })

        },
        getOrderNumbers: function () {
            let context = this
            axios
                .get(route('showroom.customers.get-customer-order-numbers', context.child_customer_id))
                .then((res) => {
                    let data = res.data.data
                    context.child_customer.name = data.customer.name
                    context.child_customer.vid = data.customer.vid
                    context.child_customer_order_numbers = data.order_numbers
                })
                .catch((res) => {
                    // Botble.handleError(res.response.data)
                })
        },
        loadCustomerAddress: function () {
            let context = this
            axios
                .get(route('customers.get-customer-addresses', context.child_customer_id))
                .then((res) => {
                    context.child_customer_addresses = res.data.data
                    if (!_.isEmpty(context.child_customer_addresses)) {
                        context.child_customer_address = _.first(context.child_customer_addresses)
                    }
                })
                .catch((res) => {
                    // Botble.handleError(res.response.data)
                })
        },



        handleClickOutside: function (event) {
            const productInputElement = this.$refs.productSearchInput;
            const customerInputElement = this.$refs.customerSearchInput;

            if (productInputElement && !productInputElement.contains(event.target)) {
                this.hidden_product_search_panel = true; // Tắt popup sản phẩm
            }

            if (customerInputElement && !customerInputElement.contains(event.target)) {
                this.hidden_customer_search_panel = true; // Tắt popup khách hàng
            }
        },
        sendDataToCustomerView(){
            let data = {
                'create' : false,
                'child_products' : JSON.parse(JSON.stringify(this.child_products)),
                'child_sub_amount_label' : this.child_sub_amount_label,
                'child_tax_amount_label' : this.child_tax_amount_label,
                'child_discount_amount_label' : this.child_discount_amount_label,
                'child_promotion_amount_label' : this.child_promotion_amount_label,
                'child_total_amount_label' : this.child_total_amount_label,
                    }
            this.channel_broadcast.postMessage(data)
        }
    },
    watch: {
        child_products :function (){
            this.sendDataToCustomerView()
        }
    },
    // beforeDestroy() {
    //     document.removeEventListener('click', this.handleClickOutside);
    // },
}
</script>
