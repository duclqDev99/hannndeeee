<?php

namespace Botble\OrderStepSetting\Tables;

use Botble\Base\Facades\BaseHelper;
use Botble\Base\Facades\Html;
use Botble\Base\Enums\BaseStatusEnum;
use Botble\Sales\Models\Customer;
use Botble\Sales\Models\Order;
use Botble\Sales\Models\Sales;
use Botble\Sales\Tables\Actions\NextStepAction;
use Botble\Sales\Tables\Actions\PrevStepAction;
use Botble\Table\Abstracts\TableAbstract;
use Botble\Sales\Actions\DeleteAction;
use Botble\Sales\Enums\OrderStatusEnum;
use Botble\Sales\Enums\StepActionEnum;
use Botble\Table\Actions\EditAction;
use Botble\Table\BulkActions\DeleteBulkAction;
use Botble\Table\Columns\Column;
use Botble\Table\Columns\CreatedAtColumn;
use Botble\Table\Columns\IdColumn;
use Botble\Table\Columns\NameColumn;
use Botble\Table\Columns\StatusColumn;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Http\JsonResponse;

class OrderTable extends TableAbstract
{
    protected int $pageLength = 20;

    public function setup(): void
    {
        $this
            ->model(Order::class)
            ->addActions([
                // PrevStepAction::make()
                //     ->route('purchase-order.prepare-id-for-action'),
                // NextStepAction::make()
                //     ->route('purchase-order.prepare-id-for-action'),
                EditAction::make()
                    ->route('purchase-order.edit'),
                DeleteAction::make()
                    ->route('purchase-order.destroy'),
            ])
            ->removeAllActions()
            ->setView('plugins/admin-handee-retail::table.index');
    }

    public function ajax(): JsonResponse
    {
        $data = $this->table
            ->eloquent($this->query())
            ->editColumn('username', function (Order $item) {
                return $item->username;
            })
            ->editColumn('collapse_action', function (Order $item) {
                return '<a class="show_collapse_btn" data-bs-toggle="collapse" role="button" data-id="' . $item->id . '">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 14 14" fill="none">
                       <path fill-rule="evenodd" clip-rule="evenodd" d="M8.50842 7.00012L3.6709 11.8376L4.49586 12.6626L10.1583 7.00012L4.49586 1.33764L3.6709 2.1626L8.50842 7.00012Z" fill="#8E8E8E"/>
                    </svg>
                </a>';
            })->editColumn('order_code', function (Order $item) {
                return '<span class="">#' . $item->order_code . '</span>';
            })
            ->editColumn('quotation', function (Order $item) {
                if ($item->quotation) {
                    return Html::link("#", "Xem báo giá");
                }
                return '---';
            })
            ->editColumn('production', function (Order $item) {
                if ($item->production) {
                    return Html::link("#", "Xem đơn hàng");
                }
                return '---';
            })
            ->editColumn('order', function (Order $item) {
                return Html::link("#", "order_code");
            })
            ->editColumn('created_by', function (Order $item) {
                if(isset($item->steps[0]->actions[0]->handler)){
                    return $item->steps[0]->actions[0]->handler->first_name . ' ' . $item->steps[0]->actions[0]->handler->last_name;
                }
                return "---";
            })
            ->editColumn('level', function (Order $item) {
                return BaseHelper::clean(trans('plugins/sales::order-step-setting.customer.level.' .  $item->level));
            })
            ->editColumn('type_order', function (Order $item) {
                return BaseHelper::clean(trans('plugins/sales::orders.' .  $item->type_order));
            })->editColumn('status', function (Order $item) {
                return 'Đang thực hiện';
            })
            ->setRowClass('on-loading position-relative')
            ->setRowAttr([
                'data-id' => fn ($item) => $item->id,
            ]);
        return $this->toJson($data);
    }
    // loading-spinner


    public function query(): Relation|Builder|QueryBuilder
    {
        // $department_code = get_department_code_curr_user();
        $query = $this
            ->getModel()
            ->query()
            ->select([
                'id',
                'type_order',
                'order_code',
                'id_user',
                'username',
                'email',
                'phone',
                'invoice_issuer_name',
                'document_number',
                'title',
                'sub_total',
                'description',
                'expected_date',
                'date_confirm',
                'created_at',
                'status',
                'created_by_id'
            ])->with([
                'quotation',
                'production',
                'steps.actions' => function ($q) {
                    $q->where('action_code', StepActionEnum::RETAIL_SALE_CREATE_ORDER);
                    $q->with('handler');
                }
            ])
            ->orderBy('expected_date', 'ASC');

        return $this->applyScopes($query);
    }

    public function htmlDrawCallbackFunction(): string|null
    {
        return <<<JS
            let catches = new Array();
            const rows = this.api().data().rows().nodes();
            const loadingElement = '<div class="loading-spinner"></div>';

            $(rows).each(function(){
                const id = $(this).attr('data-id');
                const collapseId = `rowCollapse` + id;
                const collapseElement = document.createElement('tr');
                $(this).after(collapseElement);
            });

            $(document).on('show.bs.collapse', '.collapse', function () {
               $('.collapse.show').collapse('hide');
            });

            //show steps
            $(document).off('click');
            $(document).on('click', '.show_collapse_btn', function(){
                const order_id = $(this).data('id');

                let _catch = catches.find(item => item == order_id);

                if(!_catch && !$(this).closest('tr').next().find('.collapse').hasClass('show')){
                    $(this).attr('disabled', true);
                    $(this).after(loadingElement);
                    $.ajax({
                        method: 'GET',
                        url: '/admin/admin-handee-retails/steps',
                        contentType: 'html',
                        data: {order_id},
                        success: res => {
                            $(this).closest('tr').next().html(res);
                            $(this).closest('tr').next().find('.collapse').collapse('show');
                            $(this).next().remove();
                            $(this).removeAttr('disabled');
                            catches.push(order_id);
                            setTimeout(() => {
                                catches = catches.filter(item => item != order_id);
                            }, 10000);
                        }
                    });
                }
                else $(this).closest('tr').next().find('.collapse').collapse('toggle');              
            });

            //show step detail
            $(document).on('click', '.show_detail_status_btn', function(){
                const step_detail_id = $(this).data('id');
                $('#statusDetailModal .modal-body')
                   .html(loadingElement)
                   .addClass('on-loading position-relative');
                $.ajax({
                    method: 'GET',
                    url: '/admin/admin-handee-retails/step-detail',
                    contentType: 'html',
                    data: {step_detail_id},
                    success: res => {
                        $('#statusDetailModal .modal-body').html(res);
                    }
                })
            });
        JS;
    }

    public function columns(): array
    {
        return [
            Column::make('collapse_action')
                ->title('')
                ->orderable(false)
                ->searchable(false),
            Column::make('order_code')
                ->nowrap()
                ->title('Số YCSX')
                ->orderable(false),
            Column::make('quotation')
                ->nowrap()
                ->title('Báo giá')
                ->orderable(false),
            Column::make('production')
                ->nowrap()
                ->title('Đơn đặt hàng')
                ->orderable(false),
            Column::make('username')
                ->nowrap()
                ->title('Tên khách hàng')
                ->orderable(false),
            Column::make('sub_total')
                ->nowrap()
                ->title('Trị giá')
                ->orderable(false)
                ->searchable(false),
            CreatedAtColumn::make('expected_date')
                ->dateFormat('d/m/Y')
                ->title('Ngày cần hàng')
                ->nowrap()
                ->orderable(false),
            CreatedAtColumn::make()
                ->dateFormat('d/m/Y')
                ->title('Ngày tạo'),
            Column::make('created_by')
                ->title('Sale')
                ->orderable(false)
                ->searchable(false),
            Column::make('status')
                ->title('Trạng thái'),
        ];
    }

    public function buttons(): array
    {
        return [];
    }

    public function bulkActions(): array
    {
        return [];
    }

    public function getBulkChanges(): array
    {
        return [];
    }

    public function getFilters(): array
    {
        return [
            'status' => [
                'title' => trans('core/base::tables.status'),
                'type' => 'select',
                'choices' => BaseStatusEnum::labels(),
                'validate' => 'required|in:' . implode(',', BaseStatusEnum::values()),
            ],
            'created_at' => [
                'title' => trans('core/base::tables.created_at'),
                'type' => 'date',
            ],
        ];
    }
}
