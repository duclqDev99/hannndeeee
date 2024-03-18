$(document).ready(function () {
    $('.widget__quantity').on('input', function () {
        var $tr = $(this).closest('tr.item__product');
        var $priceInput = $tr.find('.widget__price');
        var $totalPriceInput = $tr.find('.value__total__price');
        var $totalPriceSpan = $tr.find('.widget__total__price');
        var quantity = parseInt($(this).val()) || 0;
        var price = parseFloat($priceInput.val()) || 0;
        var totalPrice = quantity * price;
        $totalPriceInput.val(totalPrice);
        $totalPriceSpan.text(totalPrice);
        updateTotalAmount();
    })
    function updateTotalAmount() {
        var total_price = 0;
        $('.item__product').each(function () {
            var quantity = parseInt($(this).find('.widget__quantity').val()) || 0;
            var price = parseFloat($(this).find('.widget__price').val()) || 0;
            total_price += quantity * price;
        });
        $('input[name="ac_amount"]').val(total_price);
        $('.widget__amount').text((total_price));
    }
})
