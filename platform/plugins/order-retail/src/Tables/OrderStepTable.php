<?php

namespace Botble\OrderRetail\Tables;

use Botble\Base\Facades\BaseHelper;
use Botble\Base\Facades\Html;
use Botble\Base\Enums\BaseStatusEnum;
use Botble\OrderStepSetting\Enums\ActionEnum;
use Botble\Sales\Models\Customer;
use Botble\OrderRetail\Models\Order;
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

class OrderStepTable extends TableAbstract
{
    protected int $pageLength = 20;

    public function setup(): void
    {
        $this
            ->model(Order::class)
            ->addActions([
                EditAction::make()
                    ->route('purchase-order.edit'),
                DeleteAction::make()
                    ->route('purchase-order.destroy'),
            ])
            ->removeAllActions()
            ->setView('plugins/order-retail::table.index');
    }

    public function ajax(): JsonResponse
    {
        $data = $this->table
            ->eloquent($this->query())
            ->editColumn('customer_name', function (Order $item) {
                return $item->customer_name;
            })
            ->editColumn('code', function (Order $item) {
                return '<p role="button" class="text-primary mb-0 view-purchase-order-btn" data-id="'.$item->code.'">'.$item->code.'</p>';
            })->editColumn('collapse_action', function (Order $item) {
                return '<a class="show_collapse_btn" data-bs-toggle="collapse" role="button" data-id="' . $item->id . '">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 14 14" fill="none">
                       <path fill-rule="evenodd" clip-rule="evenodd" d="M8.50842 7.00012L3.6709 11.8376L4.49586 12.6626L10.1583 7.00012L4.49586 1.33764L3.6709 2.1626L8.50842 7.00012Z" fill="#8E8E8E"/>
                    </svg>
                </a>';
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
                return Html::link("#", "code");
            })
            ->editColumn('amount', function (Order $item) {
                return  number_format($item->amount, 0, ',', '.') . '₫';
            })
            ->editColumn('created_by', function (Order $item) {
                $created = get_action(\Botble\OrderStepSetting\Enums\ActionEnum::RETAIL_SALE_CREATE_ORDER, $item->id);
                if ($created) {
                    $created->load('handler:id,first_name,last_name');
                }
                return $created?->handler?->name;
            })
            ->editColumn('level', function (Order $item) {
                return BaseHelper::clean(trans('plugins/sales::sales.customer.level.' .  $item->level));
            })
            ->editColumn('status', function (Order $item) {
                return '<p 
                class="mb-0 view-actions-btn" 
                data-action-id="' . $item->lastAction->id . '"
                style="cursor: pointer">
                  ' . $item->lastAction->actionSetting->title . '
                </p>';
            })
            ->setRowClass('on-loading position-relative')
            ->setRowAttr([
                'data-id' => fn ($item) => $item->id,
            ]);
        return $this->toJson($data);
    }


    public function query(): Relation|Builder|QueryBuilder
    {
        // $department_code = get_department_code_curr_user();
        $query = $this
            ->getModel()
            ->query()
            ->select([
                'id',
                'order_type',
                'code',
                'customer_name',
                'customer_phone',
                'amount',
                'note',
                'expected_date',
                'created_at',
                'status',
            ])->with([
                'lastAction'
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

            //Show collapse step
            $(document).off('click');
            $(document).on('click', '.show_collapse_btn', function(){
                const order_id = $(this).data('id');

                let _catch = catches.find(item => item == order_id);

                if(!_catch && !$(this).closest('tr').next().find('.collapse').hasClass('show')){
                    $(this).attr('disabled', true);
                    $(this).after(loadingElement);
                    $.ajax({
                        method: 'GET',
                        url: '/admin/order-step/steps',
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

            //Xem chi tiết step
            $(document).on('click', '.show_detail_status_btn', function(){
                const step_detail_id = $(this).data('id');
                $('#statusDetailModal .modal-body')
                   .html(loadingElement)
                   .addClass('on-loading position-relative');
                $.ajax({
                    method: 'GET',
                    url: '/admin/order-step/detail',
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
            Column::make('code')
                ->nowrap()
                ->title('Số YCSX')
                ->orderable(false),
            Column::make('quotation')
                ->nowrap()
                ->title('Báo giá')
                ->searchable(false)
                ->orderable(false),
            Column::make('production')
                ->nowrap()
                ->title('Đơn đặt hàng')
                ->searchable(false)
                ->orderable(false),
            Column::make('customer_name')
                ->nowrap()
                ->title('Tên khách hàng')
                ->orderable(false),
            Column::make('amount')
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
                ->searchable(false)
                ->orderable(false),
            Column::make('status')
                ->title('Trạng thái')
                ->searchable(false)
                ->orderable(false),
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
