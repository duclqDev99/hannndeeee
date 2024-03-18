document.addEventListener("DOMContentLoaded", (event) => {

    const loadPickAddressFormItem = (showroom_code, service_type) =>{
        $('#form-body')
            .html(`<div class="loading-spinner"> </div>`)
            .addClass('on-loading');

        $.ajax({
            url: '/admin/ecommerce/settings/pick-address-item',
            data: {showroom_code, service_type},
            dataType: 'html',
            success: res => {
                $('#form-body').html(res).removeClass('on-loading');
            }

        })
    }

    $(document).on('click', 'button[href="#setting-pick-address-modal"]', function () {
        const showroom_code = $('select[name="showroom_code"]').val();
        const serviceType = $(this).data('service-type');
        $('input[name="service_type"]').val(serviceType);

        loadPickAddressFormItem(showroom_code, serviceType)
    })

    $(document).on('change', 'select[name="showroom_code"]', function(){
        const showroom_code = $(this).val();
        const serviceType = $('input[name="service_type"]').val();

        loadPickAddressFormItem(showroom_code, serviceType);
    })
    

    // $(document)
    //     .off('click', '#add-shipping-rule-item-button')
    //     .on('click', '#add-shipping-rule-item-button', function (event) {

    //         console.log('asdasd');
    //         event.preventDefault()

    //         let _self = $(this)
    //         let form = $('#setting-pick-address-modal').find('form');

    //         if (form.valid && !form.valid()) {
    //             return
    //         }

    //         _self.attr('disabled', 'disabled')
    //         let submitInitialText = _self.html()
    //         _self.html('<i class="fa fa-gear fa-spin me-2"></i> ' + ' đang cập nhật')

    //         $httpClient.make()
    //             .withButtonLoading(_self)
    //             .post($(form).prop('action'), $(form).serialize())
    //             .then(({ data }) => {
    //                 if (!data.error) {
    //                     // $('#main-order-content').load(`${window.location.href} #main-order-content > *`)
    //                     // _self.closest('div').remove()
    //                     Botble.showSuccess(data.message)
    //                 } else {
    //                     Botble.showError(data.message)
    //                 }
    //             })
    //     })
});