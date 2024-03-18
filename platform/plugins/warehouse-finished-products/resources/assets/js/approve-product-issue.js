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
function parseDate(input) {
    const parts = input.match(/(\d+)/g);
    // Note: months are 0-based in JavaScript Date
    return new Date(parts[2], parts[1] - 1, parts[0]);
}
$(document).ready(function () {
    $(document).on('input', '.input_quantity', function () {
        let quantity_input = parseInt($(this).val());
        let min = parseInt($(this).attr('min'));
        let max = parseInt($(this).attr('max'));
        if (quantity_input < min || quantity_input == '' || isNaN(quantity_input)) {
            $(this).val(min)
            toastr.clear()
            toastr['warning']('Số lượng phải là giá trị dương và khác 0 ')
        }
        if (quantity_input > max) {
            $(this).val(max)
            toastr.clear()
            toastr['warning']('Vượt quá số lượng trong kho')
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

    $('#examineSubmit').click(function (event) {
        event.preventDefault();
        let submit = true;

        const expectedDateStr = $('#expected_date').val(); // Get the value from the input field
        if(expectedDateStr == "")
        {
            submit = false;
            toastr.clear()
            toastr['warning']('Ngày dự kiến là bắt buộc')
            return false;
        }
        const expectedDate = parseDate(expectedDateStr);
        $('#expectDate').val($('#expected_date').val())
        $('#descriptionForm').val($('#description').val())
        const currentDate = new Date();
        currentDate.setHours(0, 0, 0, 0); // Resets hours, minutes, seconds and milliseconds

        if (expectedDate < currentDate ) {
            submit = false;
            toastr.clear()
            toastr['warning']('Ngày phải bằng hoặc sau ngày hôm này')
            return false;
        }
        $('.table-order tr.item__product').each(function () {
            const $row = $(this);
            const quantity = parseInt($row.find('input[name^="product"][name$="[quantity]"]').val());
            if (quantity < 0) {
                submit = false;
                toastr.clear()
                toastr['warning']('Số lượng sản phẩm đề xuất không phù hợp ')
                return false;
            }
        });
        var dataProduct = [];

        $('.item__product').each(function () {
            var productId = $(this).find('.product-id').val();
            var quantity = $(this).find('input[name="product[' + productId + '][quantity]"]').val();

            // Thêm dữ liệu vào mảng
            dataProduct.push({ productId, quantity });
        });
        $('#dataProduct').val(JSON.stringify(dataProduct));
        if (submit) {
            $('#examine').submit();
        }
    });

})
