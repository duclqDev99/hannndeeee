<?php

namespace Botble\Agent\Tables;

use Botble\Agent\Enums\ProposalAgentEnum;
use Botble\Agent\Models\AgentUser;
use Botble\Base\Facades\BaseHelper;
use Botble\Base\Facades\Html;
use Botble\Base\Enums\BaseStatusEnum;
use Botble\Agent\Models\ProposalAgentReceipt;
use Botble\Table\Abstracts\TableAbstract;
use Botble\Table\Actions\DeleteAction;
use Botble\Table\Actions\EditAction;
use Botble\Table\BulkActions\DeleteBulkAction;
use Botble\Table\Columns\Column;
use Botble\Table\Columns\CreatedAtColumn;
use Botble\Table\Columns\IdColumn;
use Botble\Table\Columns\NameColumn;
use Botble\Table\Columns\StatusColumn;
use Botble\WarehouseFinishedProducts\Enums\ProposalProductEnum;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Http\JsonResponse;

class ProposalAgentReceiptTable extends TableAbstract
{
    public function setup(): void
    {
        $this
            ->model(ProposalAgentReceipt::class)
            ->addActions([
                EditAction::make()
                    ->route('proposal-agent-receipt.edit'),
                DeleteAction::make()
                    ->route('proposal-agent-receipt.destroy'),

            ])->removeAllActions();
    }

    public function ajax(): JsonResponse
    {
        $data = $this->table
            ->eloquent($this->query())
            ->editColumn('name', function (ProposalAgentReceipt $item) {
                if (!$this->hasPermission('proposal-agent-receipt.edit')) {
                    return BaseHelper::clean($item->name);
                }
                return Html::link(route('proposal-agent-receipt.edit', $item->getKey()), BaseHelper::clean($item->name));
            })->editColumn('warehouse_name', function (ProposalAgentReceipt $item) {
                return $item->warehouse_name . ' - ' . $item->warehouseReceipt->agent->name;

            })
            ->editColumn('proposal_code', function (ProposalAgentReceipt $item) {
                return BaseHelper::clean(get_proposal_receipt_product_code($item->proposal_code));

            })
            ->editColumn('general_order_code', function (ProposalAgentReceipt $item) {

                return $item->general_order_code ?: '—';

            })
            ->editColumn('operator', function (ProposalAgentReceipt $item) {
                return view('plugins/agent::actions.agent-proposal-receipt', compact('item'))->render();
            })
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
                'proposal_code',
                'warehouse_name',
                'warehouse_address',
                'issuer_id',
                'invoice_issuer_name',
                'invoice_confirm_name',
                'general_order_code',
                'quantity',
                'title',
                'description',
                'expected_date',
                'date_confirm',
                'status',
            ])->orderByRaw(" CASE status
            WHEN 'pending' THEN 1
            WHEN 'wait' THEN 2
            WHEN 'approved' THEN 3
            WHEN 'confirm' THEN 4
            WHEN 'denied' THEN 5
            WHEN 'refuse' THEN 6
            ELSE 7
        END ASC
                ")->orderBy('expected_date', 'asc')
            ->when(!request()->user()->hasPermission('agent.all'), function ($q) {
                $authUserId = request()->user()->id;
                $userHub = AgentUser::where('user_id', $authUserId)->pluck('agent_id'); // Assuming you have a 'hub_id' column in HubUser
                if (request()->user()->hasPermission('proposal-agent-receipt.approve')) {
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
            Column::make('general_order_code')->title('Mã đơn hàng'),
            Column::make('proposal_code')->title('Mã phiếu'),
            Column::make('warehouse_name')->title('Tên kho nhận'),
            Column::make('title')->title('Tiêu đề'),
            Column::make('invoice_issuer_name')->title('Người đề xuất'),
            Column::make('quantity')->title('Số lượng đề xuất'),

            CreatedAtColumn::make('expected_date')->title('Ngày đề xuất')->dateFormat('d-m-y'),
            CreatedAtColumn::make('date_confirm')->title('Ngày nhận')->dateFormat('d-m-y'),
            StatusColumn::make(),
            Column::make('operator')
                ->width(100)->title('Hành động')
                ->orderable(false)
                ->searchable(false),
        ];
    }

    public function buttons(): array
    {
        return $this->addCreateButton(route('proposal-agent-receipt.create'), 'proposal-agent-receipt.create');
    }

    public function getCheckboxColumnHeading(): array
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
}
