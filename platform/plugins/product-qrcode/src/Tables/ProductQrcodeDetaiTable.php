<?php

namespace Botble\ProductQrcode\Tables;

use Botble\Base\Facades\BaseHelper;
use Botble\Base\Facades\Html;
use Botble\Base\Enums\BaseStatusEnum;
use Botble\ProductQrcode\Actions\ExportQrCodeProductAction;
use Botble\ProductQrcode\BulkActions\ExportBulkAction;
use Botble\ProductQrcode\Models\ProductQrcode;
use Botble\Table\Abstracts\TableAbstract;
use Botble\Table\Actions\DeleteAction;
use Botble\Table\Actions\EditAction;
use Botble\Table\BulkActions\DeleteBulkAction;
use Botble\Table\Columns\CheckboxColumn;
use Botble\Table\Columns\CreatedAtColumn;
use Botble\Table\Columns\IdColumn;
use Botble\Table\Columns\Column;
use Botble\Table\Columns\NameColumn;
use Botble\Table\Columns\StatusColumn;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Blade;
use Illuminate\Http\Request;

class ProductQrcodeDetaiTable extends TableAbstract
{
    public function setup(): void
    {
        $this
            ->model(ProductQrcode::class)
            ->addActions([
                EditAction::make()
                    ->route('product-qrcode.edit'),
                DeleteAction::make()
                    ->route('product-qrcode.destroy'),
            ])
            ->removeAllActions();
    }

    public function ajax(): JsonResponse
    {
        $data = $this->table
            ->eloquent($this->query())
            ->editColumn('warehouse', function (ProductQrcode $item) {
                $materialWarehouse = $item->materialWarehouse;
                if ($materialWarehouse) {
                    return $materialWarehouse->name;
                }
                return ('-');
            })
            // ->editColumn('quantity_entered', function (TimesExport $item) {
            //     return ($item->productQrcodeList ? $item->productQrcodeList->where('status', '!=', 'created')->count() : 0);
            // })
            ->editColumn('operations', function (ProductQrcode $item) {
                $routeUpdateStatus = route('product-qrcode.update-status-detail', $item->getKey());

                return view(
                    'plugins/product-qrcode::table.fields.actionsDetail',
                    compact('routeUpdateStatus', 'item')
                );
            })
            ;

        return $this->toJson($data);
    }

    public function query(): Relation|Builder|QueryBuilder
    {
        $query = $this
            ->getModel()
            ->query()
            ->where('times_product_id', request()->route('id'))
            ->with('materialWarehouse')
            ->select([
                '*'
            ])->orderByRaw("CASE WHEN status = 'cancelled' THEN 0 ELSE 1 END, status ASC");

        return $this->applyScopes($query);
    }

    public function columns(): array
    {
        return [
            Column::make('identifier')->title('Mã định danh'),
            Column::make('qr_code')->title('Mã'),
            StatusColumn::make(),
            Column::make('reason')->title('Lý do'),
            Column::make('warehouse')->title('Kho'),
            Column::make('print_times_count')->title('Số lần in Qrcode'),
            // Column::make('quantity_product')->title('Số lượng'),
            // Column::make('quantity_entered')->title('Số lượng đã nhập'),
            // Column::make('times_export')->title('Số lần xuất file'),
            // Column::make('created_by')->title('Người tạo'),
            CreatedAtColumn::make()->title('Ngày tạo'),
            Column::make('operations')->title('Hành động'),

        ];
    }

    // public function buttons(): array
    // {
    //     return $this->addCreateButton(route('product-qrcode.create'), 'product-qrcode.create');
    // }

    public function buttons(): array
    {
        return [];
        // $buttons = $this->addCreateButton(route('product-qrcode.create'), 'product-qrcode.create');
        // $buttons['product-qrcode-scan'] = [
        //     'text' => Blade::render('<span ><x-core::icon name="fa-solid fa-qrcode"/> {{ $title }} </span>', [
        //         'title' => 'Quét QR',
        //     ]),
        //     'class' => 'btn-primary product_qrcode_scan_btn',
        // ];
        // if ($this->hasPermission('product-qrcode.edit')) {
        //     $buttons['generate-invoices'] = [
        //         'text' => Blade::render('<span data-url="{{ $route }}"><x-core::icon name="ti ti-file-export"/> {{ $title }} </span>', [
        //             'title' => trans('Export code'),
        //             'route' => route('product-qrcode.export-qrcodes'),
        //         ]),
        //         'class' => 'btn-success export_qrcode',
        //     ];
        // }
        // return $buttons;
    }

    public function bulkActions(): array
    {
        return [];
    }

    public function getBulkChanges(): array
    {
        return [];
    }

    public function getFilters(): array
    {
        return $this->getBulkChanges();
    }
    public function getDefaultButtons(): array
    {
        return [
            'reload',
        ];
    }
}
