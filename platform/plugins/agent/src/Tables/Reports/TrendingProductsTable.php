<?php

namespace Botble\Agent\Tables\Reports;

use Botble\Agent\Models\Agent;
use Botble\Agent\Models\AgentProduct;
use Botble\Base\Facades\Html;
use Botble\Ecommerce\Facades\EcommerceHelper as EcommerceHelper;
use Botble\Ecommerce\Models\ProductView;
use Botble\Table\Abstracts\TableAbstract;
use Botble\Table\Columns\Column;
use Botble\Table\Columns\IdColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Http\JsonResponse;

class TrendingProductsTable extends TableAbstract
{
    public function setup(): void
    {
        $this->model(AgentProduct::class);

        $this->view = $this->simpleTableView();
        $this->setOption('id','botble-agent-tables-reports-trending-products-table');
    }

    public function ajax(): JsonResponse
    {
        $data = $this->table
            ->eloquent($this->query())
            ->editColumn('product_id', function (AgentProduct $item) {
                if (! $item->is_variation) {
                    return $item->product_id;
                }

                return $item->product_id;
            })
            ->editColumn('name', function (AgentProduct $item) {
                $product = $item->product->first();
                return Html::link($product->url, $product->name, ['target' => '_blank']);
            })
            ->editColumn('views', function (AgentProduct $product) {
                return number_format((float)$product->views_count);
            });

        return $this->toJson($data);
    }

    public function query(): Relation|Builder|QueryBuilder
    {
        [$startDate, $endDate] = EcommerceHelper::getDateRangeInReport(request());

        if(isset(request()->agent_id)){
            $agentId = (int)request()->agent_id;
        }else{
            $agentList = getListAgentIdByUser();
            $agentId = reset($agentList);
        }

        $query = $this
            ->getModel()
            ->query()
            ->where('where_type', Agent::class)
            ->where('where_id', $agentId)
            ->whereDate('created_at', '>=', $startDate)
            ->whereDate('created_at', '<=', $endDate)
            ->with('product')
            // ->groupBy('product_id')
            ->select([
                '*',
                'views_count' => ProductView::query()
                    ->selectRaw('SUM(views) as views_count')
                    ->whereColumn('product_id', 'agent_products.product_id')
                    ->whereDate('date', '>=', $startDate)
                    ->whereDate('date', '<=', $endDate)
                    ->groupBy('product_id'),
            ])
            ->orderByDesc('views_count')
            ->limit(5);

        return $this->applyScopes($query);
    }

    public function getColumns(): array
    {
        return $this->columns();
    }

    public function columns(): array
    {
        return [
            Column::make('product_id')
            ->title(trans('plugins/agent::order.product_id'))
            ->width(80)
            ->orderable(false)
            ->searchable(false),
            Column::make('name')
                ->title(trans('plugins/agent::reports.product_name'))
                ->alignStart()
                ->orderable(false)
                ->searchable(false),
            Column::make('views')
                ->title(trans('plugins/agent::reports.views'))
                ->alignEnd()
                ->orderable(false)
                ->searchable(false),
        ];
    }

    public function isSimpleTable(): bool
    {
        return true;
    }
}
