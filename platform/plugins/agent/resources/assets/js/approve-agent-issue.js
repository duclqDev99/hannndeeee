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
    $.ajax({
        url: route('hub-warehouse.getHub'),
        type: 'GET',
        success: (res) => {
            const dropdown = $('#hub_id')
            res.forEach(hub => {
                dropdown.append('<option value="' + hub.id + '">' + hub.name + '</option>');
            });
        },
        error: (res) => {
            Botble.handleError(res)
        },
    })
    $(document).on('input', '.input_quantity', function () {
        let quantity_input = parseInt($(this).val());
        let min = parseInt($(this).attr('min'));
        let max = parseInt($(this).attr('max'));
        console.log(max);
        if (quantity_input < min || quantity_input == '' || isNaN(quantity_input)) {
            $(this).val(min)
            toastr.clear()
            toastr['warning']('Số lượng không được nhỏ hơn 1 ')
        }
        if (quantity_input > max) {
            $(this).val(max)
            toastr.clear()
            toastr['warning']('Đã vượt qua số lượng sản phẩm trong kho ')
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
    $('#openApproveModel').click(function (event) {
        event.preventDefault();
        let submit = true;
        if ($('#hub_id').val() == 0) {
            submit = false;
            toastr.clear()
            toastr['warning']('Vui lòng chọn hub nhập')
            return;
        }
        if ($('#warehouse_id').val() == 0) {
            submit = false;
            toastr.clear()
            toastr['warning']('Vui lòng chọn kho nhập ')
            return;
        }

        $('.table-order tr.item__product').each(function () {
            const $row = $(this);
            const quantity = parseInt($row.find('input[name^="product"][name$="[quantity]"]').val());
            const quantityStock = parseInt($row.find('input[name^="product"][name$="[quantityStock]"]').val());
            if (quantity > quantityStock || quantityStock <= 0) {
                submit = false;
                toastr.clear()
                toastr['warning']('Đã vượt qua số lượng sản phẩm trong kho ')
                return false;
            }
        });
        if (submit) {
            $('#approve').submit();
        }

    });
    $(document).on('change', '#hub_id', function () {
        const hubId = $(this).val()
        $.ajax({
            url: route('hub-stock.getWarehouse', { id: hubId }),
            type: 'GET',
            success: (res) => {
                const dropdown = $('#warehouse_id')
                dropdown.empty()
                dropdown.append(`<option value="0">Chọn kho</option>`)
                res.forEach(warehouse => {
                    dropdown.append('<option value="' + warehouse.id + '">' + warehouse.name + '</option>');
                });

            },
            error: (res) => {
                Botble.handleError(res)
            },
        })
    })
})
