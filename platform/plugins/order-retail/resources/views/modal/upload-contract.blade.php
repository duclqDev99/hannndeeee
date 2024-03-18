<div class="modal modal-blur fade" id="uploadContractModal" tabindex="-1" aria-labelledby="exampleModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Xác nhận kí hợp đồng với khách hàng</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" style="min-height: 50px">
                <form id="upload-contract-form" action="" enctype="multipart/form-data">
                    <div class="row">
                        <div class="col-12">
                            <div class="mb-3 position-relative">
                                <label class="form-label" for="sale_price">
                                    Hợp đồng đính kèm
                                </label>
                                <div class="input-group input-group-flat">
                                    <input class="form-control" type="file" name="contract_file"
                                        id="contract_file" accept="application/pdf">
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn" data-bs-dismiss="modal">Đóng</button>
                <button id="submit-upload-contract" type="button" class="btn btn-primary">Xác nhận </button>
            </div>
        </div>
    </div>
</div>
