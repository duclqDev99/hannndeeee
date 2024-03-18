document.addEventListener("DOMContentLoaded", (event) => {
    let scanner;
    $(document).on('show.bs.modal', '#receiptScanModal', function () {
        $('#camera-loading').show();
        $('#table-content').clone().appendTo('#scanner_content');
        $('#table-wrapper').html('');
        const audio = document.getElementById('mySound');
        let batch_codes = [];
        $('input[data-name="batch_codes"]').map(function (item) {
            return batch_codes.push($(this).val());
        })

        batch_codes = [...new Set(batch_codes)];
        let batch_codes_scanned = batch_codes.map(item => ({ batch_code: item, scanned: false }))

        scanner = new Instascan.Scanner({
            video: document.getElementById('camera-preview'),
            mirror: false,
            scanPeriod: 2,
        });

        Instascan.Camera.getCameras().then(function (cameras) {
            let camera;
            if (cameras.length > 1) {
                camera = cameras[1];
            }
            else if (cameras.length > 0) {
                camera = cameras[0];
            } else {
                console.error('No cameras found.');
            }

            if (camera) {
                // FacedMode 
                // camera.facingMode = "environment";
                scanner.start(camera);
            }
        }).catch(function (e) {
            console.log(e)
        });

        scanner.addListener('active', function (barcodeScanned) {
            $('#camera-loading').hide();
        });

        scanner.addListener('scan', function (barcodeScanned) {
            audio.play();
            $(`.scanner-success[data-batch-code="${barcodeScanned}"]`).show();
            $(`span[data-batch-code="${barcodeScanned}"]`).css('color', '#36c6d3')
        });

    })

    $(document).on('hide.bs.modal', '#receiptScanModal', function () {
        scanner && scanner.stop();
        $('#table-wrapper').html($('#scanner_content').children())
        $('#scanner_content').html('');
    })

    // handle form
    const setTotalQuantity = () => {
        let total = 0;
        $('input[data-name="quantity"]').each(function() {
            total += +$(this).val();

        })
        $('#total-quantity').html(total);
    }
    $(document).off('click');
    $(document).on('click', '.quanity_reduce_btn', function() {
        let curr_quantiry = $(this).next().val();
        let min = $(this).next().prop('min');
        if (curr_quantiry > min) {
            $(this).next().val(+curr_quantiry - 1);
            setTotalQuantity();
        }
    })
    $(document).on('click', '.quanity_increment_btn', function() {
        let curr_quantiry = $(this).prev().val();
        let max = $(this).prev().prop('max');
        if (curr_quantiry < max) {
            $(this).prev().val(+curr_quantiry + 1);
            setTotalQuantity();
        }
    })

    $(document).on('change', 'input[type="number"]', function() {
        const change_value = $(this).val();
        const default_value = $(this).attr('default-value');
        const min = $(this).prop('min');
        const max = $(this).prop('max');

        if (change_value < min || change_value > max) {
            $(this).val(default_value);
        }
    })
});

