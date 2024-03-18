import { createQRForBatch } from './general-api';
import Scanner from './library/scanner';
import Modal from './modal';
import ProductService from './services/product-service';
import BaseProductScan from './base-product-scan';
import { PRODUCT_MODEL, SHOWROOM_WAREHOUSE_MODEL } from './utils';

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

const _selfModal = '#scanProductToOrderShowroom';
const createBatchScanner = new Scanner();
const scan = new BaseProductScan();
const modal = new Modal(_selfModal);
createBatchScanner.stop();

let products = [];
let dataScanned = [];
let showroom_id = null;
let showroom_order_id = null;
let warehouseIds = [];

const renderDataInfo = () => {
    modal.renderDataInfo(products.map((item, index) => {
        return $(`<tr >
        <td style="max-width:200px;">
            <div class="d-flex align-items-center flex-wrap">
                ${item?.name}
            </div>
            <div>
                <small>${item?.attributes}</small>
            </div>
        </td>
        <td><p>${item.qty}</p></td>
        <td><p>${item.scanned_count}</p></td>
    </tr>`)
    }))
}

const renderDataScanned = () => {
    if (dataScanned.length > 0) {
        $('.body-scanned').html(dataScanned?.reverse()?.map((d, index) => {
            return $(`<tr >
            <th scope="row">${dataScanned.length - index}</th>
            <td style="max-width:200px;">
                <div class="d-flex align-items-center flex-wrap">
                    ${d?.reference?.name}
                </div>
                <div>
                    <small>${d?.time_create_q_r?.variation_attributes}</small>
                </div>
            </td>
            <td>${d?.warehouse?.name || '---'}</td>
            <td>${d?.status?.label || '---'}</td>
            <td class="text-center" style="width: 50px">
                <button data-id="${d?.id}" data-product-id="${d.reference.id}" data-bs-toggle="tooltip" title="Bỏ chọn sản phẩm này" class="btn btn-sm btn-icon btn-danger cancel_product_btn">
                    <i class="fa-solid fa-xmark"></i>
                    </button>
                </td>
            </td>
        </tr>`)
        }));
        $('#empty_scan_message').removeClass('d-flex').hide();
    } else {
        $('.body-scanned').html('');
        $('#empty_scan_message').show().addClass('d-flex');
    }
}

const handleResetForm = () => {
    products = window.orderProducts?.map(item => {
        return {
            product_id: item.product.id,
            name: item.product.name,
            image: item.options.image,
            attributes: item.options.attributes,
            qty: item.qty,
            scanned_count: 0
        }
    });
    dataScanned = [];
    renderDataInfo();
    renderDataScanned();
    setEnableSaveButton();
    modal.clearScanMessage();
    modal.renderDataScanned('');
    modal.showEmptyScanned();
    scan.start();
}

const handleScanned = (res) => {
    const qr = res.data;
    if (res?.success == 0) throw new Error(res?.message);

    if (qr.reference_type !== PRODUCT_MODEL) return modal.showScanMessage('Vui lòng quét mã QR sản phẩm', 'warning');

    if (
        qr.warehouse_type !== SHOWROOM_WAREHOUSE_MODEL ||
        !warehouseIds.find(id => id == qr.warehouse_id)
    ) return modal.showScanMessage('Sản phẩm không thuộc Showroom', 'warning');

    if (dataScanned.find(item => item.id == qr.id)) return modal.showScanMessage('Sản phẩm đã quét. Vui lòng quét mã khác', 'warning');
    if (qr.status.value !== 'instock') return modal.showScanMessage(`Không hợp lệ. Sản phẩm đã ở trạng thái <span class="fw-bold">${qr.status.label}</span>`, 'warning');

    let product = products.find(item => item.product_id == qr.reference.id);
    if (!product) return modal.showScanMessage('Sản phẩm không thuộc yêu cầu đặt hàng', 'warning');
    if (product.qty == product.scanned_count) return modal.showScanMessage(`Sản phẩm <span class="fw-bold">${product.name}</span> đã quét đủ số lượng`, 'warning');

    product.scanned_count += 1;
    products = products.map(item => item.product_id == product.product_id ? product : item);
    dataScanned.push(qr);

    renderDataInfo();
    renderDataScanned(dataScanned);
    modal.showScanMessage('Mã hợp lệ', 'success');
    setEnableSaveButton();
    return $('#table_wrapper').animate({ scrollTop: 99999 })

}

const validateForm = () => {
    return !products.find(item => item.qty != item.scanned_count);
}

const setEnableSaveButton = () => {
    if (validateForm()) {
        $('#save_qr_scan_btn').removeAttr('disabled');
        modal.showScanMessage('Vui lòng bấm lưu để hoàn thành', 'success');
        scan.stop();
    } else {
        $('#save_qr_scan_btn').attr('disabled', true);
        scan.start();
    }
}

document.addEventListener("DOMContentLoaded", () => {
    warehouseIds = window.warehouseIds;
    showroom_order_id = window.showroomOrder.id;
    console.log(warehouseIds, showroom_order_id)

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

    $("body").on('click', '.show_scan_info_btn', function () {
        $(_selfModal).modal('show');
    })

    $("body").on('shown.bs.modal', _selfModal, function () {
        products = window.orderProducts?.map(item => {
            return {
                product_id: item.product.id,
                name: item.product.name,
                image: item.options.image,
                attributes: item.options.attributes,
                qty: item.qty,
                scanned_count: 0
            }
        });

        console.log(products)
        renderDataInfo(products);
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

    $('body').on('click', '#reset_batch_scan_btn', function () {
        if (confirm('Đồng ý reset form')) handleResetForm();
    })

    $('body').on('click', '.cancel_product_btn', function () {
        if (confirm('Bỏ chọn sản phẩm này?')) {
            modal.clearScanMessage();
            const id = $(this).data('id');
            const product_id = $(this).data('product-id');
            dataScanned = dataScanned.filter(item => item?.id != id);

            let product = products.find(item => item.product_id == product_id);
            if (product) product.scanned_count -= 1;
            products = products.map(item => item.product_id == product_id ? product : item);

            renderDataInfo();
            renderDataScanned();
            setEnableSaveButton();
        }
    })

    $('body').on('click', '#save_qr_scan_btn', function () {
        const qr_ids = dataScanned.map(item => item.id);
        console.log(qr_ids);
        $(this).attr('disabled', true);
        $.ajax({
            url: `/admin/showroom-orders/add-qr`,
            method: 'POST',
            data: { qr_ids, showroom_order_id },
            success: res => {
                $(this).removeAttr('disabled');
                if (!res.error) {
                    $(_selfModal).modal('hide');
                    $('#main-order-content').load(
                        `${window.location.href} #main-order-content > *`)
                    toastr.success(res.message)
                }
            }
        })
    })
});