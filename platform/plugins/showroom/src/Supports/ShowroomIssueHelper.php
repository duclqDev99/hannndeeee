<?php

namespace Botble\Showroom\Supports;

use ArPHP\I18N\Arabic;
use Barryvdh\DomPDF\Facade\Pdf;
use Barryvdh\DomPDF\PDF as PDFHelper;
use Botble\Base\Facades\BaseHelper;
use Botble\Base\Supports\TwigCompiler;
use Botble\Ecommerce\Enums\InvoiceStatusEnum;
use Botble\Media\Facades\RvMedia;
use Botble\Showroom\Models\ShowroomIssue;
use Carbon\Carbon;
use Dompdf\Dompdf;
use Dompdf\Image\Cache;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\File;
use Throwable;
use Twig\Extension\DebugExtension;
use Botble\WarehouseFinishedProducts\Supports\TwigExtension;

class ShowroomIssueHelper
{

    public function makeMaterialPDF(ShowroomIssue $invoice): PDFHelper|Dompdf
    {
        $dataForm = request()->input();

        $fontsPath = storage_path('fonts');
        if (!File::isDirectory($fontsPath)) {
            File::makeDirectory($fontsPath);
        }

        $content = $this->getInvoiceTemplate();
        if ($content) {
            $twigCompiler = new TwigCompiler([
                'autoescape' => false,
                'debug' => true,
            ]);

            $twigCompiler
                ->addExtension(new TwigExtension())
                ->addExtension(new DebugExtension());

            try {
                $content = $twigCompiler->compile($content, $this->getDataForInvoiceTemplate($invoice));
            } catch (\Exception $e) {
                dd($e);
            }
            if ((int) get_ecommerce_setting('invoice_support_arabic_language', 0) == 1) {
                $arabic = new Arabic();
                $p = $arabic->arIdentify($content);

                for ($i = count($p) - 1; $i >= 0; $i -= 2) {
                    try {
                        $utf8ar = $arabic->utf8Glyphs(substr($content, $p[$i - 1], $p[$i] - $p[$i - 1]));
                        $content = substr_replace($content, $utf8ar, $p[$i - 1], $p[$i] - $p[$i - 1]);
                    } catch (Throwable) {
                        continue;
                    }
                }
            }
        }

        Cache::$error_message = null;

        return Pdf::setWarnings(false)
            ->setOption('chroot', [public_path(), base_path()])
            ->setOption('tempDir', storage_path('app'))
            ->setOption('logOutputFile', storage_path('logs/pdf.log'))
            ->setOption('isRemoteEnabled', true)
            ->loadHTML($content, 'UTF-8')
            ->setPaper('a4');
    }

    public function downloadInvoice(ShowroomIssue $invoice): Response
    {
        return $this->makeMaterialPDF($invoice)->download(sprintf('material-%s.pdf', $invoice->proposal_code));
    }

    public function streamInvoice(ShowroomIssue $invoice): Response
    {
        return $this->makeMaterialPDF($invoice)->stream();
    }

    public function getInvoiceTemplate(): string
    {
        $defaultPath = platform_path('plugins/warehouse-finished-products/resources/views/product-pdf/proposal-product-issue.tpl');
        $storagePath = storage_path('app/templates/receipt-product.tpl');

        if ($storagePath && File::exists($storagePath)) {
            $templateHtml = BaseHelper::getFileData($storagePath, false);
        } else {
            $templateHtml = File::exists($defaultPath) ? BaseHelper::getFileData($defaultPath, false) : '';
        }

        return (string) $templateHtml;
    }

    protected function getDataForInvoiceTemplate(ShowroomIssue $invoice): array
    {
        $logo = get_ecommerce_setting('company_logo_for_invoicing') ?: (theme_option(
            'logo_in_invoices'
        ) ?: theme_option('logo'));

        $companyName = get_ecommerce_setting('company_name_for_invoicing') ?: get_ecommerce_setting('store_name');

        $companyAddress = get_ecommerce_setting('company_address_for_invoicing');

        if (!$companyAddress) {
            $companyAddress = get_ecommerce_setting('store_address') . ', ' . get_ecommerce_setting(
                'store_city'
            ) . ', ' . get_ecommerce_setting('store_state') . ', ' . get_ecommerce_setting('store_country');
        }

        $companyPhone = get_ecommerce_setting('company_phone_for_invoicing') ?: get_ecommerce_setting('store_phone');

        $companyEmail = get_ecommerce_setting('company_email_for_invoicing') ?: get_ecommerce_setting('store_email');

        $companyTaxId = get_ecommerce_setting('company_tax_id_for_invoicing') ?: get_ecommerce_setting(
            'store_vat_number'
        );

        $totalQtyDoc = 0;
        $totalAmount = 0;

        $dataProduct = [];
        $dataProductActual = [];

        foreach ($invoice->productIssueDetail as $key => $value){
            // dd($value);
            if($value->product->is_variation === 0){
                if(!empty($value->batch_id)){
                    $productBatch = $value->batch->productInBatch->groupBy('product_id');

                    foreach ($productBatch as $proId => $product) {
                        # code...
                        if(array_key_exists($proId,$dataProduct)){
                            $dataProduct[$proId]['quantity'] += $product->count();
                        }else{
                            $dataProduct[$proId]['quantity'] = $product->count();
                        }
                        $dataProduct[$proId]['product_name'] = $product->first()->product->name;
                        $dataProduct[$proId]['sku'] = $product->first()->product->sku;
                        $dataProduct[$proId]['attr1'] = $product->first()->product->variationProductAttributes[0]['title'];
                        $dataProduct[$proId]['attr2'] = $product->first()->product->variationProductAttributes[1]['title'];
                        $dataProduct[$proId]['index'] = $key;

                        $totalAmount += $product->count();

                        foreach ($invoice->actualIssue->actualDetail as $key => $actualDetail) {
                            # code...
                            if($actualDetail->product_id == $proId){
                                $dataProduct[$proId]['actual_qty'] = $actualDetail->quantity;
                                $totalQtyDoc += $actualDetail->quantity;
                            }
                        }
                    }
                }
                if(array_key_exists($value->product_id,$dataProduct)){
                    $dataProduct[$value->product_id]['quantity'] += $value->quantity;
                }else{
                    $dataProduct[$value->product_id]['quantity'] = $value->quantity;
                }
                $dataProduct[$value->product_id]['product_name'] = $value->product_name;
                $dataProduct[$value->product_id]['sku'] = $value->sku;
                $dataProduct[$value->product_id]['attr1'] = $value->color;
                $dataProduct[$value->product_id]['attr2'] = $value->size;
    
                $totalAmount += $value->quantity;

                foreach ($invoice->actualReceipt->autualDetail as $key => $actualDetail) {
                    # code...
                    if($actualDetail->product_id == $value->product_id){
                        $dataProduct[$value->product_id]['actual_qty'] = $actualDetail->quantity;
                        $totalQtyDoc += $actualDetail->quantity;
                    }
                }
            }else{
                if(array_key_exists($value->product_id,$dataProduct)){
                    $dataProduct[$value->product_id]['quantity'] += $value->quantity;
                }else{
                    $dataProduct[$value->product_id]['quantity'] = $value->quantity;
                }
                $dataProduct[$value->product_id]['product_name'] = $value->product_name;
                $dataProduct[$value->product_id]['sku'] = $value->sku;
                $dataProduct[$value->product_id]['attr1'] = $value->color;
                $dataProduct[$value->product_id]['attr2'] = $value->size;
                $dataProduct[$value->product_id]['index'] = $key;

                $totalAmount += $value->quantity;

                foreach ($invoice->actualReceipt->autualDetail as $key => $actualDetail) {
                    # code...
                    if($actualDetail->product_id == $value->product_id){
                        $dataProduct[$value->product_id]['actual_qty'] = $actualDetail->quantity;
                        $totalQtyDoc += $actualDetail->quantity;
                    }
                }
            }
        }
        
        $dataProductActual['total_qty_doc'] = $totalAmount;
        $dataProductActual['total_start_qty'] = $totalQtyDoc;

        $data = [
            'invoice' => $invoice->toArray(),
            'logo' => $logo,
            'logo_full_path' => RvMedia::getRealPath($logo),
            'site_title' => theme_option('site_title'),
            'company_name' => $companyName,
            'company_address' => $companyAddress,
            'company_phone' => $companyPhone,
            'company_email' => $companyEmail,
            'company_tax_id' => $companyTaxId,
            'settings' => [
                'using_custom_font_for_invoice' => (bool) get_ecommerce_setting('using_custom_font_for_invoice'),
                'custom_font_family' => get_ecommerce_setting('invoice_font_family', 'DejaVu Sans'),
                'font_family' => (int) get_ecommerce_setting('using_custom_font_for_invoice', 0) == 1
                    ? get_ecommerce_setting('invoice_font_family', 'DejaVu Sans')
                    : 'DejaVu Sans',
                'enable_invoice_stamp' => get_ecommerce_setting('enable_invoice_stamp'),
            ],
            'proposal_detail' => $dataProduct,
            'proposal_name' => request()->proposal_name,
            'receiver_name' => request()->receiver_name,
            'storekeeper_name' => request()->storekeeper_name,
            'chief_accountant_name' => request()->chief_accountant_name,
            'manager_name' => request()->manager_name,
            'today' => Carbon::now()->format('d \t\há\n\g m \nă\m Y'),
            'receipt_code' => $invoice->proposal->proposal_code,
            'total_qty_doc' => $dataProductActual['total_qty_doc'],
            'total_start_qty' => $dataProductActual['total_start_qty'],
            'warehouse_parent_name' => $invoice->warehouseIssue?->showroom?->name ?? '',
            'warehouse_parent_address' => $invoice->warehouseIssue?->showroom?->address ?? ''
        ];
        return apply_filters('ecommerce_invoice_variables', $data, $invoice);
    }

    public function getDataForPreview(): ShowroomIssue
    {
        $matial = new ShowroomIssue([
            'id' => 'INV-1',
            'customer_name' => 'Odie Miller',
            'store_name' => 'LinkedIn',
            'store_address' => '701 Norman Street Los Angeles California 90008',
            'customer_email' => 'contact@example.com',
            'customer_phone' => '+0123456789',
            'customer_address' => '14059 Triton Crossroad South Lillie, NH 84777-1634',
            'status' => InvoiceStatusEnum::PENDING,
            'created_at' => Carbon::now()->toDateTimeString(),
        ]);


        return $matial;
    }

    public function getVariables(): array
    {
        return [
            'invoice.*' => __('Invoice information from database, ex: invoice.code, invoice.amount, ...'),
            'logo_full_path' => __('The site logo with full URL'),
            'company_logo_full_path' => __('The company logo of invoice with full URL'),
            'payment_method' => __('Payment method'),
            'payment_status' => __('Payment status'),
            'payment_description' => __('Payment description'),
            "get_ecommerce_setting('key')" => __('Get the ecommerce setting from database'),
        ];
    }
}
