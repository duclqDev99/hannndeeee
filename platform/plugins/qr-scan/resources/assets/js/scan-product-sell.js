import BaseProductScan from "./base-product-scan";
import Modal from './modal';

const _selfModal = "#scanProductSelfModal";

const scan = new BaseProductScan();
const modal = new Modal(_selfModal);
let dataScanned = [];
let productIdsInStock = [];

const renderDataScanned = () => {
    let total = 0;
    dataScanned.forEach((val)=>{
        total+= val.reference.front_sale_price;
    });
    $('#text-total-order').text(formatCurrency(total || 0, 'vi-VN', 'VND'))

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
            <td><p>${ formatCurrency(d?.reference.original_price || 0, 'vi-VN', 'VND')}</p></td>
            <td><p>${ formatCurrency(d?.reference.front_sale_price || 0, 'vi-VN', 'VND')}</p></td>
            <td><p>${d?.status?.label}</p></td>
            <td>${d?.warehouse?.name || '---'}</td>
            <td class="text-center" style="width: 50px">
                <button data-id="${d.id}" data-product-id="${d.product_id}" data-bs-toggle="tooltip" title="Bỏ chọn sản phẩm này" class="btn btn-sm btn-icon btn-danger cancel_product_scanned_btn">
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


const setDisabledResetButton = (condition) => {
    if (condition) $(`${_selfModal} button[name="reset"]`).removeAttr('disabled');
    else $(`${_selfModal} button[name="reset"]`).attr('disabled', true);
}

const validateForm = () => {
    return dataScanned.length > 0;
}


const handleScanned = (res) => {
    const qr = res.data;
    let agent_id = $('#select-order-agent').val();
    console.log(agent_id)
    let url = $('#data-warehouse').attr('data-bs-target');
    if (qr.status.value != 'instock') {
        return modal.showScanMessage(`Không hợp lệ. Sản phẩm đang ở trạng thái <span class="fw-bolder">${qr?.status?.label}</span>`, 'danger');
    }
    if (dataScanned.find(item => item.id == qr.id))
        return modal.showScanMessage('Mã đã quét. Vui lòng quét sản phẩm khác', 'warning');
    else {
    }
    $.ajax({
        type: 'POST',
        url: url,
        data: {
            warehouse_id : res.data.warehouse_id,
            warehouse_type : res.data.warehouse_type,
            product_id : res.data.id,
            agent_id,
        },
        success: function success(res) {
            if(res.error){
                return modal.showScanMessage(res.message, 'danger');
            }else{
                const index = dataScanned.findIndex(item => item.product_id == qr.product_id);
                dataScanned.push({ ...qr, quantity: 1 })
                // if (index !== -1) {
                //     dataScanned[index].quantity += 1;
                // } else dataScanned.push({ ...qr, quantity: 1 });
                modal.setDisabledButtons(validateForm())
                setDisabledResetButton(validateForm())
                renderDataScanned();
                return modal.showScanMessage(res.message, 'success');
            }
        },
        error: function error(res) {
            console.log('error',res);
        }
    });
}

function formatCurrency(value, locale = 'en-US', currency = 'USD') {
    return new Intl.NumberFormat(locale, {
        style: 'currency',
        currency: currency,
    }).format(value);
}

const handleReset = () => {
    dataScanned = [];
    renderDataScanned();
    modal.clearScanMessage();
    setDisabledResetButton(validateForm())
    modal.setDisabledButtons(validateForm());
}

scan
    .onScan(code => {
        modal.openScannerLoading();
        modal.clearScanMessage();
    }).onFetching(res => {
        modal.closeScannerLoading();
        handleScanned(res)
    }).onScanError(err => {
        modal.closeScannerLoading();
        modal.showScanMessage(err.message, 'danger')
    })

document.addEventListener('DOMContentLoaded', function () {
    // Set danh sách product id vào đây
    // Quét sẽ check trong đây
    productIdsInStock = [];

    $(document).on('click', '.cancel_product_scanned_btn', function () {
        if (confirm('Đồng ý bỏ chọn sản phẩm này?')) {
            const id = $(this).data('id');

            dataScanned = dataScanned.filter(item => item.id != id);
            renderDataScanned(dataScanned);
            modal.clearScanMessage();
            modal.setDisabledButtons(validateForm())
            setDisabledResetButton(validateForm())
            return toastr.success('Đã bỏ chọn 1 sản phẩm');
        }
    })

    $(document).on('click', '#reset_batch_scan_btn', function () {
        if (confirm('Đồng ý reset? Dữ liệu vừa quét sẽ bị mất')) {
            handleReset();
        }
    })

    $('#select-order-agent').on('change', function(val){
        handleReset();
        // return toastr.success('Đã bỏ chọn 1 sản phẩm');
    })

    $(document).on('click', `${_selfModal} button[name="save"]`, function () {
        let url = $('#data-warehouse').attr('data-bs-toggle');
        let agent_id = $('#select-order-agent').val();
        let total = 0;
        dataScanned.forEach((val)=>{
            total+= val.reference.front_sale_price;
        });
        const qr_ids = dataScanned.map(qr => qr.id);
        $.ajax({
            type: 'POST',
            url: url,
            data: {total,qr_ids,agent_id},
            success: function success(res) {
                if(res.error){
                    return modal.showScanMessage(res.message, 'danger');
                }else{

                }
            },
            error: function error(res) {
                console.log('error',res);
            }
        });

        handleReset();
        return toastr.success(`Đã cập nhật ${qr_ids.length} sản phẩm`)
    })
})


