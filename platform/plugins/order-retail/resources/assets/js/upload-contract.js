toastr.options = {
    closeButton: true,
    positionClass: 'toast-bottom-right',
    showDuration: 1000,
    hideDuration: 1000,
    timeOut: 1000,
    extendedTimeOut: 1000,
    showEasing: 'swing',
    hideEasing: 'linear',
    showMethod: 'fadeIn',
    hideMethod: 'fadeOut',
}

let isConfirmHideModal = false;
let products = [];
let objectUrls = [];
let quotationId = null;

const resetForm = () => {
    $('#uploadContractModal #upload-contract-form').get(0).reset();
    quotationId = null;
}

const validateForm = () => {
    $.validator.addMethod("fileType", function (value, element, param) {
        let fileType = element.files;
        for (let i = 0; i < fileType.length; i++) {
            if (!param.includes(fileType[i].type)) {
                return false;
            }
        }
        return true;
    }, "Vui lòng tải file đúng định dạng yêu cầu");

    $("#upload-contract-form").validate({
        rules: {
            contract_file: {
                required: true,
                fileType: ["application/pdf"] // giới hạn kiểu file
            },
        },
        messages: {
            contract_file: {
                required: "Vui lòng tải hợp đồng",
            },
        },

        errorPlacement: function (error, element) {
            // error is the error message
            // element is the form element with the error
            // var name = element.attr("name");
            // $("#" + name + "-error").text(error.text());
        },
        showErrors: function (errorMap, errorList) {
            const currTarget = errorList[0];
            if (currTarget) {
                if (currTarget.element.name == "images[]") showImagesErrorMessage(true);
            }
            // Clean previous error messages if any
            $("div[id$='-error']").text("");
            // Default showErrors behavior
            this.defaultShowErrors();
        },

        submitHandler: function (form) {
            const fileContract = document.querySelector('input[name="contract_file');
            let formData = new FormData();
            formData.append('contract', fileContract.files[0]);
            formData.append('quotation_id', quotationId)
            
            $.ajax({
                url: '/admin/retail/sale/quotation/upload-contract',
                method: 'POST',
                processData: false, // Ngăn không xử lý dữ liệu
                contentType: false, // Ngăn không thiết lập kiểu nội dung mặc định
                data: formData,
                success: res => {
                    if (!res.error) {
                        toastr.success(res.message);
                        $('#botble-order-retail-tables-sale-quotation-table').DataTable().ajax.reload();
                        $('#uploadContractModal').modal('hide');
                    }
                },
                error: err => {
                    toastr.success(err.message);
                }
            })
        }
    });

}

document.addEventListener("DOMContentLoaded", function () {
    validateForm();

    $(document).on('hide.bs.modal', '#uploadContractModal', function () {
        resetForm();
    });

    $(document).on('click', '.upload-contract-btn', function () {
        quotationId = $(this).data('quotation-id');
        console.log(quotationId);
    });

    $(document).on('click', '#submit-upload-contract', function () {
        $('#upload-contract-form').trigger('submit');
    });
});