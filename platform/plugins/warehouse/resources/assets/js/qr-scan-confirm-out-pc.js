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
                        ${d?.material_name}
                    </a>
                </div>
                <div>
                   <small>Mã SP: ${d?.material_code}</small>
                </div>
            </td>
            <td>${d?.quantity}</td>
            <td data-code="${d?.batch_code}">
                <span>Chưa quét</span>
            </td>
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

    onScan.attachTo(document, {
        suffixKeyCodes: [13, 9], // enter-key expected at the end of a scan
        reactToPaste: true, // Compatibility to built-in scanners in paste-mode (as opposed to keyboard-mode)
        // ignoreIfFocusOn: 'input',
        onScan: function (code, iQty) { // Alternative to document.addEventListener('scan')
            openScannerLoading();
            try {
                if (window?.materialBatches?.find(item => item?.batch_code == code)) {
                    playSound('valid');
                    $(`td[data-code="${code}"]`).html('<span class="text-success">Đã quét <i class="fa-solid fa-check ml-2"></i></span>');
                    $('#scanner_message').html(`
                    <div role="alert" class="alert alert-success mt-3 mb-0">
                         <div class="d-flex">
                             <div class="w-100">
                                  <span>Mã vừa quét: </span>
                                  <span class="fw-bolder text-break">${code}</span>
                             </div>
                         </div>
                     </div>`).show();
                   closeScannerLoading();
                } else {
                    playSound('invalid');
                    throw new Error(`Mã QR không hợp lệ`);
                }

            } catch (err) {
                $('#scanner_message').html(`
                <div role="alert" class="alert alert-danger mt-3 mb-0">
                    <div class="d-flex">
                        <div class="w-100">
                            ${err?.message}!
                        </div>
                    </div>
                </div>`).show();
                closeScannerLoading();
            }
        },
        onKeyProcess: (sChart, oEvent) => {
            // $('input').blur();
        },
        onScanError: (oDebug) => {
            console.log(oDebug)
        },

    });

    $(document).off('click');
    $(document).on('click', '#open_scan_modal_pc', function () {
        $('#qrScanModalPc').modal('show');
        window.materialBatches?.map(item => {
            $('#table_list').append(renderRow(item));
        })
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
