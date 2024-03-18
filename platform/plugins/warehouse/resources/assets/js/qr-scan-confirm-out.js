const jsQR = require("jsqr");
const { forEach } = require("lodash");

const renderRow = (d, i) => {
    return $(`<tr>
            <th scope="row">${i + 1}</th>
            <td >
                <span class="text-break"> ${d?.batch_code}</span>
            </td>
            <td >
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
            <td style="max-width:50px;">${d?.quantity}</td>
            <td data-code="${d?.batch_code}">
                <span>Chưa quét</span>
            </td>
        </tr>`)
        .hide()
        .fadeIn(200);
}

const playSound = (fileName) => {
    if (!fileName) return;
    const audio = document.createElement('audio');
    audio.src = `/storage/scan-audio/${fileName}.mp3`;
    audio?.play();
    audio?.remove();
}


document.addEventListener("DOMContentLoaded", (event) => {
    console.log(window.materialBatches)
    let video = null;
    let canvasElement = document.getElementById("canvas");
    let canvas = canvasElement.getContext("2d");
    let loadingMessage = document.getElementById("loadingMessage");
    const btnOpenModal = document.getElementById('open_scan_modal');

    btnOpenModal.addEventListener('click', function () {
        $('#camera-loading').show();
        // var outputContainer = document.getElementById("output");
        // var outputMessage = document.getElementById("outputMessage");
        // var outputData = document.getElementById("outputData");

        try {
            navigator.mediaDevices.getUserMedia({ video: { facingMode: "environment" } })
                .then(function (stream) {
                    window.localStream = stream;
                    video = document.createElement("video");
                    video.srcObject = stream;
                    video.setAttribute("playsinline", true); // required to tell iOS safari we don't want fullscreen
                    video.play();
                    requestAnimationFrame(tick);
                    $('#receiptScanModal').modal('show');
                    $('#table_list_mobile').html(window?.materialBatches?.map((d, i) => renderRow(d, i)));
                    $('#camera-loading').hide();
                })
        } catch (err) {
            $('#receiptScanModal').modal('hide');
            alert('Camera không sẵn sàng. Vui lòng thử lại sau!');
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
            loadingMessage.innerText = "⌛ Loading video..."
            if (video && (video.readyState === video.HAVE_ENOUGH_DATA)) {
                loadingMessage.hidden = true;
                canvasElement.hidden = false;

                canvasElement.height = video.videoHeight;
                canvasElement.width = video.videoWidth;
                canvas.drawImage(video, 0, 0, canvasElement.width, canvasElement.height);
                var imageData = canvas.getImageData(0, 0, canvasElement.width, canvasElement.height);
                var code = jsQR(imageData.data, imageData.width, imageData.height, {
                    inversionAttempts: "dontInvert",
                });
                if (code) {
                    if (!window?.materialBatches?.find(item => item?.batch_code == code)) {
                         playSound('invalid');
                    }else{
                        playSound('valid');
                        $(`td[data-code="${code?.data}"]`).html('<span class="text-success">Đã quét <i class="fa-solid fa-check ml-2"></i></span>');
                    }

                    drawLine(code.location.topLeftCorner, code.location.topRightCorner, "#FF3B58");
                    drawLine(code.location.topRightCorner, code.location.bottomRightCorner, "#FF3B58");
                    drawLine(code.location.bottomRightCorner, code.location.bottomLeftCorner, "#FF3B58");
                    drawLine(code.location.bottomLeftCorner, code.location.topLeftCorner, "#FF3B58");
                }
            }
            video && requestAnimationFrame(tick);
        }
    })

    window.addEventListener('resize', function () {
        if (window.innerWidth > 768) $('#receiptScanModal').modal('hide');
    })

    $(document).on('hide.bs.modal', '#receiptScanModal', function () {
        localStream.getVideoTracks().forEach(track => track.stop())
        video = null;
    })

    // handle form
    const setTotalQuantity = () => {
        let total = 0;
        $('input[data-name="quantity"]').each(function () {
            total += +$(this).val();

        })
        $('#total-quantity').html(total);
    }
    $(document).off('click');
    $(document).on('click', '.quantity_reduce_btn', function () {
        let curr_quantity = $(this).next().val();
        let min = $(this).next().prop('min');
        const default_value = $(this).next().attr('default-value');
        const batch_id = $(this).next().data('batch-id');
        const valueSet = +curr_quantity - 1;

        if (parseInt(curr_quantity) > parseInt(min)) {
            $(this).next().val(valueSet);
            if (valueSet == default_value) {
                $(`.collapse[data-batch-id="${batch_id}"]`).collapse("hide");
                $(`textarea[data-batch-id="${batch_id}"]`).val('');
            } else {
                $(`.collapse[data-batch-id="${batch_id}"]`).collapse("show");
            }
            setTotalQuantity();
        }
    })
    $(document).on('click', '.quantity_increment_btn', function () {
        const curr_quantity = $(this).prev().val();
        const max = $(this).prev().prop('max');
        const default_value = $(this).prev().attr('default-value');
        const batch_id = $(this).prev().data('batch-id');

        const valueSet = parseInt(curr_quantity) + 1;

        if (parseInt(curr_quantity) < parseInt(max)) {

            $(this).prev().val(valueSet);
            if (valueSet == default_value) {
                $(`.collapse[data-batch-id="${batch_id}"]`).collapse("hide");
                $(`textarea[data-batch-id="${batch_id}"]`).val('');
            } else {
                $(`.collapse[data-batch-id="${batch_id}"]`).collapse("show");
            }
            setTotalQuantity();
        }
    })

    $(document).on('input', 'input[type="number"]', _.debounce(function () {

        const change_value = $(this).val();
        const default_value = $(this).attr('default-value');
        const min = $(this).prop('min');
        const max = $(this).prop('max');
        const batch_id = $(this).data('batch-id');

        if (parseInt(change_value) < parseInt(min)) {
            $(this).val(min);
        }
        if (parseInt(change_value) > parseInt(max)) {
            $(this).val(max);
        }
        var closestTr = $(this).closest('tr.item__product');
        var materialId = closestTr.find('#material_id').val();
        var detailIssueId = closestTr.find('#issueMaterialId').val();
        var warehouse_id = closestTr.find('#warehouse_id').val();
        var quantityStock = closestTr.find('#quantityStock').val();
        var table = closestTr.next('tr').find('#table-add');
        var data = {
            material_id: materialId,
            warehouse_id: warehouse_id,
            quantity: $(this).val(),
            quantityStock: quantityStock,
            detailIssueId: detailIssueId
        };
        $.ajax({
            method: "get",
            url: "/admin/goods-issue/get-more-quantity",
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            },
            data: data,
            success: function (res) {
                let materialBatches = window.materialBatches || [];
                materialBatches = materialBatches?.filter(item => item?.material_id != materialId);
                window.materialBatches = [...materialBatches, ...res];
                table.find('tr').remove();
                $.each(res, function (index, value) {
                    var newRow = `<tr>
                                        <td><input type="hidden"
                                        data-name="batchCode"
                                        data-code="${value?.batch_code}"
                                        name="materialDetai[${value.batch_id}][issueMaterial]"
                                        value=" ${data.detailIssueId} "><input type="hidden"
                                        name="materialDetai[${value.batch_id}][quantity_actual]"
                                        value=" ${value.quantity} "> ${value.batch_code}</td>
                                        <td>Số lượng: ${value.quantity}</td>
                                      </tr>`;
                    table.append(newRow);
                });

            },
            error: function (xhr, status, error) {
                console.error(error);
            }
        });
        var collapseElement = $(`.collapse[data-batch-id="${batch_id}"]`);
        var textareaElement = collapseElement.find('textarea');
        if (parseInt($(this).val()) == parseInt(default_value)) {
            collapseElement.collapse("hide");
            textareaElement.val('');
            textareaElement.prop('required', false);
        } else {
            collapseElement.collapse("show");
            textareaElement.prop('required', true);
        }
        totalQty = 0;
        $('input[data-name="quantity"]').each(function () {
            var quantity = parseInt($(this).val()) || 0;
            totalQty += quantity;
        });
        $('#table-wrapper .widget__amount').text(totalQty);
    }, 500))
});

