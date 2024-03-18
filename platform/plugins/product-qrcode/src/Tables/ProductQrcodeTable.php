<?php

namespace Botble\ProductQrcode\Tables;

use Botble\Base\Facades\BaseHelper;
use Botble\Base\Facades\Html;
use Botble\Base\Enums\BaseStatusEnum;
use Botble\ProductQrcode\Actions\ExportQrCodeProductAction;
use Botble\ProductQrcode\BulkActions\ExportBulkAction;
use Botble\ProductQrcode\Models\TimesExport;
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

class ProductQrcodeTable extends TableAbstract
{

    // protected string $exportClass = ExportQrCodeProductAction::class;
    // public $hasOperations=false;
    public function setup(): void
    {
        $this
            ->model(TimesExport::class)
            ->addActions([])->removeAllActions()
            ;
    }

    public function ajax(): JsonResponse
    {
        $data = $this->table
            ->eloquent($this->query())
            ->editColumn('name_product', function (TimesExport $item) {
                return ($item->products?->name);
            })
            ->editColumn('created_by', function (TimesExport $item) {
                return ($item->createdBy->first_name . ' ' . $item->createdBy->last_name);
            })
            ->editColumn('operations', function (TimesExport $item) {
                $qrcode = $item;
                $export = route('product-qrcode.export-temporary', 'id=' . $item->getKey());
                $printTimesCountRoute = route('product-qrcode.print-times-count', 'id=' . $item->getKey());
                $getRoute = route('product-qrcode.get-qrcode-by-id', 'id=' . $item->getKey());
                $routeDetail = route('product-qrcode.detail', $item->getKey());
                if (is_in_admin(true) && !$this->hasPermission('product-qrcode.export-temporary')) {
                    $export = null;
                }
                $checkSuperPrint = false;
                if(!$this->hasPermission('product-qrcode.in-super')){
                    $checkSuperPrint = $qrcode->quantity_product == $qrcode->quantity_print_qrcode ? true : false;
                }
                return view(
                    'plugins/product-qrcode::table.fields.actions',
                    compact('export', 'qrcode', 'getRoute', 'routeDetail', 'printTimesCountRoute','checkSuperPrint')
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
            ->withCount(['productQrcodeList as quantity_print_qrcode' => function($q){
                $q->where('print_times_count', '!=', 0)->where('status', '!=', 'cancelled');
            },
            'productQrcodeList as quantity_entered' => function($q){
                $q->whereNotIn('status',['created','cancelled']);
            },
            'productQrcodeList as quantity_cancell' => function($q){
                $q->where('status', '=', 'cancelled');
            },
            'QrcodeTemporary as qrcode_temporary'
            ])
            ->orderBy('created_at','desc');

        return $this->applyScopes($query);
    }

    public function columns(): array
    {
        return [
            CheckboxColumn::make(),
            Column::make('title')->title('Tiêu đề'),
            Column::make('name_product')->title('Tên')->orderable(false)->searchable(false),
            Column::make('description')->title('Mô tả'),
            Column::make('variation_attributes')->title('Thuộc tính'),
            Column::make('quantity_product')->title('Số lượng'),
            Column::make('quantity_print_qrcode')->title('Số lượng in')->orderable(false)->searchable(false),
            Column::make('quantity_entered')->title('Số lượng đã nhập')->orderable(false)->searchable(false),
            Column::make('quantity_cancell')->title('Số lượng hủy')->orderable(false)->searchable(false),
            Column::make('times_export')->title('Số lần xuất file')->orderable(false)->searchable(false),
            Column::make('created_by')->title('Người tạo'),
            CreatedAtColumn::make()->title('Ngày tạo'),
            // Column::make('operations')->title('Hành động')->orderable(false)->searchable(false),
        ];
    }

    // public function buttons(): array
    // {
    //     return $this->addCreateButton(route('product-qrcode.create'), 'product-qrcode.create');
    // }

    public function buttons(): array
    {
        $buttons = $this->addCreateButton(route('product-qrcode.create'), 'product-qrcode.create');
        $buttons['product-qrcode-scan'] = [
            'text' => Blade::render('<span ><x-core::icon name="fa-solid fa-qrcode"/> {{ $title }} </span>', [
                'title' => 'Quét QR',
            ]),
            'class' => 'btn-primary show_scan_info_btn',
        ];
        if ($this->hasPermission('product-qrcode.export-qrcodes')) {
            $buttons['generate-invoices'] = [
                'text' => Blade::render('<span data-url="{{ $route }}"><x-core::icon name="ti ti-file-export"/> {{ $title }} </span>', [
                    'title' => trans('Export code'),
                    'route' => route('product-qrcode.export-qrcodes'),
                ]),
                'class' => 'btn-success export_qrcode',
            ];
        }
        return $buttons;
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
