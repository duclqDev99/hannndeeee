
const jsQR = require("jsqr");

document.addEventListener('DOMContentLoaded', function () {
    let video = null;
    let canvasElement = document.getElementById("canvas");
    let canvas = canvasElement.getContext("2d");
    let loadingMessage = document.getElementById("loadingMessage");
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

                    $('#material_batch_qr_scan').modal('show');
                    $('#table-content').clone().appendTo('#scanner_content');
                    $('#table-wrapper').html('');
                    $('#camera-loading').hide();
                })
        } catch (err) {
            console.log(err);
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
                    drawLine(code.location.topLeftCorner, code.location.topRightCorner, "#FF3B58");
                    drawLine(code.location.topRightCorner, code.location.bottomRightCorner, "#FF3B58");
                    drawLine(code.location.bottomRightCorner, code.location.bottomLeftCorner, "#FF3B58");
                    drawLine(code.location.bottomLeftCorner, code.location.topLeftCorner, "#FF3B58");

                    $.ajax({
                        url: '/admin/material-batchs/qr-scan',
                        method: 'POST',
                        dataType: 'html',
                        data: { batch_code: code?.data, 'viewport': 'mobile' },
                        success: (res) => {
                            $('#scanner_content').html(res)
                            $("#scanner_content").show();
                        }
                    })
                }
            }
            video && requestAnimationFrame(tick);
        }
    })

    window.addEventListener('resize', function () {
        if (window.innerWidth > 768) $('#material_batch_qr_scan').modal('hide');
    })

    $(document).on('hide.bs.modal', '#material_batch_qr_scan', function () {
        localStream.getVideoTracks().forEach(track => track.stop())
        video = null;
        $('#scanner_content').html('');
        $("#scanner_content").hide();
    })

})




