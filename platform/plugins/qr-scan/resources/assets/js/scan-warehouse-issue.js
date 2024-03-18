import { flatMap } from "lodash";
import Scanner from "./library/scanner"
import { BATCH_MODEL, PRODUCT_MODEL } from "./utils"
import { SALE_WAREHOUSE_CHILD_MODEL } from "./utils"
import { data } from "jquery";
import { reference } from "@popperjs/core";
import printJS from "print-js";
import moment from "moment";
toastr.options = {
    closeButton: true,
    positionClass: 'toast-bottom-right',
    showDuration: 500,
    hideDuration: 500,
    timeOut: 1000,
    extendedTimeOut: 1000,
    showEasing: 'swing',
    hideEasing: 'linear',
    showMethod: 'fadeIn',
    hideMethod: 'fadeOut',
}

let isScanning = false;
let products = [];
let qrBatchScanned = [];
let qrProductScanned = [];
let scanType = null;
const Scan = new Scanner();
const renderProductInfo = (data) => {
    $('#product_table_list').html(data?.map((item, index) => {
        return `<tr>
        <th scope="row">${index + 1}</th>
        <td style="width: 45%">
            <div class="d-flex align-items-center flex-wrap">
                <span href="#" title=" ${item?.product?.name}" class="me-2">
                    ${item?.product?.name}
                </span>
            </div>
            <small>(Color ${item?.color || '---'}, Size ${item?.size || '---'})</small>
        </td>
        <td>
        ${item?.quantity || 0}
        </td>
        <td>${item?.scanned_count || 0}</td>
    </tr>`;
    }));
}

const renderBatchProductInfo = (data) => {
    $('#product_batch_table_list').html(data?.map((item, index) => {
        return `<tr class="drop_item" data-product-info-id=${item.id}>
        <th scope="row" data-p-info-id=${item.id}>${index + 1}</th>
        <td style="width: 45%" data-p-info-id=${item.id}>
            <div class="d-flex align-items-center flex-wrap">
                <span href="#" title=" ${item?.product?.name}" class="me-2">
                ${item?.product?.name ? item?.product?.name : item.product_name}
                <br>Màu: ${item?.color} - Size: ${item?.size}
                </span>
            </div>
        </td>
        <td data-p-info-id=${item.id}>
        ${item?.quantity} sản phẩm
        </td>
        <td data-p-info-id=${item.id}>
           ${item?.scanned_count || 0}
        </td>
        <td >
        ${item?.is_batch == 1 ? (item.scanned_count == 1 ? 'Đã quét' : 'Chưa quét') : (item.scanned_count == item.quantity ? 'Đã quét đủ' : 'Chưa quét đủ')}
        </td >
    </tr > `;
    }))
}

const renderBatchScanned = (list) => {
    if (list.length > 0) {
        $('#batch_table_list').html(list.map((d, index) => {
            if (d?.reference?.batch_code) {
                const product_info = (d?.reference?.product_in_batch);
                const productValues = product_info.map(item => `[id: ${item.product_id}, qrcode: ${item.qrcode_id}]`).join(', ');
                return `<tr draggable="false" class="drag_item">
                            <th scope="row">${index + 1}</th>
                            <td>${d?.reference?.batch_code}</td>
                            <td>${d?.warehouse?.name || '---'}</td>
                            <td>${d?.reference?.quantity}</td>
                            <td class="text-center">
                                <button data-batch-id="${d?.reference.id}" data-bs-toggle="tooltip" title="Xem sản phẩm trong lô" class="btn btn-sm btn-icon btn-primary show_batch_info_btn">
                                    <i class="fa-regular fa-eye"></i>
                                </button>
                                <button data-id="${d.id}"  data-product-info-id="${productValues}"  data-bs-toggle="tooltip" title="Bỏ chọn lô này" class="btn btn-sm btn-icon btn-danger cancel_batch_btn">
                                    <i class="fa-solid fa-xmark"></i>
                                </button>
                            </td>
                        </tr>`;
            }
            // else {
            //     return `<tr draggable="false" class="drag_item">
            //                 <th scope="row">${index + 1}</th>
            //                 <td>${d?.reference?.name}   <div><small>${d?.time_create_q_r?.variation_attributes}</small></div></td>
            //                 <td>---</td>
            //                 <td>1</td>
            //                 <td class="text-center">
            //                     <button data-id="${d ? d.id : ''}" data-product-info-id="${d ? d.product_info_id : ''}" data-bs-toggle="tooltip" title="Bỏ chọn sản phẩm này" class="btn btn-sm btn-icon btn-danger cancel_product_btn">
            //                         <i class="fa-solid fa-xmark"></i>
            //                     </button>
            //                 </td>
            //             </tr>`;

            // }
        }))
        $('#empty_scan_batch_message').removeClass('d-flex').addClass('d-none');
    } else {
        $('#batch_table_list').html('');
        $('#empty_scan_batch_message').removeClass('d-none').addClass('d-flex');
    }

}


const cancelSelectBatch = (_self, id, product_info_id) => {
    const batchFilter = qrBatchScanned.filter(item => item.id == id);
    qrBatchScanned = qrBatchScanned.filter(item => item.id != id);
    batchFilter[0].reference.product_in_batch.map(data => {
        qrProductScanned = qrProductScanned.filter(item => item.id != data.qrcode_id);
        products = products.map(item => item.id == data.product_id ? { ...item, scanned_count: item.scanned_count - 1 } : item)
    })
    renderBatchProductInfo(products)
    setDisabledButtons();
    renderBatchScanned(qrBatchScanned);
    clearScanMessage();
    toastr.success('Đã bỏ chọn 1 lô');
}

const cancelSelectProduct = (_self, id, product_info_id) => {
    qrProductScanned = qrProductScanned.filter(item => item.id != id);
    products = products.map(item => item.id == product_info_id ? { ...item, scanned_count: item.scanned_count - 1 } : item)
    let filterQrProductScan = qrProductScanned
    qrBatchScanned.forEach(qrBatch => {
        filterQrProductScan = filterQrProductScan.filter(qrProduct => {
            return !qrBatch.reference.product_in_batch.some(item => qrProduct.qrcode_id === item.qrcode_id);
        });
    });
    renderProductScanned(filterQrProductScan);
    renderBatchProductInfo(products)
    setDisabledButtons();
    clearScanMessage();
    toastr.success('Đã bỏ chọn 1 sản phẩm');
}

const renderProductScanned = (list) => {
    if (list.length > 0) {
        $('#table_list').html(list.map((d, index) => {
            return `<tr>
            <th scope="row">${index + 1}</th>
            <td style="max-width:200px;">
                <div class="d-flex align-items-center flex-wrap">
                    <a href="#"
                        title="Joan Mini Camera Bag" class="me-2">
                        ${d?.reference?.name}
                    </a>
                </div>
                <div>
                    <small>${d?.time_create_q_r?.variation_attributes}</small>
                </div>
            </td>
            <td><p>${d?.status?.label}</p></td>
            <td>${d?.warehouse?.name || '---'}</td>
            <td class="text-center">
                <button data-id="${d.id}" data-product-info-id="${d.product_info_id}" data-bs-toggle="tooltip" title="Bỏ chọn sản phẩm này" class="btn btn-sm btn-icon btn-danger cancel_product_btn">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </td>
        </tr>`;
        }));
        $('#empty_scan_product_mess').removeClass('d-flex').addClass('d-none');
    } else {
        $('#table_list').html('');
        $('#empty_scan_product_mess').removeClass('d-none').addClass('d-flex');
    }
}
const handleResponseScanProduct = (res, code) => {
    const d = res.data;
    if (d?.status?.value != 'instock') return showScanMessage('Sản phẩm phải có trạng thái đã nhập kho', 'warning');
    // if (batch && batch.some(item => item.id === d.batch_parent.batch_id)) {
    //     return showScanMessage('Sản phẩm thuộc 1 trong những lô đề xuất', 'warning');
    // }
    if (res?.success == 0) throw new Error(res?.message);
    // if (d.reference_type != PRODUCT_MODEL) return showScanMessage('Mã QR là lô. Vui lòng quét mã QR của sản phẩm', 'warning');
    if (qrProductScanned.find(item => item.id == d.id)) return showScanMessage('Sản phẩm đã quét. Vui lòng quét sản phẩm khác', 'warning');

    const index = products?.findIndex(item =>
        item?.product_id === d?.reference_id &&
        item.scanned_count < item.quantity
    );

    if (index > -1) {
        products[index].scanned_count += 1;
        qrProductScanned.push({ ...d, product_info_id: products[index].id });

        qrBatchScanned.forEach(qrBatch => {
            qrProductScanned = qrProductScanned.filter(qrProduct =>
                !qrBatch.reference.product_in_batch.some(item => qrProduct.qrcode_id === item.qrcode_id)
            );
        });

        renderProductScanned(qrProductScanned);
        renderBatchProductInfo(products);
        $('#save_qr_scan_btn, #save_qr_batch').removeAttr('disabled');
        showScanMessage('Mã QR hợp lệ', 'success');
    } else if (products?.some(item => item?.product_id === d?.reference_id)) {
        showScanMessage('Sản phẩm đã quét đủ', 'warning');
    } else {
        showScanMessage('Sản phẩm khác trong danh sách đề xuất', 'warning');
    }
}

const handleResponseScanBatch = (res, code) => {
    const d = res.data;
    if (res?.success == 0) throw new Error(res?.message);
    if (qrBatchScanned.find(item => item.id == d.id)) return showScanMessage('Lô đã quét. Vui lòng quét lô khác', 'warning');
    if (d?.status?.value != 'instock') return showScanMessage(`Không hợp lệ. Lô đang ở trạng thái <span class="fw-bolder">${d?.status?.label}</span>`, 'warning');
    if (d?.reference?.quantity == 0) return showScanMessage('Lô đã hết hàng', 'warning');
    let foundValidQR = true;
    let checkQuantity = true;
    let productQuantities = {};
    d.reference.product_in_batch.forEach(data => {
        if (!productQuantities[data.product_id]) {
            productQuantities[data.product_id] = { quantity: 1 };
        } else {
            productQuantities[data.product_id].quantity++;
        }
    });

    Object.entries(productQuantities).forEach(([productId, item]) => {
        products.forEach(data => {
            if (data.product_id == productId) {
                if ((data.quantity - data.scanned_count) < item.quantity) {
                    checkQuantity = false;
                    return;
                }
            }
        });
    });
    if (!checkQuantity) return showScanMessage('Lô vượt quá số lượng sản phẩm trong phiếu', 'danger');
    d.reference.product_in_batch.forEach(data => {
        if (!products.some(item => item.product_id === data.product_id)) {
            foundValidQR = false;
            return;
        }
    });
    if (foundValidQR) {
        d.reference.product_in_batch.forEach(data => {
            const index = products?.findIndex(item =>
                item.scanned_count < item.quantity
                && item?.product_id == data.product_id
                && window.warehouse_id == d.warehouse_id
                && window.warehouse_type == d?.warehouse_type
            );
            if (index > -1) {
                foundValidQR = true;
                products[index].scanned_count += 1;
                const findQrProduct = qrProductScanned.find(item => {
                    if (item.id == data.qrcode_id)
                        return item
                })
                if (findQrProduct) {
                    cancelSelectProduct($(this), findQrProduct.id, findQrProduct.reference_id)
                }
                qrProductScanned.push({ ...data, id: data.qrcode_id });
                renderBatchProductInfo(products);
                showScanMessage('Mã QR hợp lệ', 'success');
            }
        });
        qrBatchScanned.push({ ...d, batch: d.reference.batch_code });
        renderBatchScanned(qrBatchScanned);
        $('#save_qr_scan_btn').removeAttr('disabled');
        $('#save_qr_batch').removeAttr('disabled');


    }
    else {
        showScanMessage('Lô có sản phẩm không nằm trong phiếu xuất', 'danger');
    }
    // const index = products?.findIndex(item => {
    //     let found = false;
    //     d.reference.product_in_batch.forEach(data => {

    //     });
    //     return found === false;
    // });

    // if (index !== -1 && qrDone) {
    //     d.reference.product_in_batch.forEach(data => {
    //         products[index].scanned_count += 1;
    //         qrBatchScanned.push({ ...d, product_info_id: products[index].id });
    //         renderBatchScanned(qrBatchScanned);
    //         renderBatchProductInfo(products);
    //         showScanMessage('Mã QR hợp lệ', 'success');
    //     })
    // }
}

const checkValidFormQr = () => {
    let valid = true;
    products.forEach(item => {
        if (item.scanned_count < item.quantity) valid = false;
    });
    return valid;
}


const showScanMessage = (message, type) => {
    $('#scanner_message').html(`
        <div role = "alert" class="alert alert-${type} mt-3 mb-0" >
            <div class="d-flex">
                <div class="w-100">
                    ${message}
                </div>
            </div>
         </div > `).show();
}

const clearScanMessage = () => {
    $('#scanner_message').html('').hide();
}

const playSound = (fileName) => {
    if (!fileName) return;
    const audio = document.createElement('audio');
    audio.src = `/storage/scan-audio/${fileName}.mp3`;
    audio?.play();
    audio?.remove();
}

const openScannerLoading = () => {
    isScanning = true;
    $('#scanner_box').removeClass('d-flex').addClass('d-none');
    $('#scanner_box_loading').removeClass('d-none').addClass('d-flex');
}

const closeScannerLoading = () => {
    isScanning = false;
    $('#scanner_box').removeClass('d-none').addClass('d-flex');
    $('#scanner_box_loading').removeClass('d-flex').addClass('d-none');
}

const setDisabledButtons = () => {

}

const handleReset = () => {
    qrProductScanned = [];
    qrBatchScanned = [];
    products = [];
    window.products?.forEach(item => {
        if (item.quantity > 0) {
            products.push({ ...item, id: item.product_id, scanned_count: 0, batch: item?.batch });
        }
    });
    scanType = $('#open_scan_modal').data('type');
    renderBatchProductInfo(products);
    setDisabledButtons();
    $('#save_qr_batch').attr('disabled', true);
    $('#save_qr_scan_btn').attr('disabled', true);
    $('#qr_ids_wrapper').html('');
    $('#submit_btn').attr('disabled', true);
    $('#scanner_message').html('');
    $('#table_list').html('');
    $('#batch_table_list').html('');
    $('#empty_scan_product_mess').removeClass('d-none').addClass('d-flex');
    $('#empty_scan_batch_message').removeClass('d-none').addClass('d-flex');
}

document.addEventListener("DOMContentLoaded", (event) => {
    window.products?.forEach(item => {
        if (item.quantity > 0) {
            products.push({ ...item, id: item.product_id, scanned_count: 0, batch: item?.batch });
        }
        // batch.push({ ...item?.batch });
        // if (item?.batch == null) {
        //     productOdd.push({ ...item });
        // }
    });

    Scan.onScan(code => {
        try {
            if (checkValidFormQr()) return toastr.warning('Đã đủ số lượng. Bấm lưu để hoàn thành');
            if (isScanning == false) {
                clearScanMessage();
                openScannerLoading();

                $.ajax({
                    url: '/admin/product-qrcodes/ajax-post-qr-scan',
                    method: 'POST',
                    data: { qr_code: code },
                    success: (res) => {
                        closeScannerLoading()
                        if (window.warehouse_id == res.data?.warehouse_id
                            && window.warehouse_type == res?.data?.warehouse_type) {
                            if (window.warehouse_receipt_type != SALE_WAREHOUSE_CHILD_MODEL) {
                                if (res.data.reference_type == PRODUCT_MODEL) {
                                    handleResponseScanProduct(res, code)
                                    $('#product-tab').tab('show');
                                }
                                else {
                                    handleResponseScanBatch(res, code)
                                    $('#batch-tb').tab('show');
                                }
                                setDisabledButtons();
                            }
                            else {
                                let productionTime;

                                if (window.date_type == 'date_production') {
                                    productionTime = moment(res.data.production_time);
                                }
                                else if (window.date_type == 'date_in') {
                                    productionTime = moment(res.data.time_receipt_hub.created_at);
                                }
                                else if (res.data.reference_type == BATCH_MODEL) {
                                    productionTime = moment(res.data.reference.created_at);
                                }
                                const currentDate = moment();
                                // Tính số ngày giữa hai ngày
                                var dateSpace = currentDate.diff(productionTime, 'days');

                                console.log("Số ngày giữa hai ngày:", dateSpace);

                                if (dateSpace >= window.dateAcceipt) {
                                    if (res.data.reference_type == PRODUCT_MODEL) {
                                        handleResponseScanProduct(res, code)
                                        $('#product-tab').tab('show');
                                    }
                                    else {
                                        handleResponseScanBatch(res, code)
                                        $('#batch-tb').tab('show');
                                    }
                                }
                                else {
                                    showScanMessage('Sản phẩm hoặc lô chưa quá hạn sản xuất, không thể chuyển vào kho sale!!!', 'warning');
                                }
                            }
                        }
                        else {
                            showScanMessage('Mã sản phẩm hoặc lô không nằm trong kho xuất', 'warning');
                        }

                    },
                    error: (res) => {
                        const result = res.responseJSON;
                        closeScannerLoading();
                        showScanMessage(result.message, 'danger')
                    },
                })
            } else {
                toastr?.warning('Quét quá nhanh. Vui lòng chờ phản hồi từ hệ thống!');
            }
        } catch (err) {
            showScanMessage(res.message, 'danger')
        }
    })

    // Bặt tắt scan khi đóng mở modal
    $(document)
        .on('shown.bs.modal', '#warehouseIssueModal', () => {
            scanType = $('#open_scan_modal').data('type');
            if (scanType == 'batch') {
                renderBatchProductInfo(products);
                $('#batch-tab').addClass('show active');
                $('#product-tab').removeClass('show active');
            } else {
                renderProductInfo(products);
                $('#batch-tab').removeClass('show active');
                $('#product-tab').addClass('show active');
            }
            Scan.start();
        })
        .on('hide.bs.modal', '#warehouseIssueModal', () => {
            Scan.stop();
            scanType = null;
            clearScanMessage();
        })
    // Lưu giá trị vào form khi nhấn lưu
    $(document).on('click', '#save_qr_scan_btn', function () {
        $(this).prop('disabled', true);
        $('#save_qr_batch').prop('disabled', true);
        qrBatchScanned.forEach(qrBatch => {
            qrProductScanned = qrProductScanned.filter(qrProduct => {
                return !qrBatch.reference.product_in_batch.some(item => qrProduct.qrcode_id === item.qrcode_id);
            });
        });
        const dataProduct = qrProductScanned.map(item => ({
            id: item.id,
            reference_id: item.reference_id,
            reference:{
                name : item.reference.name,
                sku : item.reference.sku,
            },
            time_create_q_r:{
                variation_attributes: item.time_create_q_r.variation_attributes
            }
        }));
        const dataBatch = qrBatchScanned.map(item => ({
            id: item.id,
            reference_id: item.reference_id,
            reference: {
                quantity: item?.reference?.quantity,
                product_in_batch: item?.reference?.product_in_batch,
                batch_code:item?.reference?.batch_code
            }
        }));
        $.ajax({
            url: route(window.url, { type: 'odd' }),
            method: 'POST',
            data: {
                id: $('#product_issue').val(),
                products: dataProduct ? dataProduct : '',
                batchs: dataBatch ? dataBatch : '',
            },
            success: function (data) {
                data.batch.map(item => {
                    let productQuantities = {};
                    item.reference.product_in_batch.map(detail => {
                        let remainElement = $('#remain_' + detail.product_id);
                        let currentRemain = parseInt(remainElement.text());
                        let updatedRemain = currentRemain + 1;
                        remainElement.text(updatedRemain);
                        products = products.map(prd => {
                            if (detail.product_id == prd.product_id) {
                                prd.quantity -= 1;
                                prd.scanned_count = 0;
                            }
                            return prd;
                        });
                        products = products.filter(prd => prd.quantity > 0);
                        renderBatchProductInfo(products);
                        if (!productQuantities[detail.product_id]) {
                            productQuantities[detail.product_id] = {
                                name: detail.product_name,
                                sku: detail.sku,
                                quantity: 1
                            };
                        } else {
                            productQuantities[detail.product_id].quantity++;
                        }

                    });
                    let row = `
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="heading${item.reference.id}">
                            <button class="accordion-button" type="button" data-bs-toggle="collapse"
                                data-bs-target="#collapse${item.reference.id}" aria-expanded="true"
                                aria-controls="collapse${item.reference.id}">
                                ${item.reference.batch_code} - ${item.reference.quantity} sản phẩm
                            </button>
                        </h2>
                        <div id="collapse${item.reference.id}" class="accordion-collapse collapse"
                        aria-labelledby="heading${item.reference.id}" data-bs-parent="#batchTable">
                            <div class="accordion-body">
                                <div class="product-list">
                                    ${Object.values(productQuantities).map(product => `
                                    <div class="product">
                                        <span class="name">${product.name}</span>
                                        <span class="attributes">
                                            Màu:
                                            ---
                                            -
                                            Size:
                                            ---
                                        </span>
                                        <span class="sku">SKU: ${product.sku}</span>
                                        <span class="quantity">${product.quantity} sản phẩm</span>
                                    </div>
                                    `).join('')}

                                </div>
                            </div>
                        </div>

                    </div>
                `;
                    $('#batchTable').prepend(row);
                    $('#qr_ids_wrapper').append(`
                        <input type="hidden" name="batch_ids[]" value="${item.reference.id}" />`);
                });
                data.product.map(item => {
                    let remainElement = $('#remain_' + item.reference_id);
                    let currentRemain = parseInt(remainElement.text());
                    let updatedRemain = currentRemain + 1;
                    remainElement.text(updatedRemain);
                    products = products.map(prd => {
                        if (item.reference_id == prd.product_id) {
                            prd.quantity -= 1;
                            prd.scanned_count = 0;
                        }
                        return prd;
                    })
                    products = products.filter(prd => prd.quantity > 0);
                    renderBatchProductInfo(products);
                    let productQuantities = {};
                    if (!productQuantities[item.reference_id]) {
                        productQuantities[item.reference_id] = {
                            name: `${item.reference.name} `,
                            sku: item.reference.sku,
                            quantity: 1
                        };
                    } else {
                        productQuantities[item.reference_id].quantity++;
                    }
                    let row = `
                        ${Object.values(productQuantities).map(product => `
                            <div class="product">
                            <span class="name">${product.name}</span>
                            <span class="attributes">
                            ${item.time_create_q_r.variation_attributes}
                            </span>
                            <span class="sku">SKU: ${product.sku}</span>
                            <span class="quantity">${product.quantity} sản phẩm</span>
                        </div>

                        `).join('')}
                    `;
                    $('#qr_ids_wrapper').append(`
                    <input type="hidden" name="qr_ids[]" value="${item.id}" />`);
                    $('#productTable .product-list').prepend(row);

                })
                window.products = products
                qrBatchScanned = []
                qrProductScanned = []
                renderBatchScanned(qrBatchScanned);
                renderProductScanned(qrProductScanned);
                $('#warehouseIssueModal').modal('hide');
                $("#denyButton").remove();
                if (checkValidFormQr()) $('#submit_btn').removeAttr('disabled');
                else $('#submit_btn').attr('disabled', true);
            },
            error: function (xhr, status, error) {
                var errorMessage = xhr.responseJSON.error;
                alert('Error: ' + errorMessage);
            }
        });
    });
    $(document).on('click', '#save_qr_batch', function () {
        $(this).prop('disabled', true);
        $('#save_qr_scan_btn').prop('disabled', true);
        const $saveButton = $(this);
        qrBatchScanned.forEach(qrBatch => {
            qrProductScanned = qrProductScanned.filter(qrProduct => {
                return !qrBatch.reference.product_in_batch.some(item => qrProduct.qrcode_id === item.qrcode_id);
            });
        });
        const dataProduct = qrProductScanned.map(item => ({
            id: item.id,
            reference_id: item.reference_id,
        }));
        const dataBatch = qrBatchScanned.map(item => ({
            id: item.id,
            reference_id: item.reference_id,
            reference: {
                quantity: item.reference.quantity,
                product_in_batch: item.reference.product_in_batch
            }
        }));
        $.ajax({
            url: route(window.url, { type: 'batch' }),
            method: 'POST',
            data: {
                id: $('#product_issue').val(),
                products: dataProduct ? dataProduct : '',
                batchs: dataBatch ? dataBatch : '',
            },
            success: function (data) {

                if (data.err) {
                    showScanMessage('Chưa có sản phẩm quét', 'danger')
                }
                else {
                    $('.save_qr_batch').prop('disabled', true);
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
                    };
                    printJS(options);
                    data.batchDetail.map(item => {
                        let remainElement = $('#remain_' + item.product_id);
                        let currentRemain = parseInt(remainElement.text());
                        let updatedRemain = currentRemain + 1;
                        remainElement.text(updatedRemain);
                        products = products.map(prd => {
                            if (item.product_id == prd.product_id) {
                                prd.quantity -= 1;
                                prd.scanned_count = 0;
                            }
                            return prd;
                        })
                        products = products.filter(prd => prd.quantity > 0);
                        renderBatchProductInfo(products);

                    })
                    $('#qr_ids_wrapper').append(`
               <input type="hidden" name="batch_ids[]" value="${data.batch.id}" />
            `);

                    let productQuantities = {};
                    data.batchDetail.forEach(item => {
                        if (!productQuantities[item.product_id]) {
                            productQuantities[item.product_id] = {
                                name: item.product_name,
                                sku: item.sku,
                                quantity: 1
                            };
                        } else {
                            productQuantities[item.product_id].quantity++;
                        }
                    });

                    let accordionItem = `
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="heading${data.batch.id}">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                    data-bs-target="#collapse${data.batch.id}" aria-expanded="true"
                                    aria-controls="collapse${data.batch.id}">
                                    ${data.batch.batch_code} - ${data.batch.quantity} sản phẩm
                                </button>
                            </h2>
                            <div id="collapse${data.batch.id}" class="accordion-collapse collapse"
                                aria-labelledby="heading${data.batch.id}" data-bs-parent="#batchTable">
                                <div class="accordion-body">
                                    <div class="product-list">
                                        ${Object.values(productQuantities).map(product => `
                                        <div class="product">
                                            <span class="name">${product.name}</span>
                                            <span class="attributes">
                                                Màu:
                                                ---
                                                -
                                                Size:
                                                ---
                                            </span>
                                            <span class="sku">SKU: ${product.sku}</span>
                                            <span class="quantity">${product.quantity} sản phẩm</span>
                                        </div>
                                        `).join('')}

                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                    $('#batchTable').append(accordionItem);

                    window.products = products
                    qrBatchScanned = []
                    qrProductScanned = []
                    renderBatchScanned(qrBatchScanned);
                    renderProductScanned(qrProductScanned);
                    $('#warehouseIssueModal').modal('hide');
                    $("#denyButton").remove();
                    if (checkValidFormQr()) $('#submit_btn').removeAttr('disabled');
                    else $('#submit_btn').attr('disabled', true);
                }
            },
            error: function (xhr, status, error) {
                var errorMessage = xhr.responseJSON.error;
                $saveButton.attr('disabled', false);
                alert('Error: ' + errorMessage);
            }
        });

    });
    $('#batchTable').on('click', '.accordion-toggle', function () {
        $(this).next('tr').find('.collapse').collapse('toggle');
    });

    // Reset form quét
    $(document).on('click', '#reset_btn', function () {
        handleReset()
    });

    // Switch tab scan (Batch và Product)
    $(document).on('click', '.tab_btn', function () {
        tabName = $(this).data('name');
        $('.tab_btn').addClass('nav-link').removeClass('btn btn-primary');
        $(this).removeClass('nav-link').addClass('btn btn-primary');
        clearScanMessage();
    })

    // Show thông tin sản phẩm trong lô đã quét
    $(document).on('click', '.show_batch_info_btn', function () {
        const batch_id = $(this).data('batch-id');
        $("#box_scan").hide();
        $("#box_batch_info").show();

        $.ajax({
            url: '/admin/receipt-product/get-batch-info',
            method: 'POST',
            data: {
                batch_id
            },
            dataType: 'html',
            success: (res) => {
                $('#box_batch_info').children('#content').html(res);
            }
        })
    })

    //Back lại màn hình quét
    $(document).on('click', '#back_box_btn', function () {
        $("#box_batch_info").hide();
        $("#box_scan").show();
    })
    // Bỏ chọn lô hoặc sản phẩm đã quét
    $(document)
        .on('click', '.cancel_batch_btn', function () {
            if (confirm('Bỏ chọn lô này?')) {
                const id = $(this).data('id');
                const productList = $(this).data('product-info-id');
                cancelSelectBatch($(this), id, productList)
            }
        }).on('click', '.cancel_product_btn', function () {
            if (confirm('Bỏ chọn sản phẩm này?')) {
                const id = $(this).data('id');
                const product_info_id = $(this).data('product-info-id');
                cancelSelectProduct($(this), id, product_info_id)
            }
        })


    $(document).on('dragstart', '.drag_item', function (event) {
        const batch_id = event.currentTarget.dataset.batchId;

        const p = qrBatchScanned.find(item => item?.batch?.id == batch_id);
        event.originalEvent.dataTransfer.setData("batch_id", batch_id);

        var elem = document.createElement("div");
        elem.innerHTML =
            ` < button type = "button" class="btn btn-white" >
    ${p?.batch?.batch_code} <span class="badge bg-danger text-white m-1">${p?.batch?.quantity} SP</span>
         </ > `;
        elem.classList.add("shadow-lg");
        elem.textNode = "Dragging";
        elem.style.position = "absolute";
        elem.style.top = "-1000px";
        document.body.appendChild(elem);
        event.originalEvent.dataTransfer.setDragImage(elem, 160, 40);

    }).on('dragend', '.drag_item', () => $('#product_batch_table_list').children('tr').removeClass('bg-primary text-white'))

    $(document)
        .on('dragenter', '.drop_item', event => $(event.currentTarget).addClass('bg-primary text-white'))
        .on('dragleave', '.drop_item', event => $(event.currentTarget).removeClass('bg-primary text-white'))

    $(document)
        .on('dragover', '.drop_item', function (event) {
            event.preventDefault();
        }).on('drop', '.drop_item', function (event) {
            event.preventDefault();
            const batch_id = event.originalEvent.dataTransfer.getData("batch_id");
            const product = qrBatchScanned.find(item => item?.batch?.id == batch_id);

            const batch = product?.batch;

            const product_info_id = event.currentTarget.dataset.pInfoId;

            const pInfoIndex = product_info_id ? products.findIndex(item =>
                item.id = product_info_id
                && item.is_batch
                && !item.batch
                && item?.product_id == batch?.product_parent_id
            ) : null;


            if (pInfoIndex > -1) {
                products[pInfoIndex].batch = batch;
                renderBatchProductInfo(products);
                setDisabledButtons();
                return toastr.success('Thay đổi thành công');
            }
            return toastr.warning('Lô không cùng loại sản phẩm, vui lòng thử lại');
        })

});

