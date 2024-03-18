<?php

namespace Botble\Warehouse\Forms;

use Botble\Base\Forms\FormAbstract;
use Botble\Base\Enums\BaseStatusEnum;
use Botble\Warehouse\Enums\StockStatusEnum;
use Botble\Warehouse\Models\MaterialWarehouse;
use Botble\Warehouse\Models\ProposalPurchaseGoods;

class PurchaseGoodsForm extends FormAbstract
{
    public function buildForm(): void
    {
        $selectInventory = [];

        $inventory = MaterialWarehouse::where(['status' => StockStatusEnum::ACTIVE])->get();

        foreach ($inventory as $item) {
            $selectInventory[$item->id] = $item->name;
        }

        $URL_API = env('APP_URL') . '/api/v1/';
        $this
            ->setupModel(new ProposalPurchaseGoods())
            ->withCustomFields()
            ->add('form_title', 'html', [
                'html' => '<h3>Đơn đề xuất mua hàng</h3>'
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
                ],
                'value' => !empty($this->getModel()) ? $this->getModel()->id : 0,
                'wrapper' => ['class' => 'd-none']
            ])
            ->add('rowOpen1', 'html', [
                'html' => '<div class="row">'
            ])
            ->add('warehouse_id', 'customSelect', [
                'label' => 'Chọn kho',
                'label_attr' => ['class' => 'control-label required'],
                'attr' => [
                    'class' => 'form-control',
                    'id' => 'warehouse_id',
                ],
                'choices' => $selectInventory,
                'value' => !empty($this->getModel()) ? $this->getModel()->warehouse_id : 0,
                'wrapper' => ['class' => 'col-lg-8 col-12']
            ])
            ->add('general_order_code', 'text', [
                'label' => __('Mã đơn hàng'),
                'label_attr' => ['class' => 'control-label'],
                'attr' => [
                    'class' => 'form-control',
                    'id' => 'general_order_code',
                    'placeholder' => 'Nhập mã đơn hàng (nếu có)'
                ],
                'wrapper' => ['class' => 'col-lg-4 col-12']
            ])
            ->add('rowClose1', 'html', [
                'html' => '</div>'
            ])
            ->add('title', 'text', [
                'label' => __('Tiêu đề đơn'),
                'label_attr' => ['class' => 'control-label'],
                'attr' => [
                    'class' => 'form-control',
                    'id' => 'title',
                    'required' => true
                ],
            ])
            ->add('rowOpen2', 'html', [
                'html' => '
                <div class="row clearfix">
                <div class="col-md-12 column">
                    <table class="table table-bordered table-hover table__delivery" id="tab_logic">
                        <thead>
                            <tr>
                                <th class="text-center">
                                    #
                                </th>
                                <th class="text-center required" width="20%">
                                    Nguyên phụ liệu
                                </th>
                                <th class="text-center">
                                    Mã nguyên liệu
                                </th>
                                <th class="text-center required" width="15%">
                                    Số lượng
                                </th>
                                <th class="text-center" width="10%">
                                    Đơn vị
                                </th>
                                <th class="text-center" width="20%">
                                   Chọn nhà cung cấp
                                </th>
                                <th class="text-center required" width="15%">
                                Giá
                             </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr id="addr0">
                                <td>1</td>
                                    <td>
                                        <select name="material[0][name]" class="select__material form-control" hidden disable>
                                        </select>
                                        <input type="number" name="material[0][material_id]" class="value_id__material" hidden/>
                                        <input type="text" name="material[0][name]" placeholder="Tên nguyên liệu" class="name__material form-control" required/>
                                    </td>
                                    <td>
                                        <input type="text" class="input__code_material form-control" hidden/>
                                        <input type="text" name="material[0][code]" placeholder="Mã" class="input__code form-control"/>
                                    </td>
                                    <td>
                                        <input type="number" class="input__material_quantity form-control" hidden/>
                                        <input type="number" name="material[0][quantity]" min="1" placeholder="Nhập số lượng" class="input__quantity form-control" required/>
                                    </td>
                                    <td>
                                        <input type="text" class="input__material_unit form-control" hidden/>
                                        <input type="text" name="material[0][unit]" placeholder="Cái" class="input__unit form-control"/>
                                    </td>
                                    <td>
                                        <select name="material[0][supplier_id]" class="select__agency form-control">
                                        </select>
                                    </td>
                                    <td>
                                    <input type="number" name="material[0][price]" min="1" class="input__price form-control" placeholder="0"/>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="mt-3">
                <button id="add_row" type="button" class="btn btn-secondary pull-left">Thêm nguyên liệu mới</button>
                <button id="add_new" type="button" class="btn btn-primary pull-left">Thêm nguyên liệu có sẵn</button>
            </div>

            '
            ])
            ->add('expected_date', 'datePicker', [
                'label' => trans('Ngày dự kiến'),
                'label_attr' => ['class' => 'control-label'],
                'attr' => [
                    'class' => 'form-control',
                    'id' => 'expected_date',
                    'data-date-format' => 'd-m-Y',
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
