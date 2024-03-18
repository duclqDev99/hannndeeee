<?php

namespace Botble\Warehouse\Tables;

use Botble\Base\Facades\BaseHelper;
use Botble\Base\Facades\Html;
use Botble\Warehouse\Enums\MaterialProposalStatusEnum;
use Botble\Warehouse\Models\MaterialReceiptConfirm;
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

class MaterialReceiptConfirmTable extends TableAbstract
{
    public function setup(): void
    {
        $this
            ->model(MaterialReceiptConfirm::class)
            ->addActions([
                EditAction::make()
                    ->route('material-receipt-confirm.edit'),
                DeleteAction::make()
                    ->route('material-receipt-confirm.destroy'),
            ])
            ->removeAllActions();
    }

    public function ajax(): JsonResponse
    {
        $data = $this->table
            ->eloquent($this->query())
            ->editColumn('proposal_code', function (MaterialReceiptConfirm $item) {
                if($item->is_purchase_goods === 1){
                    return $item->proposal->proposal->code;//Nếu phiếu nhập được tạo từ phiếu mua thì trỏ đến phiếu mua và từ phiếu mua lấy được mã đơn đề xuất mua
                }
                return $item->proposal->proposal_code;
            })
            ->editColumn('general_order_code', function (MaterialReceiptConfirm $item) {
                if (!empty($item->general_order_code)) {
                    return $item->general_order_code;
                }
                return '--------';
            })
            ->editColumn('type_bill', function (MaterialReceiptConfirm $item) {
                return $item->is_purchase_goods == 1 ? 'Phiếu mua' : 'Phiếu nhập';
            })
            ->editColumn('operator', function (MaterialReceiptConfirm $item) {
                $actionPurchase = '';
                if ($item->status == MaterialProposalStatusEnum::PENDING && $this->hasPermission('material-receipt-confirm.confirm')) {
                    $actionPurchase = '
                    <a data-bs-toggle="tooltip" data-bs-original-title="Receipt" href="' . route('material-receipt-confirm.confirm', $item->id) . '" class="btn btn-sm btn-icon btn-success"><i class="fa-solid fa-file-import"></i><span class="sr-only">Receipt</span></a>
                    ';
                }

                $actionView = '';
                if($item->status == MaterialProposalStatusEnum::APPOROVED || Auth::user()->hasPermission('material-proposal-purchase.receipt'))
                {
                    $actionView = '
                    <a data-bs-toggle="tooltip" data-bs-original-title="View" href="' . route('material-receipt-confirm.view', $item->id) . '" class="btn btn-sm btn-icon btn-secondary"><i class="fa-regular fa-eye"></i><span class="sr-only">View</span></a>
                    ';
                }

                $actionPrintQR = '';
                if ($item->status == MaterialProposalStatusEnum::APPOROVED && $this->hasPermission('material-receipt-confirm.printQrCode')) {
                    $actionPrintQR = '
                    <button
                        data-bs-toggle="tooltip"
                        data-bs-original-title="Print QR Code"
                        onclick="printQRCode()"
                        class="btn btn-sm btn-icon btn-secondary print-qr-button"
                        style="background-color: #8ACDD7"
                        data-url = '. route('material-receipt-confirm.print-qr-code', $item->id)  .'
                    >
                            <i class="fa fa-qrcode"></i><span class="sr-only">Print QR Code</span>
                    </button>
                    ';
                }

                return '
                <div class="table-actions">
                    ' . $actionPrintQR . '
                        ' . $actionPurchase . '
                    ' . $actionView . '
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
                    'proposal_id',
                    'warehouse_name',
                    'quantity',
                    'total_amount',
                    'tax_amount',
                    'expected_date',
                    'date_confirm',
                    'is_purchase_goods',
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
                ->title('Kho nhập')
                ->width(200)
                ->searchable(true),
            Column::make('title')
                ->title('Tên phiếu')
                ->orderable(false),
            Column::make('proposal_code')
                ->title('Mã phiếu')
                ->width(100)
                ->orderable(false),
            Column::make('general_order_code')
                ->title('Mã đơn hàng')
                ->width(100)
                ->orderable(false),
            Column::make('type_bill')
                ->title('Loại phiếu')
                ->width(100)
                ->searchable(false)
                ->orderable(false),
            CreatedAtColumn::make('expected_date')
            ->dateFormat('d/m/Y')
                ->title('Ngày dự kiến'),
            CreatedAtColumn::make('date_confirm')
            ->dateFormat('d/m/Y')
                ->title('Ngày nhập'),
            CreatedAtColumn::make('created_at')
            ->dateFormat('d/m/Y')
                ->width(100)
                ->title('Ngày tạo'),
            StatusColumn::make('status')
                ->title('Trạng thái')
                ->width(100)
                ->orderable(false),
            Column::make('operator')
                ->width(50)
                ->searchable(false)
                ->orderable(false),
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

    public function getCheckboxColumnHeading(): array {
        return [];
    }
    public function getFilters(): array
    {
        return [
            'warehouse_name' => [
                'title' =>'Kho nguyên liệu',
                'type' => 'text',
                'validate' => 'required|max:120',
            ],
            'title' => [
                'title' =>'Tên phiếu',
                'type' => 'text',
                'validate' => 'required|max:120',
            ],
            'proposal_code' => [
                'title' =>'Mã đề xuất',
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
                'validate' => 'required|in:'.implode(',', MaterialProposalStatusEnum::values()),
            ],
        ];
    }
}
