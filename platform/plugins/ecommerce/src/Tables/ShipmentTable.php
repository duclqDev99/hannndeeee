<?php

namespace Botble\Ecommerce\Tables;

use Botble\Base\Facades\BaseHelper;
use Botble\Base\Facades\Html;
use Botble\Ecommerce\Enums\ShippingCodStatusEnum;
use Botble\Ecommerce\Enums\ShippingStatusEnum;
use Botble\Ecommerce\Models\Shipment;
use Botble\Ecommerce\Tables\Formatters\PriceFormatter;
use Botble\Table\Abstracts\TableAbstract;
use Botble\Table\Actions\DeleteAction;
use Botble\Table\Actions\EditAction;
use Botble\Table\BulkActions\DeleteBulkAction;
use Botble\Table\Columns\Column;
use Botble\Table\Columns\CreatedAtColumn;
use Botble\Table\Columns\IdColumn;
use Botble\Table\Columns\StatusColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Http\JsonResponse;
use Yajra\DataTables\DataTables;
use Illuminate\Contracts\Routing\UrlGenerator;
use Illuminate\Support\Facades\Blade;

class ShipmentTable extends TableAbstract
{
    protected $default_showroom_id_by_user;
    public function __construct(DataTables $table, UrlGenerator $urlGenerator)
    {
        parent::__construct($table, $urlGenerator);
        $this->default_showroom_id_by_user = get_showroom_for_user()->pluck('id')->first();
    }
    public function setup(): void
    {
        $this
            ->model(Shipment::class)
            ->addActions([
                EditAction::make()->route('showroom.shipments.edit'),
            ]);
    }

    public function htmlDrawCallbackFunction(): string|null
    {
        return <<<JS
            const table = this;
            $(document)
                .off('change', '#showroomDropdown')
                .on('change', '#showroomDropdown', function(){
                    const showroom_id = $(this).val();

                    let baseUrl = table.api().ajax.url();
                    let url = new URL(baseUrl)
                    url.searchParams.set('showroom_id', showroom_id);

                    table.DataTable().ajax.url(url).load();
                })

            $(document)
                .off('change', '#codStatusDropdown')
                .on('change', '#codStatusDropdown', function(){
                    const cod_status = $(this).val();

                    let baseUrl = table.api().ajax.url();
                    let url = new URL(baseUrl)
                    url.searchParams.set('cod_status', cod_status);

                    table.DataTable().ajax.url(url).load();
                })
        JS . $this->htmlInitCompleteFunction();
    }

    public function buttons()
    {
        $buttons = [];

        $listShowroomByUser = get_showroom_for_user()->pluck('name', 'id')->toArray();
        $route = route('showroom.shipments.index');

        $defaultShowroomId = $this->default_showroom_id_by_user;
        $showroomDropdownId = 'showroomDropdown';
        $codStatusDropdownId = 'codStatusDropdown';

        $showroomDropdown = Blade::render(view('plugins/showroom::shipments.field.dropdown', [
            'id' => $showroomDropdownId,
            'data' => $listShowroomByUser,
            'route' => $route
        ])->render());

        $codStatusDropdown = Blade::render(view('plugins/showroom::shipments.field.dropdown', [
            'id' => $codStatusDropdownId,
            'data' => ShippingCodStatusEnum::labels(),
            'route' => $route
        ])->render());

        $buttons['showroomDropdown'] = [
            'class' => 'btn m-0 p-0 b',
            'text' => $showroomDropdown,
        ];

        // $buttons['codStatusDropdown'] = [
        //     'class' => 'btn m-0 p-0 b',
        //     'text' => $codStatusDropdown,
        // ];

        return $buttons;
    }


    public function ajax(): JsonResponse
    {
        $data = $this->table
            ->eloquent($this->query())
            ->editColumn('order_id', function (Shipment $item) {
                if (!$this->hasPermission('orders.edit')) {
                    return $item->order->code;
                }

                return Html::link(
                    route('showroom.orders.edit', $item->order_id),
                    $item->order->code . ' <i class="fa fa-external-link-alt"></i>',
                    ['target' => '_blank'],
                    null,
                    false
                );
            })
            ->editColumn('user_id', function (Shipment $item) {
                return BaseHelper::clean($item->order->user->name ?: $item->order->address->name);
            })
            ->formatColumn('price', PriceFormatter::class)
            ->editColumn('cod_status', function (Shipment $item) {
                return BaseHelper::clean($item->cod_status->toHtml());
            })
            ->editColumn('payer',  function (Shipment $item) {
                return $item->payer->label();
            })
            ->filter(function ($query) {
                $keyword = $this->request->input('search.value');
                if ($keyword) {
                    return $query
                        ->whereHas('order.address', function ($subQuery) use ($keyword) {
                            return $subQuery->where('ec_order_addresses.name', 'LIKE', '%' . $keyword . '%');
                        });
                }

                return $query;
            });

        return $this->toJson($data);
    }

    public function query(): Relation|Builder|QueryBuilder
    {
        $query = $this
            ->getModel()
            ->query()
            ->select([
                'id',
                'order_id',
                'user_id',
                'price',
                'status',
                'cod_status',
                'created_at',
                'payer'
            ])
            ->when(
                request()->has('showroom_id'),
                fn ($q) => $q->whereRelation('order.showroomOrder', 'showroom_orders.where_id',  request('showroom_id')),
                fn ($q) => $q->whereRelation('order.showroomOrder', 'showroom_orders.where_id', $this->default_showroom_id_by_user)
            )
            ->when(
                request()->has('cod_status'),
                fn ($q) => $q->where('cod_status', request('cod_status'))
            )
            ->orderByDesc('created_at');


        return $this->applyScopes($query);
    }

    public function columns(): array
    {
        $columns = [];


        $columns = array_merge($columns, [
            IdColumn::make(),
            Column::make('order_id')->title(trans('plugins/ecommerce::shipping.order_id')),
            Column::make('user_id')->title(trans('plugins/ecommerce::order.customer_label'))->alignStart(),
            Column::formatted('price')
                ->title(trans('plugins/ecommerce::shipping.shipping_amount')),
            Column::make('payer')
                ->title(trans('plugins/ecommerce::shipping.payer')),
            StatusColumn::make(),
            Column::make('cod_status')->title(trans('plugins/ecommerce::shipping.cod_status')),
            CreatedAtColumn::make(),
        ]);

        return $columns;
    }

    public function getBulkChanges(): array
    {
        return [
            // 'status' => [
            //     'title' => trans('core/base::tables.status'),
            //     'type' => 'select',
            //     'choices' => ShippingStatusEnum::labels(),
            //     'validate' => 'required|in:' . implode(',', ShippingStatusEnum::values()),
            // ],
            // 'created_at' => [
            //     'title' => trans('core/base::tables.created_at'),
            //     'type' => 'datePicker',
            // ],
        ];
    }

    public function bulkActions(): array
    {
        return [
            // DeleteBulkAction::make()->permission('ecommerce.shipments.destroy'),
        ];
    }
}
