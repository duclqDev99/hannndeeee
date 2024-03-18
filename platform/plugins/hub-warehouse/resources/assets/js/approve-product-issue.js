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
    console.log(123);
    $(document).on('blur', '.input_quantity', function () {
        let quantity_input = parseInt($(this).val());
        let min = parseInt($(this).attr('min'));
        let max = parseInt($(this).attr('max'));
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
        let submit = true;
        event.preventDefault();
        $('.table-order tr.item__product').each(function () {
            const $row = $(this);
            const quantity = parseInt($row.find('input[name^="product"][name$="[quantity]"]').val());
            const quantityStock = parseInt($row.find('input[name^="product"][name$="[quantityStock]"]').val());
            if (quantity > quantityStock || quantityStock <= 0) {
                submit = false;
                toastr.clear()
                toastr['warning']('Đã vượt qua số lượng sản phẩm trong kho vui lòng nhập thêm')
                return false;
            }
        });
        if (submit) {
            $('#approve').submit();
        }
    });
})
