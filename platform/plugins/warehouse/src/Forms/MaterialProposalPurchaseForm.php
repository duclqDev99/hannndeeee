<?php

namespace Botble\Warehouse\Forms;

use Botble\Base\Forms\FormAbstract;
use Botble\Base\Enums\BaseStatusEnum;
use Botble\Warehouse\Enums\StockStatusEnum;
use Botble\Warehouse\Http\Requests\MaterialProposalReceiptRequest;
use Botble\Warehouse\Models\MaterialProposalPurchase;
use Botble\Warehouse\Models\MaterialWarehouse;
use Botble\Warehouse\Models\Supplier;

class MaterialProposalPurchaseForm extends FormAbstract
{
    public function buildForm(): void
    {
        $selectInventory = [];
        $selectSupplier = '';

        $inventory = MaterialWarehouse::where(['status' => StockStatusEnum::ACTIVE])->get();

        foreach ($inventory as $item) {
            $selectInventory[$item->id] = $item->name;
        }

        $suppliers = Supplier::where(['status' => BaseStatusEnum::PUBLISHED])->get();

        foreach ($suppliers as $item) {
            $selectSupplier .= '<option value="'.$item->id.'">'.$item->name.'</option>';
        }

        $URL_API = env('APP_URL') . '/api/v1/';
        $this
            ->setupModel(new MaterialProposalPurchase)
            ->setValidatorClass(MaterialProposalReceiptRequest::class)
            ->withCustomFields()
            ->add('form_title', 'html', [
                'html' => '<h3>Đơn đề xuất nhập kho</h3>'
            ])
            ->add('api_key', 'text', [
                'label_show' => false,
                'attr' => [
                    'class' => 'form-control',
                    'id' => 'api_key'
                ],
                'value' => env('API_KEY'),
                'wrapper' => ['class' => 'd-none']
            ])
            ->add('url_api', 'text', [
                'label_show' => false,
                'attr' => [
                    'class' => 'form-control',
                    'id' => 'url_api'
                ],
                'value' => $URL_API,
                'wrapper' => ['class' => 'd-none']
            ])
            ->add('proposal_id', 'number', [
                'label_show' => false,
                'attr' => [
                    'class' => 'form-control',
                    'id' => 'proposal_id',
                    'data-supplier' => !empty($this->getModel()) ? $this->getModel()->is_from_supplier : '',
                ],
                'value' => !empty($this->getModel()) ? $this->getModel()->id : 0,
                'wrapper' => ['class' => 'd-none']
            ])
            ->add('rowOpenA', 'html', [
                'html' => '<div class="row">'
            ])
            ->add('warehouse_id', 'customSelect', [
                'label' => __('Kho nhập'),
                'label_attr' => ['class' => 'control-label required'],
                'attr' => [
                    'class' => 'form-control',
                    'id' => 'warehouse_id'
                ],
                'choices' => $selectInventory,
                'value' => !empty($this->getModel()) ? $this->getModel()->warehouse_id : 0,
                'wrapper' => ['class' => 'col-lg-8 col-md-12 col-12']
            ])
            ->add('general_order_code', 'text', [
                'label' => __('Mã đơn hàng (Nếu có)'),
                'label_attr' => ['class' => 'control-label'],
                'attr' => [
                    'class' => 'form-control',
                    'id' => 'general_order_code'
                ],
                'wrapper' => ['class' => 'col-lg-4 col-md-12 col-12']
            ])
            ->add('rowCloseA', 'html', [
                'html' => '</div>'
            ])
            ->add('title', 'text', [
                'label' => __('Tiêu đề đơn'),
                'label_attr' => ['class' => 'control-label required'],
                'attr' => [
                    'class' => 'form-control',
                    'id' => 'title',
                    'required' => true
                ],
            ])
            ->add('navTabs', 'html', [
                'html' => '
                    <ul class="nav nav-tabs mb-3" id="myTab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="home-tab" data-bs-toggle="tab" data-bs-target="#home" type="button" role="tab" aria-controls="home" aria-selected="true"><input type="radio" name="tabRadio" id="radioStock" checked> Nhập từ kho</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="profile-tab" data-bs-toggle="tab" data-bs-target="#profile" type="button" role="tab" aria-controls="profile" aria-selected="false"><input type="radio" name="tabRadio" id="radioSupplier"> Nhập từ nhà cung cấp</button>
                        </li>
                    </ul>
                    <div class="tab-content" id="myTabContent">
                    <input type="text" name="type_proposal" value="stock" hidden/>
                        <div class="tab-pane fade show active" id="home" role="tabpanel" aria-labelledby="home-tab">
                            <div class="row mb-3">
                                <div class="col-lg-6 col-12">
                                    <label>Nhập từ kho: </label>
                                    <div class="ui-select-wrapper form-group">
                                        <select name="detination_wh_id" class="form-control ui-select">
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
                                                    Chọn nguyên phụ liệu trong kho
                                                </th>
                                                <th class="text-center">
                                                    Mã nguyên liệu
                                                </th>
                                                <th class="text-center">
                                                    Số lượng trong kho
                                                </th>
                                                <th class="text-center">
                                                    Số lượng
                                                </th>
                                                <th class="text-center">
                                                    Đơn vị
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr id="addr0">
                                                <td>1</td>
                                                <td>
                                                    <select data-quantity="0" name="stock[material][0][name]" class="select__product form-control">
                                                    </select>
                                                    <input type="number" name="stock[material][0][material_id]" class="value_id__material form-control" hidden disable/>
                                                    <input type="text" name="" placeholder="Tên nguyên liệu" class="name__material form-control" hidden disable/>
                                                </td>
                                                <td>
                                                    <input type="text" class="input__code_product form-control" hidden/>
                                                    <input type="text" name="stock[material][0][code]" placeholder="Mã" class="input__code form-control" readonly/>
                                                </td>
                                                <td>
                                                <input type="number" class="input__quantity_material form-control" readonly/>
                                                </td>
                                                <td>
                                                    <input type="number" min="0" name="stock[material][0][quantity]" placeholder="Nhập số lượng" class="input__quantity form-control"/>
                                                </td>
                                                <td>
                                                    <input type="text" class="input__material_unit form-control" hidden/>
                                                    <input type="text" name="stock[material][0][unit]" placeholder="Cái" class="input__unit form-control" readonly/>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="mt-3">
                                <button id="add_row" type="button" class="btn btn-secondary pull-left">Thêm dòng</button>
                                <a class="pull-right btn btn-primary" href="'. route('proposal-purchase-goods.create') .'">Tạo phiếu đề xuất mua hàng</a>
                            </div>
                        </div>

                        <div class="tab-pane fade" id="profile" role="tabpanel" aria-labelledby="profile-tab">
                            <div class="row mb-3">
                                <div class="col-lg-4 col-md-6 col-12">
                                    <label>Chọn nhà cung cấp: </label>
                                    <div class="ui-select-wrapper form-group">
                                        <select name="supplier_id" class="form-control ui-select">
                                        '.$selectSupplier.'
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
                                                    Chọn nguyên phụ liệu trong kho
                                                </th>
                                                <th class="text-center">
                                                    Mã nguyên liệu
                                                </th>
                                                <th class="text-center">
                                                    Số lượng
                                                </th>
                                                <th class="text-center">
                                                    Đơn vị
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr id="addr0">
                                                <td>1</td>
                                                <td>
                                                    <select data-quantity="0" name="supplier[material][0][name]" class="select__product form-control">
                                                    </select>
                                                    <input type="number" name="supplier[material][0][material_id]" class="value_id__material form-control" hidden disable/>
                                                    <input type="text" name="" placeholder="Tên nguyên liệu" class="name__material form-control" hidden disable/>
                                                </td>
                                                <td>
                                                    <input type="text" class="input__code_product form-control" hidden/>
                                                    <input type="text" name="supplier[material][0][code]" placeholder="Mã" class="input__code form-control" readonly/>
                                                </td>
                                                <td>
                                                    <input type="number" class="input__quantity_material form-control" hidden/>
                                                    <input type="number" min="1" name="supplier[material][0][quantity]" placeholder="Nhập số lượng" class="input__quantity form-control"/>
                                                </td>
                                                <td>
                                                    <input type="text" class="input__material_unit form-control" hidden/>
                                                    <input type="text" name="supplier[material][0][unit]" placeholder="Cái" class="input__unit form-control" readonly/>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="mt-3">
                                <button id="add_row" type="button" class="btn btn-secondary pull-left">Thêm dòng</button>
                                <a class="pull-right btn btn-primary" href="'. route('proposal-purchase-goods.create') .'">Tạo phiếu đề xuất mua hàng</a>
                            </div>
                        </div>
                    </div>
                '
            ])

            ->add('rowOpen2', 'html', [
                'html' => '

            '
            ])
            ->add('expected_date', 'datePicker', [
                'label' => trans('Ngày dự kiến'),
                'label_attr' => ['class' => 'control-label'],
                'attr' => [
                    'class' => 'form-control ',
                    'data-date-format' => 'd-m-Y',
                    'id' => 'expected_date',
                ],
                'default_value' => date('d-m-Y'),
            ])
            ->add('description', 'textarea', [
                'label' => trans('Ghi chú'),
                'label_attr' => ['class' => 'control-label'],
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Ghi chú',
                    'rows' => 3
                ],
            ])
            ->setBreakFieldPoint('expected_date');
    }
}
