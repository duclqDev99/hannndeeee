<template>
    <div class="row justify-content-between">
        <div class="col-lg-6 col-md-12 col-12">
            <div class="title">
                <h2 v-if="is_create">Tạo đơn đổi trả hàng</h2>
                <h2 v-else>Chi tiết đơn đổi trả hàng</h2>
            </div>
        </div>
        <div class="col-lg-6 col-md-12 col-12">
            <div class="group-btn text-end">
                <div class="d-flex justify-content-end align-item-center" style="gap: 10px;">
                    <select 
                        v-model="child_showroom_selected" 
                        class="form-control" 
                        id="showroom_id"
                        @change="selectShowroom"
                        style="max-width: 300px;"
                        :disabled="is_create ? null : 'disabled'"
                        >
                        <option
                            v-for="item in showrooms"
                            :value="item.id"
                            >{{ item.name }}</option>
                    </select>
                    <a
                        href="javascript:void(0)"
                        v-ec-modal.make_send_exchange
                        data-placement="top"
                        data-bs-toggle="tooltip"
                        data-bs-original-title="Edit email"
                        class="btn btn-primary"
                        v-if="is_create"
                    >
                        <i class="fa fa-arrows-rotate me-2"></i> Xác nhận đổi hàng
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-3">
        <div class="col-lg-6 col-md-6 col-sm-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="title">Sản phẩm trao đổi</h3>
                </div>
                <div class="card-body">
                    <div class="list__product row-header mb-3">
                        <div class="item__product header__product">
                            <div class="wrap__img">
                                Hình ảnh
                            </div>

                            <div class="content__product">
                                Thông tin SP
                            </div>

                            <div class="statused">
                                Trạng thái
                            </div>

                            <div class="created__at">Ngày sản xuất</div>

                            <div class="created__at">Địa điểm</div>
                        </div>
                    </div>
                    <div class="list__product pay">
                        <div 
                        v-for="(item,vKey) in child_qrs_pay"
                        :key="vKey"
                        :data-id="item.id"
                        class="item__product sortable-item">
                            <div class="wrap__img">
                                <img :src="`/storage/${item.reference?.images[0]}`" :alt="item.reference?.name" width="100">
                            </div>

                            <div class="content__product">
                                <div class="name"><a :href="this.getRoute('products.edit', item.reference?.parent_product[0].id)"><strong>{{ item.reference?.name }}</strong></a> <br> {{ item.time_create_q_r?.variation_attributes }}</div>
                                <div class="sku">SKU: <strong>{{ item.reference?.sku }}</strong></div>
                                <div class="sku">Giá: {{ this.formatCurrencyPrice(item.reference?.price) }}</div>
                            </div>

                            <div class="statused">
                                <span class="badge badge-danger mx-auto">{{ __('Trong kho') }}</span>
                            </div>

                            <div class="created__at">{{ this.formattedDate(item.production_time) ?? '-------' }}</div>

                            <div class="location">{{ item.warehouse?.showroom?.name }}</div>

                            <div class="btn__remove">
                                <button v-if="is_create" type="button" @click="this.hanldeRemoveProduct($event, item.id, 'pay')" class="btn btn-danger"><i class="fa fa-close"></i></button>
                            </div>
                        </div>

                        <div v-if="child_qrs_pay.length === 0">
                            <div class="widget__filtered">Vui lòng quét QR sản phẩm!!</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-6 col-md-6 col-sm-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="title">Sản phẩm nhận từ khách hàng</h3>
                </div>
                <div class="card-body">
                    <div class="list__product row-header mb-3">
                        <div class="item__product header__product">
                            <div class="wrap__img">
                                Hình ảnh
                            </div>

                            <div class="content__product">
                                Thông tin SP
                            </div>

                            <div class="statused">
                                Trạng thái
                            </div>

                            <div class="created__at">Ngày sản xuất</div>

                            <div class="location">Địa điểm</div>
                        </div>
                    </div>
                    <div class="list__product exchange">
                        <div 
                        v-for="(item,vKey) in child_qrs_exchange"
                        :key="vKey"
                        :data-id="item.id"
                        class="item__product">
                            <div class="wrap__img">
                                <img :src="`/storage/${item.reference?.images[0]}`" :alt="item.reference?.name" width="100">
                            </div>

                            <div class="content__product">
                                <div class="name"><a :href="this.getRoute('products.edit', item.reference?.parent_product[0].id)"><strong>{{ item.reference?.name }}</strong></a> <br> {{ item.time_create_q_r?.variation_attributes }}</div>
                                <div class="sku">SKU: <strong>{{ item.reference?.sku }}</strong></div>
                                <div class="sku">Giá: {{ this.formatCurrencyPrice(item.reference?.price) }}</div>
                            </div>

                            <div class="statused">
                                <span class="badge badge-danger mx-auto">{{ __('Đã bán') }}</span>
                            </div>

                            <div class="created__at">{{ this.formattedDate(item.production_time) ?? '-------' }}</div>

                            <div class="location">{{ item.warehouse?.showroom?.name ?? '-------' }}</div>

                            <div class="btn__remove">
                                <button type="button" v-if="is_create" @click="this.hanldeRemoveProduct($event, item.id, 'exchange')" class="btn btn-danger"><i class="fa fa-close"></i></button>
                            </div>
                        </div>

                        <div v-if="child_qrs_exchange.length === 0">
                            <div class="widget__filtered">Vui lòng quét QR sản phẩm!!</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div v-if="loading" class="loading-spinner"></div>

    <ec-modal
        id="make_send_exchange"
        title="Xác nhận đổi trả hàng hoá"
        ok-title="Xác nhận"
        :cancel-title="__('Đóng')" 
        @ok="createExchangeGoods($event)"
    >
        <div>
            <h3 class="title">{{ __('Bạn có chắc chắn tạo phiếu đổi trả sản phẩm?') }}</h3>
            <div class="form-group">
                <label for="description">Ghi chú:</label>
                <textarea v-model="child_description" id="description" class="form-control" rows="4"></textarea>
            </div>
        </div>
    </ec-modal>
</template>

<script>

import Scanner from '../scanner.js';
import moment from 'moment';


export default {
    props: {
        list_qrcode: {
            type: Array,
            default: () => []
        },
        showrooms: {
            type: Array,
            default: () => []
        },
        qrs_exchange: {
            type: Array,
            default: () => []
        },
        qrs_pay: {
            type: Array,
            default: () => []
        },
        showroom_selected: {
            type: Number,
            default: () => null
        },
        current_showroom: {
            type: Object,
            default: () => {}
        },
        is_create: {
            type: Boolean,
            default: true
        }
    },
    data: function() {
        return {
            child_list_qrcode: this.list_qrcode,
            loading: false,
            child_showrooms: this.showrooms,
            child_showroom_selected: this.showroom_selected,
            // child_current_showroom: this.current_showroom,
            child_qrs_exchange: this.qrs_exchange,
            child_qrs_pay: this.qrs_pay,
            child_description: null,
        }
    },
    mounted: function() {
        // Sortable.mount( new Swap());
        //Kiểm tra trang hiện tại là trong tạo đơn đổi trả
        if(this.is_create){
            const scan = new Scanner();
            scan.onScan(code => {
                this.handleScanProduct(code);
            });

            if(this.child_showrooms.length > 0){
                this.child_showroom_selected = this.child_showrooms[0].id
            }

            //Tạo sortable cho hàng trả
            this.waitForElementToExist('.list__product.pay').then(() => {
                Sortable.create(document.querySelector(".list__product.pay"),  {
                    delay: 0, // time in milliseconds to define when the sorting should start
                    disabled: false, // Disables the sortable if set to true.
                    store: null, // @see Store
                    swap: true, // Enable swap plugin
                    swapClass: "highlight",
                    animation: 150, // ms, animation speed moving items when sorting, `0` — without animation
                    handle: '.item__product',
                    ghostClass: 'sortable-ghost', // Class name for the drop placeholder
                    chosenClass: 'sortable-chosen', // Class name for the chosen item
                    dataIdAttr: 'data-id',
        
                    forceFallback: false, // ignore the HTML5 DnD behaviour and force the fallback to kick in
                    fallbackClass: 'sortable-fallback', // Class name for the cloned DOM Element when using forceFallback
                    fallbackOnBody: false, // Appends the cloned DOM Element into the Document's Body
        
                    scroll: true, // or HTMLElement
                    scrollSensitivity: 30, // px, how near the mouse must be to an edge to start scrolling.
                    scrollSpeed: 10, // px
        
                    // Changed sorting within list
                    onEnd: function (evt) {
                        // Xử lý khi kéo và thả hoàn tất
                    },
                    onUpdate: (evt) => {
                        const listItem = document.querySelectorAll(".list__product.pay .item__product")
    
                        let list_pay_change = []//Tạo mảng rỗng để lưu các giá trị mới các qr sản phẩm trả
                        
                        listItem?.forEach(product => {
                            let foundItem = this.child_qrs_pay.find(item => item.id == product.dataset.id);
                            if(typeof foundItem !== 'undefined') list_pay_change.push(foundItem)
                        })
    
                        //Cập nhật lại thứ tự qr sản phẩm theo cặp đổi trả
                        this.updateDataSubmitWhenPayChange(list_pay_change)
    
                        //Reset liên kết các cặp sp trên view
                        this.clearViewItemProduct()
                        //Cập nhật lại view khi update QR
                        this.updateViewOnScan()
                    },
                    onAdd: (evt) => {
                    },
                })
            })
    
            //Tạo sortable cho hàng đổi
            this.waitForElementToExist('.list__product.exchange').then(() => {
                Sortable.create(document.querySelector(".list__product.exchange"),  {
                    delay: 0, // time in milliseconds to define when the sorting should start
                    disabled: false, // Disables the sortable if set to true.
                    store: null, // @see Store
                    swap: true, // Cho phép swap giữa from và to
                    animation: 150, // ms, animation speed moving items when sorting, `0` — without animation
                    handle: '.item__product',
                    ghostClass: 'sortable-ghost', // Class name for the drop placeholder
                    chosenClass: 'sortable-chosen', // Class name for the chosen item
                    // dataIdAttr: 'data-id',
        
                    forceFallback: false, // ignore the HTML5 DnD behaviour and force the fallback to kick in
                    fallbackClass: 'sortable-fallback', // Class name for the cloned DOM Element when using forceFallback
                    fallbackOnBody: false, // Appends the cloned DOM Element into the Document's Body
        
                    scroll: true, // or HTMLElement
                    scrollSensitivity: 30, // px, how near the mouse must be to an edge to start scrolling.
                    scrollSpeed: 10, // px
                    swap: true,
                    // Changed sorting within list
                    onEnd: function (evt) {
                        // Xử lý khi kéo và thả hoàn tất
                    },
                    // Changed sorting within list
                    onUpdate: (evt) => {
                        const listItem = document.querySelectorAll(".list__product.exchange .item__product")
    
                        let list_exchange_change = []//Tạo mảng rỗng để lưu các giá trị mới các qr sản phẩm trả
                        
                        listItem?.forEach(product => {
                            let foundItem = this.child_qrs_exchange.find(item => item.id == product.dataset.id);
                            if(typeof foundItem !== 'undefined') list_exchange_change.push(foundItem)
                        })
    
                        //Cập nhật lại thứ tự qr sản phẩm theo cặp đổi trả
                        this.updateDataSubmitWhenExchangeChange(list_exchange_change)
    
                        //Reset liên kết các cặp sp trên view
                        this.clearViewItemProduct()
                        //Cập nhật lại view khi update QR
                        this.updateViewOnScan()
                    },
                    onAdd: (evt) => {
                    },
                })
            })
        }else{
            //Cập nhật lại view khi update QR
            this.updateViewOnScan()
        }

    },
    updated() {
        //Cập nhật lại view khi update QR
        this.updateViewOnScan()
    },
    methods: {
        handleScanProduct: function(qrcode) {
            let context = this

            context.loading = true
            axios.post(
                route('exchange-goods.scan-product'),
                {
                    showroom_id: context.child_showroom_selected,
                    qr_code: qrcode,
                }
            ).then((res) => {
                if(res.data.success === 0){
                    context.loading = false
                    return this.message('error', res.data.message, 'Thất bại');
                }

                let check = true

                console.log(this.child_list_qrcode);

                if(this.child_list_qrcode.length > 0){
                    this.child_list_qrcode.forEach(element => {
                        for (const key in element) {
                            if (Object.hasOwnProperty.call(element, key)) {
                                const el = element[key];
                                if(el !== null && typeof el !== 'undefined'){
                                    if(el.id === res.data.data.id) return check = false
                                }
                            }
                        }
                    });

                }
                if(check){
                    //Kiểm tra status của QR được quét
                    if(res.data.data.status.value == 'sold'){
                        //Là hàng đổi của khách nếu status là đã bán: sold
                        let arr = res.data.data

                        if(typeof this.child_list_qrcode[this.getCountKeyInArray(this.child_list_qrcode, 'exchange')] !== 'undefined')
                        {
                            this.child_list_qrcode[this.getCountKeyInArray(this.child_list_qrcode, 'exchange')]['exchange'] = arr
                        }else{
                            this.child_list_qrcode[this.getCountKeyInArray(this.child_list_qrcode, 'exchange')] = []
                            this.child_list_qrcode[this.getCountKeyInArray(this.child_list_qrcode, 'exchange')]['exchange'] = arr
                        }

                        //Thêm QR trên vào danh sách đổi
                        this.child_qrs_exchange.push(arr)

                        this.message('success', 'Thêm sản phẩm đổi thành công', 'Hoàn thành');
                    }else if(res.data.data.status.value == 'instock'){
                        //Là hàng đổi cho khách nếu status là trong kho: instock
                        let arr = res.data.data

                        //Kiểm tra item mới đã tồn lại sản phẩm đổi/trả nào chưa?
                        //Nếu đã tồn tại
                        if(typeof this.child_list_qrcode[this.getCountKeyInArray(this.child_list_qrcode, 'pay')] !== 'undefined')
                        {
                            //Add sản phẩm đó vào mảng
                            this.child_list_qrcode[this.getCountKeyInArray(this.child_list_qrcode, 'pay')]['pay'] = arr
                        }else{
                            //Không thì tạo kiểu dữ liệu cho key và gán giá trị
                            this.child_list_qrcode[this.getCountKeyInArray(this.child_list_qrcode, 'pay')] = []
                            this.child_list_qrcode[this.getCountKeyInArray(this.child_list_qrcode, 'pay')]['pay'] = arr
                        }

                        //Thêm QR trên vào danh sách trả
                        this.child_qrs_pay.push(arr)

                        this.message('success', 'Thêm sản phẩm trả thành công', 'Hoàn thành');
                    }else{
                        this.message('error', 'Sản phẩm không thuộc loại đã bán hoặc trong kho', 'Thất bại');
                    }

                } else{
                    this.message('error', 'Sản phẩm đã tồn tại', 'Thất bại');
                }

                context.loading = false
            }) .catch((error) => {
                this.message('error', error.message, 'Thất bại');
                console.log(error);
                context.loading = false
            })
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
        selectShowroom: function(event) {//Hàm cập nhật showroom đổi/trả hàng
            // this.child_showrooms.forEach(element => {
            //     if(element.id == event.target.value){
            //         this.child_current_showroom = element
            //     }
            // });

            //Clear all data
            this.clearAllData()

            return this.child_showroom_selected = event.target.value
        },
        updateViewOnScan: function(){
            //Kiểm tra 2 mảng qr đổi/trả xem mảng nào có độ dài bé hơn thì gán cho biến arrGeneral
            let arrGeneral = this.child_qrs_exchange.length > this.child_qrs_pay.length ? this.child_qrs_pay : this.child_qrs_exchange

            for (let i = 0; i < arrGeneral.length; i++) {
                //Tiến hành add các class css để update view
                const elExchange = document.querySelectorAll('.list__product.exchange .item__product');
                const elPay = document.querySelectorAll('.list__product.pay .item__product');
                
                if(elExchange.length > 0)
                {
                    elExchange[i].classList.add('linked')
                    elExchange[i].classList.add('linked-right')
                }

                if(elPay.length > 0)
                {
                    elPay[i].classList.add('linked')
                    elPay[i].classList.add('linked-left')
                }
            }
        },
        hanldeRemoveProduct: function(event, id, key){
            //Kiểm tra đầu vào là sản phẩm đổi hay sp trả
            if(key == 'exchange')
            {
                this.child_qrs_exchange = this.child_qrs_exchange.filter(item => item.id !== id)//Xoá sp có id chỉ định
            }else if(key == 'pay')
            {
                this.child_qrs_pay = this.child_qrs_pay.filter(item => item.id !== id)//Xoá sp có id chỉ định
            }

            this.updateDataSubmitWhenRemove(id)//Cập nhật lại dữ liệu ds sp để gửi đi
        },
        updateDataSubmitWhenRemove: function(id){
            // Sử dụng phương thức map() để duyệt qua mỗi phần tử của mảng và filter() để xóa phần tử có id cần xóa
            this.child_list_qrcode = this.child_list_qrcode.map(item => ({
                exchange: item.exchange?.id !== id ? item.exchange : null,
                pay: item.pay?.id !== id ? item.pay : null
            }))

            this.clearViewItemProduct()
            //Cập nhật lại view khi update QR
            this.updateViewOnScan()
        },
        updateDataSubmitWhenPayChange: function(arr) {
            //Cập nhật danh sách tất qr đổi trả theo cặp
            this.child_list_qrcode?.forEach((item, index) => {
                item.pay = arr[index]
            });
        },
        updateDataSubmitWhenExchangeChange: function(arr) {
            //Cập nhật danh sách tất qr đổi trả theo cặp
            this.child_list_qrcode?.forEach((item, index) => {
                item.exchange = arr[index]
            });
        },
        getCountKeyInArray: function(arr, key){//Hàm đếm số lượng key xuất hiện trong 1 mảng
            let count = 0;
            
            for (let i = 0; i < arr.length; i++) {
                if (arr[i].hasOwnProperty(key)) {
                    count++;
                }
            }

            return count
        },
        updateListQRCode: function(arr){
            arr.forEach(function(item) {
                // Thêm giá trị của khóa 'exchange' vào mảng exchangeArray
                this.child_qrs_exchange.push(item.exchange);

                // Thêm giá trị của khóa 'pay' vào mảng payArray
                this.child_qrs_exchange.push(item.pay);
            });
        },
        createExchangeGoods: function(event){
            event.preventDefault()
            $('.create-order-button').prop("disabled", true);
            $(event.target).addClass('button-loading')

            let formData = this.getExchangeFormData()

            axios
                .post(route('exchange-goods.create.store'), formData)
                .then((res) => {
                    console.log(res);
                    if(res.data.error){
                        Botble.handleError(res.data.message)
                    }else{
                        Botble.showSuccess(res.data.message)
                        $event.emit('ec-modal:close', 'make_send_exchange')
    
                        setTimeout(() => {
                            window.location.href = route('exchange-goods.view', res.data.data)
                        }, 1000)
                    }
                })
                .catch((res) => {
                    Botble.handleError(res.data.message)
                    $('.create-order-button').attr('disabled', false);
                    $('.create-order-button').removeClass('button-loading');
                })
                .then(() => {
                    $('.create-order-button').attr('disabled', false);
                    $('.create-order-button').removeClass('button-loading');
                })
        },
        getExchangeFormData: function () {
            let list_qrcode = new Array()

            _.each(this.child_list_qrcode, function (item) {
                list_qrcode.push({
                    pay: item.pay,
                    exchange: item.exchange,
                })
            })

            return {
                showroom_id: this.child_showroom_selected,
                list_qrcode,
                description: this.child_description
            }
        },
        formatCurrencyPrice: function(price) {//Hàm format chữ số sang tiền việt
            try {
                const formatted = price.toLocaleString('vi-VN', {
                    style: 'currency',
                    currency: 'VND'
                });
                return formatted;
            } catch (error) {
                console.log(error);
            }
        },
        waitForElementToExist: function(selector) {
            return new Promise(resolve => {
                if (document.querySelector(selector)) {
                return resolve(document.querySelector(selector));
                }

                const observer = new MutationObserver(() => {
                if (document.querySelector(selector)) {
                    resolve(document.querySelector(selector));
                    observer.disconnect();
                }
                });

                observer.observe(document.body, {
                    subtree: true,
                    childList: true,
                });
            });
        },
        clearViewItemProduct: function() {
            const elExchange = document.querySelectorAll('.list__product.exchange .item__product');
            const elPay = document.querySelectorAll('.list__product.pay .item__product');

            elExchange?.forEach(item => {
                item.classList.remove('linked')
                item.classList.remove('linked-right')
            })

            elPay?.forEach(item => {
                item.classList.remove('linked')
                item.classList.remove('linked-left')
            })
        },
        clearAllData: function() {
            this.child_list_qrcode = []
            this.child_qrs_exchange = []
            this.child_qrs_pay = []
        },
        getRoute: function(context, param) {
            return `${route(context, param)}`
        },
        formattedDate: function(date) {
            return moment(date).format('DD/MM/YYYY');
        }
    }
}

</script>