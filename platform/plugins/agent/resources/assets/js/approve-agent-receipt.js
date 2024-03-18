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
const checkProduct = (productsData, warehouseId) => {
    $.ajax({
        url: route('hub-stock.checkQuantityBatch', { id: warehouseId }),
        data: { productsData: productsData },
        type: 'GET',
        success: (response) => {
            const products = response.data;

            // Assuming you have a tbody element in your table
            const tbody = $('table.table-missing tbody');

            // Clear existing content in the tbody
            tbody.empty();

            // Check if there are missing products
            if (products.length > 0) {
                // Iterate over the products and append them to the tbody
                products.forEach(product => {
                    console.log(product);
                    const row = `
                    <tr class="">
                    <td class="pl5 p-r5 min-width-200-px">${product.product.name}</td>
                    <td class="pl5 p-r5">
                        <div class="inline_block">
                            <span>Mã:
                                <strong>${product.product.sku}</strong>
                            </span>
                        </div>
                    </td>
                    <td class="pl5 p-r5">
                        <div class="inline_block">
                            <span>SL lô tồn:
                                <strong>${product.quantityInStock}</strong>
                            </span>
                        </div>
                    </td>
                    <td class="pl5 p-r5">
                        <div class="inline_block">
                            <span>SL đề xuất:
                                <strong>${product.quantity}</strong>
                            </span>
                        </div>
                    </td>
                </tr>
                                `;
                    tbody.append(row);
                });
            } else {
                // If no missing products, you can display a message or handle it as needed
                const noProductMessage = '<tr><td class="text-center"> Trong kho đủ số lượng yêu cầu</td></tr>';
                tbody.append(noProductMessage);
            }
        },
        error: (res) => {
            Botble.handleError(res)
        },
    })
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
    $(document).on('change', '#hub_id', function () {
        const hubId = $(this).val()
        const tbody = $('table.table-missing tbody');

        // Clear existing content in the tbody
        tbody.empty();
        $.ajax({
            url: route('hub-stock.getWarehouse', { id: hubId }),
            type: 'GET',
            success: (res) => {
                const dropdown = $('#warehouse_id')
                res.forEach(warehouse => {
                    dropdown.append('<option value="' + warehouse.id + '">' + warehouse.name + '</option>');
                });

            },
            error: (res) => {
                Botble.handleError(res)
            },
        })
    })
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
    $(document).on('change', '#warehouse_id', function () {
        var warehouseId = $(this).val()
        var productsData = [];

        $('table.table-order tbody tr').each(function () {
            var productId = $(this).find('.product-id').val();

            var quantity = $(this).find('.input_quantity').val();

            productsData.push({
                product_id: productId,
                quantity: quantity
            });
        });
        if (warehouseId >= 1) {
            checkProduct(productsData, warehouseId)
        }
        else {
            const tbody = $('table.table-missing tbody');
            tbody.empty();
        }
    });
    $(document).on('input', '.input_quantity', function () {
        if ($('#warehouse_receipt_type').val() !== $('#warehouse_type').val() || $('#warehouse_receipt_id').val() !== $('#warehouse_receipt_id').val()) {
            let quantity_input = parseInt($(this).val());
            let min = parseInt($(this).attr('min'));
            let max = parseInt($(this).attr('max'));
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

    $('#approveProposal').click(function (event) {
        event.preventDefault();
        $('#expectDate').val($('#expected_date').val())
        $('#descriptionForm').val($('#description').val())
        var productsData = [];

        $('.item__product').each(function() {
            var productId = $(this).find('.product-id').val();
            var quantity = $(this).find('input[name="product[' + productId + '][quantity]"]').val();

            // Thêm dữ liệu vào mảng
            productsData.push({productId,  quantity});
        });
        $('#hiddenData').val(JSON.stringify(productsData));
        let submit = true;
        if ($('#hub_id').val() == 0) {
            submit = false;
            toastr.clear()
            toastr['warning']('Vui lòng chọn hub xuất')
            return;
        }
        if ($('#warehouse_id').val() == 0) {
            submit = false;
            toastr.clear()
            toastr['warning']('Vui lòng chọn kho xuất ')
            return;
        }
        if (submit) {
            $('#form-done').submit();
        }


    });
})
