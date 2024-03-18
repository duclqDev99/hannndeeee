
const jsQR = require("jsqr");

let dataScanned = [];
let isScanning = false;
let agentWarehouseIds = [];
const warehouse_type = 'Botble\\Agent\\Models\\AgentWarehouse';

function formatCurrency(value, locale = 'en-US', currency = 'USD') {
    return new Intl.NumberFormat(locale, {
        style: 'currency',
        currency: currency,
    }).format(value);
}

const renderDataScanned = () => {
    const data = [...dataScanned];
    const mergedItems = data.reduce((acc, currentItem) => {
        const index = acc.findIndex(accItem => accItem.reference.id === currentItem.reference.id);

        if (index >= 0) {
            const existingItem = acc[index];
            const updatedItem = { ...existingItem, quantity: existingItem.quantity + 1 };

            acc[index] = updatedItem;
        } else {
            acc.push(currentItem);
        }

        return acc;
    }, []);

    let total_amount = 0;

    $('#body').html(mergedItems.reverse().map(d => {
        // total_amount += d?.reference.original_price
        total_amount = calculateTotal()
        return `<tr>
        <td style="max-width:200px; z-index:1000">
            <div class="d-flex align-items-center flex-wrap">
               ${d?.reference?.name}
            </div>
            <div>
               <small>${d?.time_create_q_r?.variation_attributes}</small>
            </div>
        </td>
        <td><p>${formatCurrency(d?.reference.original_price || 0, 'vi-VN', 'VND')}</p></td>
        <td style="width: 50px">${d?.quantity}</td>
        <td class="text-center" style="width: 50px">
            <button type="button" data-id="${d.id}" data-product-id="${d.reference.id}" data-bs-toggle="tooltip" title="Bỏ chọn sản phẩm này" class="btn btn-sm btn-icon btn-danger cancel_scanned_btn">
            <i class="fa-solid fa-xmark"></i>
            </button>
        </td>
    </tr>`
    }));
    // <td style="width:30px;"><p>${d?.quantity || 1}</p></td>

    if (dataScanned.length > 0) {
        $('#total_amount').html(`${formatCurrency(calculateTotal() || 0, 'vi-VN', 'VND')}`)
    } else {
        $('#total_amount').html('0đ')
    }
}

const renderDataSuccess = () => {
    const data = [...dataScanned];
    const mergedItems = data.reduce((acc, currentItem) => {
        const index = acc.findIndex(accItem => accItem.reference.id === currentItem.reference.id);

        if (index >= 0) {
            const existingItem = acc[index];
            const updatedItem = { ...existingItem, quantity: existingItem.quantity + 1 };

            acc[index] = updatedItem;
        } else {
            acc.push(currentItem);
        }

        return acc;
    }, []);

    let total_amount = 0;
    $('#product_success_list').html(mergedItems.reverse().map(d => {
        // total_amount += d?.reference.original_price
        total_amount = calculateTotal()
        return `<tr>
       <td style="max-width:200px;">
           <div class="p-0">
               <div class="d-flex gap-3 align-items-center flex-wrap">
                   <span> ${d?.reference?.name}</span>
               </div>
               <div class="">
                   <small>${d?.time_create_q_r?.variation_attributes}</small>
               </div>
           </div>
       </td>
       <td class="px-0"><p class="mb-0">${formatCurrency(d?.reference.original_price || 0, 'vi-VN', 'VND')}</p></td>
       <td style="width: 50px">${d?.quantity}</td>
   </tr>`;
    }));

    $('#total_amount_success').html(`${formatCurrency(total_amount || 0, 'vi-VN', 'VND')}`);
}

    function parseCurrencyToNumber(currencyString) {
        return Number(currencyString.replace(/[^0-9,-]+/g, "").replace(',', '.'));
    }

    function calculateTotal() {
        let total = 0;

        $("#table-scanned tbody tr").each(function() {
            let priceText = $(this).find("td:nth-child(2) p").text();
            let quantity = parseInt($(this).find("td:nth-child(3)").text());

            let price = parseCurrencyToNumber(priceText);

            total += price * quantity;
        });

        return total;

        // return totalAmount.toLocaleString('vi-VN', { style: 'currency', currency: 'VND' });
    }

const setValidForm = () => {
    if (dataScanned.length > 0) {
        $('button[name="submit"]').removeAttr('disabled');
        $('#empty_scanned_message').removeClass('d-flex').hide();
    } else {
        $('button[name="submit"]').attr('disabled', true);
        $('#empty_scanned_message').addClass('d-flex').show();
    }
}

const handleReset = () => {
    dataScanned = [];
    renderDataScanned();
    setValidForm();
    $('#skeleton-loading').hide();
    $('#box_camera_scanner').show();
    $(this).removeClass('rotate-180');
}

const getAgentWarehouseIds = (agent_id) => {
    $.ajax({
        url: '/admin/agents/get-agent-warehouse',
        method: 'GET',
        data: { agent_id },
        async: false,
        success: res => {
            agentWarehouseIds = res.data;
        }
    })
}

const handleScanned = (res) => {
    const qr = res.data;

    if (qr.status.value != 'instock') return alert(`Không hợp lệ. Sản phẩm ở trạng thái ${qr?.status?.label}`);
    if (dataScanned.find(item => item.id == qr.id)) return alert('Sản phẩm đã quét, vui lòng quét sản phẩm khác!');
    // if (!agentWarehouseIds.find(id => id == qr.warehouse_id) || qr.warehouse_type != warehouse_type)
    //     return alert('Sản phẩm không thuộc đại lý!');

    const index = dataScanned.findIndex(item => item.product_id == qr.product_id);
    // const hasProductId = dataScanned.findIndex(item => item.reference.id == qr.reference.id);
    // if(hasProductId !== -1)dataScanned[index].quantity +=1;
    // else dataScanned.push({ ...qr, quantity: 1 });
    dataScanned.push({ ...qr, quantity: 1 });
    renderDataScanned();
    setValidForm();
    isScanning = false;
}

document.addEventListener('DOMContentLoaded', function () {
    getAgentWarehouseIds($('#select-order-agent :selected').val());
    let video = null;
    setTimeout(function() {
        let canvasElement = document.getElementById("canvas");
        let canvas = canvasElement.getContext("2d");

        const btnShowCamera = document.getElementById('show_full_product_info_btn');

        btnShowCamera.addEventListener('click', function () {
            $('#camera-loading').show();

            try {
                navigator.mediaDevices.getUserMedia({ video: { facingMode: "environment" } })
                    .then(function (stream) {
                        window.localStream = stream;
                        video = document.createElement("video");
                        video.srcObject = stream;
                        video.setAttribute("playsinline", true); // required to tell iOS safari we don't want fullscreen
                        video.play();
                        requestAnimationFrame(tick);

                        $('#agent_order_scan_mobile_modal').modal('show');
                        $('#camera-loading').hide();
                    })
            } catch (err) {
                console.log(err);
                $('#agent_order_scan_mobile_modal').modal('hide');
                alert('Không kết nối được camera. Vui lòng thử lại sau!');
            }

            function drawLine(begin, end, color) {
                canvas.beginPath();
                canvas.moveTo(begin.x, begin.y);
                canvas.lineTo(end.x, end.y);
                canvas.lineWidth = 4;
                canvas.strokeStyle = color;
                canvas.stroke();
            }

            function tick() {
                if (video && (video.readyState === video.HAVE_ENOUGH_DATA)) {
                    canvasElement.hidden = false;

                    canvasElement.height = video.videoHeight;
                    canvasElement.width = video.videoWidth;
                    canvas.drawImage(video, 0, 0, canvasElement.width, canvasElement.height);
                    var imageData = canvas.getImageData(0, 0, canvasElement.width, canvasElement.height);
                    var code = jsQR(imageData.data, imageData.width, imageData.height, {
                        inversionAttempts: "dontInvert",
                    });
                    if (code && code.data.length > 0) {
                        if (!isScanning) {
                            isScanning = true;
                            $('#skeleton-loading').show();
                            $.ajax({
                                url: '/admin/product-qrcodes/ajax-post-qr-scan',
                                method: 'POST',
                                data: { qr_code: code?.data },
                                success: (res) => {
                                    $('#skeleton-loading').hide();
                                    $('#box_camera_scanner').show();
                                    $(this).removeClass('rotate-180');
                                    handleScanned(res);
                                    isScanning = false;
                                    setTimeout(() => {
                                        isScanning = false;
                                        requestAnimationFrame(tick);
                                    }, 1500)
                                },
                                error: (err) => {
                                    $('#skeleton-loading').hide();
                                    const res = err.responseJSON;
                                    setTimeout(() => {
                                        isScanning = false;
                                        requestAnimationFrame(tick);
                                    }, 1500)
                                    return alert(res.message);
                                }
                            });
                        }

                        drawLine(code.location.topLeftCorner, code.location.topRightCorner, "#FF3B58");
                        drawLine(code.location.topRightCorner, code.location.bottomRightCorner, "#FF3B58");
                        drawLine(code.location.bottomRightCorner, code.location.bottomLeftCorner, "#FF3B58");
                        drawLine(code.location.bottomLeftCorner, code.location.topLeftCorner, "#FF3B58");

                    }
                }
                video && !isScanning && requestAnimationFrame(tick);
            }
        });

        const btnOpenModal = document.getElementById('open_scan_modal');



        btnOpenModal.addEventListener('click', function () {
            $('#camera-loading').show();

            try {
                navigator.mediaDevices.getUserMedia({ video: { facingMode: "environment" } })
                    .then(function (stream) {
                        window.localStream = stream;
                        video = document.createElement("video");
                        video.srcObject = stream;
                        video.setAttribute("playsinline", true); // required to tell iOS safari we don't want fullscreen
                        video.play();
                        requestAnimationFrame(tick);

                        $('#agent_order_scan_mobile_modal').modal('show');
                        $('#camera-loading').hide();
                    })
            } catch (err) {
                console.log(err);
                $('#agent_order_scan_mobile_modal').modal('hide');
                alert('Không kết nối được camera. Vui lòng thử lại sau!');
            }

            function drawLine(begin, end, color) {
                canvas.beginPath();
                canvas.moveTo(begin.x, begin.y);
                canvas.lineTo(end.x, end.y);
                canvas.lineWidth = 4;
                canvas.strokeStyle = color;
                canvas.stroke();
            }

            function tick() {
                if (video && (video.readyState === video.HAVE_ENOUGH_DATA)) {
                    canvasElement.hidden = false;

                    canvasElement.height = video.videoHeight;
                    canvasElement.width = video.videoWidth;
                    canvas.drawImage(video, 0, 0, canvasElement.width, canvasElement.height);
                    var imageData = canvas.getImageData(0, 0, canvasElement.width, canvasElement.height);
                    var code = jsQR(imageData.data, imageData.width, imageData.height, {
                        inversionAttempts: "dontInvert",
                    });
                    if (code && code.data.length > 0) {
                        if (!isScanning) {
                            isScanning = true;
                            $('#skeleton-loading').show();
                            $.ajax({
                                url: '/admin/product-qrcodes/ajax-post-qr-scan',
                                method: 'POST',
                                data: { qr_code: code?.data },
                                success: (res) => {
                                    $('#skeleton-loading').hide();
                                    $('#box_camera_scanner').show();
                                    $(this).removeClass('rotate-180');
                                    handleScanned(res);
                                    isScanning = false;
                                    setTimeout(() => {
                                        isScanning = false;
                                        requestAnimationFrame(tick);
                                    }, 1500)
                                },
                                error: (err) => {
                                    $('#skeleton-loading').hide();
                                    const res = err.responseJSON;
                                    setTimeout(() => {
                                        isScanning = false;
                                        requestAnimationFrame(tick);
                                    }, 1500)
                                    return alert(res.message);
                                }
                            });
                        }

                        drawLine(code.location.topLeftCorner, code.location.topRightCorner, "#FF3B58");
                        drawLine(code.location.topRightCorner, code.location.bottomRightCorner, "#FF3B58");
                        drawLine(code.location.bottomRightCorner, code.location.bottomLeftCorner, "#FF3B58");
                        drawLine(code.location.bottomLeftCorner, code.location.topLeftCorner, "#FF3B58");

                    }
                }
                video && !isScanning && requestAnimationFrame(tick);
            }

            $(document).off('paste');
            document.addEventListener('paste', function (event) {
                if (!isScanning) {
                    isScanning = true;
                    const qr_code = event.clipboardData.getData('text');
                    $('#skeleton-loading').show();
                    $.ajax({
                        url: '/admin/product-qrcodes/ajax-post-qr-scan',
                        method: 'POST',
                        data: { qr_code },
                        success: (res) => {
                            $('#skeleton-loading').hide();
                            handleScanned(res);
                            setTimeout(() => {
                                isScanning = false;
                                requestAnimationFrame(tick);
                            }, 1500)                        // $('#scanner_content').html(res)
                            // $("#scanner_content").show();
                        },
                        error: (err) => {
                            $('#skeleton-loading').hide();
                            setTimeout(() => {
                                isScanning = false;
                                requestAnimationFrame(tick);
                            }, 1500)
                            return alert(err.message);
                        }
                    })
                }
            });
        });

    }, 2000);





    window.addEventListener('resize', function () {
        if (window.innerWidth > 768) $('#agent_order_scan_mobile_modal').modal('hide');
    });

    $(document).on('hide.bs.modal', '#agent_order_scan_mobile_modal', function () {
        localStream.getVideoTracks().forEach(track => track.stop())
        video = null;
        $('#scanner_content').html('');
        $("#scanner_content").hide();
    });

    $(document).on('click', '.cancel_scanned_btn', function () {
        if (confirm('Bỏ chọn sản phẩm này?')) {
            const id = $(this).data('id');
            const product_id = $(this).data('product-id');
            dataScanned = dataScanned.filter(item => item?.reference.id != product_id);
            renderDataScanned();
            setValidForm();
        }
    })

    $('#select-order-agent').on('select2:select', function (e) {
        const agent_id = $(this).val();
        getAgentWarehouseIds(agent_id);
    });


    $(document).on('click', `button[name="cancel"]`, function () {
        $('#box_scan').show();
        $('#box_create_success').hide();
        handleReset();
    });

    $(document).on('click', `button[name="submit"]`, function () {
        if (confirm('Xác nhận tạo đơn hàng?')) {
            let url = $('#data-warehouse').attr('data-bs-toggle');
            let agent_id = $('#select-order-agent').val();
            let total = 0;
            dataScanned.forEach((val) => {
                total += val.reference.original_price;
            });
            const qr_ids = dataScanned.map(qr => qr.id);

            $.ajax({
                type: 'POST',
                url: url,
                data: { total, qr_ids, agent_id },
                success: function success(res) {
                    if (res.error) return alert('Tạo đơn hàng thất bại!')
                    else {
                        $('#box_scan').hide();
                        $('#box_create_success').show();
                        renderDataSuccess();
                        handleReset();
                    }
                },
                error: function error(res) {
                    return alert('Tạo đơn hàng thất bại!')
                }
            });

            // return toastr.success(`Tạo thành công đơn hàng ${qr_ids.length} sản phẩm`)
        }
    });

    $(document).on('click', `button[name="continue"]`, function () {
        $('#box_scan').show();
        $('#box_create_success').hide();
    });

    $(document).off('click', '#show_full_product_info_btn');
    $(document).on('click', '#show_full_product_info_btn', function () {
        $('#box_camera_scanner').slideToggle();
        var icon = $(this).find('i.fa-chevron-up');
        if (icon.hasClass('rotate-180')) icon.removeClass('rotate-180');
        else icon.addClass('rotate-180');
    });
})




