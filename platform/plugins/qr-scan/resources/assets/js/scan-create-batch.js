import { createQRForBatch } from './general-api';
import Scanner from './library/scanner';
import Modal from './modal';
import ProductService from './services/product-service';
import BaseProductScan from './base-product-scan';
import { PRODUCT_MODEL } from './utils';

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
const _selfModal = '#QrScanReceiveModal';
const createBatchScanner = new Scanner();
const scan = new BaseProductScan();
const modal = new Modal('#QrScanReceiveModal');
createBatchScanner.stop();

let isScanning = false;
let dataScanned = [];
let productType = null;
let products = [];
let product_parent_id_selected = null;


const renderDataScanned = () => {
    if (dataScanned.length > 0) {
        modal.renderDataScanned(dataScanned?.reverse()?.map((d, index) => {
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
            <td><p>${d?.status?.label}</p></td>
            <td>${d?.warehouse?.name || '---'}</td>
            <td class="text-center" style="width: 50px">
                <button data-id="${d.id}" data-product-id="${d.reference_id}" data-bs-toggle="tooltip" title="Bỏ chọn sản phẩm này" class="btn btn-sm btn-icon btn-danger cancel_product_scanned_btn">
                <i class="fa-solid fa-xmark"></i>
                </button>
            </td>
        </tr>`)
        }))
        return modal.hideEmptyScanned();
    }
    modal.renderDataScanned('');
    modal.showEmptyScanned();
}

const renderDataInfo = (data) => {
    modal.renderDataInfo(data?.map((item, index) => {
        return `<tr >
        <th scope="row">${index + 1}</th>
        <td style="width: 45%">
            <div class="d-flex align-items-center flex-wrap">
                <span href="#" title=" ${item?.product_name}" class="me-2">
                    ${item?.product_name}
                    <div>
                       <small>(Color: ${item?.color}, Size: ${item.size})</small>
                    </div>
                </span>
                <div>
            </div>
            </div>
        </td>
        <td>
        ${item?.curr_quantity || 0}
        </td>
        <td>${item?.scanned_count || 0}</td>
    </tr>`;
    }))
}

const renderProductTags = () => {
    const data = window.products.map(item => {
        const curr_quantity = $(`li[data-id="${item.product_id}"] input[class="current_quantity"]`).attr('data-quantity') || 0;
        const product_parent_id = $(`li[data-id="${item.product_id}"] input[class="slt_parent_id"]`).val() || null;
        return ({ ...item, curr_quantity, product_parent_id: product_parent_id, scanned_count: 0 });
    }).filter(item => item.curr_quantity > 0);

    const dataGroupByParentId = data.reduce((accumulator, currentItem) => {
        const { product_parent_id } = currentItem;
        if (!accumulator[product_parent_id]) {
            accumulator[product_parent_id] = currentItem;
        } else {
            const accumulatorQuantity = parseInt(accumulator[product_parent_id].curr_quantity);
            accumulator[product_parent_id].curr_quantity = accumulatorQuantity + parseInt(currentItem.curr_quantity);
        }
        return accumulator;
    }, {});

    const objectValue = Object.values(dataGroupByParentId);

    $(`#product_info_list`).html(objectValue.map(item => {
        return `<span class="d-flex align-items-center gap-2 badge p-2 ${product_parent_id_selected == item.product_parent_id ? 'bg-primary text-white' : 'bg-light'}" >
            ${item.product_name}
            <span class="badge" style="background-color:#d1d5db" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Số lượng đề xuất">
                ${item.curr_quantity}
           </span>
        </span>`
    }));
}

const setToggleProductByBatch = () => {
    if (product_parent_id_selected) $('#product_by_batch_box').show();
    else $('#product_by_batch_box').hide();
}

const validationForm = () => {
    let valid = false;
    products.length > 0
        ? products.forEach(item => {
            if (item.scanned_count > 0) valid = true;
        })
        : valid = false;
    return valid;
}

const initProducts = () => {

    products = window.products.map(item => {
        const curr_quantity = $(`li[data-id="${item.product_id}"] input[class="current_quantity"]`).attr('data-quantity') || 0;
        const product_parent_id = $(`li[data-id="${item.product_id}"] input[class="slt_parent_id"]`).val() || null;
        return ({ ...item, curr_quantity, product_parent_id: product_parent_id, scanned_count: 0 });
    }).filter(item => item.curr_quantity > 0);
}

const handleResetForm = () => {
    initProducts();
    dataScanned = [];
    // renderProductTags();
    renderDataScanned(products);
    // setToggleProductByBatch();
    renderDataInfo(products);
    modal.clearScanMessage();
    modal.setDisabledButtons(validationForm());
    modal.renderDataScanned('');
    modal.showEmptyScanned();
}

const handleScanned = (res) => {
    const qr = res.data;
    if (res?.success == 0) throw new Error(res?.message);

    if (qr?.reference_type !== PRODUCT_MODEL) return modal.showScanMessage('Vui lòng quét sản phẩm', 'warning');
    let statusCheck = productType == 'isInventory' ? 'created' : 'pendingstock';

    if (qr?.status?.value == statusCheck) {
        let product = products?.find(item =>
            item?.product_id == qr?.reference_id
            && (productType != 'isInventory' ? item.qr_ids.find(item => item == qr.id) : true)
        );

        // if (!product_parent_id_selected && !qr.reference.parent_product.find(parent => products.find(item => item.product_parent_id == parent.id))) {
        //     return modal.showScanMessage('Sản phẩm không nằm trong phiếu đề xuất nhập kho', 'warning');
        // }

        // if (product_parent_id_selected && !qr.reference.parent_product.find(parent => parent.id == product_parent_id_selected)) {
        //     return modal.showScanMessage('Vui lòng quét sản phẩm cùng loại để tạo lô', 'warning');
        // }

        if (product) {
            if (product.scanned_count == product.curr_quantity)
                return modal.showScanMessage(`sản phẩm <span class="fw-bolder">${product.product_name}</span> đã quét đủ số lượng`, 'warning');
            if (dataScanned.find(item => item.id == qr.id))
                return modal.showScanMessage(`Sản phẩm đã được quét. Vui lòng quét sản phẩm khác`, 'warning');

            dataScanned.push(qr);
            product.scanned_count += 1;
            products = products
                .map(p => p.product_id == product.product_id ? product : p);

            // if (product_parent_id_selected == null) {
            //     product_parent_id_selected = qr.reference.parent_product[0].id;
            // }
            // renderProductTags();
            renderDataInfo(products);
            renderDataScanned(dataScanned);
            // setToggleProductByBatch();
            modal.setDisabledButtons(validationForm());
            return modal.showScanMessage('Mã hợp lệ', 'success');
        } else return modal.showScanMessage('Sản phẩm không nằm trong phiếu đề xuất nhập kho', 'danger');
    }

    return modal.showScanMessage(`Không hợp lệ. Sản phẩm đang ở trạng thái <span class="fw-bolder">${qr?.status?.label}</span>`, 'danger')
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

    $("body").on('shown.bs.modal', _selfModal, function () {

        // renderProductTags()
        initProducts();
        renderDataInfo(products);
        products.forEach(item => {
            if (item.qr_ids == undefined) productType = 'isInventory'
        });
        scan.start();

    }).on('hide.bs.modal', _selfModal, function (e) {
        scan.stop();
        // handleResetForm();
    });

    $(document).off('click');
    $(document).on('click', `${_selfModal} #save_qr_scan_btn, ${_selfModal} #btn_create_batch_and_receipt`, function () {
        // Vô hiệu hóa nút sau khi được nhấp
        $(this).prop('disabled', true);

        // Vô hiệu hóa tất cả các nút khác
        $(`${_selfModal} #save_qr_scan_btn, ${_selfModal} #btn_create_batch_and_receipt`).not(this).prop('disabled', true);

        // Đảm bảo rằng các nút khác không thể nhấp khi một nút đã được nhấp
        $(`${_selfModal} #save_qr_scan_btn, ${_selfModal} #btn_create_batch_and_receipt`).off('click');

        // Logic xử lý khi nhấp vào nút đã được chọn
        let dataQr = [];
        for (const itemData in dataScanned) {
            dataQr.push(dataScanned[itemData].id);
        }
        const formData = {
            list_id: dataQr,
            parent_id: product_parent_id_selected
        }

        createQRForBatch($(this), formData, [], 'right');
        $(`${_selfModal} #submit_btn`).show();
        $(`${_selfModal} #QrScanReceiveModal`).modal('hide');
        dataScanned = []
        renderDataScanned()
    });


    $('body').on('click', '.cancel_product_scanned_btn', function () {
        if (confirm('Đồng ý bỏ chọn sản phẩm này?')) {
            const id = $(this).data('id');
            const product_id = $(this).data('product-id');
            dataScanned = dataScanned.filter(item => item.id != id);
            products = products.map(item => item.product_id == product_id
                ? { ...item, scanned_count: item.scanned_count - 1 }
                : item
            );
            if (dataScanned.length == 0) product_parent_id_selected = null;

            renderDataInfo(products);
            renderDataScanned(dataScanned);
            // setToggleProductByBatch();

            // renderProductTags();
            modal.setDisabledButtons(validationForm());
            modal.clearScanMessage();
            return toastr.success('Đã bỏ chọn 1 sản phẩm');
        }
    })

    $('body').on('click', `${_selfModal} button[name="reset"]`, function () {
        if (dataScanned.length > 0 && confirm('Đồng ý reset form? Dữ liệu vừa quét sẽ bị mất')) {
            handleResetForm();
            toastr.success('Reset form thành công')
        }
    })
});
