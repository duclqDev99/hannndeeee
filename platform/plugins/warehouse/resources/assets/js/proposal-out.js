toastr.options = {
    closeButton: true,
    positionClass: 'toast-bottom-right',
    onclick: function () { window.location.href = `${data.action_url}`; },
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
    $('.widget__quantity').on('input', function () {
        let quantity_input = parseInt($(this).val());
        let min = parseInt($(this).attr('min'));
        let max = parseInt($(this).attr('max'));
        if (quantity_input < min) {
            $(this).val(min)

        }
        if (quantity_input > max) {
            $(this).val(max)

        }
        let totalAmount = 0;
        let totalQuantity = 0;
        $('.item__product').each(function () {
            const quantity = parseFloat($(this).find('.widget__quantity').val()) || 0;
            const price = parseFloat($(this).find('.widget__price').val()) || 0;
            totalAmount += quantity * price;
            totalQuantity += quantity;
        });

        // Update the display of total amount and total quantity
        $('.widget__totl__amount').text(format_price(totalAmount));
        $('.widget__quantity').text(totalQuantity);
    })
    $('.btn-primary').click(function (event) {
        event.preventDefault();
        let submit = true; // Assuming 'submit' is a global variable

        $('.table-order tr.item__product').each(function () {
            let $row = $(this);
            let quantity = parseInt($row.find('input[name^="material"][name$="[quantity]"]').val());
            let quantityStock = parseInt($row.find('input[name^="material"][name$="[quantityStock]"]').val(), 10);
            if (quantity > quantityStock || quantity <= 0 || quantityStock == 0) {
                toastr.clear();
                toastr['warning']('Trong kho không đủ nguyên phụ liệu ');
                submit = false;
                return false;
            }
        });
        if (submit) {
            $('#approve').submit();
        }
    });
})
