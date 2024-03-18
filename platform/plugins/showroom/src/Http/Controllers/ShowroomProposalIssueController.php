<?php

namespace Botble\Showroom\Http\Controllers;


use Botble\Base\Facades\Assets;
use Botble\Base\Facades\BaseHelper;
use Botble\Base\Facades\PageTitle;
use Botble\Base\Forms\FormBuilder;
use Botble\Base\Http\Controllers\BaseController;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\Ecommerce\Models\Product;
use Botble\HubWarehouse\Models\Warehouse;
use Botble\Showroom\Forms\ShowroomProposalIssueForm;
use Botble\Showroom\Http\Requests\ShowroomApproveRequest;
use Botble\Showroom\Http\Resources\ShowroomProposalIssueResource;
use Botble\Showroom\Models\Showroom;
use Botble\Showroom\Models\ShowroomIssue;
use Botble\Showroom\Models\ShowroomIssueDetail;
use Botble\Showroom\Models\ShowroomProposalIssue;
use Botble\Showroom\Models\ShowroomProposalIssueDetail;
use Botble\Showroom\Models\ShowroomWarehouse;
use Botble\Showroom\Supports\ProposalIssueHelper;
use Botble\Showroom\Tables\ShowroomProposalIssueTable;
use Botble\WarehouseFinishedProducts\Enums\ProposalIssueStatusEnum;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ShowroomProposalIssueController extends BaseController
{
    public function index(ShowroomProposalIssueTable $table)
    {
        PageTitle::setTitle(trans('Danh sách đề xuất'));

        return $table->renderTable();
    }

    private function expectedDate($expected_date)
    {
        return Carbon::createFromFormat('Y-m-d', $expected_date);
    }

    public function create(FormBuilder $formBuilder)
    {
        PageTitle::setTitle(trans('Tạo đơn đề xuất'));

        return $formBuilder->create(ShowroomProposalIssueForm::class)->renderForm();
    }

    public function store(ShowroomApproveRequest $request, BaseHttpResponse $response)
    {
        $requestData = $request->input();
        $showroom = Showroom::where('id', $requestData['showroom_id'])->first();
        $hubWarehouse = $showroom->hub?->warehouseWatseInHub->first();
        if (!$hubWarehouse) {
            return $response->setError()
                ->setMessage(trans('Trong hub không có kho phế phẩm'));
        }
        $expectedDate = $this->expectedDate($requestData['expected_date']);
        $warehouse = ShowroomWarehouse::where('id', $requestData['warehouse_issue_id'])->first();
        $totalQuantity = array_sum(array_column($requestData['product'], 'quantity'));

        $lastProposalIssue = ShowroomProposalIssue::orderByDesc('id')->first();
        $proposalIssueCode = $lastProposalIssue ? (int)$lastProposalIssue->proposal_code + 1 : 1;
        $dataCreate = [
            'warehouse_issue_id' => $warehouse->id,
            'warehouse_name' => $warehouse->name,
            'warehouse_address' => $warehouse->address,
            'proposal_code' => $proposalIssueCode,
            'general_order_code' => $requestData['general_order_code'],
            'issuer_id' => Auth::user()->id,
            'invoice_issuer_name' => Auth::user()->name,
            'quantity' => $totalQuantity,
            'title' => $requestData['title'],
            'expected_date' => $expectedDate,
            'is_batch' => 0,
            'description' => $requestData['description'],
            'warehouse_id' => $hubWarehouse->id,
            'warehouse_type' => Warehouse::class,
        ];
        DB::beginTransaction();
        try {
            $showroomProposalIssue = ShowroomProposalIssue::query()->create($dataCreate);
            foreach ($requestData['product'] as $key => $prd) {
                $product = Product::find($key);
                $color = '';
                $size = '';
                foreach ($product->variationProductAttributes as $attribute) {
                    if ($attribute->color) {
                        $color = $attribute->title;
                    }
                    else
                    {
                        $size = $attribute->title;
                    }
                }
                $dataDetail = [
                    'proposal_id' => $showroomProposalIssue->id,
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'sku' => $product->sku,
                    'quantity' => $prd['quantity'],
                    'size' => $size,
                    'color' => $color
                ];
                ShowroomProposalIssueDetail::query()->create($dataDetail);
            }

            $arrNoti = [
                'action' => 'tạo',
                'permission' => "showroom-proposal-issue.approve",
                'route' => route('showroom-proposal-issue.approveView', $showroomProposalIssue),
                'status' => 'tạo'
            ];
            send_notify_cms_and_tele($showroomProposalIssue, $arrNoti);
            DB::commit();

            return $response
                ->setPreviousUrl(route('showroom-proposal-issue.index'))
                ->setNextUrl(route('showroom-proposal-issue.edit', $showroomProposalIssue->getKey()))
                ->setMessage(trans('core/base::notices.create_success_message'));
        } catch (Exception $e) {
            DB::rollBack();
            return $response
                ->setError()
                ->setMessage($e->getMessage());
        }
    }

    public function edit(ShowroomProposalIssue $showroomProposalIssue, FormBuilder $formBuilder, BaseHttpResponse $response)
    {
        abort_if(check_user_depent_of_showroom($showroomProposalIssue->warehouse_issue_id), 403, 'Bạn không có quyền thao tác trên đơn này!!!!');

        if ($showroomProposalIssue->status != ProposalIssueStatusEnum::PENDING) {
            return $response
                ->setError()
                ->setMessage(__('Đơn hàng đã duyệt hoặc từ chối!'));
        }
        PageTitle::setTitle(trans('Sửa đơn trả hàng :name', ['name' => BaseHelper::clean(get_proposal_issue_product_code($showroomProposalIssue->proposal_code))]));

        return $formBuilder->create(ShowroomProposalIssueForm::class, ['model' => $showroomProposalIssue])->renderForm();
    }

    public function update(ShowroomProposalIssue $showroomProposalIssue, Request $request, BaseHttpResponse $response)
    {
        abort_if(check_user_depent_of_showroom($showroomProposalIssue->warehouse_issue_id), 403, 'Bạn không có quyền thao tác trên đơn này!!!!');
        if($showroomProposalIssue->status != ProposalIssueStatusEnum::PENDING)
        {
            return $response
            ->setError()
            ->setMessage('Đơn không thể thay đổi');
        }
        $requestData = $request->input();
        $expectedDate = $this->expectedDate($requestData['expected_date']);
        $warehouse = ShowroomWarehouse::where('id', $requestData['warehouse_issue_id'])->first();
        $totalQuantity = array_sum(array_column($requestData['product'], 'quantity'));
        $showroom = Showroom::where('id', $requestData['showroom_id'])->first();
        $hubWarehouse = $showroom->hub?->warehouseWatseInHub->first();
        if (!$hubWarehouse) {
            return $response->setError()
                ->setMessage(trans('Trong hub không có kho'));
        }
        $dataUpdate = [
            'warehouse_issue_id' => $warehouse->id,
            'warehouse_name' => $warehouse->name,
            'warehouse_address' => $warehouse->address,
            'general_order_code' => $requestData['general_order_code'],
            'issuer_id' => Auth::user()->id,
            'invoice_issuer_name' => Auth::user()->name,
            'quantity' => $totalQuantity,
            'title' => $requestData['title'],
            'expected_date' => $expectedDate,
            'description' => $requestData['description'],
            'warehouse_id' => $hubWarehouse->id,
            'warehouse_type' => Warehouse::class,
        ];
        DB::beginTransaction();
        try {
            $showroomProposalIssue->fill($dataUpdate);
            $showroomProposalIssue->save();
            $details = $showroomProposalIssue->proposalAgentIssueDetail;
            foreach ($details as $detail) {
                $detail->delete();
            }
            foreach ($requestData['product'] as $key => $prd) {
                $product = Product::find($key);
                $color = '';
                $size = '';

                $arrAttribute = $product->variationProductAttributes;
                foreach ($product->variationProductAttributes as $attribute) {
                    if ($attribute->color) {
                        $color = $attribute->title;
                    }
                    else
                    {
                        $size = $attribute->title;
                    }
                }
                $dataDetail = [
                    'proposal_id' => $showroomProposalIssue->id,
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'sku' => $product->sku,
                    'quantity' => $prd['quantity'],
                    'size' => $size,
                    'color' => $color
                ];
                ShowroomProposalIssueDetail::query()->create($dataDetail);
            }

            $arrNoti = [
                'action' => 'chỉnh sửa',
                'permission' => "showroom-proposal-issue.approve",
                'route' => route('showroom-proposal-issue.approveView', $showroomProposalIssue),
                'status' => 'chỉnh sửa'
            ];
            send_notify_cms_and_tele($showroomProposalIssue, $arrNoti);
            DB::commit();
            return $response
                ->setPreviousUrl(route('showroom-proposal-issue.index'))
                ->setMessage(trans('core/base::notices.update_success_message'));
        } catch (Exception $exception) {
            return $response
                ->setError()
                ->setMessage($exception->getMessage());
        }
    }

    public function destroy(ShowroomProposalIssue $showroomProposalIssue, Request $request, BaseHttpResponse $response)
    {
        abort_if(check_user_depent_of_showroom($showroomProposalIssue->warehouse_issue_id), 403, 'Bạn không có quyền thao tác trên đơn này!!!!');

        DB::beginTransaction();
        try {
            $showroomProposalIssue->delete();
            $details = $showroomProposalIssue->proposalAgentIssueDetail;
            foreach ($details as $detail) {
                $detail->delete();
            }
            DB::commit();

            return $response->setMessage(trans('core/base::notices.delete_success_message'));
        } catch (Exception $exception) {
            DB::rollBack();
            return $response
                ->setError()
                ->setMessage($exception->getMessage());
        }
    }

    public function approveView(ShowroomProposalIssue $proposal, BaseHttpResponse $response)
    {
        abort_if(check_user_depent_of_showroom($proposal->warehouse_issue_id), 403, 'Bạn không có quyền thao tác trên đơn này!!!!');

        Assets::addStylesDirectly(['vendor/core/plugins/ecommerce/css/ecommerce.css'])
            ->addScriptsDirectly([
                'vendor/core/plugins/gallery/js/gallery-admin.js',
                'vendor/core/plugins/ecommerce/libraries/jquery.textarea_autosize.js',
                'vendor/core/plugins/ecommerce/js/order.js',
                'vendor/core/plugins/showroom/js/approve-showroom-issue.js',
            ])
            ->addScripts(['blockui', 'input-mask']);


        if ($proposal->status != ProposalIssueStatusEnum::PENDING) {
            return $response
                ->setError()
                ->setMessage(__('Đơn hàng đã duyệt hoặc từ chối!'));
        }
        // dd($proposal->proposalProductIssueDetail);
        PageTitle::setTitle(__('Duyệt đơn đề xuất yêu cầu trả hàng'));
        return view('plugins/showroom::proposal-issue.approve', compact('proposal'));
    }

    public function approve(ShowroomProposalIssue $proposal, ShowroomApproveRequest $request, BaseHttpResponse $response)
    {
        abort_if(check_user_depent_of_showroom($proposal->warehouse_issue_id), 403, 'Bạn không có quyền thao tác trên đơn này!!!!');

        DB::beginTransaction();
        try {

            $requestData = $request->input();
            $proposal->status = ProposalIssueStatusEnum::APPOROVED;
            $proposal->invoice_confirm_name = Auth::user()->name;
            $proposal->date_confirm = Carbon::now();
            $proposal->save();
            $expectedDate = Carbon::createFromFormat('d-m-Y', $requestData['expected_date']);
            $lastIssue = ShowroomIssue::orderByDesc('id')->first();
            $issueCode = $lastIssue ? (int)$lastIssue->issue_code + 1 : 1;
            $agentIssue = ShowroomIssue::query()->create([
                'warehouse_issue_id' => $proposal->warehouse_issue_id,
                'proposal_id' => $proposal->id,
                'warehouse_name' => $proposal->warehouse_name,
                'warehouse_address' => $proposal->warehouse_address,
                'issuer_id' => $proposal->issuer_id,
                'invoice_issuer_name' => $proposal->invoice_issuer_name,
                'warehouse_id' => $proposal->warehouse_id,
                'warehouse_type' => $proposal->warehouse_type,
                'general_order_code' => $proposal->general_order_code,
                'title' => $proposal->title,
                'description' => $proposal->receipt,
                'expected_date' => $expectedDate,
                'issue_code' => $issueCode,
            ]);
            foreach ($requestData['product'] as $key => $prd) {
                $product = Product::find($key);

                $dataDetail = [
                    'showroom_issue_id' => $agentIssue->id,
                    'product_id' => $key,
                    'product_name' => $product->name,
                    'sku' => $product->sku,
                    'color' => $prd['color'],
                    'size' => $prd['size'],
                    'price' => $product->price,
                    'quantity' => (int)$prd['quantity'],
                ];
                ShowroomIssueDetail::query()->create($dataDetail);
            }
            // $arrNoti = [
            //     'action' => 'duyệt',
            //     'permission' => "agent-proposal-issue.index",
            //     'route' => route('agent-proposal-issue.view', $proposal),
            //     'status' => 'duyệt'
            // ];
            // send_notify_cms_and_tele($proposal, $arrNoti);
            // $arrNoti = [
            //     'action' => 'tạo',
            //     'permission' => "agent-issue.confirm",
            //     'route' => route('agent-issue.confirmView', $agentIssue),
            //     'status' => 'tạo'
            // ];
            // send_notify_cms_and_tele($agentIssue, $arrNoti);
            DB::commit();
            return $response
                ->setPreviousUrl(route('showroom-proposal-issue.index'))
                ->setNextUrl(route('showroom-proposal-issue.index'))
                ->setMessage(trans('Đã duyệt đơn'));
        } catch (Exception $e) {
            DB::rollBack();
            return $response->setError()->setMessage(trans('Lỗi ' . $e->getMessage()));
        }
    }

    public function view($id)
    {
        $proposal = ShowroomProposalIssue::find($id);

        abort_if(check_user_depent_of_showroom($proposal->warehouse_issue_id), 403, 'Bạn không có quyền thao tác trên đơn này!!!!');

        $receipt = ShowroomIssue::where('proposal_id',$id)->with('productIssueDetail')->first();
        return view('plugins/showroom::proposal-issue.view', compact('proposal', 'receipt'));
    }

    public function proposal(int|string $id)
    {
        $proposal = ShowroomProposalIssue::where('id', $id)
            ->first();
        $products = $proposal->proposalAgentIssueDetail->map(function ($item) use ($proposal) {
            $item->warehouse_id = $proposal->warehouse_issue_id;
            return $item;
        });
        $productsResource = ShowroomProposalIssueResource::collection($products);
        $showroom = $proposal->warehouseIssue->showroom->id;
        $warehouseIssue = $proposal->warehouse_issue_id;
        return response()->json(['data' => $productsResource, 'showroom' => $showroom, 'warehouseIssue' => $warehouseIssue], 200);
    }

    public function denied($id, Request $request, BaseHttpResponse $response)
    {
        $proposal = ShowroomProposalIssue::find($id);
        $proposal->status = ProposalIssueStatusEnum::DENIED;
        $proposal->invoice_confirm_name = Auth::user()->name;
        $proposal->reason_cancel = $request->input('denyReason');
        $proposal->save();
        $arrNoti = [
            'action' => 'từ chối',
            'permission' => "showroom-proposal-issue.create",
            'route' => route('showroom-proposal-issue.view', $proposal->id),
            'status' => 'từ chối'
        ];
        send_notify_cms_and_tele($proposal, $arrNoti);
        return $response->setPreviousUrl(route('showroom-proposal-issue.index'))
            ->setNextUrl(route('showroom-proposal-issue.index'))->setMessage(trans('Từ chối duyệt đơn'));
    }

    public function getGenerateReceiptProduct(Request $request, ProposalIssueHelper $issueHelper)
    {
        $data = ShowroomProposalIssue::with('proposalAgentIssueDetail')->find($request->id);

        if ($request->button_type === 'print') {
            return $issueHelper->streamInvoice($data);
        }
        return $issueHelper->downloadInvoice($data);
    }
}
