<?php

namespace Botble\Warehouse\Http\Controllers;

use Botble\Base\Facades\Assets;
use Botble\Warehouse\Http\Requests\WarehouseRequest;
use Botble\Warehouse\Models\QuantityMaterialStock;
use Botble\Warehouse\Models\Receipt;
use Botble\Base\Facades\PageTitle;
use Botble\Base\Http\Controllers\BaseController;
use Illuminate\Http\Request;
use Exception;
use Botble\Warehouse\Tables\ReceiptInventoryTable;
use Botble\Base\Events\CreatedContentEvent;
use Botble\Base\Events\DeletedContentEvent;
use Botble\Base\Events\UpdatedContentEvent;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\Warehouse\Facades\InvoiceHelper;
use Botble\Warehouse\Models\ReceiptInventory;
use Botble\Warehouse\Models\Mtproposal;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ReceiptInventoryController extends BaseController
{
    public function index()
    {
        PageTitle::setTitle(trans('plugins/warehouse::check_inventory.name'));


        Assets::addStylesDirectly('vendor/core/plugins/payment/css/payment-methods.css');
        $quantity_stocks = (ReceiptInventory::with('materials')->orderByDesc(DB::raw('POSITION("waiting" IN status)'))
        ->orderByDesc('created_at')->get());
        // return $quantity_stocks;
        return view('plugins/warehouse::material-proposal.list', compact('quantity_stocks'));
    }

    public function destroyReceiptByCode(int|string $code, Request $request, BaseHttpResponse $response)
    {
        try {
            $receipts = Receipt::where(['proposal_code' => $code])->get();
            foreach ($receipts as $key => $receipt) {
                # code...
                $receipt->delete();
                event(new DeletedContentEvent(RECEIPT_INVENTORY_MODULE_SCREEN_NAME, $request, $receipt));
            }

            return $response->setMessage(trans('core/base::notices.delete_success_message'));
        } catch (Exception $exception) {
            return $response
                ->setError()
                ->setMessage($exception->getMessage());
        }
    }

    public function receiptByCode(int|string $code)
    {
        Assets::addStylesDirectly(['vendor/core/plugins/ecommerce/css/ecommerce.css'])
            ->addScriptsDirectly([
                'vendor/core/plugins/ecommerce/libraries/jquery.textarea_autosize.js',
                'vendor/core/plugins/ecommerce/js/order.js',
                'vendor/core/plugins/warehouse/js/receipt.js',
            ])
            ->addScripts(['blockui', 'input-mask']);

        $receipt = ReceiptInventory::where(['proposal_code' => $code])->with('materials')->get();

        PageTitle::setTitle(trans('plugins/ecommerce::order.edit_order', ['code' => $code]));

        return view('plugins/warehouse::receipt-inventory.edit', compact('receipt'));
    }

    public function storeInventoryStock(Request $request, BaseHttpResponse $response)
    {
        $receipt = ReceiptInventory::where(['proposal_code' => $request->input('proposal_code')])->get();
        $inventoryId = get_inventory_id_by_user();

        // abort_if($receipt[0]->inventory_id !== $inventoryId, 403);
        foreach ($receipt as $key => $value) {
            $stockBy = QuantityMaterialStock::where(['inventory_id' => $value->inventory_id, 'material_id' => $value->material_id])->first();

            if (!empty($stockBy)) {
                $total = $stockBy->quantity + $value->quantity;
                QuantityMaterialStock::where('inventory_id',$value->inventory_id)
                    ->where('material_id',  $value->material_id)
                    ->update([
                            'quantity' => $total,
                        ]);

                event(new UpdatedContentEvent(RECEIPT_INVENTORY_MODULE_SCREEN_NAME, $request, $stockBy));
            } else {
                $dataInsert = [
                    'inventory_id' => $value->inventory_id,
                    'material_id' => $value->material_id,
                    'quantity' => $value->quantity,
                ];

                $stock = QuantityMaterialStock::create($dataInsert);
                event(new CreatedContentEvent(RECEIPT_INVENTORY_MODULE_SCREEN_NAME, $request, $stock));
            }

            $value->update([
                'status' => 'stocked',
            ]);//Update status for receipt
        }

        return $response
            ->setPreviousUrl(route('receipt-inventory.index'))
            ->setNextUrl(route('receipt-inventory.index'))
            ->setMessage(trans('core/base::notices.create_success_message'));
    }

    public function getGenerateInvoice(int|string $code, Request $request)
    {
        $receipt = ReceiptInventory::where(['proposal_code' => $code])->first();
        dd(InvoiceHelper::streamInvoice($receipt));
        if ($request->input('type') == 'print') {
            return InvoiceHelper::streamInvoice($receipt->invoice);
        }

        // return InvoiceHelper::downloadInvoice($order->invoice);
    }
}
