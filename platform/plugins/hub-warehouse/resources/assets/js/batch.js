import { showListProductExist } from '../../../../qr-scan/resources/assets/js/general-api';
import { getStartQuantityProductById } from '../../../../qr-scan/resources/assets/js/general-api';
import { setCurrentQuantityProductById } from '../../../../qr-scan/resources/assets/js/general-api';
import { setCurrentQuantityProductIntoCreatedShipment } from '../../../../qr-scan/resources/assets/js/general-api';

let ARR_QTY_PRODUCT_CREATED_BATCH = [];

class WidgetManagement {
    init() {
        let listWidgets = [
            {
                name: 'wrap-widgets',
                pull: 'clone',
                put: false,
            },
        ]

        $.each($('.sidebar-item'), () => {
            listWidgets.push({ name: 'wrap-widgets', pull: true, put: true })
        })

        let saveWidget = (parentElement) => {
            if (parentElement.length > 0) {
                let items = []
                $.each(parentElement.find('li[data-id]'), (index, widget) => {
                    items.push($(widget).find('form').serialize())
                })

                // $httpClient
                //     .make()
                //     .post(BWidget.routes.save_widgets_sidebar, {
                //         items: items,
                //         sidebar_id: parentElement.data('id'),
                //     })
                //     .then(({ data }) => {
                //         parentElement.find('ul').html(data.data)
                //         Botble.callScroll($('.list-page-select-widget'))
                //         Botble.initResources()
                //         Botble.initMediaIntegrate()
                //         Botble.showSuccess(data.message)
                //     })
                //     .finally(() => {
                //         parentElement.find('.widget-save i').remove()
                //     })
            }
        }

        listWidgets.forEach((groupOpts, i) => {
            Sortable.create(document.getElementById('wrap-widget-' + (i + 1)), {
                sort: i !== 0,
                group: groupOpts,
                filter: '.filtered', // 'filtered' class is not draggable
                delay: 0, // time in milliseconds to define when the sorting should start
                disabled: false, // Disables the sortable if set to true.
                store: null, // @see Store
                animation: 150, // ms, animation speed moving items when sorting, `0` — without animation
                handle: '.card-header',
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
                onUpdate: (evt) => {
                    if (evt.from !== evt.to) {
                        saveWidget($(evt.from).closest('.sidebar-item'))
                    }
                    saveWidget($(evt.item).closest('.sidebar-item'))
                    addDropzoneWhenUpdateBatch(evt);
                    checkItemInBatchScanQR();
                },
                onAdd: (evt) => {
                    if (evt.from !== evt.to) {
                        saveWidget($(evt.from).closest('.sidebar-item'))
                    }
                    saveWidget($(evt.item).closest('.sidebar-item'));

                    if ($(evt.to).find('li.widget-item').length > 0) {
                        $(evt.to).find('li.dropzone').remove()
                    }
                    addDropzoneWhenUpdateBatch(evt);
                    checkItemInBatchScanQR();
                },
            })
        })

        $('#wrap-widgets')
            .on('click', '.widget-control-delete', (event) => {
                event.preventDefault()
                let _self = $(event.currentTarget)

                let widget = _self.closest('li')
                let widgetParent = _self.closest('ul')

                let productId = widget.data('id');

                Botble.showButtonLoading(_self)

                $httpClient
                    .make()
                    .delete('/admin/receipt-product/delete-widget', {
                        sidebar_id: _self.closest('.sidebar-item').data('id'),
                    })
                    .then(({ data }) => {
                        Botble.showSuccess('Xoá sản phẩm khỏi lô thành công!!')
                        // _self.closest('.sidebar-item').find('ul').html(data.data)
                        widget.remove();
                        if ($(widgetParent).find('li').length === 0) {
                            $(widgetParent).html(`<li class="dropzone px-1 py-3 text-center">
                            <div class="dz-default dz-message">Kéo và thả sản phẩm vào đây!</div>
                            </li>
                            `)
                        }
                        checkItemInBatchScanQR();

                        let typeBatch = '';

                        const option_sample = document.querySelector('.option_sample');
                        if (option_sample) {
                            const optionCustom = option_sample.querySelector('#custom');
                            const optionSample = option_sample.querySelector('#sample');

                            if (optionCustom.checked) {
                                typeBatch = 'custom';
                            } else if (optionSample.checked) {
                                typeBatch = 'sample';
                            }
                        }
                        setCurrentQuantityProductById(typeBatch);
                    })
                    .finally(() => {
                        Botble.showButtonLoading(widget.find('.widget-control-delete'))
                    })
            })
            .on('click', '.widget-item .card-header', (event) => {
                let _self = $(event.currentTarget)

                if ($(_self).closest('ul').attr('id') != 'wrap-widget-1') {
                    _self.closest('.widget-item').find('.widget-content').slideToggle(300)
                    if (_self.find('.ti').hasClass('ti-chevron-up')) {
                        setTimeout(function () {
                            _self.closest('.card').toggleClass('card-no-border-bottom-radius')
                        }, 300)
                    } else {
                        _self.closest('.card').toggleClass('card-no-border-bottom-radius')
                    }
                }
                _self.find('.ti').toggleClass('ti-chevron-down').toggleClass('ti-chevron-up')
            })
            .on('click', '.sidebar-item .card-header .button-sidebar.btn-action', (event) => {
                let _self = $(event.currentTarget)
                _self.closest('.card').find('.card-body').slideToggle(300)
                _self.find('.ti').toggleClass('ti-chevron-down').toggleClass('ti-chevron-up')
            })
            .on('click', '.btn-remove-batch', (event) => {
                Botble.showButtonLoading(_self)
                let _self = $(event.currentTarget)
                _self.closest('.sidebar-item').remove();
                Botble.showButtonLoading(_self.closest('.sidebar-item').find('.widget-control-delete'))
            })
            .on('click', '.widget-save', (event) => {
                event.preventDefault()
                let _self = $(event.currentTarget)
                Botble.showButtonLoading(_self)
                saveWidget(_self.closest('.sidebar-item'))
            })
    }
}

function addDropzoneWhenUpdateBatch(evt) {
    let count = 0;
    let countProductParent = 0;
    let countProductParentColor = 0;

    $(evt.to).find('li').each(function (index, item) {
        if ($(evt.item).attr('data-id') == $(item).attr('data-id')) {
            if (count > 0) {
                $(evt.item).remove();
            } else {
                count++;
            }
        }

        toastr.options = {
            closeButton: true,
            progressBar: true,
            positionClass: 'toast-bottom-right',
            onclick: null,
            showDuration: 1000,
            hideDuration: 1000,
            timeOut: 10000,
            extendedTimeOut: 1000,
            showEasing: 'swing',
            hideEasing: 'linear',
            showMethod: 'fadeIn',
            hideMethod: 'fadeOut',
        }

        if ($(evt.item).find('input.slt_parent_id').val() != $(item).find('input.slt_parent_id').val()) {
            $(evt.item).remove();
            if (countProductParent === 0) {
                toastr['error']('Các sản phẩm trong lô bắt buộc phải cùng loại!!', 'Thông báo')
            }
            countProductParent++;
        }
        else if ($(evt.item).find('input.product_color').data('color') != $(item).find('input.product_color').data('color')) {
            $(evt.item).remove();
            if (countProductParentColor === 0) {
                toastr['error']('Các sản phẩm trong lô bắt buộc phải cùng màu!!', 'Thông báo')
            }
            countProductParentColor++;
        }
    })

    //Nếu trong lô không còn sp nữa thì hiện ui kéo thả vào lô
    $('.batch__list .sidebar-item').each(function (index, item) {
        if ($(item).find('ul.content').find('li').children().length === 0) {
            $(item).find('ul.content').html(`
            <li class="dropzone px-1 py-3 text-center">
                <div class="dz-default dz-message">Kéo và thả sản phẩm vào đây!</div>
            </li>
            `)
        }
    })

    //Show widget content of evt.item
    $(evt.item).find('.widget-content').css({
        'display': 'block',
    })

    //Set name into batch
    let batchCount = $(evt.item).closest('ul[id*=wrap-widget-]').attr('data-batch');
    let idProduct = $(evt.item).attr('data-id');

    $(evt.item).find('input.slt_quantity').prop('name', `batch[${batchCount}][product][${idProduct}][quantity]`);
    $(evt.item).find('input.slt_reasoon').prop('name', `batch[${batchCount}][product][${idProduct}][reasoon]`);
    $(evt.item).find('textarea.slt_note').prop('name', `batch[${batchCount}][product][${idProduct}][description]`);
    $(evt.item).find('input.slt_parent_id').prop('name', `batch[${batchCount}][parent_id]`);
    $(evt.item).find('input.slt_product_id').prop('name', `batch[${batchCount}][product][${idProduct}][product_id]`);
    $(evt.item).find('input.count_batch').prop('name', `batch[${batchCount}][count_batch]`);
}

document.addEventListener('DOMContentLoaded', function () {
    const mainContainBatch = document.querySelector('#main__contain-batch');

    if (mainContainBatch) {
        mainContainBatch.innerHTML = `
        <div class="pd-all-20 p-none-t border-top-title-main">
            <div class="col" id="added-widget">

                <input type="number" id="input__branch" name="branch_id" hidden="">
                <div class="batch__list">
                    <div class="col sidebar-item">
                        <div class="btn-remove-batch">
                            <i class="fa-solid fa-xmark"></i>
                        </div>
                        <div class="card">
                            <div class="card-body">
                                <div class="title d-flex justify-content-between">
                                    <h3>Tạo lô hàng</h3>
                                    <div class="widget__quantity-batch d-none">
                                        <div class="form-group">
                                            <label style="white-space:nowrap;">Chọn số lượng lô được tạo:</label>
                                            <input type="number" class="form-control count_batch" name="batch[1][count-batch]"/>
                                            <small>Số lượng còn lại: <strong class="remaining_batch"></strong></small>
                                        </div>
                                    </div>
                                </div>
                                <ul id="wrap-clone-" class="content p-1" data-batch="1">
                                    <li class="dropzone px-1 py-3 text-center">
                                        <div class="dz-default dz-message">Kéo và thả sản phẩm vào đây!</div>
                                    </li>
                                </ul>
                                <div class="wrapper_scan_qrcode d-none">
                                    <div class="d-flex justify-content-end">
                                        <a type="button" class="btn btn-primary btn_scan_qrcode d-inline" data-bs-toggle="modal" href="#QrScanReceiveModal"
                                            id="open_scan_modal" data-batch="1" data-direction="right">
                                            <span class="me-2">
                                                <i class="fa-solid fa-qrcode"></i>
                                            </span>
                                            Quét QR
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-body pd-all-20 p-none-t">
            <div class="mt10">
                <button id="btn__insert-batch" type="button" class="btn btn-primary">Tạo
                    lô mới</button>
                <button class="btn btn-primary d-none" id="submit_btn" data-bs-toggle="modal" data-bs-target="#receipt-actual"
                    type="button">Xác nhận</button>
            </div>
        </div>
        `;

        const upWidget = mainContainBatch.querySelector('ul[id*=wrap-clone-]');

        upWidget.setAttribute('id', 'wrap-widget-2')

        new WidgetManagement().init();

        $(upWidget).closest('.sidebar-item').find('.widget__quantity-batch small').css({
            'display': 'none',
        })

        //Add new batch
        const btnInsertBatch = $('#btn__insert-batch');
        btnInsertBatch.on('click', (event) => {
            event.preventDefault();
            let countItem = $('#added-widget').find('.batch__list .sidebar-item').length + 2;

            //Lấy option loại tạo lô hàng
            const valueOptionCreateBatch = document.querySelectorAll('input[name="create-batch"]:checked');

            let nameChecked = '';
            var checkedValues = Array.from(valueOptionCreateBatch).map(function (checkbox) {
                nameChecked = checkbox.getAttribute('id');
            });

            let className = ``;
            let elQuantityBatch = ``;

            if (nameChecked == 'sample') {
                className = `d-flex justify-content-between`;
                elQuantityBatch = `
                <div class="widget__quantity-batch">
                    <div class="form-group">
                        <label style="white-space:nowrap;">Chọn số lượng lô được tạo:</label>
                        <input type="number" class="form-control count_batch" name="batch[${$('#added-widget').find('.batch__list .sidebar-item').length + 2}][count-batch]"/>
                        <small>Số lượng còn lại: <strong class="remaining_batch"></strong></small>
                    </div>
                </div>
                `;
            }

            let strBatch = `
            <div class="col sidebar-item">
                <div class="btn-remove-batch">
                    <i class="fa-solid fa-xmark"></i>
                </div>
                <div class="card">
                    <div class="card-body">
                        <div class="title ${className}">
                            <h3>Tạo lô hàng</h3>
                            ${elQuantityBatch}
                        </div>
                        <ul id="wrap-widget-${countItem}" data-batch="${countItem}" class="content p-1">
                            <li class="dropzone px-1 py-3 text-center">
                                <div class="dz-default dz-message">Kéo và thả sản phẩm vào đây!</div>
                            </li>
                        </ul>
                        <div class="wrapper_scan_qrcode d-none">
                            <div class="d-flex justify-content-end">
                                <a type="button" class="btn btn-primary btn_scan_qrcode d-inline" data-bs-toggle="modal" href="#QrScanReceiveModal"
                                    id="open_scan_modal" data-batch="${$('#added-widget').find('.batch__list .sidebar-item').length + 2}" data-direction="right">
                                    <span class="me-2">
                                        <i class="fa-solid fa-qrcode"></i>
                                    </span>
                                    Quét QR
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            `;//Batch default

            $('#added-widget').find('.batch__list').append(strBatch);

            $(document.getElementById('wrap-widget-' + countItem)).closest('.sidebar-item').find('.widget__quantity-batch small').css({
                'display': 'none',
            })

            Sortable.create(document.getElementById('wrap-widget-' + countItem), {
                group: 'wrap-widgets',
                filter: '.filtered', // 'filtered' class is not draggable
                delay: 0, // time in milliseconds to define when the sorting should start
                disabled: false, // Disables the sortable if set to true.
                store: null, // @see Store
                animation: 150, // ms, animation speed moving items when sorting, `0` — without animation
                handle: '.card-header',
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
                onUpdate: (evt) => {
                    // if (evt.from !== evt.to) {
                    //     saveWidget($(evt.from).closest('.sidebar-item'))
                    // }
                    // saveWidget($(evt.item).closest('.sidebar-item'))
                    addDropzoneWhenUpdateBatch(evt);
                    checkItemInBatchScanQR();
                },
                onAdd: (evt) => {
                    if ($(evt.to).find('li.widget-item').length > 0) {
                        $(evt.to).find('li.dropzone').remove()
                    }
                    addDropzoneWhenUpdateBatch(evt);
                    checkItemInBatchScanQR();
                },
            });

            addEventUpdateBatchId()//Cập nhật id batch cho modal
        });

        const option_sample = document.querySelector('.option_sample');
        if (option_sample) {
            const optionCustom = option_sample.querySelector('#custom');
            const optionSample = option_sample.querySelector('#sample');

            optionCustom.addEventListener('change', function (event) {
                if (event.target.checked === true) {
                    const listCurBatch = document.querySelectorAll('.batch__list .sidebar-item');

                    listCurBatch.forEach((item, index) => {
                        if (index !== 0) {
                            item.remove();
                        } else {
                            item.querySelector('ul[id*=wrap-widget-]').innerHTML = `
                            <li class="dropzone px-1 py-3 text-center">
                                <div class="dz-default dz-message">Kéo và thả sản phẩm vào đây!</div>
                            </li>
                            `;
                        }
                    })

                    //Ẩn phần chọn số lượng tạo lô
                    const widgetAddBatch = document.querySelector('#added-widget .widget__quantity-batch');
                    if (widgetAddBatch) {
                        if (!widgetAddBatch.classList.contains('d-none')) {
                            widgetAddBatch.classList.add('d-none');
                        }
                    }
                }
            });

            optionSample.addEventListener('change', function (event) {
                if (event.target.checked === true) {
                    const listCurBatch = document.querySelectorAll('.batch__list .sidebar-item');

                    listCurBatch.forEach((item, index) => {
                        if (index !== 0) {
                            item.remove();
                        } else {
                            item.querySelector('ul[id*=wrap-widget-]').innerHTML = `
                            <li class="dropzone px-1 py-3 text-center">
                                <div class="dz-default dz-message">Kéo và thả sản phẩm vào đây!</div>
                            </li>
                            `;
                        }
                    })

                    //Thêm phần chọn số lượng tạo lô
                    const widgetAddBatch = document.querySelector('#added-widget .widget__quantity-batch');
                    if (widgetAddBatch) {
                        if (widgetAddBatch.classList.contains('d-none')) {
                            widgetAddBatch.classList.remove('d-none');
                        }
                    }
                }
            })
        }

    }
    //Lấy danh sách lô hàng đã được tạo
    const receiptId = document.querySelector('input#receipt_id');

    if (receiptId) {
        ajaxGetCreatedShipment(receiptId.value)
    }
    addEventUpdateBatchId()//Cập nhật id batch cho modal

    //Thực nhập kho
    const btnActualInModal = document.getElementById('btn_actual');

    btnActualInModal?.addEventListener('click', function () {
        const formReceipt = document.getElementById('form__receipt');
        btnActualInModal.disabled = true;
        if (formReceipt) {
            formReceipt.submit();
        }
    })

    $('#submit_btn').hide();
})

function addEventUpdateBatchId() {
    const btnScanQRCode = document.querySelectorAll('.btn_scan_qrcode');
    const modalQRScan = document.querySelector('#QrScanReceiveModal');

    if (btnScanQRCode) {
        btnScanQRCode.forEach(item => {
            //Kiểm tra nút quét Qr đã được gắn sự kiện cập nhật batch id chưa, nếu chưa thì gắn sự kiện khi onclick
            if (typeof item[`onclick`] !== 'function') {
                item.onclick = (event) => {
                    const inputBatchIdModal = modalQRScan.querySelector('input#modal_batch_id');

                    if (inputBatchIdModal) {
                        inputBatchIdModal.value = item.dataset.batch;

                        if (item.hasAttribute('data-direction')) {
                            inputBatchIdModal.setAttribute('data-direction', item.dataset.direction)
                        }
                    }
                }
            }
        })
    }
}

function checkItemInBatchScanQR(param = null) {
    const mainContainBatch = document.querySelector('#main__contain-batch');

    if (mainContainBatch) {
        const listItemBatch = mainContainBatch.querySelectorAll('.batch__list .sidebar-item');

        let typeBatch = '';

        const option_sample = document.querySelector('.option_sample');
        if (option_sample) {
            const optionCustom = option_sample.querySelector('#custom');
            const optionSample = option_sample.querySelector('#sample');

            if (optionCustom.checked) {
                typeBatch = 'custom';
            } else if (optionSample.checked) {
                typeBatch = 'sample';
            }
        }

        listItemBatch?.forEach(batch => {
            const listProductInBatch = batch.querySelectorAll('ul[id*="wrap-widget-"] li');
            const elBatch = batch.querySelector('ul[id*="wrap-widget-"]');

            let activeQR = true;
            listProductInBatch.forEach(product => {
                //Kiểm tra thẻ li có phải dropzone không, nếu không thì tiếp tục kiểm tra
                if (product.classList.contains('widget-item')) {
                    let valueQuantity = product.querySelector('input.slt_quantity');

                    if (!valueQuantity || valueQuantity.value == '') {
                        activeQR = false;
                    }

                    if (param == null) {
                        valueQuantity?.addEventListener('keyup', function (event) {
                            checkItemInBatchScanQR(false);
                        })
                        valueQuantity?.addEventListener('blur', function (event) {
                            let productId = product.dataset.id;

                            let startQty = getStartQuantityProductById(productId);
                            if (event.target.value != '') {
                                let totalQtty = getTotalQuantityProductNotCurrentBatch(elBatch.dataset.batch, productId, typeBatch);//Tổng số lượng sản phẩm được sử dụng
                                console.log(startQty, totalQtty);

                                if ((startQty - totalQtty) < event.target.value * 1) {
                                    toastr['error']('Số lượng nhập để tạo lô phải nhỏ hơn số lượng hiện tại!!', 'Thông báo')
                                    valueQuantity.value = '';
                                    valueQuantity.focus()
                                }

                                if (typeBatch == 'sample') {
                                    //Cập nhật số lượng lô có thể tạo
                                    setQuantityABatchCreate(startQty, totalQtty, batch, product);
                                }

                                //Cập nhật số lượng còn lại của sản phẩm
                                setCurrentQuantityProductById(typeBatch);
                            }
                        })
                    }
                } else {
                    activeQR = false;
                }
            })

            //Gắn sự kiện cho input số lượng lô tạo ở option tạo lô mẫu
            const elCountBatch = batch.querySelector('input.count_batch');

            elCountBatch?.addEventListener('blur', function (event) {
                listProductInBatch.forEach(product => {
                    //Kiểm tra thẻ li có phải dropzone không, nếu không thì tiếp tục kiểm tra
                    if (product.classList.contains('widget-item')) {
                        let valueQuantity = product.querySelector('input.slt_quantity');
                        let productId = product.dataset.id;

                        let startQty = getStartQuantityProductById(productId);
                        if (event.target.value != '') {
                            let totalQtty = getTotalQuantityProductNotCurrentBatch(elBatch.dataset.batch, productId, typeBatch);//Tổng số lượng sản phẩm được sử dụng
                            if ((startQty - totalQtty) < (valueQuantity.value * event.target.value)) {
                                toastr['error']('Số lượng lô được tạo đã vượt mức tối đa!!', 'Thông báo')
                                event.target.value = '';
                                event.target.focus()
                            } else {
                                //Cập nhật số lượng còn lại của sản phẩm
                                setCurrentQuantityProductById(typeBatch);
                            }
                        }
                    }
                })
            })

            const wrapperQR = batch.querySelector('.wrapper_scan_qrcode');
            if (activeQR) {
                if (wrapperQR?.classList.contains('d-none')) {
                    wrapperQR.classList.remove('d-none')
                }
            } else {
                if (!wrapperQR?.classList.contains('d-none')) {
                    wrapperQR.classList.add('d-none');
                }
            }
        })
    }
}

//Tính tổng số lượng của 1 sản phẩm đang được thêm vào lô
//Lấy giá trị từ input người nhập
function getTotalQuantityProductCreateBatch(productId, type = null) {
    const mainContainBatch = document.querySelector('#main__contain-batch');

    let totalQuantity = 0;

    if (mainContainBatch) {
        const listItemBatch = mainContainBatch.querySelectorAll('.batch__list .sidebar-item');
        listItemBatch?.forEach(batch => {
            const listProductInBatch = batch.querySelectorAll('ul[id*="wrap-widget-"] li');
            const inputCountBatch = batch.querySelector('input[name="count-batch"]');

            listProductInBatch?.forEach((product, index) => {
                if (product.dataset.id == productId) {
                    let inputQty = product.querySelector('input.slt_quantity');

                    if (inputQty.value != '') {
                        if (type == 'custom') {
                            totalQuantity += parseInt(inputQty.value);
                        } else {
                            let curCountBatch = 0;

                            let curQty = getCurrentQuantityProductById(productId);//14
                            if (inputQty.value != '') {
                                let countBatch = parseInt(curQty * 1 / inputQty.value * 1);

                                if (inputCountBatch.value != '' && inputCountBatch.value * 1 > 0) {
                                    curCountBatch = inputCountBatch.value
                                    if (curCountBatch > countBatch || curCountBatch === 0) {
                                        curCountBatch = countBatch;
                                    }
                                    totalQuantity += parseInt(inputQty.value * curCountBatch);
                                } else {
                                    // if(curCountBatch > countBatch || curCountBatch === 0){
                                    //     curCountBatch = countBatch;
                                    // }
                                    totalQuantity += parseInt(inputQty.value * countBatch);
                                }
                            }

                        }
                    }
                }
            })
        })
    }
    return totalQuantity;
}

//Lấy tổng sản phẩm bên lô được tạo khác lô hiện tại
function getTotalQuantityProductNotCurrentBatch(batchId, productId, type) {
    const listItemBatch = document.querySelectorAll('.batch__list .sidebar-item');
    let totalQty = 0;

    listItemBatch?.forEach(batch => {
        const elBatch = batch.querySelector('ul[id*="wrap-widget-"]');

        if (elBatch.dataset.batch != batchId) {
            const listProductInBatch = batch.querySelectorAll('ul[id*="wrap-widget-"] li');
            const elCountBatch = batch.querySelector('.widget__quantity-batch input.count_batch');

            listProductInBatch?.forEach(product => {
                if (productId == product.dataset.id) {
                    const elCountProduct = product.querySelector('input.slt_quantity');

                    if (type == 'sample') {
                        if (elCountProduct.value != '' && elCountProduct.value * 1 > 0 && elCountBatch.value != '' && elCountBatch.value * 1 > 0) {
                            totalQty += parseInt(elCountProduct.value * elCountBatch.value);
                        }
                    } else if (type == 'custom') {
                        if (elCountProduct.value != '' && elCountProduct.value * 1 > 0) {
                            totalQty += parseInt(elCountProduct.value);
                        }
                    }
                }
            })
        }
    })
    if (Object.keys(ARR_QTY_PRODUCT_CREATED_BATCH).length > 0) {
        Object.entries(ARR_QTY_PRODUCT_CREATED_BATCH).forEach(([key, value]) => {
            if (productId == key) {
                totalQty += value;
            }
        })
    }

    return totalQty;
}

//Lấy tổng số lượng sản phẩm của tất cả lô đang được tạo
function getTotalQuantityProductInBatch(productId, type) {
    const listItemBatch = document.querySelectorAll('.batch__list .sidebar-item');
    let totalQty = 0;

    listItemBatch?.forEach(batch => {
        const listProductInBatch = batch.querySelectorAll('ul[id*="wrap-widget-"] li');
        const elCountBatch = batch.querySelector('.widget__quantity-batch input.count_batch');

        listProductInBatch?.forEach(product => {
            if (productId == product.dataset.id) {
                const elCountProduct = product.querySelector('input.slt_quantity');

                if (type == 'sample') {
                    if (elCountProduct.value != '' && elCountProduct.value * 1 > 0 && elCountBatch.value != '' && elCountBatch.value * 1 > 0) {
                        totalQty += elCountProduct.value * elCountBatch.value;
                    }
                } else if (type == 'custom') {
                    if (elCountProduct.value != '' && elCountProduct.value * 1 > 0) {
                        totalQty += elCountProduct.value * 1;
                    }
                }
            }
        })

    })

    return totalQty;
}

//Cập nhật số lượng có thể tạo lô cho các sản phẩm khi nhập số lượng sản phẩm
function setQuantityABatchCreate(startQty, qty, batchEl, productEl) {
    let qtyRemin = startQty - qty;

    const qtyProductInCurBatch = productEl.querySelector('input.slt_quantity');
    const elCountBatch = batchEl.querySelector('input.count_batch');

    if (qtyProductInCurBatch.value != '' && qtyProductInCurBatch.value * 1 > 0) {
        let countBatch = parseInt(qtyRemin / qtyProductInCurBatch.value);

        if (elCountBatch.value * 1 > 0 && countBatch > elCountBatch.value * 1) {
            return;
        } else {
            elCountBatch.value = countBatch;
            elCountBatch.max = countBatch;
        }
    }
}

function setCountBatchSample(batch) {
    const listItemProduct = batch.querySelectorAll('ul[id*="wrap-widget-"] li.widget-item');

    listItemProduct?.forEach((product) => {
        let productId = product.dataset.id;
        let totalQtys = getTotalQuantityProductCreateBatch(productId);

        setCurrentQuantityProductById(productId, totalQtys)
    })
}

function getInfoFromBatchId(batchId) {
    const listBatch = document.querySelectorAll('.batch__list .sidebar-item');

    let result = {};

    listBatch?.forEach(batch => {
        let curBatch = batch.querySelector('ul[id*=wrap-widget-]');

        if (curBatch.dataset.batch == batchId) {
            //Lấy tất cả các sản phẩm trong lô
            const listPorduct = batch.querySelectorAll('li.widget-item');

            listPorduct?.forEach((product, index) => {
                //Lấy tất cả thông tin của sản phẩm từ element
                let proName = product.querySelector('input.product_name').dataset.name;
                let proSize = product.querySelector('input.product_size').dataset.size;
                let proColor = product.querySelector('input.product_color').dataset.color;
                let proSku = product.querySelector('input.product_sku').dataset.sku;
                let proStartQty = product.querySelector('input.start_quantity').dataset.quantity;

                let proId = product.querySelector('input.slt_product_id').value;
                let proParent = product.querySelector('input.slt_parent_id').value;

                Object.assign(result, {
                    [proId]: {
                        'product_name': proName,
                        'product_size': proSize,
                        'product_color': proColor,
                        'product_sku': proSku,
                        'start_qty': proStartQty,
                        'parent_id': proParent,
                    }
                })
            });
        };
    });

    return result;
}

//Api lấy toàn bộ lô đã tạo của phiếu nhập hiện tại
function ajaxGetCreatedShipment(receiptId) {
    let urlAPI = document.querySelector('input#url_api_batch_created').value ?? 'get-created-shipment';

    $.ajax({
        url: `/api/v1/${urlAPI}/${receiptId}`,
        type: 'get',
        success: function (data) {
            let objProduct = data.body.qtyPro
            for (const key in objProduct) {
                if (Object.hasOwnProperty.call(objProduct, key)) {
                    const product = objProduct[key];
                    showListProductExist(product)
                }
            }

            ARR_QTY_PRODUCT_CREATED_BATCH = data.body.qtyPro

            const submit = setCurrentQuantityProductIntoCreatedShipment(data.body.qtyPro)
            if (submit) {
                $('#submit_btn').removeClass('d-none').addClass('d-inline');
            }
            else {
                $('#submit_btn').hide();
            }
            //Cập lại số lượng hiện tại của mỗi sản phẩm
        },
        error: function (error) {
            console.log(error);
        }
    })
}

// window.addEventListener('beforeunload', function (event) {
//     // Hỏi người dùng có chắc chắn muốn rời khỏi trang hay không
//     const confirmationMessage = 'Bạn có chắc chắn muốn rời khỏi trang?';
//     (event || window.event).returnValue = confirmationMessage; // For some older browsers
//     return confirmationMessage;
// });
