$(document).ready(function () {
    var qrCodeIdChecked = [];

    $(document).on("click", ".export_qrcode", () => {
        qrCodeIdChecked = [];
        collectCheckedBoxes();
    });
    function collectCheckedBoxes() {
        $(".form-check-input.checkboxes:checkbox:checked").each(function () {
            qrCodeIdChecked.push($(this).val());
        });
        if (qrCodeIdChecked.length > 0) {
            let url = $(
                '.export_qrcode span[data-action="generate-invoices"] > span',
            ).attr("data-url");
            window.open(
                url +
                    "?ids=" +
                    encodeURIComponent(JSON.stringify(qrCodeIdChecked)),
                "_blank",
            );
            var button = $(".buttons-reload");
            if (button) {
                button.click();
            }
        } else {
            toastr.clear();

            toastr.options = {
                closeButton: true,
                positionClass: "toast-bottom-right",
                // onclick: function () { window.location.href = `${data.action_url}`; },
                showDuration: 1000,
                hideDuration: 1000,
                timeOut: 60000,
                extendedTimeOut: 1000,
                showEasing: "swing",
                hideEasing: "linear",
                showMethod: "fadeIn",
                hideMethod: "fadeOut",
            };
            toastr["warning"]("Bạn chưa chọn sản phẩm để xuất", "Cảnh báo!");
        }
    }

    $(document).on("click", ".btn-trigger-print-qrcode-version", function () {
        let data = jQuery.parseJSON($(this).attr("data-data"));
        let typeButton = $(this).attr("type-button");
        let url = $(this).attr("data-target");
        console.log(url);
        let chunkSizePrint = $(this).attr("chunk-size");
        let chunkIdPrint = $(this).attr("chunk-id");
        let chunks = Math.ceil(data["quantity_product"] / chunkSizePrint);
        let dataUrlConfirm = $(this).attr("data-url-confirm");
        let myUrl = new URL(url);
        let queryParams = new URLSearchParams(myUrl.search);
        // Add new query parameters
        queryParams.append("typeButton", typeButton);
        queryParams.append("chunkSize", chunkSizePrint);
        queryParams.append("next", chunkIdPrint);
        myUrl.search = queryParams.toString();
        window.open(myUrl.toString(), "_blank");
    });

    $(document).on("click", ".btn-trigger-export-qrcode-version", function () {
        let data = jQuery.parseJSON($(this).attr("data-data"));
        let typeButton = $(this).attr("type-button");
        let url = $(this).attr("data-target");
        $(".dataTables_processing").attr("style", "display :block");
        if (typeButton == "create") {
            let chunkSizeCreate = 100;
            promiseCreate(typeButton, url, chunkSizeCreate, data, () => {
                $(".dataTables_processing").attr("style", "display :none");
                var button = $(".buttons-reload");
                if (button) {
                    button.click();
                }
                message("success", `Tạo mã qr thành công`, "Thành công");
            });
        }
    });

    const promiseCreate = async (
        typeButton,
        url,
        chunkSizeCreate,
        data,
        callback,
    ) => {
        let chunks = Math.ceil(data["quantity_product"] / chunkSizeCreate);
        for (let i = 0; i < chunks; i++) {
            await sendAjaxRequest(typeButton, url, chunkSizeCreate, i);
        }
        if (callback && typeof callback === "function") {
            callback();
        }
    };



    $(document).on("click", "#print-qrcode", function () {
        $("#print-qrcode").val("data-id");
        let url = $(this).attr("data-target");
        let dataUrlConfirm = $(this).attr("data-url-confirm");
        $.ajax({
            url: url,
            type: "GET",
            data: {
                dataUrlConfirm,
            },
            success: function (response) {
                $("#modalPrint").html(response);
                $("#modal-print-qrcode").modal("show");
            },
            error: function (error) {
                console.log(error);
            },
        });
    });

    $(document).on("click", "#close-modal-print-qrcode", function () {
        $("#modal-print-qrcode").modal("hide");

        $("#modalPrint").html("");
    });
});

const message = (type, message, title) => {
    toastr.clear();

    toastr.options = {
        closeButton: true,
        positionClass: "toast-bottom-right",
        showDuration: 1000,
        hideDuration: 1000,
        timeOut: 60000,
        extendedTimeOut: 1000,
        showEasing: "swing",
        hideEasing: "linear",
        showMethod: "fadeIn",
        hideMethod: "fadeOut",
    };
    toastr[type](message, title);
};

const sendAjaxRequest = (typeButton, url, chunkSize, next) => {
    return new Promise((resolve, reject) => {
        $.ajax({
            url: url,
            data: {
                typeButton,
                chunkSize,
                next,
            },
            type: "GET",
            success: function (response) {
                resolve(response);
            },
            error: function (error) {
                $(".dataTables_processing").attr("style", "display :none");
                reject(error);
            },
        });
    });
};
