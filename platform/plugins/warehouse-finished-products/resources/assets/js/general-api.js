import printJS from 'print-js';
import moment from 'moment';

//Xử lý thông tin của lô khi tạo và in thành công lô
export function showListBatchExist(product, receiptDetail){
    //Tạo thông tin cho lô được tạo
    // let textEl = `  
    // <div class="card-item py-2">
    //     <a class="w-100 collapsed" data-bs-toggle="collapse" href="#collapse-item-${batch.id}" role="button" aria-expanded="false" aria-controls="collapse-item-${batch.id}">
    //         <div class="d-flex justify-content-between">
    //             <div>Tên sản phẩm: <strong>${product.name}</strong></div>
    //             <div>SKU: <strong>${product.sku}</strong></div>
    //             <div>Số lượng: <strong>${receiptDetail[product.id]}</strong></div>
    //         </div>
    //     </a>
    // </div>
    // `;

    // const mainBatchExits = document.querySelector('#collapseBatch .card-body');
    // if(mainBatchExits){
    //     mainBatchExits.insertAdjacentHTML('afterbegin', textEl);
    //     $('#btn_cancel_receipt').remove();
    //     $('#submit_btn').removeClass('d-none');
    //     $('#submit_btn').addClass('d-inline');
    // }
}

export function createQRForBatch(event, qr_ids = [], arr_info = [], direction) {
    var batch = {};
    batch.product = {}

    const modalContentQR = $(event).closest('.modal-content');
    let batchId = modalContentQR.find('input#modal_batch_id').val();

    //Tìm batch theo id
    const receiptId = document.querySelector('input#receipt_id');
    const curUserId = document.querySelector('input#current_user_id');
    let elBatch;
    let listBatch;

    if(direction == 'left'){
        listBatch = document.querySelectorAll('ul[id*=wrap-widget-1] li');

        listBatch?.forEach(item => {
            const ulCxt = item.querySelector('.widget_batch_warehouse');
    
            if (ulCxt?.dataset.batch == batchId) {
                elBatch = item;
                const listProduct = ulCxt.querySelectorAll('.widget_batch_warehouse_item');
    
                listProduct?.forEach(product => {
                    let proId = product.querySelector('input.slt_product_id').value;
                    let parentId = product.querySelector('input.slt_parent_id').value;
                    batch = {
                        'batch': {
                            'parent_id': parentId,
                            'product': {
                                [proId]: {
                                    'quantity': parseInt(product.querySelector('input.start_quantity').dataset.quantity),
                                }
                            },
                            'qr_ids': qr_ids,
                            'batch_id': arr_info['batch_id'],
                            'qrcode_parent_id': arr_info['qrcode_parent_id'],
                            'scan_batch_warehouse': arr_info['scan_batch_warehouse'],
                        },
                        'receipt_id': receiptId.value,
                        'current_user_id': curUserId.value
                    }
                })
            }
        })
    }else{
        listBatch = document.querySelectorAll('.batch__list .sidebar-item');
    
        listBatch?.forEach(item => {
            const ulCxt = item.querySelector('ul[id*=wrap-widget-]');
            if(direction){
                const ulCxt = item.querySelector('ul[id*=wrap-widget-1]');
    
            }
    
            if (ulCxt.dataset.batch == batchId) {
                elBatch = item;
                const listProduct = ulCxt.querySelectorAll('li.widget-item');
    
                let arrProduct = {};
    
                listProduct?.forEach(product => {
                    let proId = product.dataset.id;
                    let parentId = product.querySelector('input.slt_parent_id').value;
                    batch = {
                        'batch': {
                            'parent_id': parentId,
                            'product': {
                                [proId]: {
                                    'quantity': product.querySelector('input.slt_quantity').value,
                                }
                            },
                            'qr_ids': qr_ids,
                            'batch_id': arr_info['batch_id'],
                            'qrcode_parent_id': arr_info['qrcode_parent_id'],
                        },
                        'receipt_id': receiptId.value,
                        'current_user_id': curUserId.value
                    }
                })
            }
        })
    }


    const urlAPI = document.querySelector('input#url_app');
    $.ajax({
        url: `/api/v1/print-qrcode-for-batch`,
        type: 'POST',
        data: JSON.stringify(batch),
        beforeSend: function(xhr) {
            // Thiết lập header Authorization với token
            xhr.setRequestHeader("Authorization", "Bearer " + $('input#api_key').val());
        },
        success: function (data) {
            if(direction == 'left'){
                showListBatchExist(data.body.batch, data.body.receiptDetail);

                //Xoá lô nếu đã nhập thành công
                listBatch?.forEach(item => {
                    const ulCxt = item.querySelector('.widget_batch_warehouse');
            
                    if (ulCxt?.dataset.batch == batchId) {
                        item.remove();
                    }
                })
            }else{
                // let tempDiv = $('<div id="print-container" style="display: none;">').html(data['view']);
                // let contentToPrint = tempDiv[0];
                // const currentDate = moment().format('YYYY-MM-DD-HHmmss');
                // let options = {
                //     printable: contentToPrint,
                //     type: 'html',
                //     documentTitle: 'warehouse-material-' + currentDate,
                //     style: `
                //         @page {
                //         size: 100mm 60mm;
                //         margin:0;
                //         padding:0;
                //         }
                //         body {
                //         width: 100mm;
                //         height:60mm;
                //         }
                //         #test{text-align: center}
                //         .item > div > ul > li {
                //             list-style:none;
    
                //         }
                //         .item div:nth-child(2) {
                //             margin-left: -20px;
                //         }
                //     `,
                //     showModal: false,
                //     modalMessage: 'Đang chuẩn bị in...',
                //     onPrintDialogClose: function () {
                //         // Thực hiện các tác vụ tùy chỉnh sau khi người dùng đóng hộp thoại in
                //         console.log('Print dialog closed');
                //     }
                // };
    
                // printJS(options);
    
                const option_sample = document.querySelector('.option_sample');
                if (option_sample) {
                    const optionCustom = option_sample.querySelector('#custom');
                    const optionSample = option_sample.querySelector('#sample');
                    const btnScanQRCode = elBatch.querySelector('.btn_scan_qrcode');
    
                    if (optionCustom.checked) {
                        if (btnScanQRCode.classList.contains('d-inline')) {
                            btnScanQRCode.classList.remove('d-inline');
                        }
                        btnScanQRCode.style.display = 'none';
                    } else if (optionSample.checked) {
                        //Ẩn input set lô được tạo ở option lô mẫu
                        const widgetCountBatch = elBatch.querySelector('.widget__quantity-batch');
            
                        if(widgetCountBatch){
                            const inputBatch = widgetCountBatch.querySelector('input.count_batch');
                            const cxtBatch = widgetCountBatch.querySelector('.remaining_batch');
                            const inputQuantity = elBatch.querySelector('input.slt_quantity');
                            
                            $(widgetCountBatch).closest('.sidebar-item').find('.widget__quantity-batch small').css({
                                'display': 'block',
                            })
                            
                            if(!inputBatch.getAttribute('readonly')){
                                inputBatch.setAttribute('readonly', true);
                            }
                            
                            if(!inputQuantity.getAttribute('readonly')){
                                inputQuantity.setAttribute('readonly', true);
                            }
                            
                            if (cxtBatch.textContent != '' && cxtBatch.textContent*1 > 0) {
                                cxtBatch.textContent = cxtBatch.textContent*1 - 1;
                            }else{
                                cxtBatch.textContent = inputBatch.value*1 - 1;
                            }
                            
                            if (cxtBatch.textContent*1 == 0) {
                                if (btnScanQRCode.classList.contains('d-inline')) {
                                    btnScanQRCode.classList.remove('d-inline');
                                }
                                btnScanQRCode.remove()
                            }
                        }
                    }
                }

                const listProductInBatch = elBatch.querySelectorAll('ul[id*=wrap-widget-] li');

                listProductInBatch?.forEach(product => {
                    const btnWidgetDelete = product.querySelector('.widget-control-delete');

                    btnWidgetDelete?.remove();
                })
                showListBatchExist(data.batch, data.receiptDetail)
            }

            $('#submit_btn').removeClass('d-none').addClass('d-inline');
            $('#btn_cancel_receipt').remove();
        
        },
        error: function (jqXHR, textStatus, text) {

        }
    })
}
