@php
    use Botble\WarehouseFinishedProducts\Models\WarehouseFinishedProducts;
    use Botble\HubWarehouse\Models\Warehouse;
@endphp
<div class="row mb-3" id="warehouse">
    <div class="col-lg-6 col-12">
        <label>Kho thành phẩm: </label>
        <div class="ui-select-wrapper form-group">
            <select data-quantity="0" name="warehouse_product" id="select_warehouse_product" class="select_warehouse_product form-control select-search-full" data-model="{{WarehouseFinishedProducts::class}}">
                <option value="0">Chọn kho thành phẩm</option>
            </select>
        </div>
    </div>

</div>
<div class="row mb-3" id="hub" style="display:none">
    <div class="row">
        <div class="col-lg-6 col-12">
            <label for="control-label required">Hub:</label>
            <select data-quantity="0" name="hub" class="select_hub form-control select-search-full">
                <option value="0">Chọn Hub</option>
            </select>
        </div>
        <div class="col-lg-6 col-12">
            <label for="control-label required">Chọn kho:</label>
            <select data-quantity="0" name="hub" class="select_hub_warehouse form-control select-search-full" data-model="{{Warehouse::class}}">
                <option value="0">Vui lòng chọn hub</option>
            </select>

        </div>
    </div>
</div>
<div class="row mb-3" id="warehouse-hub-other" style="display:none">
    <div class="col-lg-6 col-12">
        <label>Kho khác: </label>
        <div class="ui-select-wrapper form-group">
            <select data-quantity="0" id="warehouse_out" name="warehouse_out"
                class="select_warehouse_out form-control select-search-full "  data-model="{{Warehouse::class}}">
                <option value="">Chọn kho nhận</option>
            </select>
        </div>
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
                            Chọn sản phẩm
                        </th>
                        <th class="text-center" width="25%">
                            Mã sản phẩm
                        </th>
                        <th class="text-center" width="25%">
                            Số lượng
                        </th>
                        <th class="text-center" width="15%">
                            Số lượng trong kho
                        </th>

                    </tr>
                </thead>
                <tbody>
                    <tr id="addr0" class="material-row" style="display:none">
                        <td class="row-number">1</td>
                        <td>
                            <select data-quantity="0" name="product[]" prev=""
                                class="select_product form-control">
                                <option value="">Chọn sản phẩm</option>
                            </select>
                        </td>
                        <td>
                            <input type="text" name="input__code_product_process[]" placeholder="Mã"
                                style="text-align: center; background-color:#f0f5f5" class="input__code form-control"
                                readonly />
                        </td>
                        <td>
                            <input type="number" name="quantity[]" id="quantity" placeholder="Nhập số lượng"
                                class="input_quantity form-control required" />
                        </td>
                        <input type="hidden" name="price[]" style="text-align: center; background-color:#f0f5f5"
                            class="price form-control" readonly />
                        <td>
                            <input type="text" name="quantityStock[]" id="quantityStock"
                                style="text-align: center; background-color:#f0f5f5; width: 100px;"
                                class="quantity-stock form-control" readonly />
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-3">
        <button id="add_row" type="button" class="btn btn-secondary pull-left">Thêm sản phẩm</button>
    </div>
</div>
