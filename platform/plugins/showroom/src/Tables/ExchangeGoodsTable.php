<?php

namespace Botble\Showroom\Tables;

use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Facades\BaseHelper;
use Botble\Base\Facades\Html;
use Botble\Showroom\Models\ExchangeGoods;
use Botble\Table\Abstracts\TableAbstract;
use Botble\Table\Actions\EditAction;
use Botble\Table\BulkChanges\StatusBulkChange;
use Botble\Table\Columns\Column;
use Botble\Table\Columns\CreatedAtColumn;
use Botble\Table\Columns\IdColumn;
use Botble\Table\Columns\NameColumn;
use Botble\Table\Columns\StatusColumn;
use Illuminate\Contracts\Routing\UrlGenerator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class ExchangeGoodsTable extends TableAbstract
{

    protected $permission = [];

    public function setup(): void
    {
        $this
            ->model(ExchangeGoods::class)
            ->addActions([
                EditAction::make()
                    ->route('exchange-goods.view'),
            ])->removeAllActions();
    }

    public function ajax(): JsonResponse
    {
        $data = $this->table
            ->eloquent($this->query())
            ->editColumn('operators', function(ExchangeGoods $item){
                return view('plugins/showroom::exchange-goods.partirial.view-action', compact('item'));
            })
            ->editColumn('description', function(ExchangeGoods $item){
                return Str::limit($item->description, 50);
            });
        ;

        return $this->toJson($data);
    }

    public function query(): Relation|Builder|QueryBuilder
    {
        $query = $this
            ->getModel()
            ->query()
            ->select([
                'id',
                'showroom_id',
                'total_quantity',
                'total_amount',
                'description',
                'status',
                'created_at',
            ])->with('showroom');

        return $this->applyScopes($query);
    }

    public function columns(): array
    {
        $title_name = trans('plugins/showroom::showroom.column_name_table');
        return [
            IdColumn::make(),
            Column::make('showroom.name')->searchable(false)->orderable(false),
            Column::make('total_quantity')->title(__('Tổng sản phẩm đổi'))->searchable(false)->width(50),
            Column::make('total_amount')->title(__('Tổng tiền sản phẩm đổi'))->searchable(false)->width(50),
            Column::make('description')->title(__('Ghi chú'))->searchable(false)->orderable(false)->width(450),
            CreatedAtColumn::make()->searchable(false),
            StatusColumn::make()->searchable(false)->orderable(false),
            Column::make('operators')
                ->title('Hành động')
                ->width(50)
                ->orderable(false)
                ->searchable(false)
        ];
    }

    public function buttons(): array
    {
        return $this->addCreateButton(route('exchange-goods.create'));
    }

    public function getBulkChanges(): array
    {
        return [];
    }

    public function getFilters(): array
    {
        return [];
    }
}
