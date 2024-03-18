<?php

namespace Botble\Showroom\Http\Controllers;

use Auth;
use Botble\Base\Events\CreatedContentEvent;
use Botble\Base\Events\DeletedContentEvent;
use Botble\Base\Events\UpdatedContentEvent;
use Botble\Base\Facades\Assets;
use Botble\Base\Facades\PageTitle;
use Botble\Base\Forms\FormBuilder;
use Botble\Base\Http\Controllers\BaseController;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\Ecommerce\Models\Product;
use Botble\HubWarehouse\Models\HubReceipt;
use Botble\HubWarehouse\Models\HubReceiptDetail;
use Botble\ProductQrcode\Enums\QRStatusEnum;
use Botble\ProductQrcode\Models\ProductQrcode;
use Botble\Showroom\Forms\ShowroomIssueForm;
use Botble\Showroom\Http\Requests\ShowroomIssueRequest;
use Botble\Showroom\Models\ShowroomActualIssue;
use Botble\Showroom\Models\ShowroomActualIssueDetail;
use Botble\Showroom\Models\ShowroomIssue;
use Botble\Showroom\Models\ShowroomProduct;
use Botble\Showroom\Models\ShowroomWarehouse;
use Botble\Showroom\Supports\ShowroomIssueHelper;
use Botble\Showroom\Tables\ShowroomIssueTable;
use Botble\WarehouseFinishedProducts\Enums\BatchDetailStatusEnum;
use Botble\WarehouseFinishedProducts\Enums\ProductIssueStatusEnum;
use Botble\WarehouseFinishedProducts\Enums\ProposalIssueStatusEnum;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ShowroomIssueController extends BaseController
{
    public function index(ShowroomIssueTable $table)
    {
        PageTitle::setTitle(trans('plugins/showroom::showroom-issue.name'));

        return $table->renderTable();
    }

    public function create(FormBuilder $formBuilder)
    {
        PageTitle::setTitle(trans('plugins/showroom::showroom-issue.create'));

        return $formBuilder->create(ShowroomIssueForm::class)->renderForm();
    }

    public function store(ShowroomIssueRequest $request, BaseHttpResponse $response)
    {
        $showroomIssue = ShowroomIssue::query()->create($request->input());

        event(new CreatedContentEvent(SHOWROOM_ISSUE_MODULE_SCREEN_NAME, $request, $showroomIssue));

        return $response
            ->setPreviousUrl(route('showroom-issue.index'))
            ->setNextUrl(route('showroom-issue.edit', $showroomIssue->getKey()))
            ->setMessage(trans('core/base::notices.create_success_message'));
    }

    public function edit(ShowroomIssue $showroomIssue, FormBuilder $formBuilder)
    {
        PageTitle::setTitle(trans('core/base::forms.edit_item', ['name' => $showroomIssue->name]));

        abort_if(check_user_depent_of_showroom($showroomIssue->warehouse_issue_id), 403, 'Bạn không có quyền thao tác trên đơn này!!!!');

        return $formBuilder->create(ShowroomIssueForm::class, ['model' => $showroomIssue])->renderForm();
    }

    public function update(ShowroomIssue $showroomIssue, ShowroomIssueRequest $request, BaseHttpResponse $response)
    {
        abort_if(check_user_depent_of_showroom($showroomIssue->warehouse_issue_id), 403, 'Bạn không có quyền thao tác trên đơn này!!!!');

        $showroomIssue->fill($request->input());

        $showroomIssue->save();

        event(new UpdatedContentEvent(SHOWROOM_ISSUE_MODULE_SCREEN_NAME, $request, $showroomIssue));

        return $response
            ->setPreviousUrl(route('showroom-issue.index'))
            ->setMessage(trans('core/base::notices.update_success_message'));
    }

    public function destroy(ShowroomIssue $showroomIssue, Request $request, BaseHttpResponse $response)
    {
        try {
            abort_if(check_user_depent_of_showroom($showroomIssue->warehouse_issue_id), 403, 'Bạn không có quyền thao tác trên đơn này!!!!');

            $showroomIssue->delete();

            event(new DeletedContentEvent(SHOWROOM_ISSUE_MODULE_SCREEN_NAME, $request, $showroomIssue));

            return $response->setMessage(trans('core/base::notices.delete_success_message'));
        } catch (Exception $exception) {
            return $response
                ->setError()
                ->setMessage($exception->getMessage());
        }
    }

    public function confirmView(ShowroomIssue $showroomIssue, BaseHttpResponse $response)
    {
        Assets::addScriptsDirectly([
            'vendor/core/plugins/gallery/js/gallery-admin.js',
        ]);
        PageTitle::setTitle('Phiếu thực xuất kho');

        abort_if(check_user_depent_of_showroom($showroomIssue->warehouse_issue_id), 403, 'Bạn không có quyền thao tác trên đơn này!!!!');

        $productIssue = $showroomIssue;
        if ($productIssue->status != ProductIssueStatusEnum::PENDING) {
            return $response
                ->setError()
                ->setMessage(__('Đơn hàng đã duyệt hoặc từ chối!'));
        }
        return view('plugins/showroom::issue.confirm', compact('productIssue'));
    }

    public function confirm(ShowroomIssue $showroomIssue, Request $request, BaseHttpResponse $response)
    {
        abort_if(check_user_depent_of_showroom($showroomIssue->warehouse_issue_id), 403, 'Bạn không có quyền thao tác trên đơn này!!!!');

        $requestData = $request->input();
        DB::beginTransaction();
        try {
            $proposal = $showroomIssue->proposal;
            $proposal->update(['status' => ProposalIssueStatusEnum::CONFIRM]);
            $showroomIssue->update(['status' => ProductIssueStatusEnum::APPOROVED]);
            $arrNoti = [
                'action' => 'xuất',
                'permission' => "showroom-proposal-issue.confirm",
                'route' => route('showroom-issue.view', $showroomIssue->id),
                'status' => 'xuất'
            ];
            send_notify_cms_and_tele($showroomIssue, $arrNoti);

            $filteredImages = array_filter($request->input('images'));
            $imageJson = json_encode($filteredImages);
            $showroomActualIssue = ShowroomActualIssue::query()->create([
                'showroom_issue_id' => $showroomIssue->id,
                'image' => $imageJson,
            ]);
            $lastReceipt = HubReceipt::orderByDesc('id')->first();
            $receiptCode = $lastReceipt ? (int) $lastReceipt->receipt_code + 1 : 1;
            $warehouse = $proposal->warehouse;
            $hubReceipt = HubReceipt::query()->create([
                'warehouse_receipt_id' => $warehouse->id,
                'proposal_id' => $proposal->id,
                'warehouse_name' => $warehouse->name,
                'warehouse_address' => $warehouse->address,
                'issuer_id' => $showroomIssue->issuer_id,
                'invoice_issuer_name' => $showroomIssue->invoice_issuer_name,
                'warehouse_id' => $showroomIssue->warehouse_issue_id,
                'warehouse_type' => ShowroomWarehouse::class,
                'general_order_code' => $showroomIssue->general_order_code,
                'quantity' => $proposal->quantity,
                'title' => $showroomIssue->title,
                'description' => $showroomIssue->description,
                'expected_date' => $showroomIssue->expected_date,
                'receipt_code' => $receiptCode,
                'is_batch' => 0,
                'is_issue' => $showroomIssue->id
            ]);

            foreach ($requestData['qr_ids'] as $qrId) {
                $qrCode = ProductQrcode::find($qrId);
                if (!$qrCode) {
                    throw new \Exception("Lỗi không tìm thấy QR.");
                }
                $qrCode?->batchParent?->update(['status' => BatchDetailStatusEnum::CHANGE]);
                $qrCode?->batchParent?->productBatch?->decrement('quantity');
                $product = Product::find($qrCode->reference->id);
                $color = '';
                $size = '';

                foreach ($product->variationProductAttributes as $attribute) {
                    if ($attribute->color) {
                        $color = $attribute->title;
                    } else {
                        $size = $attribute->title;
                    }
                }

                HubReceiptDetail::query()->create([
                    'hub_receipt_id' => $hubReceipt->id,
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'sku' => $product->sku,
                    'price' => $product->price,
                    'quantity' => 1,
                    'color' => $color,
                    'size' => $size,
                    'is_odd' => 1,
                    'qrcode_id' => $qrId
                ]);
                ShowroomActualIssueDetail::query()->create([
                    'actual_id' => $showroomActualIssue->id,
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'sku' => $product->sku,
                    'price' => $product->price,
                    'quantity' => 1,
                    'color' => $color,
                    'size' => $size,
                    'qrcode_id' => $qrId,
                ]);

                $showroomProduct = ShowroomProduct::where([
                    'warehouse_id' => $showroomIssue->warehouse_issue_id,
                    'product_id' => $product->id
                ])->first();

                if ($showroomProduct) {
                    $showroomProduct->quantity_qrcode--;
                    $showroomProduct->quantity_qrcode_issue++;
                    $showroomProduct->save();
                } else {
                    throw new \Exception("Lỗi không có sản phẩm trong kho.");
                }

                $qrCode->status = QRStatusEnum::PENDINGSTOCK;
                $qrCode->save();
                $batchDetail = $qrCode->productBatchDetail;
                if ($batchDetail) {
                    $batch = $batchDetail->productBatch;
                    if ($batch) {
                        $batch->decrement('quantity');
                    }

                    $batchDetail->delete();
                }
            }

            $arrNoti = [
                'action' => 'tạo',
                'permission' => "hub-receipt.confirm",
                'route' => route('hub-receipt.confirm', $hubReceipt->id),
                'status' => 'tạo'
            ];
            send_notify_cms_and_tele($hubReceipt, $arrNoti);
            DB::commit();

            return $response
                ->setPreviousUrl(route('showroom-issue.index'))
                ->setNextUrl(route('showroom-issue.index'))
                ->setMessage(trans('Thành công'));
        } catch (Exception $e) {
            DB::rollBack();
            dd($e);
            return $response
                ->setError()
                ->setMessage($e->getMessage());
        }
    }

    public function view($id)
    {
        $productIssue = $showroomIssue = ShowroomIssue::find($id);

        abort_if(check_user_depent_of_showroom($showroomIssue->warehouse_issue_id), 403, 'Bạn không có quyền thao tác trên đơn này!!!!');

        $actualIssue = ShowroomActualIssue::where('showroom_issue_id', $productIssue->id)->first();
        return view('plugins/showroom::issue.view', compact('productIssue', 'actualIssue'));
    }

    public function denied($id, Request $request, BaseHttpResponse $response)
    {
        DB::beginTransaction();
        try {
            $showroomIssue = ShowroomIssue::find($id);

            abort_if(check_user_depent_of_showroom($showroomIssue->warehouse_issue_id), 403, 'Bạn không có quyền thao tác trên đơn này!!!!');

            $showroomIssue->reason = $request->input('denyReason');
            $showroomIssue->status = ProductIssueStatusEnum::DENIED;
            $showroomIssue->invoice_confirm_name = Auth::user()->name;
            $showroomIssue->save();
            $proposal = $showroomIssue->proposal;
            $proposal->status = ProposalIssueStatusEnum::REFUSE;
            $proposal->save();
            $arrNoti = [
                'action' => 'từ chối',
                'permission' => "showroom-proposal-issue.confirm",
                'route' => route('showroom-issue.view', $proposal->id),
                'status' => 'từ chối'
            ];
            send_notify_cms_and_tele($proposal, $arrNoti);
            DB::commit();
            return $response->setPreviousUrl(route('agent-issue.index'))
                ->setNextUrl(route('agent-issue.index'))->setMessage(trans('Từ chối xuất kho'));

        } catch (Exception $e) {
            DB::rollBack();
            return $response
                ->setError()
                ->setMessage($e->getMessage());
        }
    }

    public function getGenerateReceiptProduct(Request $request, ShowroomIssueHelper $issueHelper)
    {
        $data = ShowroomIssue::with('productIssueDetail')->find($request->id);

        if ($request->button_type === 'print') {
            return $issueHelper->streamInvoice($data);
        }
        return $issueHelper->downloadInvoice($data);
    }
}
