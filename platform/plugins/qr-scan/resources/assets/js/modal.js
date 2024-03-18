export default class Modal {
    constructor(selfModal) {
        this._selfModal = selfModal;
    }

    openScannerLoading = () => {
        $(`${this._selfModal} #scanner_box`).removeClass('d-flex').addClass('d-none');
        $(`${this._selfModal} #scanner_box_loading`).removeClass('d-none').addClass('d-flex');
    }

    closeScannerLoading = () => {
        $(`${this._selfModal} #scanner_box`).removeClass('d-none').addClass('d-flex');
        $(`${this._selfModal} #scanner_box_loading`).removeClass('d-flex').addClass('d-none');
    }

    showScanMessage = (message, type) => {
        $(`${this._selfModal} #scanner_message`).html(`
            <div role = "alert" class="alert alert-${type} mb-0" >
                <div class="d-flex">
                    <div class="w-100">
                        ${message}
                    </div>
                </div>
             </div > `)
            .show();
    }

    clearScanMessage = () => {
        $(`${this._selfModal} #scanner_message`).html('').hide();
    }

    setDisabledButtons = (condition) => {
        if (condition) {
            $(`${this._selfModal} button[name="create-batch"]`).removeAttr('disabled');
            $(`${this._selfModal} button[name="save"]`).removeAttr('disabled');
        } 
        else {
            $(`${this._selfModal} button[name="create-batch"]`).attr('disabled', true);
            $(`${this._selfModal} button[name="save"]`).attr('disabled', true);
        }
    }

    renderDataInfo = (html) => {
        $(`${this._selfModal} #table-info .body-info`).html(html);
    }

    renderDataScanned = (html) => {
        $(`${this._selfModal} #table-scanned .body-scanned`).html(html);
    }

    showEmptyScanned = () => {
        $(`${this._selfModal} .empty_scanned_message`).removeClass('d-none').addClass('d-flex');
    }

    hideEmptyScanned = () => {
        $(`${this._selfModal} .empty_scanned_message`).removeClass('d-flex').addClass('d-none');
    }
}