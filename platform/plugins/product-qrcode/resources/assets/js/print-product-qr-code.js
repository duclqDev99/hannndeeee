import printJS from 'print-js';
import moment from 'moment';
$(document).ready(function () {
    const printProductQRCode = () => {
        $(document).off().on('click','.print-qr-button',function (){
            let url = $(this).attr('data-url')
            $(this).attr('disabled',true)
            let thisBtn = this
            $.ajax({
                type : "GET",
                url : url,
                dataType: 'html',
                success: function(response) {
                    let tempDiv = $('<div id="print-container" style="display: none;">').html(response);
                    let contentToPrint = tempDiv[0];
                    const currentDate = moment().format('YYYY-MM-DD-HHmmss');
                    let options = {
                        printable: contentToPrint,
                        type: 'html',
                        documentTitle: 'product-qr-' + currentDate,
                        style: `
                            @page {
                                font-family: 'Arial', sans-serif !important;
                                font-weight: normal;
                              size: 30mm 30mm;
                              margin: 5px;
                            }
                            body {
                              width: 30mm;
                              margin: 5px;
                              height:30mm;
                        `,
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

    window.printProductQRCode = printProductQRCode;
})



