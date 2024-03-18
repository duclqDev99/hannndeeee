const onScan = require('onscan.js');

function hasVietnameseCharacters(inputString) {
    const vietnameseCharacters = 'àáãạảăắằẳẵặâấầẩẫậèéẹẻẽêềếểễệđìíĩỉịòóõọỏôốồổỗộơớờởỡợùúũụủưứừửữựỳỵỷỹýÀÁÃẠẢĂẮẰẲẴẶÂẤẦẨẪẬÈÉẸẺẼÊỀẾỂỄỆĐÌÍĨỈỊÒÓÕỌỎÔỐỒỔỖỘƠỚỜỞỠỢÙÚŨỤỦƯỨỪỬỮỰỲỴỶỸÝ';
    inputString = inputString.normalize('NFC');
    for (let char of inputString) {
        if (vietnameseCharacters.includes(char)) {
            return true;
        }
    }

    return false;
}

function hasVietnameseCharacters2(inputString) {
    const vietnameseCharacters = [
        'à', 'á', 'ả', 'ã', 'ạ',
        'ă', 'ằ', 'ắ', 'ẳ', 'ẵ', 'ặ',
        'â', 'ầ', 'ấ', 'ẩ', 'ẫ', 'ậ',
        'è', 'é', 'ẻ', 'ẽ', 'ẹ',
        'ê', 'ề', 'ế', 'ể', 'ễ', 'ệ',
        'ì', 'í', 'ỉ', 'ĩ', 'ị',
        'ò', 'ó', 'ỏ', 'õ', 'ọ',
        'ô', 'ồ', 'ố', 'ổ', 'ỗ', 'ộ',
        'ơ', 'ờ', 'ớ', 'ở', 'ỡ', 'ợ',
        'ù', 'ú', 'ủ', 'ũ', 'ụ',
        'ư', 'ừ', 'ứ', 'ử', 'ữ', 'ự',
        'ỳ', 'ý', 'ỷ', 'ỹ', 'ỵ',
        'đ',
        'À', 'Á', 'Ả', 'Ã', 'Ạ',
        'Ă', 'Ằ', 'Ắ', 'Ẳ', 'Ẵ', 'Ặ',
        'Â', 'Ầ', 'Ấ', 'Ẩ', 'Ẫ', 'Ậ',
        'È', 'É', 'Ẻ', 'Ẽ', 'Ẹ',
        'Ê', 'Ề', 'Ế', 'Ể', 'Ễ', 'Ệ',
        'Ì', 'Í', 'Ỉ', 'Ĩ', 'Ị',
        'Ò', 'Ó', 'Ỏ', 'Õ', 'Ọ',
        'Ô', 'Ồ', 'Ố', 'Ổ', 'Ỗ', 'Ộ',
        'Ơ', 'Ờ', 'Ớ', 'Ở', 'Ỡ', 'Ợ',
        'Ù', 'Ú', 'Ủ', 'Ũ', 'Ụ',
        'Ư', 'Ừ', 'Ứ', 'Ử', 'Ữ', 'Ự',
        'Ỳ', 'Ý', 'Ỷ', 'Ỹ', 'Ỵ',
        'Đ'
    ];

    for (let char of inputString) {
        if (vietnameseCharacters.includes(char)) {
            return true;
        }
    }

    return false;
}


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
                <span class="text-break"> ${d?.qr_code_encrypt}</span>
            </td>
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
            <td>${d?.status?.label}</td>
            <td>${d?.warehouse?.name || '---'}</td>
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
        reactToPaste: true, // Compatibility to built-in scanners in paste-mode (as opposed to keyboard-mode)2
        // ignoreIfFocusOn: 'input',
        onScan: function (code, iQty) { // Alternative to document.addEventListener('scan')
            try {
                if (qrProductScanned?.find(i => i?.qr_code_encrypt == code)) {
                    playSound('duplicate');
                    throw new Error(`<span class="text-break">${code}</span> đã quét`);
                }
                if ($('#productCodeQrScanModal').hasClass('show')) {
                    if (isScanning == false) {
                        isScanning = true;
                        openScannerLoading();
                        $.ajax({
                            url: '/admin/product-qrcodes/ajax-post-qr-scan',
                            method: 'POST',
                            data: { qr_code: code },
                            success: (res) => {
                                isScanning = false;
                                closeScannerLoading();
                                if (res?.success == 0) throw new Error(res?.message);
                           
                                qrProductScanned.push(res?.data);
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
                                playSound(res?.data?.status?.value);
                            },
                            error: (res) => {
                                isScanning = false;
                                closeScannerLoading();

                                if(res?.status == 404){
                                    let invalid = false;
                                    if(hasVietnameseCharacters(code)) invalid = true;
                                    if(code?.length != 44) invalid = true;
                                    console.log(invalid)
                                    if(invalid == true) return alert('Mã QR không hợp lệ. Vui lòng tắt các bộ gõ tiếng Việt trước khi quét!');
                                }

                                playSound('not-found');
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
        keyCodeMapper: function (oEvent) {
            // Check if the pressed key is a special character
            if (!oEvent.key.match(/[a-zA-Z0-9]/)) {
                return oEvent.key;
            }
            // Fall back to the default decoder in all other cases
            return onScan.decodeKeyEvent(oEvent);
        },
        onKeyProcess: (sChart, oEvent) => {
            // $('input').blur();
        },
        onScanError: (oDebug) => {
            console.log(oDebug)
        },
    });

    $(document).off('click');
    $(document).on('click', '.product_qrcode_scan_btn', function () {
        $('#productCodeQrScanModal').modal('show');
    })

    $(document).on('hide.bs.modal', '#productCodeQrScanModal', function () {
        qrProductScanned = [];
        $('#table_list').html('');
        $('#empty_scanned_message').removeClass('d-none').addClass('d-flex');
        $('#scanner_message').html('').hide();
    })
});

