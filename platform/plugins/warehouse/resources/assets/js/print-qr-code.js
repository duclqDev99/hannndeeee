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
                dataType: 'html',
                success: function(response) {
                    let tempDiv = $('<div id="print-container" style="display: none;">').html(response);
                    let contentToPrint = tempDiv[0];
                    const currentDate = moment().format('YYYY-MM-DD-HHmmss');
                    let options = {
                        printable: contentToPrint,
                        type: 'html',
                        documentTitle: 'warehouse-material-' + currentDate,
                        style: `
                            @page {
                            size: 100mm 60mm;
                            margin:0;
                            padding:0;
                            }
                            body {
                            width: 100mm;
                            height:60mm;
                            }
                            #test{text-align: center}
                            .item > div > ul > li {
                                list-style:none;

                            }
                            .item div:nth-child(2) {
                                margin-left: -20px;
                            }
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

    window.printQRCode = printQRCode;
})



