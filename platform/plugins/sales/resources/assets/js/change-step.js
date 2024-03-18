import Botble from "./utils";

document.addEventListener("DOMContentLoaded", function(){
    
    // prev step
    $(document).on('click', '.prev_step_btn', function(e){
        e.preventDefault();
        const order_id = $(this).attr('href').split('/').pop();
        if(confirm('Đồng ý xác nhận đơn hàng chưa hợp lệ?')){
            $.ajax({
                url: '/admin/customer/order/ajax/prev-step',
                method: 'POST',
                data: {order_id},
                success: (res) => {
                    $('#botble-sales-tables-order-table').DataTable().ajax.reload();
                }
            })
        }
    })  

    // next step
    $(document).on('click', '.next_step_btn', function(e){
        e.preventDefault();
        const order_id = $(this).attr('href').split('/').pop();
        if(confirm('Đồng ý chuyển tiếp trạng thái')){
            $.ajax({
                url: '/admin/customer/order/ajax/next-step',
                method: 'POST',
                data: {order_id},
                success: (res) => {
                    Botble.showSuccess('Cập nhật thành công!')
                    $('#botble-sales-tables-order-table').DataTable().ajax.reload();
                }
            })
        }
    })

})