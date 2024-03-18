<?php

namespace Botble\Agent\Tables;

use Botble\Agent\Actions\ConfirmAction;
use Botble\Agent\Models\AgentUser;
use Botble\Base\Facades\BaseHelper;
use Botble\Base\Facades\Html;
use Botble\Base\Enums\BaseStatusEnum;
use Botble\Agent\Models\AgentReceipt;
use Botble\Table\Abstracts\TableAbstract;
use Botble\Table\Actions\DeleteAction;
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

class AgentReceiptTable extends TableAbstract
{
    public function setup(): void
    {
        $this
            ->model(AgentReceipt::class)
            ->addActions([
                ConfirmAction::make()
                    ->route('agent-receipt.confirmView'),
            ])->removeAllActions();
    }

    public function ajax(): JsonResponse
    {
        $data = $this->table
            ->eloquent($this->query())
            ->editColumn('name', function (AgentReceipt $item) {
                if (!$this->hasPermission('agent-receipt.edit')) {
                    return BaseHelper::clean($item->name);
                }
                return Html::link(route('agent-receipt.edit', $item->getKey()), BaseHelper::clean($item->name));
            })
            ->editColumn('warehouse_receipt_id', function (AgentReceipt $item) {
                return $item->warehouseReceipt->name . ' - ' . $item->warehouseReceipt->agent->name;
            })
            ->editColumn('receipt_code', function (AgentReceipt $item) {
                return BaseHelper::clean(get_proposal_receipt_product_code($item->receipt_code));
            })
            ->editColumn('warehouse', function (AgentReceipt $item) {
                return $item->warehouse->name . ' - ' . $item->warehouse->hub->name;
            })
            ->editColumn('general_order_code', function (AgentReceipt $item) {
                return $item->general_order_code ?: '—';
            })->editColumn('operators', function (AgentReceipt $item) {
                return view('plugins/agent::actions.agent-receipt', compact('item'))->render();
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
                'warehouse_receipt_id',
                'proposal_id',
                'warehouse_name',
                'warehouse_address',
                'issuer_id',
                'invoice_issuer_name',
                'invoice_confirm_name',
                'warehouse_id',
                'warehouse_type',
                'general_order_code',
                'quantity',
                'title',
                'description',
                'expected_date',
                'date_confirm',
                'reason_cancel',
                'receipt_code',
                'status',
            ])->orderByRaw(" CASE status

            WHEN 'pending' THEN 1
            WHEN 'cancel' THEN 2
            WHEN 'approved' THEN 3
            ELSE 4
             END ASC
            ")->orderBy('expected_date', 'asc')
            ->when(!request()->user()->hasPermission('agent.all'), function ($q) {
                $authUserId = request()->user()->id;
                $userHub = AgentUser::where('user_id', $authUserId)->pluck('agent_id'); // Assuming you have a 'hub_id' column in HubUser
                if (request()->user()->hasPermission('agent-receipt.confirm')) {
                    $q->whereHas('warehouseReceipt.agent', function ($query) use ($userHub) {
                        $query->whereIn('id', $userHub);
                    });
                } else {
                    $q->whereHas('warehouseReceipt.agent', function ($query) use ($userHub) {
                        $query->whereIn('id', $userHub)->where('issuer_id', request()->user()->id);
                        ;
                    });
                }

            });
        return $this->applyScopes($query);
    }

    public function columns(): array
    {
        return [
            Column::make('receipt_code')->title('Mã phiếu nhập'),
            Column::make('general_order_code')->title('Mã đơn'),
            Column::make('warehouse_receipt_id')->title('Kho nhập'),
            Column::make('warehouse')->title('Kho xuất'),
            Column::make('title')->title('Mục đích nhập kho'),
            CreatedAtColumn::make('expected_date')->title('Ngày dự kiến')->dateFormat('d-m-Y'),
            CreatedAtColumn::make('date_confirm')->title('Ngày nhập'),
            StatusColumn::make(),
            Column::make('operators')
                ->title('Hành động')
                ->width(100)
                ->orderable(false)
                ->searchable(false)
        ];
    }

    public function buttons(): array
    {
        return [];
    }

    public function bulkActions(): array
    {
        return [

        ];
    }

    public function getBulkChanges(): array
    {
        return [

        ];
    }

    public function getFilters(): array
    {
        return $this->getBulkChanges();
    }

    public function getCheckboxColumnHeading(): array
    {
        return [];
    }
}
