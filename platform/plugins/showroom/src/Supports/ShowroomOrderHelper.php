<?php

namespace Botble\Showroom\Supports;

use ArPHP\I18N\Arabic;
use Barryvdh\DomPDF\Facade\Pdf;
use Barryvdh\DomPDF\PDF as PDFHelper;
use Botble\Base\Facades\BaseHelper;
use Botble\Base\Supports\TwigCompiler;
use Botble\Department\Models\DepartmentUser;
use Botble\WarehouseFinishedProducts\Supports\TwigExtension;
use Botble\Ecommerce\Enums\InvoiceStatusEnum;
use Botble\Ecommerce\Enums\OrderStatusEnum;
use Botble\Media\Facades\RvMedia;
use Botble\Showroom\Models\ShowroomOrderViewEc;
use Carbon\Carbon;
use Dompdf\Dompdf;
use Dompdf\Image\Cache;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\File;
use Throwable;
use Twig\Extension\DebugExtension;

class ShowroomOrderHelper
{
    public function makeMaterialPDF(ShowroomOrderViewEc $invoice): PDFHelper|Dompdf
    {
        $fontsPath = storage_path('fonts');

        if (! File::isDirectory($fontsPath)) {
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

            $content = $twigCompiler->compile($content, $this->getDataForInvoiceTemplate($invoice));
            if ((int)get_ecommerce_setting('invoice_support_arabic_language', 0) == 1) {
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
            ->loadHTML($content, 'UTF-8')// Đặt chiều rộng giấy là 80mm
            // ->setOption(['dpi' => 105])
            // ->setPaper('a7');
            ->setPaper([0, 0, 226, 526])
            ; // Đặt khoảng trống phải; //1mm ~~ 3.78px
    }

    public function downloadInvoice(ShowroomOrderViewEc $invoice): Response
    {
        return $this->makeMaterialPDF($invoice)->download(sprintf('material-%s.pdf', $invoice->proposal_code));
    }

    public function streamInvoice(ShowroomOrderViewEc $invoice): Response
    {
        return $this->makeMaterialPDF($invoice)->stream();
    }

    public function getInvoiceTemplate(): string
    {
        $defaultPath = platform_path('plugins/showroom/resources/views/orders/pdf/payment-order.tpl');
        $storagePath = storage_path('app/templates/payment-order.tpl');
        
        if ($storagePath && File::exists($storagePath)) {
            $templateHtml = BaseHelper::getFileData($storagePath, false);
        } else {
            $templateHtml = File::exists($defaultPath) ? BaseHelper::getFileData($defaultPath, false) : '';
        }
        
        return (string)$templateHtml;
    }

    protected function getDataForInvoiceTemplate(ShowroomOrderViewEc $invoice): array
    {
        $logo = get_ecommerce_setting('company_logo_for_invoicing') ?: (theme_option(
            'logo_in_invoices'
        ) ?: theme_option('logo'));

        $companyName = get_ecommerce_setting('company_name_for_invoicing') ?: get_ecommerce_setting('store_name');

        $companyAddress = get_ecommerce_setting('company_address_for_invoicing');

        if (! $companyAddress) {
            $companyAddress = get_ecommerce_setting('store_address') . ', ' . get_ecommerce_setting(
                    'store_city'
                ) . ', ' . get_ecommerce_setting('store_state') . ', ' . get_ecommerce_setting('store_country');
        }

        $companyPhone = get_ecommerce_setting('company_phone_for_invoicing') ?: get_ecommerce_setting('store_phone');

        $companyEmail = get_ecommerce_setting('company_email_for_invoicing') ?: get_ecommerce_setting('store_email');

        $companyTaxId = get_ecommerce_setting('company_tax_id_for_invoicing') ?: get_ecommerce_setting(
            'store_vat_number'
        );

        $nameDepartment = get_name_department_by_user();

        //
        $dataPrice = [];
        $totalAmount = 0;
        $totalQtyDoc = 0;

        $dataProduct = [];
        $dataProductActual = [];

        foreach ($invoice->products as $key => $value) {
            $strtolower = strtolower($value->product_name);
            $capitalizedString = mb_convert_case($strtolower, MB_CASE_TITLE, "UTF-8");

            $dataProduct[$value->product_id]['quantity'] = $value->qty;
            $dataProduct[$value->product_id]['product_name'] = $capitalizedString;
            $dataProduct[$value->product_id]['attr'] = $value->options['attributes'];
            $dataProduct[$value->product_id]['sku'] = $value->options['sku'];
            $dataProduct[$value->product_id]['index'] = $key;
            $dataProduct[$value->product_id]['price'] = $value->price;
            $dataProduct[$value->product_id]['price_num'] = number_format($value->price);
            $dataProduct[$value->product_id]['sub_total'] = ($value->price) * $value->qty;
            $dataProduct[$value->product_id]['sub_total_num'] = number_format(($value->price) * $value->qty);

            $totalQtyDoc += $value->qty;
            $totalAmount += intval(($value->price + $value->tax_amount) * $value->qty);
        }

        $dataProductActual['total_qty_doc'] = $totalQtyDoc;
        $dataProductActual['total_amount'] = ($totalAmount);

        $dataProductActual['status'] = $invoice->status == OrderStatusEnum::COMPLETED ? 'Đã thanh toán' : 'Chưa thanh toán';

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
            'proposal_print_name' => \Auth::user()->name,
            'proposal_print_phone' => \Auth::user()->phone,
            'settings' => [
                'using_custom_font_for_invoice' => (bool)get_ecommerce_setting('using_custom_font_for_invoice'),
                'custom_font_family' => get_ecommerce_setting('invoice_font_family', 'DejaVu Sans'),
                'font_family' => (int)get_ecommerce_setting('using_custom_font_for_invoice', 0) == 1
                    ? get_ecommerce_setting('invoice_font_family', 'DejaVu Sans')
                    : 'DejaVu Sans',
                'enable_invoice_stamp' => get_ecommerce_setting('enable_invoice_stamp'),
            ],
            'proposal_detail' => $dataProduct,
            'total_amount_number' => number_format($totalAmount),
            'total_amount_string' => ucfirst(convert_number_to_words(intval($invoice['amount']))),
            'proposal_name' => request()->proposal_name,
            'today' => Carbon::now()->format('d/m/Y'),
            'total_qty_doc' => $dataProductActual['total_qty_doc'],
            'total_amount' => number_format($dataProductActual['total_amount']),
            'name_department' => $invoice->where->name,
            'phone_department' => $invoice->where->phone_number,
            'name_customer' => $invoice->user?->name ?? '0',
            'status_order' => $dataProductActual['status'],
            'font_name' => get_ecommerce_setting('invoice_font_family') ?? 'Roboto',
            'showroom_address' => $invoice->location?->address,
            'showroom_phone' => $invoice->location?->showroom_phone,
        ];

        return apply_filters('ecommerce_invoice_variables', $data, $invoice);
    }

    public function getDataForPreview(): ShowroomOrderViewEc
    {
        $matial = new ShowroomOrderViewEc([
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
