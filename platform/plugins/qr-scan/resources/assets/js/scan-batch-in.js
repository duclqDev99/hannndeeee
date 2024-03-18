import Scanner from './library/scanner';
import { BATCH_MODEL} from './utils';
console.log(122221312);
const _selfModal = '#scanBatchInModal';
let isScanning = false;
let batches = [];
let qrBatchScanned = [];

toastr.options = {
    closeButton: true,
    positionClass: 'toast-bottom-right',
    showDuration: 500,
    hideDuration: 500,
    timeOut: 10000,
    extendedTimeOut: 1000,
    showEasing: 'swing',
    hideEasing: 'linear',
    showMethod: 'fadeIn',
    hideMethod: 'fadeOut',
}

const renderBatches = (data) => {
    $(`${_selfModal} #batch_table_list`).html(data?.map((item, index) => {
        return `<tr>
        <th scope="row">${index + 1}</th>
        <td>${item.batch_code}</td>
        <td> ${window.warehouse.wh_departure_name} </td>
        <td >
        ${item.is_scan
                ? '<span class="text-success"> Đã quét</span>'
                : '<span class="text-warning"> Chưa quét</span>'
            }
        </td>
        <td class="text-center">
            <button data-batch-id="${item.id}" data-bs-toggle="tooltip" title="Xem sản phẩm trong lô" class="btn btn-sm btn-icon btn-primary show_batch_info_btn">
                <i class="fa-regular fa-eye"></i>
            </button>
        </td>
    </tr>`;
    }));
}

const openScannerLoading = () => {
    isScanning = true;
    $(`${_selfModal} #scanner_box`).removeClass('d-flex').addClass('d-none');
    $(`${_selfModal} #scanner_box_loading`).removeClass('d-none').addClass('d-flex');
}

const closeScannerLoading = () => {
    isScanning = false;
    $(`${_selfModal} #scanner_box`).removeClass('d-none').addClass('d-flex');
    $(`${_selfModal} #scanner_box_loading`).removeClass('d-flex').addClass('d-none');
}

const playSound = (fileName) => {
    if (!fileName) return;
    const audio = document.createElement('audio');
    audio.src = `/storage/scan-audio/${fileName}.mp3`;
    audio?.play();
    audio?.remove();
}

const checkValidFormQr = () => {
    let valid = true;

    batches.length > 0
        ? batches.forEach(item => {
            if (!item.is_scan) valid = false;
        })
        : valid = false;

    return valid;
}

const setDisabledButtons = () => {
    const valid = checkValidFormQr();
    if (valid) {
        $(`${_selfModal} #save_qr_scan_btn`).removeAttr('disabled');
        $(`${_selfModal} #btn_create_batch_and_receipt`).removeAttr('disabled');
    } else {
        $(`${_selfModal} #save_qr_scan_btn`).attr('disabled', true);
        $(`${_selfModal} #btn_create_batch_and_receipt`).attr('disabled', true);
    }
}

const handleResetForm = () => {
    qrBatchScanned = [];
    batches = window.batches.map(item => ({ ...item, is_scan: false }))
    renderBatches(batches);
    setDisabledButtons();
    clearScanMessage();
    $(`${_selfModal} #qr_ids_wrapper`).html('');
    $(`#submit_btn`).attr('disabled', true);
}

const showScanMessage = (message, type) => {
    $(`${_selfModal} #scanner_message`).html(`
        <div role = "alert" class="alert alert-${type} mt-3 mb-0" >
            <div class="d-flex">
                <div class="w-100">
                    ${message}
                </div>
            </div>
         </div > `)
        .show();
}

const clearScanMessage = () => {
    $(`${_selfModal} #scanner_message`).html('');
}

const handleScanned = (res) => {
    if (res?.success == 0) throw new Error(res?.message);
    const qr = res.data;

    if(qr.reference_type != BATCH_MODEL) return modal.showScanMessage('Vui lòng quét lô sản phẩm', 'warning');

    if (qr.status.value == 'pendingstock') {
        const batchIndex = batches.findIndex(batch =>
            batch.id == qr.reference.id
            && window.wh_departure_id == qr.reference.warehouse_id
        );

        if (batchIndex == -1) return showScanMessage('Mã không hợp lệ', 'danger');
        if (batches[batchIndex].is_scan) return showScanMessage('Mã đã quét', 'warning');

        if (batchIndex > -1) {
            qrBatchScanned.push(qr)
            batches[batchIndex].is_scan = true;
            renderBatches(batches)
            setDisabledButtons();
            return showScanMessage('Mã QR hợp lệ', 'success');
        }else return showScanMessage('Mã QR không hợp lệ', 'success');

    }

    switch (qr?.status?.value) {
        case 'instock': showScanMessage('Không hợp lệ. Sản phẩm đang ở trạng thái đã nhập kho', 'danger'); break;
        case 'created': showScanMessage('Không hợp lệ. Sản phẩm đang ở trạng thái đã tạo', 'danger'); break;
        case 'sold': showScanMessage('Không hợp lệ. Sản phẩm đang ở trạng thái đã bán', 'danger'); break;
        case 'cancelled': showScanMessage('Không hợp lệ. Sản phẩm đang ở trạng thái đã hủy', 'danger'); break;
    }
}

const Scan = new Scanner();
Scan.stop();

Scan.onScan(code => {
    try {
        if (isScanning == false) {
            clearScanMessage();
            if (checkValidFormQr()) return toastr.warning('Đã quét đủ số lượng lô')
            openScannerLoading();

            $.ajax({
                url: '/admin/receipt-product/ajax-post-qr-scan',
                method: 'POST',
                data: { qr_code: code },
                success: (res) => {
                    closeScannerLoading();
                    handleScanned(res)
                },
                error: (res) => {
                    closeScannerLoading();
                    const result = res.responseJSON;
                    showScanMessage(result.message, 'danger')
                }
            })
        } else {
            toastr?.warning('Quét quá nhanh. Vui lòng chờ phản hồi từ hệ thống!');
        }
    } catch (err) {
        closeScannerLoading();
        showScanMessage(err?.message, 'danger')
    }
})


document.addEventListener("DOMContentLoaded", (event) => {

    batches = window.batches.map(item => ({ ...item, is_scan: false }));
    renderBatches(batches);

    $("body")
        .on('shown.bs.modal', _selfModal, () => Scan.start())
        .on('hide.bs.modal', _selfModal, () => Scan.stop());

    $("body").on('click', `${_selfModal} .show_batch_info_btn`, function () {
        const batch_id = $(this).data('batch-id');
        $(`${_selfModal} #box_scan`).hide();
        $(`${_selfModal} #box_batch_info`).show();
        $(`${_selfModal} #box_batch_info`).children('#content').html('');

        $.ajax({
            url: '/admin/receipt-product/get-batch-info',
            method: 'POST',
            data: {
                batch_id
            },
            dataType: 'html',
            success: (res) => {
                $(`${_selfModal} #box_batch_info`).children('#content').html(res);
            }
        })
    });

    //Back lại màn hình quét
    $("body").on('click', `${_selfModal} #back_box_btn`, function () {
        $(`${_selfModal} #box_batch_info`).hide();
        $(`${_selfModal} #box_scan`).show();
    })

    $("body").on('click', `${_selfModal} #save_qr_scan_btn`, function () {

        $(`#qr_ids_wrapper`).html(qrBatchScanned.map(qr => {
            return `<input type="hidden" name="batch_ids[]" value="${qr?.reference?.id}">`
        }))
        $(`#submit_btn`).removeAttr('disabled');
        $(_selfModal).modal('hide');
    })

    $("body").on('click', `${_selfModal} #btn_create_batch_and_receipt`, function () {

        $(`#qr_ids_wrapper`).html(qrBatchScanned.map(qr => {
            return `<input type="hidden" name="batch_ids[]" value="${qr?.reference?.id}">`
        }))
        $(`#submit_btn`).removeAttr('disabled');
        $(_selfModal).modal('hide');
    })

    $("body").on('click', `${_selfModal} #reset_batch_scan_btn`, function () {
        if (qrBatchScanned.length > 0 && confirm('Đồng ý reset form?')) {
            handleResetForm();
        }
    });
});
