<?php

namespace Botble\Showroom\Tables;

use Botble\Base\Facades\BaseHelper;
use Botble\Base\Facades\Html;
use Botble\Ecommerce\Enums\OrderStatusEnum;
use Botble\Ecommerce\Facades\EcommerceHelper;
use Botble\Ecommerce\Facades\OrderHelper;
use Botble\Ecommerce\Models\Customer;
use Botble\Ecommerce\Models\Order;
use Botble\Ecommerce\Models\OrderHistory;
use Botble\Ecommerce\Tables\Formatters\PriceFormatter;
use Botble\Showroom\Models\Showroom;
use Botble\Showroom\Models\ShowroomOrderViewEc;
use Botble\Table\Abstracts\TableAbstract;
use Botble\Table\Actions\EditAction;
use Botble\Table\Columns\Column;
use Botble\Table\Columns\CreatedAtColumn;
use Botble\Table\Columns\IdColumn;
use Botble\Table\Columns\StatusColumn;
use Illuminate\Contracts\Routing\UrlGenerator;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Blade;
use Symfony\Component\HttpFoundation\Response;
use Yajra\DataTables\DataTables;

class ShowroomOrderTable extends TableAbstract
{
    protected $defaultShowroomId;
    public function __construct(DataTables $table, UrlGenerator $urlGenerator)
    {
        parent::__construct($table, $urlGenerator);
        $this->defaultShowroomId = get_showroom_for_user()->pluck('id')->first();
    }
    public function setup(): void
    {
        $this
            ->model(ShowroomOrderViewEc::class)
            ->addActions([
                EditAction::make()->route('showroom.orders.edit'),
                // DeleteAction::make()->route('showroom.orders.destroy'),
            ])->displayActionsAsDropdownWhenActionsMoresThan(0);
    }

    public function ajax(): JsonResponse
    {
        $data = $this->table
            ->eloquent($this->query())
            ->editColumn('payment_status', function (ShowroomOrderViewEc $item) {
                if (!is_plugin_active('payment')) {
                    return '&mdash;';
                }

                return $item->payment->status->label() ? BaseHelper::clean(
                    $item->payment->status->toHtml()
                ) : '&mdash;';
            })
            ->editColumn('refunded_point_amount', function (ShowroomOrderViewEc $item) {
                if ($item->payment->refunded_point_amount == 0 && $item->payment->is_refunded_point == 0) {
                    return '<span class="badge bg-warning text-warning-fg">
                    Chưa hoàn điểm
                    </span>';
                }
                return $item->payment->refunded_point_amount;
            })
            ->editColumn('payment_method', function (ShowroomOrderViewEc $item) {
                if (!is_plugin_active('payment')) {
                    return '&mdash;';
                }

                return BaseHelper::clean($item->payment->payment_channel->label() ?: '&mdash;');
            })
            ->editColumn('content_banking', function (ShowroomOrderViewEc $item){
                if($item->payment->payment_channel){
                    return $item->payment->content_banking;
                }
            })
            ->formatColumn('amount', PriceFormatter::class)
            ->formatColumn('shipping_amount', PriceFormatter::class)
            ->editColumn('user_id', function (ShowroomOrderViewEc $item) {
                return BaseHelper::clean($item->user->name ?: $item->address->name);
            })
            ->editColumn('customer_email', function (ShowroomOrderViewEc $item) {
                return BaseHelper::clean($item->user->email ?: $item->address->email);
            })
            ->editColumn('customer_phone', function (ShowroomOrderViewEc $item) {
                return BaseHelper::clean($item->user->phone ?: $item->address->phone);
            })
            ->editColumn('orderer', function (ShowroomOrderViewEc $item) {
                if (!is_plugin_active('payment')) {
                    return '&mdash;';
                }

                switch ($item->payment->customer_type) {
                    case Customer::class:
                        return Html::tag('span', 'Trang chủ', ['class' => ' badge bg-info text-info-fg'])->toHtml();
                    case Showroom::class:
                        return Html::tag('span', 'Showroom', ['class' => ' badge bg-success text-success-fg'])->toHtml();
                    default:
                        return '&mdash;';
                };
            });

        if (EcommerceHelper::isTaxEnabled()) {
            $data = $data->formatColumn('tax_amount', PriceFormatter::class);
        }

        $data = $data
            ->filter(function ($query) {
                if ($keyword = $this->request->input('search.value')) {
                    return $query
                        ->whereHas('address', function ($subQuery) use ($keyword) {
                            return $subQuery
                                ->where('name', 'LIKE', '%' . $keyword . '%')
                                ->orWhere('email', 'LIKE', '%' . $keyword . '%')
                                ->orWhere('phone', 'LIKE', '%' . $keyword . '%');
                        })
                        ->orWhereHas('user', function ($subQuery) use ($keyword) {
                            return $subQuery
                                ->where('name', 'LIKE', '%' . $keyword . '%')
                                ->orWhere('email', 'LIKE', '%' . $keyword . '%')
                                ->orWhere('phone', 'LIKE', '%' . $keyword . '%');
                        })
                        ->orWhere('code', 'LIKE', '%' . $keyword . '%');
                }

                return $query;
            });
        return $this->toJson($data);
    }

    public function query(): Relation|Builder|QueryBuilder
    {
        $with = ['user'];

        if (is_plugin_active('payment')) {
            $with[] = 'payment';
        }

        $query = $this
            ->getModel()
            ->query()
            ->with($with)
            ->select([
                'id',
                'code',
                'status',
                'user_id',
                'created_at',
                'amount',
                'tax_amount',
                'shipping_amount',
                'payment_id',
            ]);
        $filterWhereId = request()->query('filter_values')[0] ?? null;
        if ($filterWhereId === null) {
            $query->where('where_id', $this->defaultShowroomId);
        } else {
            $query->where('where_id', (int)$filterWhereId);
        }

        return $this->applyScopes($query);
    }

    public function columns(): array
    {
        $columns = [
            IdColumn::make(),
            Column::make('user_id')
                ->title(trans('plugins/ecommerce::order.customer_label'))
                ->alignStart(),
            Column::make('customer_phone')
                ->title(trans('plugins/ecommerce::order.phone'))
                ->alignStart()
                ->orderable(false),
            Column::formatted('amount')
                ->title(trans('plugins/ecommerce::order.total_amount')),
            CreatedAtColumn::make(),
        ];

        // if (EcommerceHelper::isTaxEnabled()) {
        //     $columns = array_merge($columns, [
        //         Column::formatted('tax_amount')
        //             ->title(trans('plugins/ecommerce::order.tax_amount')),
        //     ]);
        // }

        $columns = array_merge($columns, [
            Column::make('orderer')
                ->title(trans('plugins/ecommerce::order.orderer'))
                ->orderable(false),
        ]);

        // $columns = array_merge($columns, [
        //     Column::formatted('shipping_amount')
        //         ->title(trans('plugins/ecommerce::order.shipping_amount')),
        // ]);

        if (is_plugin_active('payment')) {
            $columns = array_merge($columns, [
                Column::make('payment_method')
                    ->name('payment_id')
                    ->title(trans('plugins/ecommerce::order.payment_method'))
                    ->alignStart(),
                Column::make('payment_status')
                    ->name('payment_id')
                    ->title(trans('plugins/ecommerce::order.payment_status_label')),
                Column::make('content_banking')
                    ->title(trans('Nội dung')),
            ]);
        }
        $columns = array_merge($columns, [
            Column::make('refunded_point_amount')
                ->title(trans('Số điểm hoàn lại ví'))
                ->alignCenter(),
        ]);


        return array_merge($columns, [
            StatusColumn::make(),
        ]);
    }

    public function buttons(): array
    {
        $buttons = $this->addCreateButton(route('showroom.orders.create'), 'showroom.orders.create');
        if (Auth::user()->hasPermission('showroom.orders.create')) {
            $buttons['payment-customer'] = [
                'class' => 'btn-success view-payment-for-customer',
                'text' => Blade::render('<span data-url="{{ $route }}"><x-core::icon name="ti ti-wallet"/> {{ $title }} </span>', [
                    'title' => 'Khách hàng',
                    'route' => route('showroom.orders.checkout-payment'),
                ]),
                'permission' => 'showroom.orders.create'
            ];
        }

        $listShowroomByUser = get_showroom_for_user()->pluck('name', 'id')->toArray();
        $route = route('showroom.orders.index');
        $defaultShowroomId = $this->defaultShowroomId;

        $selectHtml = Blade::render(view('plugins/showroom::reports.field.dropdown', compact('listShowroomByUser', 'route', 'defaultShowroomId'))->render());

        $buttons['selectField'] = [
            'class' => 'btn m-0 p-0 b',
            'text' => $selectHtml,
        ];

        return $buttons;
    }

    public function bulkActions(): array
    {
        return [
            //            DeleteBulkAction::make()->permission('orders.destroy'),
        ];
    }

    public function getBulkChanges(): array
    {
        return [];
    }

    public function getFilters(): array
    {
        return [];
    }

    public function renderTable($data = [], $mergeData = []): View|Factory|Response
    {
        return parent::renderTable($data, $mergeData);
    }

    public function getDefaultButtons(): array
    {
        return array_merge(['export'], parent::getDefaultButtons());
    }

    public function saveBulkChangeItem(Model|Order $item, string $inputKey, string|null $inputValue): Model|bool
    {
        if ($inputKey === 'status' && $inputValue == OrderStatusEnum::CANCELED) {
            /**
             * @var Order $item
             */
            if (!$item->canBeCanceledByAdmin()) {
                return $item;
            }

            OrderHelper::cancelOrder($item);

            OrderHistory::query()->create([
                'action' => 'cancel_order',
                'description' => trans('plugins/showroom::order.order_was_canceled_by'),
                'order_id' => $item->getKey(),
                'user_id' => Auth::id(),
            ]);

            return $item;
        }

        return parent::saveBulkChangeItem($item, $inputKey, $inputValue);
    }

    public function applyFilterCondition(
        Builder|QueryBuilder|Relation $query,
        string                        $key,
        string                        $operator,
        string|null                   $value
    ): Builder|QueryBuilder|Relation {
        switch ($key) {
                // case 'status':
                //     if (!OrderStatusEnum::isValid($value)) {
                //         return $query;
                //     }

                //     break;
                // case 'shipping_method':
                //     if (!$value) {
                //         break;
                //     }

                //     if (!ShippingMethodEnum::isValid($value)) {
                //         return $query;
                //     }

                //     break;
                // case 'payment_method':
                //     if (!is_plugin_active('payment') || !ExtendedPaymentMethodEnum::isValid($value)) {
                //         return $query;
                //     }

                //     return $query->whereHas('payment', function ($subQuery) use ($value) {
                //         $subQuery->where('payment_channel', $value);
                //     });

                // case 'payment_status':
                //     if (!is_plugin_active('payment') || !PaymentStatusEnum::isValid($value)) {
                //         return $query;
                //     }

                //     return $query->whereHas('payment', function ($subQuery) use ($value) {
                //         $subQuery->where('status', $value);
                //     });
                // case 'store_id':
                //     if (!is_plugin_active('marketplace')) {
                //         return $query;
                //     }
                //     if ($value == -1) {
                //         return $query->where(function ($subQuery) {
                //             $subQuery->whereNull('store_id')
                //                 ->orWhere('store_id', 0);
                //         });
                //     }
        }
        return parent::applyFilterCondition($query, $key, $operator, $value);
    }

    protected function filterByCustomer(
        Builder|QueryBuilder|Relation $query,
        string                        $column,
        string                        $operator,
        string|null                   $value
    ): Builder|QueryBuilder|Relation {
        if ($operator === 'like') {
            $value = '%' . $value . '%';
        } elseif ($operator !== '=') {
            $operator = '=';
        }

        return $query
            ->where(function ($query) use ($column, $operator, $value) {
                $query
                    ->whereHas('user', function ($subQuery) use ($column, $operator, $value) {
                        $subQuery->where($column, $operator, $value);
                    })
                    ->orWhereHas('address', function ($subQuery) use ($column, $operator, $value) {
                        $subQuery->where($column, $operator, $value);
                    });
            });
    }
}
