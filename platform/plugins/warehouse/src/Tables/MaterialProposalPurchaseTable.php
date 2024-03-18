<?php

namespace Botble\Warehouse\Tables;

use Botble\Base\Facades\BaseHelper;
use Botble\Base\Facades\Html;
use Botble\Warehouse\Enums\MaterialProposalStatusEnum;
use Botble\Warehouse\Models\MaterialProposalPurchase;
use Botble\Table\Abstracts\TableAbstract;
use Botble\Table\Actions\DeleteAction;
use Botble\Table\Actions\EditAction;
use Botble\Table\BulkActions\DeleteBulkAction;
use Botble\Table\Columns\CreatedAtColumn;
use Botble\Table\Columns\IdColumn;
use Botble\Table\Columns\NameColumn;
use Botble\Table\Columns\StatusColumn;
use Botble\Table\Columns\Column;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class MaterialProposalPurchaseTable extends TableAbstract
{
    public function setup(): void
    {
        $this
            ->model(MaterialProposalPurchase::class)
            ->addActions([
                EditAction::make()
                    ->route('material-proposal-purchase.edit'),
                DeleteAction::make()
                    ->route('material-proposal-purchase.destroy'),
            ])
            ->removeAllActions();
    }

    public function ajax(): JsonResponse
    {
        $data = $this->table
            ->eloquent($this->query())
            ->editColumn('invoice_confirm_name', function (MaterialProposalPurchase $item) {
                if (!empty($item->invoice_confirm_name)) {
                    return $item->invoice_confirm_name;
                }
                return '------------';
            })
            ->editColumn('date_confirm', function (MaterialProposalPurchase $item) {
                if (!empty($item->date_confirm)) {
                    return $item->date_confirm;
                }
                return '------------';
            })
            ->editColumn('general_order_code', function (MaterialProposalPurchase $item) {
                if (!empty($item->general_order_code)) {
                    return $item->general_order_code;
                }
                return '--------';
            })
            ->editColumn('status', function (MaterialProposalPurchase $item) {
                return $item->status->toHtml();
            })
            ->editColumn('operator', function (MaterialProposalPurchase $item) {

                $actionPurchase = '';
                $actionDelete = '';

                if ($item->status == MaterialProposalStatusEnum::PENDING) {
                    if (!isset($item->proposal_out_id)) {
                        $actionDelete = '
                    <li><a data-bs-toggle="tooltip" data-bs-original-title="Delete" href="' . route('material-proposal-purchase.destroy', $item->id) . '" class="dropdown-item" data-dt-single-action="" data-method="DELETE" data-confirmation-modal="true" data-confirmation-modal-title="Confirm delete" data-confirmation-modal-message="Do you really want to delete this record?" data-confirmation-modal-button="Delete" data-confirmation-modal-cancel-button="Cancel">
                        Xoá
                    </a></li>
                    ';
                    }

                    if ($this->hasPermission('material-proposal-purchase.receipt')) {
                        $actionPurchase = '
                        <li><a data-bs-original-title="Duyệt" href="' . route('material-proposal-purchase.receipt', $item->id) . '" class="dropdown-item">Duyệt đơn</a></li>
                        ';
                    }
                }

                $actionView = '';
                if ($item->status != MaterialProposalStatusEnum::PENDING || !Auth::user()->hasPermission('material-proposal-purchase.receipt')) {
                    $actionView = '
                    <li><a data-bs-original-title="View" href="' . route('material-proposal-purchase.view.code', $item->id) . '" class="dropdown-item">Xem chi tiết</a></li>
                    ';
                }

                $actionEdit = '';
                if ($item->status != MaterialProposalStatusEnum::APPOROVED && empty($item->proposal_out_id) && Auth::user()->hasPermission('material-proposal-purchase.edit')) {
                    $actionEdit = '
                    <li><a data-bs-original-title="Edit" href="' . route('material-proposal-purchase.edit', $item->id) . '" class="dropdown-item">Chỉnh sửa</a></li>
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
                'general_order_code',
                'title',
                'invoice_issuer_name',
                'invoice_confirm_name',
                'proposal_code',
                'warehouse_name',
                'quantity',
                'total_amount',
                'tax_amount',
                'expected_date',
                'date_confirm',
                'status',
                'created_at',
                'proposal_out_id',
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
                ->title('Tên phiếu')
                ->width(350)
                ->orderable(false),
            Column::make('proposal_code')
                ->title('Mã phiếu')
                ->width(100)
                ->orderable(false),
            Column::make('general_order_code')
                ->title('Mã đơn hàng')
                ->width(100)
                ->orderable(false),
            CreatedAtColumn::make('expected_date')
            ->dateFormat('d/m/Y')
                ->title('Ngày dự kiến')
                ->orderable(false),
                CreatedAtColumn::make('date_confirm')
            ->dateFormat('d/m/Y')
                ->width(100)
                ->title('Ngày duyệt'),
            CreatedAtColumn::make('created_at')
            ->dateFormat('d/m/Y')
                ->width(100)
                ->title('Ngày tạo'),
            Column::make('status')
                ->title('Trạng thái')
                ->width(100)
                ->orderable(false),
            Column::make('operator')
                ->width(20)
                ->searchable(false)
                ->orderable(false),
        ];
    }

    public function buttons(): array
    {
        return $this->addCreateButton(route('material-proposal-purchase.create'), 'material-proposal-purchase.create');
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
            'warehouse_name' => [
                'title' => 'Kho nguyên liệu',
                'type' => 'text',
                'validate' => 'required|max:120',
            ],
            'title' => [
                'title' => 'Tên phiếu',
                'type' => 'text',
                'validate' => 'required|max:120',
            ],
            'proposal_code' => [
                'title' => 'Mã đề xuất',
                'type' => 'text',
                'validate' => 'required|max:120',
            ],
            'expected_date' => [
                'title' => 'Ngày dự kiến',
                'type' => 'date',
            ],
            'date_confirm' => [
                'title' => 'Ngày duyệt',
                'type' => 'date',
            ],
            'created_at' => [
                'title' => 'Ngày tạo',
                'type' => 'date',
            ],
            'status' => [
                'title' => 'Trạng thái',
                'type' => 'select',
                'choices' => MaterialProposalStatusEnum::labels(),
                'validate' => 'required|in:' . implode(',', MaterialProposalStatusEnum::values()),
            ],
        ];
    }
}
