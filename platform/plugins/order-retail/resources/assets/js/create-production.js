
document.addEventListener("DOMContentLoaded", (event) => {
    $(document).on('click', '#submit-pick-purchase-order-btn', function () {
        const order_code = window.pick_order_code
        if (order_code) {
            console.log(order_code)
            $('input[name="order_code"]').val(order_code);
            $('#searchProductModal').modal('hide');

            $.ajax({
                url :'/admin/retail/sale/purchase-order/append-to-production-form',
                method : 'POST',
                data: {order_code},
                dataType: 'html',
                success: html => {
                    $('.order-pick-info').html(html);
                },
                error: err => console.log(err)
            })
        } else
            alert('Vui lòng tìm kiếm và chọn 1 YCSX')
    });

});