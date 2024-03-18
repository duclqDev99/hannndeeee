const onScan = require('onscan.js');

toastr.options = {
    closeButton: true,
    positionClass: 'toast-bottom-right',
    showDuration: 1000,
    hideDuration: 1000,
    timeOut: 10000,
    extendedTimeOut: 1000,
    showEasing: 'swing',
    hideEasing: 'linear',
    showMethod: 'fadeIn',
    hideMethod: 'fadeOut',
}

const renderRow = (d) => {
    const date = new Date(d?.created_at);
    const dateFormat = `${date.getDate()}-${date.getMonth() + 1}-${date.getFullYear()}`
    const stt = $('#table_list').children().length + 1;
    return $(`<tr>
            <th scope="row">${stt}</th>
            <td style="max-width:250px;">
                <span class="text-break"> ${d?.batch_code}</span>
            </td>
            <td style="max-width:200px;">
                <div class="d-flex align-items-center flex-wrap">
                    <a href="#"
                        title="Joan Mini Camera Bag" class="me-2">
                        ${d?.material?.name}
                    </a>
                </div>
                <div>
                   <small>Mã SP: ${d?.material_code}</small>
                </div>
            </td>
            <td>${dateFormat}</td>
            <td>${d?.receipt?.invoice_confirm_name || '---'}</td>
            <td>${d?.start_qty || '---'}</td>
            <td>${d?.quantity || '---'}</td>
        </tr>`)
        .hide()
        .fadeIn(200);
}

const openScannerLoading = () => {
    $('#scanner_box').removeClass('d-flex').addClass('d-none');
    $('#scanner_box_loading').removeClass('d-none').addClass('d-flex');
}

const closeScannerLoading = () => {
    $('#scanner_box').removeClass('d-none').addClass('d-flex');
    $('#scanner_box_loading').removeClass('d-flex').addClass('d-none');
}

const playSound = (fileName) => {
    if (!fileName) return;
    const audio = document.createElement('audio');
    audio.src = `/storage/scan-audio/${fileName}.mp3`;
    audio?.play();
    audio?.remove();
}

document.addEventListener("DOMContentLoaded", (event) => {
    let isScanning = false;
    let qrProductScanned = [];

    onScan.attachTo(document, {
        suffixKeyCodes: [13, 9], // enter-key expected at the end of a scan
        reactToPaste: true, // Compatibility to built-in scanners in paste-mode (as opposed to keyboard-mode)
        // ignoreIfFocusOn: 'input',
        onScan: function (code, iQty) { // Alternative to document.addEventListener('scan')
            try {
                if (qrProductScanned?.includes(code)) {
                    throw new Error(`${code} đã quét`);
                }
                if ($('#qrScanModalPc').hasClass('show')) {
                    if (isScanning == false) {
                        isScanning = true;
                        openScannerLoading();

                        $.ajax({
                            url: '/admin/material-batchs/qr-scan',
                            method: 'POST',
                            data: { batch_code: code?.data },
                            success: (res) => {
                                isScanning = false;
                                closeScannerLoading();
                                if (res?.success == 0) throw new Error(res?.message);

                                qrProductScanned.push(code);
                                $('#scanner_message').html(`
                            <div role="alert" class="alert alert-success mt-3 mb-0">
                                 <div class="d-flex">
                                     <div class="w-100">
                                          <span>Mã vừa quét: </span>
                                          <span class="fw-bolder text-break">${code}</span>
                                     </div>
                                 </div>
                             </div>`).show();
                                $('#table_list').prepend(renderRow(res.data));
                                // $('#table_wrapper').animate({ scrollTop: 9999 }, 'fast');
                                $('#empty_scanned_message').removeClass('d-flex').addClass('d-none');
                            },
                            error: (res) => {
                                isScanning = false;
                                playSound('not-found');
                                closeScannerLoading();
                                const result = res.responseJSON;
                                $('#scanner_message').html(`
                            <div role="alert" class="alert alert-danger mt-3 mb-0">
                                <div class="d-flex">
                                    <div class="w-100">
                                        ${result?.message}!
                                    </div>
                                </div>
                            </div>`).show();
                            }
                        })
                    } else {
                        toastr?.error('Quét quá nhanh. Vui lòng chờ phản hồi từ hệ thống!');
                    }
                }
            } catch (err) {
                $('#scanner_message').html(`
                <div role="alert" class="alert alert-danger mt-3 mb-0">
                    <div class="d-flex">
                        <div class="w-100">
                            ${err?.message}!
                        </div>
                    </div>
                </div>`);
            }
        },
        onKeyProcess: (sChart, oEvent) => {
            // $('input').blur();
        },
        onScanError: (oDebug) => {
            console.log(oDebug)
        }
    });

    $(document).off('click');
    $(document).on('click', '.open_scan_pc_modal', function () {
        $('#qrScanModalPc').modal('show');
    })

    window.addEventListener('resize', function () {
        if (window.innerWidth < 992) $('#qrScanModalPc').modal('hide');
    })

    $(document).on('hide.bs.modal', '#qrScanModalPc', function () {
        $('#table_list').html('');
        $('#empty_scanned_message').removeClass('d-none').addClass('d-flex');
        $('#scanner_message').html('').hide();
    })
});