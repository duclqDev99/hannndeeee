


$(document).ready(function() {
    $(document).on('click', '#open-modal-view-flowchart',function () {
        console.log('test');
        $('#print-qrcode').val('data-id')
        let url =  $(this).attr('data-target');
        $('.dataTables_processing').attr('style','display :block')
        $.ajax({
            url:url,
            type: 'GET',
            success: function(response) {
                $('#modalFlowchart').html(response);
                $('#modal-view-flowchart').modal('show');
                $('.dataTables_processing').attr('style','display :none')
            },
            error: function(error) {
                console.log(error);
                $('.dataTables_processing').attr('style','display :none')
            }
        });
    });

    $(document).on('click', '.close-modal-view-flowchart',function () {
        $('#modal-view-flowchart').modal('hide');
        $('#modalFlowchart').html('');
    });


    // event onclick button update status
    $(document).on('click', '#update-stauts',function () {
        let url =  $(this).attr('data-target');
        $('.dataTables_processing').attr('style','display :block')
        $.ajax({
            url:url,
            type: 'GET',
            success: function(response) {
                var button = $('.buttons-reload');
                if (button) {
                    button.click();
                }
                $('.dataTables_processing').attr('style','display :none')
                message('success', `Cập nhật trạng thái thành công!`, 'Thành công');
            },
            error: function(error) {
                console.log(error);
                $('.dataTables_processing').attr('style','display :none')
                message('error', `Cập nhật trạng thái thất bại!`, 'Thất bại');
            }
        });
    });

    //open modal table procedure order by group_id
    $(document).on('click', '#open-modal-table-procedure-order',function () {
        $('#print-qrcode').val('data-id')
        let url =  $(this).attr('data-target');
        $('.dataTables_processing').attr('style','display :block')
        $.ajax({
            url:url,
            type: 'GET',
            success: function(response) {
                $('#modalFlowchart').html(response);
                $('#modal-table-procedure-order').modal('show');
                $('.dataTables_processing').attr('style','display :none')

            },
            error: function(error) {
                console.log(error);
                $('.dataTables_processing').attr('style','display :none')
            }
        });
    });

    $(document).on('click', '.close-modal-table-procedure-order',function () {
        $('#modal-table-procedure-order').modal('hide');
        $('#modalFlowchart').html('');
    });

    //open modal form procedure order by group_id
    $(document).on('click', '#open-modal-form-procedure-order',function () {
        let formType =  $(this).attr('data-type');
        console.log(formType);
        if(formType === "create"){
            $.ajax({
                url:url,
                type: 'GET',
                success: function(response) {
                    $('#modalFlowchart').html(response);
                    $('#modal-form-procedure-order').modal('show');
                    $('.dataTables_processing').attr('style','display :none')

                },
                error: function(error) {
                    console.log(error);
                    $('.dataTables_processing').attr('style','display :none')
                }
            });

        }else{
            let url =  $(this).attr('data-target');
            $.ajax({
                url:url,
                type: 'GET',
                success: function(response) {
                    $('#modalFlowchart').html(response);
                    $('#modal-form-procedure-order').modal('show');
                    $('.dataTables_processing').attr('style','display :none')

                },
                error: function(error) {
                    console.log(error);
                    $('.dataTables_processing').attr('style','display :none')
                }
            });
        }

    });

    $(document).on('click', '.close-modal-form-procedure-order',function () {
        $('#modal-table-procedure-order').modal('hide');
        $('#modalFlowchart').html('');
    });

    const message = (type ,message, title) => {
        toastr.clear()

        toastr.options = {
            closeButton: true,
            positionClass: 'toast-bottom-right',
            showDuration: 1000,
            hideDuration: 1000,
            timeOut: 60000,
            extendedTimeOut: 1000,
            showEasing: 'swing',
            hideEasing: 'linear',
            showMethod: 'fadeIn',
            hideMethod: 'fadeOut',
        }
        toastr[type](message, title);
    }
});

