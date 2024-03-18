const { isSet } = require("lodash");

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

$(document).ready(function () {
    $(document).on('input', '.input_quantity', function () {
        let quantity_input = parseInt($(this).val());
        let min = parseInt($(this).attr('min'));
        let max = parseInt($(this).attr('max'));
        if ($('#is_warehouse').val() != 3) {
            if (quantity_input < min || quantity_input === '' || isNaN(quantity_input)) {
                $(this).val(min);
                toastr.clear();
                toastr['warning']('Số lượng không được nhỏ hơn 1 ');
            }
            if (quantity_input > max) {
                $(this).val(max);
                toastr.clear();
                toastr['warning']('Đã vượt qua số lượng sản phẩm trong kho ');
            }
        }
        let totalAmount = 0;
        let totalQuantity = 0;
        $('.item__product').each(function () {
            const quantity = parseFloat($(this).find('.input_quantity').val()) || 0;
            const price = parseFloat($(this).find('.price').val()) || 0;
            totalAmount += quantity * price;
            totalQuantity += quantity;
        });

        $('.widget__totl__amount').text(format_price(totalAmount));
        $('.widget__quantity').text(totalQuantity);
    })

    $('.btn-primary').click(function (event) {
        event.preventDefault();
        let submit = true;
        if ($('#warehouse_receipt_type').val() === $('#warehouse_type').val() && $('#warehouse_receipt_id').val() === $('#warehouse_receipt_id').val()) {
            $('#approve').submit();
        }
        else {
            $('.table-order tr.item__product').each(function () {
                const $row = $(this);
                const quantity = parseInt($row.find('input[name^="product"][name$="[quantity]"]').val());
                const quantityStock = parseInt($row.find('input[name^="product"][name$="[quantityStock]"]').val());
                if (quantity > quantityStock && quantityStock > 0) {
                    submit = false;
                    toastr.clear()
                    toastr['warning']('Đã vượt qua số lượng sản phẩm trong kho ')
                    return false;
                }
            });
            if (submit) {
                $('#approve').submit();
            }
        }
    });
})
