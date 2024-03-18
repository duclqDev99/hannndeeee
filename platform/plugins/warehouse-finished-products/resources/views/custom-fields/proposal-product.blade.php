@php
use Botble\Warehouse\Models\ProcessingHouse;
use Botble\Warehouse\Enums\StockStatusEnum;

$selectProcessing = ProcessingHouse::where(['status' => StockStatusEnum::ACTIVE])->pluck('name', 'id')->all();
@endphp
<div id="fui-toast"></div>
<ul class="nav nav-tabs" id="myTab" role="tablist">
    <li class="nav-item d-none" role="presentation">
        <button class="nav-link" id="stock-tab" data-bs-toggle="tab" data-bs-target="#stock" type="button"
            role="tab" aria-controls="stock" aria-selected="true"><input type="radio" name="tabRadio"
                id="radioStock"> Nhập lô từ kho</button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link active" id="stock-odd-tab" data-bs-toggle="tab" data-bs-target="#stock-odd" type="button"
            role="tab" aria-controls="stock-odd" aria-selected="true"><input type="radio" name="tabRadio"
                id="radioStockOdd" checked> Nhập lẻ từ kho</button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="inventory-tab" data-bs-toggle="tab" data-bs-target="#inventory" type="button"
            role="tab" aria-controls="inventory" aria-selected="false"><input type="radio" name="tabRadio"
                id="radioInventory"> Nhập lẻ</button>
    </li>
</ul>
<div class="tab-content mt-3" id="myTabContent">
    <input type="text" name="type_proposal" value="stock" hidden />
    <div class="tab-pane fade" id="stock" role="tabpanel" aria-labelledby="stock-tab">
        <div class="row mb-3">
            <div class="col-lg-6 col-12">
                <label>Nhập từ kho: </label>
                <div class="ui-select-wrapper form-group">
                    <select name="stock[detination_wh_id]" class="stock_detination form-control ui-select">
                    </select>
                </div>
            </div>
        </div>
        <div class="row clearfix">
            <div class="col-md-12 column">
                <table class="table table-bordered table-hover table__delivery" id="tab_logic">
                    <thead>
                        <tr>
                            <th class="text-center">
                                #
                            </th>
                            <th class="text-center" width="20%">
                                Chọn loại sản phẩm trong kho
                            </th>
                            <th class="text-center">
                                SKU
                            </th>
                            <th class="text-center">
                                Số lượng lô trong kho
                            </th>
                            <th class="text-center">
                                Số lượng lô
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr id="addr0">
                            <td>1</td>
                            <td>
                                <select data-quantity="0" name="stock[product][0][name]"
                                    class="select__product form-control">
                                </select>
                                <input type="number" name="stock[product][0][product_id]"
                                    class="value_id__product form-control" hidden disable />
                                <input type="text" name="" placeholder="Tên sản phẩm"
                                    class="name__product form-control" hidden disable />
                            </td>
                            <td>
                                <input type="text" class="input_sku_product form-control" hidden />
                                <input type="text" name="stock[product][0][sku]" placeholder="Mã"
                                    class="input__sku form-control" readonly />
                            </td>
                            <td>
                                <input type="number" class="input__quantity_product form-control" readonly/>
                            </td>
                            <td>
                                <input type="number" min="0" name="stock[product][0][quantity]"
                                    placeholder="Nhập số lượng" class="input__quantity form-control" />
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

    <div class="tab-pane fade show active" id="stock-odd" role="tabpanel" aria-labelledby="stock-odd-tab">
        <div class="row mb-3">
            <div class="col-lg-6 col-12">
                <label>Nhập từ kho: </label>
                <div class="ui-select-wrapper form-group">
                    <select name="stock-odd[detination_wh_id]" class="stock_odd_detination form-control ui-select">
                    </select>
                </div>
            </div>
        </div>
        <div class="row clearfix">
            <div class="col-md-12 column">
                <table class="table table-bordered table-hover table__delivery" id="tab_logic">
                    <thead>
                        <tr>
                            <th class="text-center">
                                #
                            </th>
                            <th class="text-center" width="20%">
                                Chọn sản phẩm trong kho
                            </th>
                            <th class="text-center">
                                SKU
                            </th>
                            <th class="text-center">
                                Số lượng sản phẩm trong kho
                            </th>
                            <th class="text-center">
                                Số lượng nhập
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr id="addr0">
                            <td>1</td>
                            <td>
                                <select data-quantity="0" name="stock[product][0][name]"
                                    class="select__product form-control">
                                </select>
                                <input type="number" name="stock[product][0][product_id]"
                                    class="value_id__product form-control" hidden disable />
                                <input type="text" name="" placeholder="Tên sản phẩm"
                                    class="name__product form-control" hidden disable />
                            </td>
                            <td>
                                <input type="text" class="input_sku_product form-control" hidden />
                                <input type="text" name="stock[product][0][sku]" placeholder="Mã"
                                    class="input__sku form-control" readonly />
                            </td>
                            <td>
                                <input type="number" class="input__quantity_product form-control" readonly/>
                            </td>
                            <td>
                                <input type="number" min="0" name="stock[product][0][quantity]"
                                    placeholder="Nhập số lượng" class="input__quantity form-control" />
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

    <div class="tab-pane fade" id="inventory" role="tabpanel" aria-labelledby="inventory-tab">
        <div class="row clearfix">
            <div class="col-md-12 column">
                <table class="table table-bordered table-hover table__delivery" id="tab_logic">
                    <thead>
                        <tr>
                            <th class="text-center">
                                #
                            </th>
                            <th class="text-center" width="20%">
                                Chọn sản phẩm
                            </th>
                            <th class="text-center">
                                Sku
                            </th>
                            <th class="text-center">
                                Số lượng sản phẩm
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr id="addr0">
                            <td>1</td>
                            <td>
                                <select data-quantity="0" name="processing[product][0][name]"
                                    class="select__product form-control">
                                </select>
                                <input type="number" name="processing[product][0][product_id]"
                                    class="value_id__product form-control" hidden disable />
                                <input type="text" name="" placeholder="Tên sản phẩm"
                                    class="name__product form-control" hidden disable />
                            </td>
                            <td>
                                <input type="text" class="input_sku_product form-control" hidden />
                                <input type="text" name="processing[product][0][sku]" placeholder="Mã"
                                    class="input__sku form-control" readonly />
                            </td>
                            <td>
                                <input type="number" class="input__quantity_product form-control" hidden />
                                <input type="number" min="0" name="processing[product][0][quantity]"
                                    placeholder="Nhập số lượng" class="input__quantity form-control" />
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
</div>
