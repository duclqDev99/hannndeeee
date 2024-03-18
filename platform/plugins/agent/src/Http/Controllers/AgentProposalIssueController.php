<?php

namespace Botble\Agent\Http\Controllers;

use Botble\Agent\Http\Requests\AngentProposalIssueRequest;
use Botble\Agent\Http\Resources\AgentProposalIssueResource;
use Botble\Agent\Models\AgentIssue;
use Botble\Agent\Models\AgentIssueDetail;
use Botble\Agent\Models\AgentWarehouse;
use Botble\Agent\Models\AngentProposalIssue;
use Botble\Agent\Models\AngentProposalIssueDetail;
use Botble\Base\Facades\Assets;
use Botble\Base\Facades\PageTitle;
use Botble\Base\Http\Controllers\BaseController;
use Botble\Ecommerce\Models\Product;
use Botble\HubWarehouse\Models\Warehouse;
use Botble\WarehouseFinishedProducts\Enums\ProposalIssueStatusEnum;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Exception;
use Botble\Agent\Tables\AgentProposalIssueTable;
use Botble\Base\Events\CreatedContentEvent;
use Botble\Base\Events\DeletedContentEvent;
use Botble\Base\Events\UpdatedContentEvent;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\Agent\Forms\AngentProposalIssueForm;
use Botble\Agent\Models\Agent;
use Botble\Agent\Supports\ProposalIssueHelper;
use Botble\Base\Forms\FormBuilder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Throwable;

class AgentProposalIssueController extends BaseController
{
    public function index(AgentProposalIssueTable $table)
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

        return $formBuilder->create(AngentProposalIssueForm::class)->renderForm();
    }

    public function store(AngentProposalIssueRequest $request, BaseHttpResponse $response)
    {
        $requestData = $request->input();
        $expectedDate = $this->expectedDate($requestData['expected_date']);
        $warehouse = AgentWarehouse::where('id', $requestData['warehouse_issue_id'])->first();
        $totalQuantity = array_sum(array_column($requestData['product'], 'quantity'));
        $agent = Agent::find($requestData['agent_id']);
        $hubWarehouse = $agent->hub?->warehouseInHub->first();
        if (!$hubWarehouse) {
            return $response->setError()
                ->setMessage(trans('Trong hub không có kho'));
        }
        $lastProposalIssue = AngentProposalIssue::orderByDesc('id')->first();
        $proposalIssueCode = $lastProposalIssue ? (int) $lastProposalIssue->proposal_code + 1 : 1;
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
            $agentProposalIssue = AngentProposalIssue::query()->create($dataCreate);
            foreach ($requestData['product'] as $key => $prd) {
                $product = Product::find($key);
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
                $dataDetail = [
                    'proposal_id' => $agentProposalIssue->id,
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'sku' => $product->sku,
                    'quantity' => $prd['quantity'],
                    'size' => $size,
                    'color' => $color
                ];
                AngentProposalIssueDetail::query()->create($dataDetail);
            }

            $arrNoti = [
                'action' => 'tạo',
                'permission' => "agent-proposal-issue.approve",
                'route' => route('agent-proposal-issue.approveAgentProposal', $agentProposalIssue),
                'status' => 'tạo'
            ];
            send_notify_cms_and_tele($agentProposalIssue, $arrNoti);
            DB::commit();
            event(new CreatedContentEvent(ANGENT_PROPOSAL_ISSUE_MODULE_SCREEN_NAME, $request, $agentProposalIssue));

            return $response
                ->setPreviousUrl(route('agent-proposal-issue.index'))
                ->setNextUrl(route('agent-proposal-issue.edit', $agentProposalIssue->getKey()))
                ->setMessage(trans('core/base::notices.create_success_message'));
        } catch (Exception $e) {
            DB::rollBack();
            return $response
                ->setError()
                ->setMessage($e->getMessage());
        }


    }

    public function edit(AngentProposalIssue $agentProposalIssue, FormBuilder $formBuilder, BaseHttpResponse $response)
    {
        abort_if(check_user_depent_of_agent($agentProposalIssue->warehouse_issue_id), 403, 'Bạn không có quyền thao tác trên đơn này!!!!');

        if ($agentProposalIssue->status != ProposalIssueStatusEnum::PENDING) {
            return $response
                ->setError()
                ->setMessage(__('Không có quyền truy cập! Đơn hàng đã được duyệt hoặc từ chối'));
        }
        PageTitle::setTitle(trans('Sửa đơn đề xuất :name', ['name' => $agentProposalIssue->name]));

        return $formBuilder->create(AngentProposalIssueForm::class, ['model' => $agentProposalIssue])->renderForm();
    }

    public function update(AngentProposalIssue $agentProposalIssue, AngentProposalIssueRequest $request, BaseHttpResponse $response)
    {
        abort_if(check_user_depent_of_agent($agentProposalIssue->warehouse_issue_id), 403, 'Bạn không có quyền thao tác trên đơn này!!!!');
        if($agentProposalIssue->status != ProposalIssueStatusEnum::PENDING)
        {
            return $response
            ->setError()
            ->setMessage('Đơn không thể thay đổi');
        }
        $requestData = ($request->input());
        $expectedDate = $this->expectedDate($requestData['expected_date']);
        $warehouse = AgentWarehouse::where('id', $requestData['warehouse_issue_id'])->first();
        $totalQuantity = array_sum(array_column($requestData['product'], 'quantity'));
        $agent = Agent::find($requestData['agent_id']);
        $hubWarehouse = $agent->hub?->warehouseInHub->first();
        if (!$hubWarehouse) {
            return $response->setError()
                ->setMessage(trans('Trong hub không có kho'));
        }
        $dataUpdate = [
            'warehouse_issue_id' => $warehouse->id,
            'warehouse_name' => $warehouse->name,
            'warehouse_address' => $warehouse->address,
            'general_order_code' => $requestData['general_order_code'],
            'quantity' => $totalQuantity,
            'title' => $requestData['title'],
            'expected_date' => $expectedDate,
            'description' => $requestData['description'],
            'warehouse_id' => $hubWarehouse->id,
            'warehouse_type' => Warehouse::class,
        ];
        DB::beginTransaction();
        try {

            $agentProposalIssue->fill($dataUpdate);
            $agentProposalIssue->save();
            $details = $agentProposalIssue->proposalAgentIssueDetail;
            foreach ($details as $detail) {
                $detail->delete();
            }
            foreach ($requestData['product'] as $key => $prd) {
                $product = Product::find($key);
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
                $dataDetail = [
                    'proposal_id' => $agentProposalIssue->id,
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'sku' => $product->sku,
                    'quantity' => $prd['quantity'],
                    'size' => $size,
                    'color' => $color
                ];
                AngentProposalIssueDetail::query()->create($dataDetail);
            }
            event(new UpdatedContentEvent(ANGENT_PROPOSAL_ISSUE_MODULE_SCREEN_NAME, $request, $agentProposalIssue));
            $arrNoti = [
                'action' => 'chỉnh sửa',
                'permission' => "agent-proposal-issue.approve",
                'route' => route('agent-proposal-issue.approveAgentProposal', $agentProposalIssue),
                'status' => 'chỉnh sửa'
            ];
            send_notify_cms_and_tele($agentProposalIssue, $arrNoti);
            DB::commit();
            return $response
                ->setPreviousUrl(route('agent-proposal-issue.index'))
                ->setMessage(trans('core/base::notices.update_success_message'));
        } catch (Exception $e) {
            DB::rollBack();
            return $response
                ->setError()
                ->setMessage($e->getMessage());
        }



    }

    public function destroy(AngentProposalIssue $agentProposalIssue, Request $request, BaseHttpResponse $response)
    {
        try {
            abort_if(check_user_depent_of_agent($agentProposalIssue->warehouse_issue_id), 403, 'Bạn không có quyền thao tác trên đơn này!!!!');

            $agentProposalIssue->delete();

            event(new DeletedContentEvent(ANGENT_PROPOSAL_ISSUE_MODULE_SCREEN_NAME, $request, $agentProposalIssue));

            return $response->setMessage(trans('core/base::notices.delete_success_message'));
        } catch (Exception $exception) {
            return $response
                ->setError()
                ->setMessage($exception->getMessage());
        }
    }
    public function approveAgentProposal(AngentProposalIssue $proposal, BaseHttpResponse $response)
    {
        abort_if(check_user_depent_of_agent($proposal->warehouse_issue_id), 403, 'Bạn không có quyền thao tác trên đơn này!!!!');

        Assets::addStylesDirectly(['vendor/core/plugins/ecommerce/css/ecommerce.css'])
            ->addScriptsDirectly([
                'vendor/core/plugins/gallery/js/gallery-admin.js',
                'vendor/core/plugins/ecommerce/libraries/jquery.textarea_autosize.js',
                'vendor/core/plugins/ecommerce/js/order.js',
                'vendor/core/plugins/agent/js/approve-agent-issue.js',
            ])
            ->addScripts(['blockui', 'input-mask']);


        if ($proposal->status != ProposalIssueStatusEnum::PENDING) {
            return $response
                ->setError()
                ->setMessage(__('Không có quyền truy cập! Đơn hàng đã được duyệt hoặc từ chối'));
        }
        // dd($proposal->proposalProductIssueDetail);
        PageTitle::setTitle(__('Duyệt đơn đề xuất yêu cầu trả hàng'));
        return view('plugins/agent::proposal-issue.approve', compact('proposal'));

    }
    public function approve(AngentProposalIssue $proposal, Request $request, BaseHttpResponse $response)
    {
        abort_if(check_user_depent_of_agent($proposal->warehouse_issue_id), 403, 'Bạn không có quyền thao tác trên đơn này!!!!');

        DB::beginTransaction();
        try {
            $requestData = $request->input();
            $expectedDate = Carbon::createFromFormat('d-m-Y', $requestData['expected_date']);
            $proposal->update([
                'status' => ProposalIssueStatusEnum::APPOROVED,
                'warehouse_id' => $requestData['warehouse_id'],
                'warehouse_type' => Warehouse::class,
                'date_confirm' => Carbon::now(),
                'expected_date_submit' => $expectedDate,
                'invoice_confirm_name' => Auth::user()->name,
            ]);
            $proposalDetails = $proposal->proposalAgentIssueDetail;
            $lastIssue = AgentIssue::orderByDesc('id')->first();
            $issueCode = $lastIssue ? (int) $lastIssue->issue_code + 1 : 1;
            $agentIssue = AgentIssue::query()->create([
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
                    'agent_issue_id' => $agentIssue->id,
                    'product_id' => $key,
                    'product_name' => $product->name,
                    'sku' => $product->sku,
                    'color' => $prd['color'],
                    'size' => $prd['size'],
                    'price' => $product->price,
                    'quantity' => (int) $prd['quantity'],
                ];
                AgentIssueDetail::query()->create($dataDetail);
                foreach ($proposalDetails as $proposalDetail) {
                    if ($proposalDetail->product_id == $key) {
                        $proposalDetail->quantity_submit = $prd['quantity'];
                        $proposalDetail->save();
                    }
                }
            }
            $arrNoti = [
                'action' => 'duyệt',
                'permission' => "agent-proposal-issue.index",
                'route' => route('agent-proposal-issue.view', $proposal),
                'status' => 'duyệt'
            ];
            send_notify_cms_and_tele($proposal, $arrNoti);
            $arrNoti = [
                'action' => 'tạo',
                'permission' => "agent-issue.confirm",
                'route' => route('agent-issue.confirmView', $agentIssue),
                'status' => 'tạo'
            ];
            send_notify_cms_and_tele($agentIssue, $arrNoti);
            DB::commit();
            return $response
                ->setPreviousUrl(route('agent-proposal-issue.index'))
                ->setNextUrl(route('agent-proposal-issue.index'))
                ->setMessage(trans('Đã duyệt đơn'));
        } catch (Exception $e) {
            DB::rollBack();
            return $response
                ->setError()
                ->setMessage($e->getMessage());

        }
    }
    public function proposal(int|string $id)
    {
        $proposal = AngentProposalIssue::where('id', $id)

            ->first();
        $products = $proposal->proposalAgentIssueDetail->map(function ($item) use ($proposal) {
            $item->warehouse_id = $proposal->warehouse_issue_id;
            return $item;
        });
        $productsResource = AgentProposalIssueResource::collection($products);
        $agent = $proposal->warehouseIssue->agent->id;
        $warehouseIssue = $proposal->warehouse_issue_id;
        return response()->json(['data' => $productsResource, 'agent' => $agent, 'warehouseIssue' => $warehouseIssue], 200);
    }
    public function view($id)
    {
        $proposal = AngentProposalIssue::find($id);

        abort_if(check_user_depent_of_agent($proposal->warehouse_issue_id), 403, 'Bạn không có quyền thao tác trên đơn này!!!!');

        return view('plugins/agent::proposal-issue.view', compact('proposal'));
    }

    public function denied($id, Request $request, BaseHttpResponse $response)
    {
        $proposal = AngentProposalIssue::find($id);

        abort_if(check_user_depent_of_agent($proposal->warehouse_issue_id), 403, 'Bạn không có quyền thao tác trên đơn này!!!!');

        $proposal->status = ProposalIssueStatusEnum::DENIED;
        $proposal->invoice_confirm_name = Auth::user()->name;
        $proposal->reason_cancel = $request->input('denyReason');
        $proposal->save();
        $arrNoti = [
            'action' => 'từ chối duyệt',
            'permission' => "agent-proposal-issue.create",
            'route' => route('agent-proposal-issue.view', $proposal->id),
            'status' => 'từ chối duyệt'
        ];
        send_notify_cms_and_tele($proposal, $arrNoti);
        return $response->setPreviousUrl(route('agent-proposal-issue.index'))
            ->setNextUrl(route('agent-proposal-issue.index'))->setMessage(trans('Từ chối duyệt đơn'));
    }

    public function getGenerateReceiptProduct(Request $request, ProposalIssueHelper $issueHelper)
    {
        $data = AngentProposalIssue::with('proposalAgentIssueDetail')->find($request->id);

        if ($request->button_type === 'print') {
            return $issueHelper->streamInvoice($data);
        }
        return $issueHelper->downloadInvoice($data);
    }
}
