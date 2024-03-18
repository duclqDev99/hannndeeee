<?php

namespace Botble\Warehouse\Tables;

use Botble\Base\Facades\BaseHelper;
use Botble\Base\Facades\Html;
use Botble\Warehouse\Models\ReceiptInventory;
use Botble\Table\Abstracts\TableAbstract;
use Botble\Table\Actions\DeleteAction;
use Botble\Table\Actions\EditAction;
use Botble\Table\BulkActions\DeleteBulkAction;
use Botble\Table\Columns\Column;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class ReceiptInventoryTable extends TableAbstract
{
    public function setup(): void
    {
        $this
            ->model(ReceiptInventory::class)
            ->addActions([
                EditAction::make()
                    ->route('receipt-inventory.edit'),
                DeleteAction::make()
                    ->route('receipt-inventory.destroy'),
            ])
            ->removeAllActions();
    }

    public function ajax(): JsonResponse
    {
        $data = $this->table
            ->eloquent($this->query())
            ->editColumn('username', function (ReceiptInventory $item) {
                if (! $this->hasPermission('users.edit')) {
                    return BaseHelper::clean($item->users->username);
                }
                return Html::link(route('users.profile.view', $item->users->id), BaseHelper::clean($item->users->username));
            })
            ->editColumn('count_product', function (ReceiptInventory $item) {
                return BaseHelper::clean(count(ReceiptInventory::where(['proposal_code' => $item->proposal_code])->get()));
            })
            ->editColumn('status', function (ReceiptInventory $item) {
                $status = '';
                if ($item->status == 'waiting') {
                    $status = 'warning';
                } else if ($item->status == 'approved') {
                    $status = 'success';
                } else {
                    $status = 'danger';
                }
                return '<span class="label-' . $status . ' status-label">' . $item->status . '</span>';
            })
            ->editColumn('operator', function (ReceiptInventory $item) {
                if (! $this->hasPermission('receipt-inventory.*')) {
                    return BaseHelper::clean('');
                }
                $actionDelete = '
                <a data-bs-toggle="tooltip" data-bs-original-title="Delete" href="'.route('receipt.destroy.code', $item->proposal_code).'" class="btn btn-sm btn-icon btn-danger" data-dt-single-action="" data-method="DELETE" data-confirmation-modal="true" data-confirmation-modal-title="Confirm delete" data-confirmation-modal-message="Do you really want to delete this record?" data-confirmation-modal-button="Delete" data-confirmation-modal-cancel-button="Cancel">
                        <i class="fa fa-trash"></i>
                    <span class="sr-only">Delete</span>
                </a>
                ';

                $actionPurchase = '';
                if($item->status == 'waiting_stock')
                {
                    $actionPurchase = '
                    <a data-bs-toggle="tooltip" data-bs-original-title="Receipt" href="'.route('receipt-inventory.code', $item->proposal_code).'" class="btn btn-sm btn-icon btn-success"><i class="fa-solid fa-file-import"></i><span class="sr-only">Receipt</span></a>
                    ';
                }

                return '
                <div class="table-actions">
                    '.$actionPurchase.'
                 </div>
                ';
            });

        return $this->toJson($data);
    }

    public function query(): Relation|Builder|QueryBuilder
    {
        $inventoryId = get_inventory_id_by_user();
        $query = $this
            ->getModel()
            ->query()
            ->select([
                'user_id',
                'proposal_code',
                'inventory_id',
                'amount',
                'status',
                'expected_date',
                'created_at',
            ])->orderByDesc(DB::raw('POSITION("waiting_stock" IN status)'))->orderByDesc('created_at')->groupBy('proposal_code','user_id','inventory_id','amount','status','expected_date','created_at');

        if(!Auth::user()->isSuperUser() && $inventoryId !== 0)
        {
            return $this->applyScopes($query->where(['inventory_id' => $inventoryId]));
        }
        return $this->applyScopes($query);

    }

    public function columns(): array
    {
        return [
            Column::make('username')
                ->title('Người duyệt')
                ->width(100)
                ->orderable(false),
            Column::make('proposal_code')
                ->title('Mã phiếu')
                ->orderable(false),
            Column::make('count_product')
                ->title('Số sản phẩm trong đơn')
                ->width(80)
                ->orderable(false),
            Column::make('amount')
                ->title('Tổng tiền')
                ->orderable(false),
            Column::make('status')
                ->orderable(false),
            Column::make('operator')
            ->width(70)
            ->orderable(false),
        ];
    }


    public function bulkActions(): array
    {
        return [
            DeleteBulkAction::make()->permission('receipt-inventory.destroy'),
        ];
    }

    public function getBulkChanges(): array
    {
        return [
            'created_at' => [
                'title' => trans('core/base::tables.created_at'),
                'type' => 'date',
            ],
        ];
    }

    public function getFilters(): array
    {
        return $this->getBulkChanges();
    }
}
