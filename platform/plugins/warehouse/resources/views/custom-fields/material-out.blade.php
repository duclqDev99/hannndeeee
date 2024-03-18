

<div id="fui-toast"></div>
<div class="row mb-3"  id="warehouse" style="display:none">
        <div class="col-lg-6 col-12">
            <label>Kho nhận: </label>
            <div class="ui-select-wrapper form-group">
            <select data-quantity="0" id="warehouse_out" name="warehouse_out" class="select_warehouse_out form-control select-search-full " >
                <option value="">Chọn kho nhận</option>
            </select>
            </div>
        </div>

</div>
<div class="row mb-3" id="processing_house" >
    <div class="col-md-6 column">
        <label for="control-label required">Nhà gia công:</label>
        <select data-quantity="0" name="processing_house" class="select_processing form-control select-search-full">
            <option value="">Chọn nhà gia công</option>
        </select>

    </div>
</div>
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
                            Chọn nguyên phụ liệu trong kho
                        </th>
                        <th class="text-center">
                            Mã nguyên liệu
                        </th>
                        <th class="text-center"  width="15%">
                            Số lượng
                        </th>

                        <th class="text-center">
                        Số lượng còn trong kho
                        </th>
                        <th class="text-center">
                        Đơn vị
                        </th>

                    </tr>
                </thead>
                <tbody>
                    <tr id="addr0" class="material-row" style="display:none">
                        <td class="row-number">1</td>
                        <td>
                            <select data-quantity="0" name="material[]" id="material"  prev=""  class="select_material form-control">
                                <option value="">Chọn nguyên phụ liệu</option>
                            </select>
                        </td>
                        <td>
                        <input type="text" name="input__code_product_process[]" placeholder="Mã" style="text-align: center; background-color:#f0f5f5" class="input__code form-control" readonly/>
                    </td>
                        <td>
                            <input type="number" name="quantity[]" id="quantity" placeholder="Nhập số lượng" class="input_quantity form-control required"/>
                        </td>
                        <input  type="hidden"" name="price[]"  style="text-align: center; background-color:#f0f5f5" class="select_price form-control" readonly/>

                        <td>
                        <input type="number" name="quantity_in_stock[]"  id="quantityStock" style="text-align: center; background-color:#f0f5f5" class="select_quantity_stock form-control" readonly/>

                        </td>
                        <td>
                        <input type="text" name="unit[]"  style="text-align: center; background-color:#f0f5f5; width: 100px;" class="select_unit form-control" readonly/>

                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-3">
        <button id="add_row" type="button" class="btn btn-secondary pull-left">Thêm dòng</button>
    </div>
</div>

