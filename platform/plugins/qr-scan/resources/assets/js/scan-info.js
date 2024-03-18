import { createQRForBatch } from './general-api';
import Scanner from './library/scanner';
import Modal from './modal';
import ProductService from './services/product-service';
import BaseProductScan from './base-product-scan';
import { PRODUCT_MODEL, BATCH_MODEL } from './utils';

toastr.options = {
    closeButton: true,
    positionClass: 'toast-bottom-right',
    showDuration: 500,
    hideDuration: 500,
    timeOut: 500,
    extendedTimeOut: 500,
    showEasing: 'swing',
    hideEasing: 'linear',
    showMethod: 'fadeIn',
    hideMethod: 'fadeOut',
}
const _selfModal = '#scanInfo';
const createBatchScanner = new Scanner();
const scan = new BaseProductScan();
const modal = new Modal(_selfModal);
createBatchScanner.stop();

let dataScanned = [];

const renderDataScanned = () => {
    console.log(dataScanned);
    if (dataScanned.length > 0) {
        modal.renderDataScanned(dataScanned?.reverse()?.map((d, index) => {
            switch(d.reference_type){
                case PRODUCT_MODEL:
                    return $(`<tr >
                    <th scope="row">${dataScanned.length - index}</th>
                    <th scope="row">Sản phẩm lẻ</th>
                    <td style="max-width:200px;">
                        <div class="d-flex align-items-center flex-wrap">
                            ${d?.reference?.name}
                        </div>
                        <div>
                            <small>${d?.time_create_q_r?.variation_attributes}</small>
                        </div>
                    </td>
                    <td><p>${d?.status?.label}</p></td>
                    <td>${d?.warehouse?.name || '---'}</td>
                    <td><p>${d?.reference?.production_time}</p></td>
                    <td class="text-center" style="width: 50px"></td>
                </tr>`)
                case BATCH_MODEL:
                    return $(`<tr >
                    <th scope="row">${dataScanned.length - index}</th>
                    <th scope="row">Lô hàng</th>
                    <td style="max-width:200px;">
                        <div class="d-flex align-items-center flex-wrap">
                            ${d?.reference?.product?.name}
                        </div>
                        <div>
                            <small>${""}</small>
                        </div>
                    </td>
                    <td><p>${d?.status?.label}</p></td>
                    <td>${d?.warehouse?.name || '---'}</td>
                    <td class="text-center">
                        <button data-batch-id="${d?.reference?.id}" data-bs-toggle="tooltip" title="Xem lô sản phẩm" class="btn btn-sm btn-icon btn-primary show_batch_info_btn">
                            <i class="fa-regular fa-eye"></i>
                        </button>
                </td>
                </tr>`)
            }
        }))
        return modal.hideEmptyScanned();
    }
    modal.renderDataScanned('');
    modal.showEmptyScanned();
}

const handleResetForm = () => {
    dataScanned = [];
    modal.clearScanMessage();
    modal.renderDataScanned('');
    modal.showEmptyScanned();
}

const handleScanned = (res) => {
    const qr = res.data;
    if (res?.success == 0) throw new Error(res?.message);
    if(dataScanned.find(item =>item.id == qr.id)) return modal.showScanMessage('Mã đã quét. Vui lòng quét mã khác' , 'warning');
    dataScanned.push(qr);
    renderDataScanned(dataScanned);
    return modal.showScanMessage('Mã hợp lệ', 'success');
}

document.addEventListener("DOMContentLoaded", () => {
    scan.stop();
    scan.onScan(code => {
        if (!scan.isFetching) {
            modal.clearScanMessage();
            modal.openScannerLoading();
        } else toastr?.warning('Quét quá nhanh. Vui lòng chờ phản hồi từ hệ thống!');
    }).onFetching(res => {
        modal.closeScannerLoading();
        handleScanned(res)
    }).onScanError(err => {
        modal.closeScannerLoading();
        modal.showScanMessage(err.message, 'danger');
    });

    $("body").on('click', '.show_scan_info_btn', function(){
        $(_selfModal).modal('show');
    })

    $("body").on('shown.bs.modal', _selfModal, function () {
        scan.start();
    }).on('hide.bs.modal', _selfModal, function (e) {
        scan.stop();
        handleResetForm();
    });

    $("body").on('click', `${_selfModal} .show_batch_info_btn`, function () {
        const batch_id = $(this).data('batch-id');
        $(`${_selfModal} #box_scan`).hide();
        $(`${_selfModal} #box_batch_info`).show();
        $(`${_selfModal} #box_batch_info`).children('#content').html('');
        modal.clearScanMessage();
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
        });
    });

    $("body").on('click', `${_selfModal} #back_box_btn`, function () {
        $(`${_selfModal} #box_batch_info`).hide();
        $(`${_selfModal} #box_scan`).show();
    });

    $('body').on('click', `${_selfModal} button[name="reset"]`, function () {
        if (dataScanned.length > 0 && confirm('Đồng ý reset form? Dữ liệu vừa quét sẽ bị mất')) {
            $(`${_selfModal} #box_batch_info`).hide();
            $(`${_selfModal} #box_scan`).show();
            handleResetForm();
            toastr.success('Reset form thành công');
        }
    })
});
