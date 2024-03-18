<?php

namespace Botble\Agent\Tables;

use Botble\Agent\Actions\OrderDetailAction;
use Botble\Agent\Models\Agent;
use Botble\Agent\Models\AgentOrder;
use Botble\Base\Facades\BaseHelper;
use Botble\Agent\Enums\OrderStatusEnum;
use Botble\Ecommerce\Enums\ShippingMethodEnum;
use Botble\Ecommerce\Facades\EcommerceHelper;
use Botble\Ecommerce\Facades\OrderHelper;
use Botble\Ecommerce\Models\Order;
use Botble\Ecommerce\Models\OrderHistory;
use Botble\Ecommerce\Tables\Formatters\PriceFormatter;
use Botble\Payment\Enums\ExtendedPaymentMethodEnum;
use Botble\Payment\Enums\PaymentStatusEnum;
use Botble\Table\Abstracts\TableAbstract;
use Botble\Table\Actions\DeleteAction;
use Botble\Table\Actions\EditAction;
use Botble\Table\BulkActions\DeleteBulkAction;
use Botble\Table\Columns\CheckboxColumn;
use Botble\Table\Columns\Column;
use Botble\Table\Columns\CreatedAtColumn;
use Botble\Table\Columns\IdColumn;
use Botble\Table\Columns\StatusColumn;
use Yajra\DataTables\DataTables;
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
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class AgentOrderTable extends TableAbstract
{
    protected $agentList;

    public function __construct(protected DataTables $table, UrlGenerator $urlGenerator)
    {
        parent::__construct($table, $urlGenerator);
        $this->agentList = get_agent_for_user()->pluck('name', 'id')->toArray();

    }
    public function setup(): void
    {
        $this
            ->model(AgentOrder::class)
            ->addActions([
                OrderDetailAction::make()
                ->route('agent.orders.detail'),
            ])
            ->displayActionsAsDropdownWhenActionsMoresThan(0);
    }

    public function ajax(): JsonResponse
    {
        $data = $this->table
            ->eloquent($this->query())
            // ->editColumn('payment_status', function (Order $item) {
            //     if (! is_plugin_active('payment')) {
            //         return '&mdash;';
            //     }

            //     return $item->payment->status->label() ? BaseHelper::clean(
            //         $item->payment->status->toHtml()
            //     ) : '&mdash;';
            // })
            // ->editColumn('payment_method', function (Order $item) {
            //     if (! is_plugin_active('payment')) {
            //         return '&mdash;';
            //     }

            //     return BaseHelper::clean($item->payment->payment_channel->label() ?: '&mdash;');
            // })
            // ->formatColumn('amount', PriceFormatter::class)
            // ->formatColumn('shipping_amount', PriceFormatter::class)
            // ->editColumn('user_id', function (Order $item) {
            //     return BaseHelper::clean($item->user->name ?: $item->address->name);
            // })
            // ->editColumn('customer_email', function (Order $item) {
            //     return BaseHelper::clean($item->user->email ?: $item->address->email);
            // })
            ->editColumn('amount', function (AgentOrder $item) {
                return number_format($item?->amount, 0, ',', '.').' VNĐ';
            })
        ;

        return $this->toJson($data);
    }

    public function query(): Relation|Builder|QueryBuilder
    {
        // $with = ['user','agentOrder.where.users'];
        $query = $this
            ->getModel()
            ->query()
            // ->with($with)
            ->select([
                'id',
                'code',
                'status',
                'description',
                'amount',
                'list_id_product_qrcode',
                'created_at',
            ]);
        $filterWhereId = request()->query('filter_values') ?? null;
        if ($filterWhereId === null) {
            $query->where('where_id', key($this->agentList));
        } else {
            $query->where('where_id', (int)$filterWhereId);
        }

        return $this->applyScopes($query);
    }

    public function columns(): array
    {
        $columns = [
            IdColumn::make(),
            Column::make('code')
                ->title(trans('Mã đơn hàng'))
                ->alignStart(),
            Column::make('amount')
                ->title(trans('Tổng tiền'))
                ->alignStart(),
            StatusColumn::make(),
            Column::make('description')
                ->title(trans('Ghi chú'))
                ->alignStart(),
            CreatedAtColumn::make(),
            ];

            return $columns;
    }

    public function buttons(): array
    {
        $agentList = $this->agentList;
        $route = route('agent.orders.index');
        $defaultShowroomId = $this->defaultShowroomId;
        $nameAgent = count($agentList) > 1 ? null : reset($agentList);
        $agent_id = key($agentList);
        $buttons = $this->addCreateButton(route('agent.orders.create',['select_id' => key($agentList)]), 'agent.orders.create');

        // $buttonCreateRender = Blade::render(view('plugins/agent::reports.button.create', compact('agent_id', 'agentList', 'nameAgent'))->render());

        $selectHtml = Blade::render(view('plugins/agent::reports.field.dropdown', compact('agentList', 'route', 'defaultShowroomId'))->render());
        // $buttons['create'] = [
        //     'class' => 'btn m-0 p-0 b',
        //     'text' => $buttonCreateRender,
        // ];
        $buttons['selectField'] = [
            'class' => 'btn m-0 p-0 b',
            'text' => $selectHtml,
        ];

        return $buttons;
    }

    public function bulkActions(): array
    {
        return [
            // DeleteBulkAction::make()->permission('orders.destroy'),
        ];
    }

    public function getBulkChanges(): array
    {
        return [

        ];
    }

    public function getFilters(): array
    {
        $filters = parent::getFilters();
        return $filters;
    }

    // public function renderTable($data = [], $mergeData = []): View|Factory|Response
    // {
    //     if ($this->isEmpty()) {
    //         return view('plugins/agent::orders.intro');
    //     }

    //     return parent::renderTable($data, $mergeData);
    // }

    public function getDefaultButtons(): array
    {
        return array_merge(['export'], parent::getDefaultButtons());
    }
    public function getCheckboxColumnHeading(): array
    {
        return [];
    }
}
