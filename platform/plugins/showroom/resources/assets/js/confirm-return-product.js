toastr.options = {
    closeButton: true,
    positionClass: 'toast-bottom-right',
    showDuration: 500,
    hideDuration: 500,
    timeOut: 500,
    extendedTimeOut: 500,
    showEasing: 'swing',
    hideEasing: 'linear',
    showMethod: 'fadeIn',
    hideMethod: 'fadeOut',
}

document.addEventListener("DOMContentLoaded", (event) => {
    $(document).on('click', '#confirm-return-product', function () {
        const showroom_order_id = $(this).data('showroom-order-id');
        event.preventDefault()

        const _self = $(event.currentTarget)

        $httpClient.make()
            .withButtonLoading(_self)
            .post('/admin/showroom-products/confirm-return-product', {showroom_order_id})
            .then(({ data }) => {
               if (!data.error) {
                    $('#return-product-modal').modal('hide');
                    $('#main-order-content').load(
                        `${window.location.href} #main-order-content > *`)
                    return toastr.success(data.message)
                }else{
                    return toastr.error(data.message)
                }
            })
    })
});
