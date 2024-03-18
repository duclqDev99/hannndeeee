<?php

namespace Botble\Agent\Http\Controllers;

use Botble\Agent\Http\Requests\AgentIssueRequest;
use Botble\Agent\Models\AgentActualIssue;
use Botble\Agent\Models\AgentActualIssueDetail;
use Botble\Agent\Models\AgentIssue;
use Botble\Agent\Models\AgentProduct;
use Botble\Agent\Models\AgentWarehouse;
use Botble\Base\Facades\Assets;
use Botble\Base\Facades\PageTitle;
use Botble\Base\Http\Controllers\BaseController;
use Botble\Ecommerce\Models\Product;
use Botble\HubWarehouse\Models\HubReceipt;
use Botble\HubWarehouse\Models\HubReceiptDetail;
use Botble\ProductQrcode\Enums\QRStatusEnum;
use Botble\ProductQrcode\Models\ProductQrcode;
use Botble\WarehouseFinishedProducts\Enums\ProductIssueStatusEnum;
use Botble\WarehouseFinishedProducts\Enums\ProposalIssueStatusEnum;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Exception;
use Botble\Agent\Tables\AgentIssueTable;
use Botble\Base\Events\CreatedContentEvent;
use Botble\Base\Events\DeletedContentEvent;
use Botble\Base\Events\UpdatedContentEvent;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\Agent\Forms\AgentIssueForm;
use Botble\Agent\Supports\AgentIssueHelper;
use Botble\Base\Forms\FormBuilder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AgentIssueController extends BaseController
{
    public function index(AgentIssueTable $table)
    {
        PageTitle::setTitle(trans('plugins/agent::agent-issue.name'));

        return $table->renderTable();
    }

    public function create(FormBuilder $formBuilder)
    {
        PageTitle::setTitle(trans('plugins/agent::agent-issue.create'));

        return $formBuilder->create(AgentIssueForm::class)->renderForm();
    }

    public function store(AgentIssueRequest $request, BaseHttpResponse $response)
    {
        $agentIssue = AgentIssue::query()->create($request->input());

        event(new CreatedContentEvent(AGENT_ISSUE_MODULE_SCREEN_NAME, $request, $agentIssue));

        return $response
            ->setPreviousUrl(route('agent-issue.index'))
            ->setNextUrl(route('agent-issue.edit', $agentIssue->getKey()))
            ->setMessage(trans('core/base::notices.create_success_message'));
    }

    public function edit(AgentIssue $agentIssue, FormBuilder $formBuilder)
    {
        PageTitle::setTitle(trans('core/base::forms.edit_item', ['name' => $agentIssue->name]));

        return $formBuilder->create(AgentIssueForm::class, ['model' => $agentIssue])->renderForm();
    }

    public function update(AgentIssue $agentIssue, AgentIssueRequest $request, BaseHttpResponse $response)
    {
        $agentIssue->fill($request->input());

        $agentIssue->save();

        event(new UpdatedContentEvent(AGENT_ISSUE_MODULE_SCREEN_NAME, $request, $agentIssue));

        return $response
            ->setPreviousUrl(route('agent-issue.index'))
            ->setMessage(trans('core/base::notices.update_success_message'));
    }

    public function destroy(AgentIssue $agentIssue, Request $request, BaseHttpResponse $response)
    {
        try {
            $agentIssue->delete();

            event(new DeletedContentEvent(AGENT_ISSUE_MODULE_SCREEN_NAME, $request, $agentIssue));

            return $response->setMessage(trans('core/base::notices.delete_success_message'));
        } catch (Exception $exception) {
            return $response
                ->setError()
                ->setMessage($exception->getMessage());
        }
    }
    public function confirmView(AgentIssue $agentIssue, BaseHttpResponse $response)
    {
        abort_if(check_user_depent_of_agent($agentIssue->warehouse_issue_id), 403, 'Bạn không có quyền thao tác trên đơn này!!!!');

        Assets::addScriptsDirectly([
            'vendor/core/plugins/gallery/js/gallery-admin.js',
        ]);
        PageTitle::setTitle('Phiếu thực xuất kho');
        $productIssue = $agentIssue;
        if ($productIssue->status->toValue() != ProductIssueStatusEnum::PENDING) {
            return $response
                ->setError()
                ->setMessage('Đơn hàng xuất kho hoặc từ chối!');
        }
        return view('plugins/agent::issue.confirm', compact('productIssue'));
    }
    public function view($id)
    {
        PageTitle::setTitle('Thông tin phiếu xuất kho');

        $productIssue = $agentIssue = AgentIssue::find($id);

        abort_if(check_user_depent_of_agent($agentIssue->warehouse_issue_id), 403, 'Bạn không có quyền thao tác trên đơn này!!!!');

        $actualIssue = AgentActualIssue::where('anget_issue_id', $id)->first();

        return view('plugins/agent::issue.view', compact('productIssue', 'actualIssue'));
    }
    public function confirm(AgentIssue $agentIssue, Request $request, BaseHttpResponse $response)
    {
        abort_if(check_user_depent_of_agent($agentIssue->warehouse_issue_id), 403, 'Bạn không có quyền thao tác trên đơn này!!!!');

        $requestData = $request->input();
        DB::beginTransaction();
        try {
            $proposal = $agentIssue->proposal;
            $proposal->update(['status' => ProposalIssueStatusEnum::CONFIRM]);
            $agentIssue->update(['status' => ProductIssueStatusEnum::APPOROVED, 'date_confirm' => Carbon::now()]);
            $filteredImages = array_filter($request->input('images'));
            $imageJson = json_encode($filteredImages);
            $agentActualIssue = AgentActualIssue::query()->create([
                'anget_issue_id' => $agentIssue->id,
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
                'issuer_id' => $agentIssue->issuer_id,
                'invoice_issuer_name' => $agentIssue->invoice_issuer_name,
                'warehouse_id' => $agentIssue->warehouse_issue_id,
                'warehouse_type' => AgentWarehouse::class,
                'general_order_code' => $agentIssue->general_order_code,
                'quantity' => $proposal->quantity,
                'title' => $agentIssue->title,
                'description' => $agentIssue->description,
                'expected_date' => $agentIssue->expected_date,
                'receipt_code' => $receiptCode,
                'is_batch' => 0,
            ]);
            foreach ($requestData['qr_ids'] as $qrId) {
                $qrCode = ProductQrcode::find($qrId);
                if (!$qrCode) {
                    throw new \Exception("QR Code with ID $qrId not found.");
                }
                $product = Product::find($qrCode->productBatchDetail->product_id);
                $color = '';
                $size = '';

                $arrAttribute = $product->variationProductAttributes;
                if (count($arrAttribute) > 0) {
                    if (count($arrAttribute) === 1) {
                        $color = $arrAttribute[0]->color == null ? '' : $arrAttribute[0]->title;
                        $size = $arrAttribute[0]->color == null ? $arrAttribute[0]->title : '';
                    } else if (count($arrAttribute) === 2) {
                        $color = $arrAttribute[0]->color == null ? $arrAttribute[1]->title : $arrAttribute[0]->title;
                        $size = $arrAttribute[0]->color == null ? $arrAttribute[0]->title : $arrAttribute[1]->title;
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
                $existingActualRecord = AgentActualIssueDetail::where('actual_id', $agentActualIssue->id)
                    ->where('product_id', $product->id)
                    ->first();
                if ($existingActualRecord) {
                    $existingActualRecord->increment('quantity');
                } else {
                    AgentActualIssueDetail::query()->create([
                        'actual_id' => $agentActualIssue->id,
                        'product_id' => $product->id,
                        'product_name' => $product->name,
                        'sku' => $product->sku,
                        'price' => $product->price,
                        'quantity' => 1,
                        'color' => $color,
                        'size' => $size,
                    ]);
                }
                $agentProduct = AgentProduct::where([
                    'warehouse_id' => $agentIssue->warehouse_issue_id,
                    'product_id' => $product->id
                ])->first();

                if ($agentProduct) {
                    $agentProduct->quantity_qrcode--;
                    $agentProduct->quantity_qrcode_issue++;

                    $agentProduct->save();
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

            DB::commit();

            return $response
                ->setPreviousUrl(route('agent-issue.index'))
                ->setNextUrl(route('agent-issue.index'))
                ->setMessage(trans('Thành công'));
        } catch (Exception $e) {
            DB::rollBack();
            return $response
                ->setError()
                ->setMessage($e->getMessage());
        }
    }
    public function denied($id, Request $request, BaseHttpResponse $response)
    {
        DB::beginTransaction();
        try {
            $agentIssue = AgentIssue::find($id);
            $agentIssue->reason = $request->input('denyReason');
            $agentIssue->status = ProductIssueStatusEnum::DENIED;
            $agentIssue->invoice_confirm_name = Auth::user()->name;
            $agentIssue->save();
            $proposal = $agentIssue->proposal;
            $proposal->status = ProposalIssueStatusEnum::REFUSE;
            $proposal->save();
            $arrNoti = [
                'action' => 'từ chối',
                'permission' => "agent-proposal-issue.confirm",
                'route' => route('agent-issue.view', $proposal->id),
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

    public function getGenerateReceiptProduct(Request $request, AgentIssueHelper $issueHelper)
    {
        $data = AgentIssue::with('productIssueDetail')->find($request->id);

        if ($request->button_type === 'print') {
            return $issueHelper->streamInvoice($data);
        }
        return $issueHelper->downloadInvoice($data);
    }
}
