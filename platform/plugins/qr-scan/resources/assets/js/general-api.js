import printJS from 'print-js';
import moment from 'moment';

//Xử lý thông tin của lô khi tạo và in thành công lô
export function showListBatchExist(batch){
    //Tạo chi tiết sản phẩm có trong lô
    let txtBatchDetail = ``;
    let newArray = Object.keys(batch.product_in_batch).map(key => {
        return { key: parseInt(key), value: batch.product_in_batch[key] };
    });

    let arrIdPro = [];
    newArray.forEach(product => {
        for(const product_id in receiptDetail){
            if(product_id == product.value.product_id){
                if (arrIdPro.indexOf(product_id) === -1) {
                    txtBatchDetail += `
                    <div class="item d-flex justify-content-between">
                        <div class="name">
                            Tên: ${product.value.product_name}, màu: ${receiptDetail[product_id][0].color}, size: ${receiptDetail[product_id][0].size}
                        </div>
                        <div class="sku">
                            SKU: ${product.value.sku}
                        </div>
                    </div>
                    `;

                    arrIdPro.push(product_id);
                }
            }
        }
    })

    //Tạo thông tin cho lô được tạo
    let textEl = `
    <div class="card-item py-2">
        <a class="w-100 collapsed" data-bs-toggle="collapse" href="#collapse-item-${batch.id}" role="button" aria-expanded="false" aria-controls="collapse-item-${batch.id}">
            <div class="d-flex justify-content-between">
                <div>Mã lô: <strong>${batch.batch_code}</strong></div>
                <div>Tổng số lượng trong lô: <strong>${batch.quantity}</strong></div>
            </div>
        </a>
        <div class="collapse" id="collapse-item-${batch.id}">
            <div class="card-body border">
                ${txtBatchDetail}
            </div>
        </div>
    </div>
    `;

    const mainBatchExits = document.querySelector('#collapseBatch .card-body');
    if(mainBatchExits){
        mainBatchExits.insertAdjacentHTML('afterbegin', textEl);
        $('#btn_cancel_receipt').remove();
        // $('#submit_btn').removeClass('d-none');
        // $('#submit_btn').addClass('d-inline');
    }
}
//Xử lý thông tin của lô khi tạo và in thành công lô
export function showListProductExist(product){
    //Tạo thông tin cho lô được tạo
    let textEl = `
    <div class="card-item py-2">
        <a class="w-100 collapsed" data-bs-toggle="collapse" href="#collapse-item-${product.data.id}" role="button" aria-expanded="false" aria-controls="collapse-item-${product.data.id}">
            <div class="d-flex justify-content-between">
                <div>Tên sản phẩm: <strong>${product.data.name}</strong></div>
                <div>SKU: <strong>${product.data.sku}</strong></div>
                <div>Số lượng: <strong>${product.qty}</strong></div>
            </div>
        </a>
    </div>
    `;

    const mainBatchExits = document.querySelector('#collapseBatch .card-body');
    if(mainBatchExits){
        mainBatchExits.insertAdjacentHTML('afterbegin', textEl);
        $('#btn_cancel_receipt').remove();
        // $('#submit_btn').removeClass('d-none');
        // $('#submit_btn').addClass('d-inline');
    }
}

export function createQRForBatch(event, qr_ids = [], arr_info = [], direction) {
    var batch = {};
    batch.product = {}

    const modalContentQR = $(event).closest('.modal-content');

    //Tìm batch theo id
    const receiptId = document.querySelector('input#receipt_id');
    const curUserId = document.querySelector('input#current_user_id');
    let elBatch;
    let listBatch;

    if(direction == 'left'){
        listBatch = document.querySelectorAll('ul[id*=wrap-widget-1] li');

        listBatch?.forEach(item => {
            const ulCxt = item.querySelector('.widget_batch_warehouse');
            elBatch = item;
            let listBatch = [];
            let listQRid = [];
            for (let index = 0; index < arr_info.data.length; index++) {
                listBatch.push(arr_info.data[index].batch_id)
                listQRid.push(arr_info.data[index].qr_id)

            }
            batch = {
                'batch': {
                    'qr_ids': listQRid,
                    'batch_id': listBatch,
                    'qrcode_parent_id': arr_info['qrcode_parent_id'],
                    'scan_batch_warehouse': arr_info['scan_batch_warehouse'],
                },
                'receipt_id': receiptId.value,
                'current_user_id': curUserId.value
            }
        })
    }else{
        batch = {
            'batch': {
                'qr_ids': qr_ids,
            },
            'receipt_id': receiptId.value,
            'current_user_id': curUserId.value
        }
    }

    let urlAPI = document.querySelector('input#url_api_qrcode_for_batch').value ?? 'print-qrcode-for-batch';
    $.ajax({
        url: `/api/v1/${urlAPI}?type=${$(event).attr('name')}`,
        type: 'POST',
        data: JSON.stringify(batch),
        beforeSend: function(xhr) {
            // Thiết lập header Authorization với token
            xhr.setRequestHeader("Authorization", "Bearer " + $('input#api_key').val());
        },
        success: function (data) {
            if(direction == 'left'){
                //Xoá lô nếu đã nhập thành công
                const listBatch = document.querySelectorAll('ul[id*=wrap-widget-1] li');
                listBatch?.forEach(item => {
                    if(item.getAttribute('data-batch')){
                        item.classList.add('filtered');
                    }
                })

                const btnShowQR = document.querySelector('ul[id*=wrap-widget-] #open_scan_modal_batch_warehouse');
                btnShowQR?.remove();

                toastr['success'](data.msg, 'Thông báo')
            }else{
                if($(event).attr('name') == 'create-batch'){
                    let tempDiv = $('<div id="print-container" style="display: none;">').html(data['view']);
                    let contentToPrint = tempDiv[0];
                    const currentDate = moment().format('YYYY-MM-DD-HHmmss');
                    let options = {
                        printable: contentToPrint,
                        type: 'html',
                        documentTitle: 'warehouse-material-' + currentDate,
                        font_size: '8pt',
                        showModal: false,
                        modalMessage: 'Đang chuẩn bị in...',
                        onPrintDialogClose: function () {
                            // Thực hiện các tác vụ tùy chỉnh sau khi người dùng đóng hộp thoại in
                            console.log('Print dialog closed');
                        }
                    };

                    printJS(options);
                    for (const key in data.listProduct) {
                        if (Object.hasOwnProperty.call(data.listProduct, key)) {
                            const element = data.listProduct[key];
                            showListProductExist(element)
                        }
                    }
                }else{
                    for (const key in data.body) {
                        if (Object.hasOwnProperty.call(data.body, key)) {
                            const element = data.body[key];
                            showListProductExist(element)
                        }
                    }
                }
            }

            $('#btn_cancel_receipt').remove();

            $(`#QrScanReceiveModal`).modal('hide');
            ajaxGetCreatedShipment(receiptId.value);
        },
        error: function (jqXHR, textStatus, text) {

        }
    })
}

function ajaxGetCreatedShipment(receiptId){
    let urlAPI = document.querySelector('input#url_api_batch_created').value ?? 'get-created-shipment';
    $.ajax({
        url: `/api/v1/${urlAPI}/${receiptId}`,
        type: 'get',
        beforeSend: function(xhr) {
            // Thiết lập header Authorization với token
            xhr.setRequestHeader("Authorization", "Bearer " + $('input#api_key').val());
        },
        success: function(data){
            //Cập lại số lượng hiện tại của mỗi sản phẩm
            console.log(1);
           const submit = setCurrentQuantityProductIntoCreatedShipment(data.body.qtyPro)
           console.log(data.body);
            if(submit)
            {
                $('#submit_btn').removeClass('d-none').addClass('d-inline');
            }
            else{
                $('#submit_btn').hide();
            }
        },
        error: function(error){
            console.log(error);
        }
    })
}

//Lấy số lượng còn lại của sản phẩm theo id
export function getStartQuantityProductById(productId) {
    const wrapperContentFrom = document.querySelector('#wrap-widget-1');

    let lastQty = 0;
    if (wrapperContentFrom) {
        const listProduct = wrapperContentFrom.querySelectorAll('li.widget-item');
        listProduct?.forEach(item => {
            if (item.dataset.id == productId) {
                let startQty = item?.querySelector('input.start_quantity');

                if(startQty != ''){
                    return lastQty = startQty?.dataset.quantity;
                }
            }
        })
    }
    return lastQty;
}

//Set số lượng còn lại của sản phẩm theo id
export function setCurrentQuantityProductById(type) {
    const wrapperContentFrom = document.querySelector('#wrap-widget-1');

    let result = true;

    if (wrapperContentFrom) {
        const listProduct = wrapperContentFrom.querySelectorAll(`li.widget-item`);

        listProduct?.forEach(product => {
            if(product.dataset.id){
                let amountQtyProduct = getTotalQuantityProductInBatch(product.dataset.id, type);
                let startQty = getStartQuantityProductById(product.dataset.id);

                let lastQty = product.querySelector('input.current_quantity');
                let contentQty = product.querySelector('.content-current-quantity');
                let qty = startQty - amountQtyProduct;

                if(Object.keys(ARR_QTY_PRODUCT_CREATED_BATCH).length > 0){
                    Object.entries(ARR_QTY_PRODUCT_CREATED_BATCH).forEach(([key, value]) => {
                        if(product.dataset.id == key){
                            qty = qty - value;
                        }
                    })
                }

                if(qty < 0){
                    result = false;
                    return;
                }else{
                    lastQty.dataset.quantity = qty;

                    if(contentQty){
                        contentQty.innerHTML = qty;
                    }

                    //Hidden if current quantity is smaller than 0
                    if (qty === 0) {
                        if (!product.classList.contains('filtered')) {
                            product.classList.add('filtered');
                        }
                    } else {
                        if (product.classList.contains('filtered')) {
                            product.classList.remove('filtered');
                        }
                    }
                }
            }
        })
    }

    return result;
}

//Set lại số lượng hiện tại của từng sản phẩm theo lô đã tạo
export function setCurrentQuantityProductIntoCreatedShipment(arrQty){
    const wrapperContentFrom = document.querySelector('#wrap-widget-1');
    let submit_btn = true;
    if (arrQty == '') {
        submit_btn = false;
    }
    if (wrapperContentFrom) {
        const listProduct = wrapperContentFrom.querySelectorAll(`li.widget-item`);

        listProduct?.forEach(product => {
            const batchWarehouse = product.querySelector('.widget_batch_warehouse')
            Object.entries(arrQty).forEach(([key, value]) => {
                $(product).find('.widget_batch_warehouse_item');
                if(product.dataset.id == key){
                    let startQty = getStartQuantityProductById(product.dataset.id);

                    let lastQty = product.querySelector('input.current_quantity');
                    const contentQty = product.querySelector('.content-current-quantity');

                    let qty = startQty - value.qty;

                    if(lastQty != null){
                        lastQty.dataset.quantity = qty;
                    }

                    if(!batchWarehouse && contentQty){
                        contentQty.innerHTML = qty;
                    }

                    //Hidden if current quantity is smaller than 0
                    if (qty <= 0) {
                        if (!product.classList.contains('filtered')) {
                            product.classList.add('filtered');
                        }
                    } else {
                        submit_btn = false;
                        if (product.classList.contains('filtered')) {
                            product.classList.remove('filtered');
                        }
                    }
                }
            });

        })
    }
    else {
        submit_btn = false
    }
    return submit_btn;
}

