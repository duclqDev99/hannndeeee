<?php

namespace Botble\Warehouse\Http\Controllers;

use Botble\Base\Events\BeforeEditContentEvent;
use Botble\Base\Events\DeletedContentEvent;
use Botble\Base\Facades\PageTitle;
use Botble\Base\Http\Controllers\BaseController;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\Ecommerce\Models\Invoice;
use Botble\Ecommerce\Models\Order;
use Botble\Ecommerce\Tables\InvoiceTable;
use Botble\Warehouse\Http\Requests\ExportBillRequest;
use Botble\Warehouse\Models\MaterialProposalPurchase;
use Botble\Warehouse\Models\MaterialReceiptConfirm;
use Exception;
use Illuminate\Http\Request;
use Botble\Warehouse\Supports\MaterialReceiptHelper;

class MaterialReceiptPdfController extends BaseController
{
    public function index(InvoiceTable $table)
    {
        $this->pageTitle(trans('plugins/ecommerce::invoice.name'));

        return $table->renderTable();
    }

    public function edit(Invoice $invoice, Request $request)
    {
        event(new BeforeEditContentEvent($request, $invoice));

        $this->pageTitle(trans('core/base::forms.edit_item', ['name' => $invoice->code]));

        return view('plugins/ecommerce::invoices.edit', compact('invoice'));
    }

    public function destroy(Invoice $invoice, Request $request, BaseHttpResponse $response)
    {
        try {
            $invoice->delete();

            event(new DeletedContentEvent(INVOICE_MODULE_SCREEN_NAME, $request, $invoice));

            return $response->setMessage(trans('core/base::notices.delete_success_message'));
        } catch (Exception $exception) {
            return $response
                ->setError()
                ->setMessage($exception->getMessage());
        }
    }



    public function getGenerateMatial(ExportBillRequest $request, MaterialReceiptHelper $MaterialHelper)
    {
        $data = MaterialReceiptConfirm::with('receiptDetail')->find($request->id);

        if ($request->button_type === 'print') {
            return $MaterialHelper->streamInvoice($data);
        }
        return $MaterialHelper->downloadInvoice($data);
    }
}
