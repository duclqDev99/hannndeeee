<?php

namespace Botble\OrderRetail\Supports;

use ArPHP\I18N\Arabic;
use Barryvdh\DomPDF\Facade\Pdf;
use Barryvdh\DomPDF\PDF as PDFHelper;
use Botble\Base\Facades\BaseHelper;
use Botble\Base\Supports\TwigCompiler;
use Botble\Base\Supports\TwigExtension;
use Botble\Ecommerce\Enums\InvoiceStatusEnum;
use Botble\Ecommerce\Facades\EcommerceHelper as EcommerceHelperFacade;
use Botble\Media\Facades\RvMedia;
use Botble\OrderRetail\Models\OrderQuotation;
use Botble\Sales\Models\Order;
use Botble\WarehouseFinishedProducts\Models\ReceiptProduct;
use Carbon\Carbon;
use Dompdf\Dompdf;
use Dompdf\Image\Cache;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\File;
use Throwable;
use Twig\Extension\DebugExtension;

class QuotationHelper
{
    public function makeMaterialPDF(OrderQuotation $invoice): PDFHelper|Dompdf
    {
        $dataForm = request()->input();

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
            ->loadHTML($content, 'UTF-8')
            ->setPaper('a4');
    }

    public function downloadInvoice(OrderQuotation $quotation): Response
    {
        return $this->makeMaterialPDF($quotation)->download(sprintf('material-%s.pdf', $quotation->order->code));
    }

    public function streamInvoice(OrderQuotation $quotation): Response
    {
        return $this->makeMaterialPDF($quotation)->stream();
    }

    public function getInvoiceTemplate(): string
    {
        $defaultPath = platform_path('plugins/order-retail/resources/templates/invoice.tpl');
        $storagePath = storage_path('app/templates/invoice.tpl');

        if ($storagePath && File::exists($storagePath)) {
            $templateHtml = BaseHelper::getFileData($storagePath, false);
        } else {
            $templateHtml = File::exists($defaultPath) ? BaseHelper::getFileData($defaultPath, false) : '';
        }

        return (string)$templateHtml;
    }

    protected function getDataForInvoiceTemplate(OrderQuotation $invoice): array
    {
        // $logo = get_ecommerce_setting('company_logo_for_invoicing') ?: (theme_option(
        //     'logo_in_invoices'
        // ) ?: theme_option('logo'));

        // $companyName = get_ecommerce_setting('company_name_for_invoicing') ?: get_ecommerce_setting('store_name');

        // $companyAddress = get_ecommerce_setting('company_address_for_invoicing');

        // if (! $companyAddress) {
        //     $companyAddress = get_ecommerce_setting('store_address') . ', ' . get_ecommerce_setting(
        //             'store_city'
        //         ) . ', ' . get_ecommerce_setting('store_state') . ', ' . get_ecommerce_setting('store_country');
        // }

        // $companyPhone = get_ecommerce_setting('company_phone_for_invoicing') ?: get_ecommerce_setting('store_phone');

        // $companyEmail = get_ecommerce_setting('company_email_for_invoicing') ?: get_ecommerce_setting('store_email');

        // $companyTaxId = get_ecommerce_setting('company_tax_id_for_invoicing') ?: get_ecommerce_setting(
        //     'store_vat_number'
        // );

        $invoice->loadMissing(['order']);
        $order = $invoice->order;

        $products = $order->products;
        $data = [
            'invoice' => $invoice->toArray(),
            'order' => $order->toArray(),
            'products' => $products->toArray(),
            'logo' => '',
            'logo_full_path' =>'',
            'site_title' => theme_option('site_title'),
            'company_logo_full_path' => '',
            'company_name' => '',
            'company_address' => '',
            'company_phone' => '',
            'company_email' => '',
            'company_tax_id' =>'',
            'total_quantity' => 0,
            'total_amount' => $invoice->amount,
            'payment_description' => null,
            'is_tax_enabled' => EcommerceHelperFacade::isTaxEnabled(),
            'settings' => [
                'using_custom_font_for_invoice' => (bool)get_ecommerce_setting('using_custom_font_for_invoice'),
                'custom_font_family' => get_ecommerce_setting('invoice_font_family', 'DejaVu Sans'),
                'font_family' => (int)get_ecommerce_setting('using_custom_font_for_invoice', 0) == 1
                    ? get_ecommerce_setting('invoice_font_family', 'DejaVu Sans')
                    : 'DejaVu Sans',
                'enable_invoice_stamp' => get_ecommerce_setting('enable_invoice_stamp'),
            ],
            'invoice_header_filter' => apply_filters('ecommerce_invoice_header', null, $invoice),
            'invoice_body_filter' => apply_filters('ecommerce_invoice_body', null, $invoice),
            'ecommerce_invoice_footer' => apply_filters('ecommerce_invoice_footer', null, $invoice),
            'invoice_payment_info_filter' => apply_filters('invoice_payment_info_filter', null, $invoice),
        ];

        return apply_filters('ecommerce_invoice_variables', $data, $invoice);
    }
}