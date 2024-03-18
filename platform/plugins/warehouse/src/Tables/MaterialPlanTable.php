<?php

namespace Botble\Warehouse\Tables;

use Botble\ACL\Models\User;
use Botble\Base\Facades\BaseHelper;
use Botble\Base\Facades\Html;
use Botble\Warehouse\Enums\BaseStatusEnum;
use Botble\Table\Columns\Column;
use Botble\Warehouse\Actions\DetailAction;
use Botble\Warehouse\Enums\MaterialProposalStatusEnum;
use Botble\Warehouse\Enums\ProposalGoodIssueStatusEnum;
use Botble\Warehouse\Models\MaterialOut;
use Botble\Warehouse\Models\MaterialPlan;
use Botble\Table\Abstracts\TableAbstract;
use Botble\Table\Actions\DeleteAction;
use Botble\Table\Actions\EditAction;
use Botble\Table\BulkActions\DeleteBulkAction;
use Botble\Table\Columns\CreatedAtColumn;
use Botble\Table\Columns\IdColumn;
use Botble\Table\Columns\NameColumn;
use Botble\Table\Columns\StatusColumn;
use Botble\Warehouse\Models\Supplier;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class MaterialPlanTable extends TableAbstract
{

    public function setup(): void
    {
        $this
            ->model(MaterialOut::class)
            ->addActions([
                EditAction::make()
                    ->route('proposal-goods-issue.edit'),
                DetailAction::make()
                    ->route('proposal-goods-issue.edit'),
                DeleteAction::make()
                    ->route('proposal-goods-issue.destroy'),
            ])
            ->removeAllActions();
        ;

    }

    public function ajax(): JsonResponse
    {
        $data = $this->table
            ->eloquent($this->query())
            ->editColumn('invoice_confirm_name', function (MaterialOut $item) {
                if (!empty($item->invoice_confirm_name)) {
                    return $item->invoice_confirm_name;
                }
                return '------------';
            })

            ->editColumn('warehouse_type', function (MaterialOut $item) {

                if ($item->warehouse_type) {
                    return $item->warehouse?->name;
                } else {
                    return '-----';
                }
            })
            ->editColumn('total_amount', function (MaterialOut $item) {
                return format_price($item->total_amount);
            })
            ->editColumn('is_processing_house', function (MaterialOut $item) {
                if ($item->is_processing_house == 1) {
                    return 'Nhà gia công';
                }
                return 'Kho';
            })
            ->editColumn('operator', function (MaterialOut $item) {

                $actionPurchase = '';
                $actionDelete = '';

                if ($item->status->toValue() == ProposalGoodIssueStatusEnum::PENDING) {
                    if (
                        $item->issuer_id == Auth::user()->id && !isset($item->proposal_out_id)
                    ) {
                        if ($item->proposalPurchase?->status->toValue() == MaterialProposalStatusEnum::PENDING || $item->is_processing_house == 1) {
                            $actionDelete = '
                                <li> <a data-bs-toggle="tooltip" class="dropdown-item" data-bs-original-title="Delete"
                                href="' . route('proposal-goods-issue.destroy', $item->id) . '"
                                class="btn btn-sm btn-icon btn-danger" data-dt-single-action="" data-method="DELETE" data-confirmation-modal="true"
                                data-confirmation-modal-title="Xác nhận xóa" data-confirmation-modal-message="Khi xóa không thể khôi phục lại đơn hàng?"
                                data-confirmation-modal-button="Xóa đơn" data-confirmation-modal-cancel-button="Đóng"> Xóa đơn
                                </a> </li>
                            ';
                        }

                    }
                    if ($this->hasPermission('proposal-goods-issue.receipt')) {
                        $actionPurchase = '
                        <li><a class="dropdown-item" data-bs-toggle="tooltip" data-bs-original-title="Receipt" href="' . route('proposal-goods-issue.list.out', $item->id) . '"
                         class="btn btn-sm btn-icon btn-success"> Duyệt đơn</a>
                         </li>';
                    }
                }
                if ($actionPurchase == '') {
                    $actionView = '
                    <li><a class="dropdown-item" data-bs-toggle="tooltip" data-bs-original-title="View" href="' . route('proposal-goods-issue.view.code', $item->id) . '"
                    class="btn btn-sm btn-icon btn-secondary">Xem chi tiết</a></li>
                    ';
                } else {
                    $actionView = "";
                }
                $editView = '';
                if (
                    $item->status->toValue() != ProposalGoodIssueStatusEnum::APPROVED &&
                    $item->status->toValue() != ProposalGoodIssueStatusEnum::CONFIRM &&
                    $item->issuer_id == Auth::user()->id
                ) {
                    if ($item->proposalPurchase?->status->toValue() == MaterialProposalStatusEnum::PENDING || $item->is_processing_house == 1) {
                        $editView = '<li><a class="dropdown-item" data-bs-toggle="tooltip" data-bs-original-title="Edit" href="' . route('proposal-goods-issue.edit', $item->id) . '"
                    class="btn btn-sm btn-icon btn-secondary">Cập nhật đơn</a></li>';
                    }
                }
                return '
                <div class="btn-group">
                    <button type="button" class="btn btn-secondary" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fa fa-plus" aria-hidden="true"></i>
                    </button>
                    <ul class="dropdown-menu">
                    ' . $actionPurchase . '
                    ' . $actionView . '
                    ' . $editView . '
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
                'warehouse_type',
                'warehouse_id',
                'warehouse_out_id',
                'proposal_code',
                'warehouse_name',
                'quantity',
                'total_amount',
                'expected_date',
                'date_confirm',
                'status',
                'created_at',
                'is_processing_house',
                'general_order_code',
                'issuer_id'
            ])
            ->orderByDesc(DB::raw('POSITION("pending" IN status)'))->orderByDesc(DB::raw('CASE WHEN status = "denied" THEN 1 ELSE 0 END'))->orderByDesc('updated_at');
        if (!$this->hasPermission('proposal-goods-issue.receipt')) {
            $query->where('issuer_id', Auth::user()->id);
        }
        return $this->applyScopes($query);
    }

    public function columns(): array
    {
        return [
            // Column::make('is_processing_house')
            //     ->title('Kiểu xuất')
            //     ->width(150),
            Column::make('warehouse_name')
                ->title('Kho nguyên liệu')
                ->width(150),
            Column::make('warehouse_type')
                ->title('Xuất đến'),
            Column::make('title')
                ->title('Tên phiếu')
                ->orderable(false),
            Column::make('general_order_code')
                ->title('Mã đơn hàng')
                ->orderable(false),
            Column::make('proposal_code')
                ->title('Mã phiếu')
                ->orderable(false),

            // Column::make('invoice_issuer_name')->title('Người đề xuất')
            //     ->width(150)
            //     ->orderable(false),
            // Column::make('invoice_confirm_name')->title('Người duyệt')
            //     ->width(150)
            //     ->orderable(false),
            // Column::make('quantity')
            //     ->title('Số lượng')
            //     ->orderable(false),
            CreatedAtColumn::make('expected_date')
                ->dateFormat('d/m/y')
                ->title('Ngày dự kiến')
                ->orderable(false),
            CreatedAtColumn::make('date_confirm')
                ->dateFormat('d/m/y')
                ->title('Ngày duyệt')
                ->orderable(false),

            StatusColumn::make('status')
                ->title('Trạng thái')
                ->width(50)
                ->orderable(false),
            Column::make('operator')
                ->width(50)->title('Hành động')
                ->orderable(false)
                ->searchable(false),
        ];
    }

    public function buttons(): array
    {
        return $this->addCreateButton(route('proposal-goods-issue.create'), 'proposal-goods-issue.create');
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
            'name' => [
                'title' => trans('core/base::tables.name'),
                'type' => 'text',
                'validate' => 'required|max:120',
            ],

            'warehouse_name' => [
                'title' => 'Kho xuất',
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
            'status' => [
                'title' => 'Trạng thái',
                'type' => 'select',
                'choices' => MaterialProposalStatusEnum::labels(),
                'validate' => 'required|in:' . implode(',', MaterialProposalStatusEnum::values()),
            ],


        ];
    }
}
