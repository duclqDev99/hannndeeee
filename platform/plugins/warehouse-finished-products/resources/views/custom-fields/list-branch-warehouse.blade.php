<div>
    <div class="row clearfix">

        <br>
        <div class="col-md-12 column">
            <table class="table table-bordered table-hover" id="tab_logic">
                <thead>
                    <tr>
                        <th class="text-center">
                            #
                        </th>
                        <th class="text-center" width="35%">
                            Tên sản phẩm
                        </th>
                        <th class="text-center" width="25%">
                            Mã sản phẩm
                        </th>
                        <th class="text-center" width="25%">
                            Số lượng
                        </th>
                        <th class="text-center" width="15%">
                            Số lượng lô kho
                        </th>

                    </tr>
                </thead>
                <tbody>
                    <tr id="addr0" class="material-row" style="display:none">
                        <td class="row-number">1</td>
                        <td>
                            <input hidden value="" name="product_id[]" />
                            <input type="text" name="product_name[]" placeholder="Tên sản phẩm"
                                style="text-align: center; "class="product_name form-control" readonly />
                        </td>
                        <td>
                            <input type="text" name="input__code_product_process[]" placeholder="Mã"
                                style="text-align: center; background-color:#f0f5f5" class="input__code form-control"
                                readonly />
                        </td>
                        <td>
                            <input type="number" name="quantity[]" id="quantity"
                                placeholder="Nhập số lượng lô muốn xuất" class="input_quantity form-control required" />
                        </td>
                        <input type="hidden" name="price[]" style="text-align: center; background-color:#f0f5f5"
                            class="price form-control" readonly />
                        <td>
                            <input type="text" name="quantityStock[]" id="quantityStock" class="unit form-control"
                                readonly />
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

</div>
