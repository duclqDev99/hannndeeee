
window.waitForElementToExist('#confirm-payment-modal').then(element => {
    const orderId = $(element).data('order-id');
    let source; 
    const qrSuccessChannel = new BroadcastChannel('QR-customer-channel-success');

    $(element).on('shown.bs.modal', (event) => {
        if (!window.EventSource) {
            // EventSource không được hỗ trợ
            console.log("EventSource is not supported in your browser.");
            return;
        }
        source = new EventSource(`/api/v1/order-transactions-client/notifications/${orderId}`);

        source.onmessage = function(event) {
            const data = JSON.parse(event.data);
            if (data.error_code == 0) {
                source.close();
                let modalQR = document.querySelector('#confirm-payment-modal .modal-body');
                console.log('Đã thanh toán');
                $('.btn-trigger-confirm-payment-qr').remove();
                let dataQR = {
                    'success' : 1
                }
                qrSuccessChannel.postMessage(dataQR)
                showSuccessfulPaymentProduct($(modalQR).find('.text-start'));

            }
        };
    
        source.onerror = function(event) {
        };

    })
    $(element).on('hide.bs.modal', (event) => {
        if (source) {
            source.close();
        }
    })
    window.addEventListener('beforeunload', function() {
        if (source) {
            source.close();
        }
    });
})

function showSuccessfulPaymentProduct(element){
    $(element).html(` 
    <div class="widget-payment">
        <div class="success-animation">
            <svg class="checkmark" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 52 52"><circle class="checkmark__circle" cx="26" cy="26" r="25" fill="none" /><path class="checkmark__check" fill="none" d="M14.1 27.2l7.1 7.2 16.7-16.8" /></svg>
        </div>
        <div class="info">
            <h4>Xác nhận thanh toán thành công!!</h4>
        </div>
    </div>
    `);

    //Đổi trạng thái đơn thành success
    $('#main-order-content').find('.badge').removeClass('bg-warning');
    $('#main-order-content').find('.badge').addClass('bg-info')
    $('#main-order-content').find('.badge').text('Completed')
}


