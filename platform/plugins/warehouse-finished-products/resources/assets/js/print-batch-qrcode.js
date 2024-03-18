import printJS from 'print-js';
import moment from 'moment';
$(document).ready(function () {
    const printQRCode = () => {
        $(document).off().on('click','.print-qr-button',function (){
            let url = $(this).attr('data-url')
            $(this).attr('disabled',true)
            let thisBtn = this
            $.ajax({
                type : "GET",
                url : url,
                beforeSend: function(xhr) {
                    // Thiết lập header Authorization với token
                    xhr.setRequestHeader("Authorization", "Bearer " + $('input#api_key').val());
                },
                dataType: 'html',
                success: function(response) {
                    let tempDiv = $('<div id="print-container" style="display: none;">').html(response);
                    let contentToPrint = tempDiv[0];
                    console.log('content: ', contentToPrint);
                    const currentDate = moment().format('YYYY-MM-DD-HHmmss');
                    let options = {
                        printable: contentToPrint,
                        type: 'html',
                        documentTitle: 'warehouse-material-' + currentDate,
                        font_size: '8pt',
                        showModal: false,
                        modalMessage: 'Đang chuẩn bị in...',
                        onPrintDialogClose: successClosePopup(thisBtn)
                    };

                    printJS(options);
                },
                error: function(response) {
                    $(thisBtn).removeAttr("disabled")
                    $('#print-container').remove()
                    Botble.showError('Có lỗi trong quá trình xử lý')
                }
            });

        })

    }
    const successClosePopup = (thisBtn) => {
        $(thisBtn).removeAttr("disabled")
        $('#print-container').remove()
    }

    window.printQRCode = printQRCode;
})



