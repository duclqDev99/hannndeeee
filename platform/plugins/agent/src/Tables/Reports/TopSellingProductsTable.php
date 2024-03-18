<?php

namespace Botble\Agent\Tables\Reports;

use Botble\Agent\Models\Agent;
use Botble\Agent\Models\AgentProduct;
use Botble\Base\Facades\BaseHelper;
use Botble\Base\Facades\Html;
use Botble\Ecommerce\Facades\EcommerceHelper;
use Botble\Payment\Enums\PaymentStatusEnum;
use Botble\Table\Abstracts\TableAbstract;
use Botble\Table\Columns\Column;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class TopSellingProductsTable extends TableAbstract
{
    public function setup(): void
    {
        $this->model(AgentProduct::class);

        $this->type = self::TABLE_TYPE_SIMPLE;
        $this->view = $this->simpleTableView();
        // $this->setOption('id','botble-agent-tables-reports-top-selling-products-table');

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
                if (! $product->is_variation) {
                    return Html::link($product ->url, BaseHelper::clean($product ->name), ['target' => '_blank']);
                }

                $attributeText = $product ->variation_attributes;

                return Html::link(
                    $product ->original_product->url,
                    BaseHelper::clean($product ->original_product->name),
                    ['target' => '_blank']
                )
                        ->toHtml() . ' ' . Html::tag('small', $attributeText);
            })
            ;

        return $this->toJson($data);
    }

    public function query(): Relation|Builder|QueryBuilder
    {
        if(isset(request()->agent_id)){
            $agentId = (int)request()->agent_id;
        }else{
            $agentList = getListAgentIdByUser();
            $agentId = reset($agentList);
        }
        [$startDate, $endDate] = EcommerceHelper::getDateRangeInReport(request());
        $query = $this->getModel()
            ->query()

            ->where('where_type', Agent::class)
            ->where('where_id', $agentId)
            ->whereDate('created_at', '>=', $startDate)
            ->whereDate('created_at', '<=', $endDate)
            ->with('product')
            // ->groupBy('product_id')
            ->select([
                '*',
                DB::raw('COALESCE(quantity_sold_not_qrcode, 0) + COALESCE(quantity_qrcode_sold, 0) as total_sold')
            ])
            ->orderBy('total_sold', 'desc')
            // ->all()
            ;
        // dd($query);
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
            Column::make('total_sold')
                ->title(trans('plugins/agent::reports.quantity'))
                ->width(60)
                ->alignEnd()
                ->orderable(false)
                ->searchable(false),
        ];
    }
}
