<?php

namespace Botble\Warehouse\Tables;

use Botble\Base\Facades\BaseHelper;
use Botble\Base\Facades\Html;
use Botble\Warehouse\Enums\MaterialProposalStatusEnum;
use Botble\Warehouse\Models\ProposalPurchaseGoods;
use Botble\Table\Abstracts\TableAbstract;
use Botble\Table\Actions\DeleteAction;
use Botble\Table\Actions\EditAction;
use Botble\Table\BulkActions\DeleteBulkAction;
use Botble\Table\Columns\CreatedAtColumn;
use Botble\Table\Columns\IdColumn;
use Botble\Table\Columns\NameColumn;
use Botble\Table\Columns\StatusColumn;
use Botble\Table\Columns\Column;
use Botble\Warehouse\Enums\PurchaseOrderStatusEnum;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class ProposalPurchaseGoodsTable extends TableAbstract
{
    public function setup(): void
    {
        $this
            ->model(ProposalPurchaseGoods::class)
            ->addActions([
                EditAction::make()
                    ->route('proposal-purchase-goods.edit'),
                DeleteAction::make()
                    ->route('proposal-purchase-goods.destroy'),
            ])
            ->removeAllActions();
    }

    public function ajax(): JsonResponse
    {
        $data = $this->table
            ->eloquent($this->query())
            ->editColumn('date_confirm', function (ProposalPurchaseGoods $item) {
                if (!empty($item->date_confirm)) {
                    return $item->date_confirm;
                }
                return '------------';
            })
            ->editColumn('general_order_code', function (ProposalPurchaseGoods $item) {
                if (!empty($item->general_order_code)) {
                    return $item->general_order_code;
                }
                return '--------';
            })
            ->editColumn('operator', function (ProposalPurchaseGoods $item) {
                $actionPurchase = '';
                $actionDelete = '';

                if ($item->status == PurchaseOrderStatusEnum::PENDING) {
                    $actionDelete = '
                    <li><a data-bs-original-title="Xoá" href="' . route('proposal-purchase-goods.destroy', $item) . '" class="dropdown-item" data-dt-single-action="" data-method="DELETE" data-confirmation-modal="true" data-confirmation-modal-title="Xác nhận" data-confirmation-modal-message="Bạn có chắn chắc muốn xoá đơn này?" data-confirmation-modal-button="Delete" data-confirmation-modal-cancel-button="Thoát">
                        Xoá
                    </a></li>
                    ';

                    if ($this->hasPermission('receipt-purchase-goods.receipt')) {
                        $actionPurchase = '
                        <li><a data-bs-original-title="Duyệt" href="' . route('proposal-purchase-goods.receipt', $item->id) . '" class="dropdown-item">Duyệt đơn</a></li>
                        ';
                    }
                }

                $actionView = '';
                if ($item->status != PurchaseOrderStatusEnum::PENDING || !Auth::user()->hasPermission('receipt-purchase-goods.receipt')) {
                    $actionView = '
                    <li><a data-bs-original-title="View" href="' . route('proposal-purchase-goods.view.code', $item->id) . '" class="dropdown-item">Xem chi tiết</a></li>
                    ';
                }


                $actionEdit = '';
                if ($item->status != PurchaseOrderStatusEnum::APPOROVED && Auth::user()->hasPermission('proposal-purchase-goods.edit')) {
                    $actionEdit = '
                    <li><a data-bs-original-title="Chỉnh sửa" href="' . route('proposal-purchase-goods.edit', $item->id) . '" class="dropdown-item">Chỉnh sửa</a></li>
                    ';
                }

                return '
                <div class="btn-group">
                <button type="button" class="btn btn-secondary" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="fa fa-plus" aria-hidden="true"></i>
                </button>
                  <ul class="dropdown-menu">
                    ' . $actionPurchase . '
                    ' . $actionEdit . '
                    ' . $actionView . '
                    ' . $actionDelete . '
                  </ul>
                </div>
                ';
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
                'title',
                'invoice_issuer_name',
                'invoice_confirm_name',
                'code',
                'general_order_code',
                'warehouse_name',
                'quantity',
                'total_amount',
                'expected_date',
                'date_confirm',
                'status',
                'created_at',
            ])
            ->orderByDesc(DB::raw('POSITION("pending" IN status)'))->orderByDesc('created_at');
        return $this->applyScopes($query);
    }

    public function columns(): array
    {
        return [
            Column::make('warehouse_name')
                ->title('Kho nguyên liệu')
                ->width(200)
                ->orderable(false),
            Column::make('title')
                ->width(400)
                ->title('Tên phiếu')
                ->orderable(false),
            Column::make('code')
                ->title('Mã phiếu')
                ->width(100)
                ->orderable(false),
            Column::make('general_order_code')
                ->title('Mã đơn hàng')
                ->width(100)
                ->orderable(false),
            CreatedAtColumn::make('expected_date')
                ->dateFormat('d/m/Y')
                ->title('Ngày dự kiến'),
            CreatedAtColumn::make('date_confirm')
                ->dateFormat('d/m/Y')
                ->title('Ngày duyệt'),
            CreatedAtColumn::make('created_at')
                ->dateFormat('d/m/Y')
                ->width(100)
                ->title('Ngày tạo'),
            StatusColumn::make('status')
                ->title('Trạng thái')
                ->width(50),
            Column::make('operator')
                ->width(50)
                ->searchable(false)
                ->orderable(false),
        ];
    }

    public function buttons(): array
    {
        return $this->addCreateButton(route('proposal-purchase-goods.create'), 'proposal-purchase-goods.create');
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
        return [
            'invoice_issuer_name' => [
                'title' => 'Người đề xuất ',
                'type' => 'text',
                'validate' => 'required|max:120',
            ],
            'invoice_confirm_name' => [
                'title' => 'Người xác nhận ',
                'type' => 'text',
                'validate' => 'required|max:120',
            ],
            'warehouse_name' => [
                'title' => 'Kho nguyên liệu ',
                'type' => 'text',
                'validate' => 'required|max:120',
            ],
            'title' => [
                'title' => 'Tên phiếu ',
                'type' => 'text',
                'validate' => 'required|max:120',
            ],

            'general_order_code' => [
                'title' => 'Mã phiếu phiếu ',
                'type' => 'text',
                'validate' => 'required|max:120',
            ],

            'expected_date' => [
                'title' => 'Ngày dự kiến',
                'type' => 'date',
            ],
            'date_confirm' => [
                'title' => 'Ngày xác nhận',
                'type' => 'date',
            ],

            'created_at' => [
                'title' => 'Ngày tạo',
                'type' => 'date',
            ],
        ];
    }
}
