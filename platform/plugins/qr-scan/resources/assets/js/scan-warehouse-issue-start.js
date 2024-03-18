import Scanner from "./library/scanner"

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

let tabName = "batch";
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
                <span href="#" title=" ${item?.product_name}" class="me-2">
                    ${item?.product_name}
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
                <span href="#" title=" ${item?.product_name}" class="me-2">
                    ${item?.product_name}
                </span>
            </div>
        </td>
        <td data-p-info-id=${item.id}>
           ${item?.quantity || 0} lô
        </td>
        <td data-p-info-id=${item.id}>
           ${item?.scanned_count || 0}
        </td>
        <td >
            ${item.scanned_count < item.quantity ? '<span>Chưa quét đủ</span>' : ''}
            ${item.scanned_count == item.quantity ? '<span class="text-success">Đã quét đủ</span>' : ''}
        </td >
    </tr > `;
    }))
}

const renderBatchScanned = (list) => {
    if (list.length > 0) {
        $('#batch_table_list').html(list.map((d, index) => {
            return `<tr draggable="false" class="drag_item">
                <th scope="row">${index + 1}</th>
                <td>${d?.reference?.batch_code}</td>
                <td>${d?.warehouse?.name || '---'}</td>
                <td>${d?.reference?.quantity}</td>
                <td class="text-center">
                <button data-batch-id="${d?.reference.id}" data-bs-toggle="tooltip" title="Xem sản phẩm trong lô" class="btn btn-sm btn-icon btn-primary show_batch_info_btn">
                    <i class="fa-regular fa-eye"></i>
                </button>
                <button data-id="${d.id}" data-product-info-id="${d.product_info_id}" data-bs-toggle="tooltip" title="Bỏ chọn lô này" class="btn btn-sm btn-icon btn-danger cancel_batch_btn">
                    <i class="fa-solid fa-xmark"></i>
                    </button>
                </td>
            </tr>`
        }))
        $('#empty_scan_batch_message').removeClass('d-flex').addClass('d-none');
    } else {
        $('#batch_table_list').html('');
        $('#empty_scan_batch_message').removeClass('d-none').addClass('d-flex');
    }

}

const renderProductScanned = (list) => {
    if (list.length > 0) {
        console.log(list)
        $('#table_list').html(list.map((d, index) => {
            return `<tr>
            <th scope="row">${index + 1}</th>
            <td style="max-width:200px;">
                <div class="d-flex align-items-center flex-wrap">
                    <a href="#"
                        title="Joan Mini Camera Bag" class="me-2">
                        ${d?.product?.name}
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

const cancelSelectBatch = (_self, id, product_info_id) => {
    qrBatchScanned = qrBatchScanned.filter(item => item.id != id);
    products = products.map(item => item.id == product_info_id ? { ...item, scanned_count: item.scanned_count - 1 } : item)
    renderBatchProductInfo(products)
    setDisabledButtons();
    renderBatchScanned(qrBatchScanned);
    clearScanMessage();
    toastr.success('Đã bỏ chọn 1 lô');
}

const cancelSelectProduct = (_self, id, product_info_id) => {
    qrProductScanned = qrProductScanned.filter(item => item.id != id);
    products = products.map(item => item.id == product_info_id ? { ...item, scanned_count: item.scanned_count - 1 } : item)
    renderProductInfo(products)
    renderProductScanned(qrProductScanned)
    setDisabledButtons();
    clearScanMessage();
    toastr.success('Đã bỏ chọn 1 sản phẩm');
}

const handleResponseScanProduct = (res, code) => {
    const d = res.data;
    if (res?.success == 0) throw new Error(res?.message);
    if (qrProductScanned.find(item => item.id == d.id)) return showScanMessage('Sản phẩm đã quét. Vui lòng quét sản phẩm khác', 'danger');
    if (d?.status?.value != 'instock') return showScanMessage('Lô phải có trạng thái đã nhập kho', 'danger');

    const index = products?.findIndex((item =>
        item.scanned_count < item.quantity
        && item?.product_id == d?.reference_id
        && window.warehouse_id == d.warehouse_id
        && window.warehouse_type == d?.warehouse_type
    ));

    if (index > -1) {
        products[index].scanned_count += 1;
        qrProductScanned.push({ ...d, product_info_id: products[index].id });
        renderProductScanned(qrProductScanned)
        renderProductInfo(products);
        showScanMessage('Mã QR hợp lệ', 'success');
    } else showScanMessage('Mã QR không hợp lệ', 'danger');
}

const handleResponseScanBatch = (res, code) => {
    const d = res.data;

    if (res?.success == 0) throw new Error(res?.message);
    if (qrBatchScanned.find(item => item.id == d.id)) return showScanMessage('Lô đã quét. Vui lòng quét lô khác', 'warning');
    if (d?.status?.value != 'instock') return showScanMessage(`Không hợp lệ. Lô đang ở trạng thái <span class="fw-bolder">${d?.status?.label}</span>`, 'warning');
    if (d?.reference?.quantity == 0) return showScanMessage('Lô đã hết hàng', 'warning');

    const index = products?.findIndex((item =>
        item.scanned_count < item.quantity
        && item.product_id == d?.reference.product_parent_id
        && window.warehouse_id == d?.warehouse_id
        && window.warehouse_type == d?.warehouse_type
    ));

    if (index !== -1) {
        products[index].scanned_count += 1;
        qrBatchScanned.push({ ...d, product_info_id: products[index].id });
        renderBatchScanned(qrBatchScanned);
        renderBatchProductInfo(products);
        showScanMessage('Mã QR hợp lệ', 'success');
    } else showScanMessage('Lô khác loại sản phẩm hoặc khác kho!', 'warning');
}

const checkValidFormQr = () => {
    let valid = true;
    products.length > 0
        ? products.forEach(item => {
            if (item.scanned_count < item.quantity) valid = false;
        })
        : valid = false;
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
    if (checkValidFormQr()) $('#save_qr_scan_btn').removeAttr('disabled');
    else $('#save_qr_scan_btn').attr('disabled', true);
}

const handleReset = () => {
    qrProductScanned = [];
    qrBatchScanned = [];
    products = [];
    window.products?.forEach(item => {
        products.push({ ...item, id: Math.random().toString(16).slice(2), scanned_count: 0, batch: null });
    });
    scanType = $('#open_scan_modal').data('type');
    renderProductInfo(products);
    renderBatchProductInfo(products);
    setDisabledButtons();
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
        products.push({ ...item, id: Math.random().toString(16).slice(2), scanned_count: 0, batch: null });
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
                        scanType == 'product'
                            ? handleResponseScanProduct(res, code)
                            : handleResponseScanBatch(res, code)
                        setDisabledButtons();

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
        console.log('qrscan: ', qrBatchScanned);
        qrBatchScanned.forEach(batch_qr => {
            $('#qr_ids_wrapper').append(`
               <input type="hidden" name="batch_ids[]" value="${batch_qr.reference.id}" />
            `);
        });
        qrProductScanned.forEach(product_qr => {
            $('#qr_ids_wrapper').append(`
                <input type="hidden" name ="qr_ids[]" value="${product_qr.id}" />
            `);
        });
        $('#submit_btn').removeAttr('disabled');
        $('#warehouseIssueModal').modal('hide');
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
                const product_info_id = $(this).data('product-info-id');
                cancelSelectBatch($(this), id, product_info_id)
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
            console.log(product_info_id)

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

