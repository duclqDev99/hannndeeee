<?php

namespace Botble\Warehouse\Facades;

use Botble\Warehouse\Supports\InvoiceHelper as BaseInvoiceHelper;
use Illuminate\Support\Facades\Facade;

/**
 * @method static mixed store(\Botble\Warehouse\Models\Order $order)
 * @method static \Barryvdh\DomPDF\PDF|\Dompdf\Dompdf makeInvoicePDF(\Botble\Ecommerce\Models\Invoice $invoice)
 * @method static string generateInvoice(\Botble\Ecommerce\Models\Invoice $invoice)
 * @method static \Illuminate\Http\Response downloadInvoice(\Botble\Ecommerce\Models\Invoice $invoice)
 * @method static \Illuminate\Http\Response streamInvoice(\Botble\Ecommerce\Models\Invoice $invoice)
 * @method static string getInvoiceTemplate()
 * @method static \Botble\Ecommerce\Models\Invoice getDataForPreview()
 * @method static array getVariables()
 *
 * @see \Botble\Warehouse\Supports\InvoiceHelper
 */
class InvoiceHelper extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return BaseInvoiceHelper::class;
    }
}
