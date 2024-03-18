document.addEventListener("DOMContentLoaded", (event) => {
    let catches = [];

    function debounce(func, wait, immediate) {
        var timeout;
        return function () {
            var context = this, args = arguments;
            var later = function () {
                timeout = null;
                if (!immediate) func.apply(context, args);
            };
            var callNow = immediate && !timeout;
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
            if (callNow) func.apply(context, args);
        };
    }

    $(document).on('focus', 'input[name="order_code"]', function () {
        $('#searchProductModal').modal('show');
    })

    $(document).on('show.bs.collapse', '.collapse', function () {
        $('.collapse.show').collapse('hide');
    });

    $(document).on('change', '.form-check-input', function () {
        
        const loadingElement = '<div class="loading-spinner"></div>';
        const target = $(this).data('target-id');
        const order_code = $(this).val();
        window.pick_order_code = order_code;

        if (!catches.find(code => code == order_code)) {
            catches.push(order_code);

            $(`div[id="${target}"]`).append(loadingElement).addClass('on-loading position-relative')
            $.ajax({
                url: '/admin/retail/sale/product/get-by-order',
                method: 'POST',
                data: { order_code },
                dataType: 'html',
                success: html => {
                    $(`div[id="${target}"]`).html(html);
                }
            })
        }
    });

    $(document).on('input', 'input[name="search_order"]', debounce(function () {
        const val = $(this).val();
        if(val == '') catches = [];
        $.ajax({
            url: '/admin/retail/sale/purchase-order/search',
            method: 'POST',
            data: { search: val, type: $('input[name="search_type"]').val(), },
            success: res => {

                $('.order-preview-wrapper').show();
                $('#products-response')
                    .html(res.data.map((item, index) => {
                        return `<div class="w-100">
                       <div class="py-1">  
                            <label for="${index}" 
                                class="order-preview-label text-primary  d-inline-flex gap-3" 
                                data-bs-toggle="collapse" 
                                data-bs-target="#collapse${index}"
                            > 
                                <div>
                                    <input id="${index}" 
                                        class="form-check-input form-control" type="radio" name="order-code" 
                                        data-target-id="collapse${index}" 
                                        data-order-code="${item.code}" 
                                        value="${item.code}"
                                    >
                                </div>
                            ${item.code}</label>
                            <div>
                                <div class="mt-2 collapse" id="collapse${index}"> </div>
                            </div>
                       </div>
                   </div>`
                    }));

                if (res.data.length > 0) {
                    $('#count-result').html(`(${res.data.length})`).show();
                } else {
                    $('#count-result').hide();
                    $('#products-response').html('<span>No results found</span>')
                }
            }
        })
    }, 200))
});