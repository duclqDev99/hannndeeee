<?php

namespace Botble\SaleWarehouse\Http\Controllers;

use Botble\Ecommerce\Models\Product;
use Botble\SaleWarehouse\Http\Requests\ApproveProposalRequest;
use Botble\SaleWarehouse\Http\Requests\SaleProposalIssueRequest;
use Botble\SaleWarehouse\Models\SaleIssue;
use Botble\SaleWarehouse\Models\SaleIssueDetail;
use Botble\SaleWarehouse\Models\SaleProduct;
use Botble\SaleWarehouse\Models\SaleProposalIssue;
use Botble\Base\Facades\PageTitle;
use Botble\Base\Http\Controllers\BaseController;
use Botble\SaleWarehouse\Models\SaleProposalIssueDetail;
use Botble\SaleWarehouse\Models\SaleWarehouseChild;
use Botble\WarehouseFinishedProducts\Enums\ProposalIssueStatusEnum;
use Botble\WarehouseFinishedProducts\Enums\ProposalProductEnum;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Exception;
use Botble\SaleWarehouse\Tables\SaleProposalIssueTable;
use Botble\Base\Events\CreatedContentEvent;
use Botble\Base\Events\DeletedContentEvent;
use Botble\Base\Events\UpdatedContentEvent;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\SaleWarehouse\Forms\SaleProposalIssueForm;
use Botble\Base\Forms\FormBuilder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SaleProposalIssueController extends BaseController
{
    public function index(SaleProposalIssueTable $table)
    {
        PageTitle::setTitle(trans('Danh sách đề xuất'));

        return $table->renderTable();
    }

    public function create(FormBuilder $formBuilder)
    {
        PageTitle::setTitle(trans('Tạo mới đề xuất'));

        return $formBuilder->create(SaleProposalIssueForm::class)->renderForm();
    }

    public function store(SaleProposalIssueRequest $request, BaseHttpResponse $response)
    {
        $requestData = $request->input();
        $expectedDate = Carbon::createFromFormat('Y-m-d', $requestData['expected_date']);
        $warehouse = SaleWarehouseChild::where('id', $requestData['warehouse_issue_id'])->first();

        $totals = array_reduce($requestData['product'], function ($carry, $item) {
            return $carry + intval($item['quantity']);
        }, 0);
        $lastProposalIssue = SaleProposalIssue::orderByDesc('id')->first();
        $proposalCode = $lastProposalIssue ? (int) $lastProposalIssue->proposal_code + 1 : 1;
        $dataCreate = [
            'warehouse_issue_id' => $requestData['warehouse_issue_id'],
            'warehouse_name' => $warehouse->name,
            'title' => $requestData['title'],
            'warehouse_address' => $warehouse->saleWarehouse->address,
            'issuer_id' => Auth::user()->id,
            'invoice_issuer_name' => Auth::user()->name,
            'general_order_code' => $requestData['general_order_code'],
            'description' => $requestData['description'],
            'expected_date' => $expectedDate,
            'status' => 'pending',
            'is_warehouse' => $requestData['is_warehouse'],
            'quantity' => $totals,
            'proposal_code' => $proposalCode,
        ];
        DB::beginTransaction();
        try {
            $saleProposalIssue = SaleProposalIssue::query()->create($dataCreate);
            foreach ($requestData['product'] as $key => $val) {
                $product = Product::find($key);
                $data = [
                    'proposal_id' => $saleProposalIssue->id,
                    'product_id' => $product->id,
                    'quantity' => (int) $val['quantity'],
                ];
                SaleProposalIssueDetail::query()->create($data);
            }
            $arrNoti = [
                'action' => 'tạo',
                'permission' => "sale-proposal-issue.confirm",
                'route' => route('sale-proposal-issue.approveView', $saleProposalIssue->id),
                'status' => 'tạo'
            ];
            send_notify_cms_and_tele($saleProposalIssue, $arrNoti);
            event(new CreatedContentEvent(SALE_PROPOSAL_ISSUE_MODULE_SCREEN_NAME, $request, $saleProposalIssue));
            DB::commit();
            return $response
                ->setPreviousUrl(route('sale-proposal-issue.index'))
                ->setNextUrl(route('sale-proposal-issue.edit', $saleProposalIssue->getKey()))
                ->setMessage(trans('core/base::notices.create_success_message'));
        } catch (Exception $e) {
            DB::rollBack();
            return $response
                ->setError()
                ->setMessage($e->getMessage());
        }
    }

    public function edit(SaleProposalIssue $saleProposalIssue, FormBuilder $formBuilder, BaseHttpResponse $response)
    {
        if (($saleProposalIssue->whereIn('warehouse_issue_id', get_list_sale_warehouse_id_for_current_user())->exists() || Auth::user()->hasPermission('sale-warehouse.all')) && Auth::user()->id == $saleProposalIssue->issuer_id) {
            if ($saleProposalIssue->status != ProposalIssueStatusEnum::PENDING) {
                return $response
                    ->setError()
                    ->setMessage(__('Đơn hàng đã duyệt hoặc từ chối không có quyền truy cập!!'))
                    ->setNextUrl(route('sale-proposal-issue.view', $saleProposalIssue->id))
                ;
            }
            PageTitle::setTitle(trans('Sửa đề xuất :name', ['name' => get_proposal_issue_product_code($saleProposalIssue->proposal_code)]));

            return $formBuilder->create(SaleProposalIssueForm::class, ['model' => $saleProposalIssue])->renderForm();
        }
        return $response
            ->setError()
            ->setMessage(__('không có quyền truy cập đơn hàng!!'))
            ->setNextUrl(route('sale-proposal-issue.index'))
        ;
    }

    public function update(SaleProposalIssue $saleProposalIssue, SaleProposalIssueRequest $request, BaseHttpResponse $response)
    {
        $requestData = $request->input();
        $expectedDate = Carbon::createFromFormat('Y-m-d', $requestData['expected_date']);
        $warehouse = SaleWarehouseChild::where('id', $requestData['warehouse_issue_id'])->first();

        $totals = array_reduce($requestData['product'], function ($carry, $item) {
            return $carry + intval($item['quantity']);
        }, 0);

        $dataUpdate = [
            'warehouse_issue_id' => $requestData['warehouse_issue_id'],
            'warehouse_name' => $warehouse->name,
            'title' => $requestData['title'],
            'warehouse_address' => $warehouse->saleWarehouse->address,
            'issuer_id' => Auth::user()->id,
            'invoice_issuer_name' => Auth::user()->name,
            'general_order_code' => $requestData['general_order_code'],
            'description' => $requestData['description'],
            'expected_date' => $expectedDate,
            'is_warehouse' => $requestData['is_warehouse'],
            'quantity' => $totals,
        ];
        DB::beginTransaction();
        try {
            $saleProposalIssue->update($dataUpdate);
            foreach($saleProposalIssue->proposalHubIssueDetail as $detail)
            {
                $detail->delete();
            }
            foreach ($requestData['product'] as $key => $val) {
                $product = Product::find($key);
                $data = [
                    'proposal_id' => $saleProposalIssue->id,
                    'product_id' => $product->id,
                    'quantity' => (int) $val['quantity'],
                ];
                SaleProposalIssueDetail::query()->create($data);
            }
            $arrNoti = [
                'action' => 'tạo',
                'permission' => "sale-proposal-issue.confirm",
                'route' => route('sale-proposal-issue.approveView', $saleProposalIssue->id),
                'status' => 'tạo'
            ];
            send_notify_cms_and_tele($saleProposalIssue, $arrNoti);
            event(new UpdatedContentEvent(SALE_PROPOSAL_ISSUE_MODULE_SCREEN_NAME, $request, $saleProposalIssue));
            DB::commit();
            return $response
                ->setPreviousUrl(route('sale-proposal-issue.index'))
                ->setNextUrl(route('sale-proposal-issue.edit', $saleProposalIssue->getKey()))
                ->setMessage(trans('core/base::notices.create_success_message'));
        } catch (Exception $e) {
            DB::rollBack();
            return $response
                ->setError()
                ->setMessage($e->getMessage());
        }

    }

    public function destroy(SaleProposalIssue $saleProposalIssue, Request $request, BaseHttpResponse $response)
    {
        try {
            $saleProposalIssue->delete();

            event(new DeletedContentEvent(SALE_PROPOSAL_ISSUE_MODULE_SCREEN_NAME, $request, $saleProposalIssue));

            return $response->setMessage(trans('core/base::notices.delete_success_message'));
        } catch (Exception $exception) {
            return $response
                ->setError()
                ->setMessage($exception->getMessage());
        }
    }

    public function approveView($id, BaseHttpResponse $response)
    {
        PageTitle::setTitle(trans('Duyệt đơn xuất kho'));
        $proposal = SaleProposalIssue::where('id', $id)->with('proposalHubIssueDetail')->first();
        if ($proposal->whereIn('warehouse_issue_id', get_list_sale_warehouse_id_for_current_user())->exists() || Auth::user()->hasPermission('sale-warehouse.all')) {
            if ($proposal->status != ProposalIssueStatusEnum::PENDING) {
                return $response
                    ->setError()
                    ->setMessage(__('Đơn hàng đã duyệt hoặc từ chối không có quyền truy cập!!'))
                    ->setNextUrl(route('sale-proposal-issue.view', $proposal->id))
                ;
            }
            return view('plugins/sale-warehouse::proposal-issue.approve', compact('proposal'));
        }
        return $response
            ->setError()
            ->setMessage(__('Không có quyền truy cập đơn hàng!!'))
            ->setNextUrl(route('sale-proposal-issue.index'))
        ;
    }
    public function view(int|string $id, BaseHttpResponse $response)
    {
        PageTitle::setTitle(trans('Chi tiết đơn xuất kho'));
        $proposal = SaleProposalIssue::where(['id' => $id])
            ->with('proposalHubIssueDetail')->first();
        $receipt = $proposal->saleIssue;

        if ($proposal->whereIn('warehouse_issue_id', get_list_sale_warehouse_id_for_current_user())->exists() || Auth::user()->hasPermission('sale-warehouse.all')) {
            return view('plugins/sale-warehouse::proposal-issue/view', compact('proposal', 'receipt'));
        }
        return $response
            ->setError()
            ->setMessage(__('Không có quyền truy cập đơn hàng!!'))
            ->setNextUrl(route('sale-proposal-issue.index'));
    }

    public function approve($id, ApproveProposalRequest $request, BaseHttpResponse $response)
    {
        $requestData = $request->input();
        $expectedDate = Carbon::createFromFormat('d-m-Y', $requestData['expected_date']);
        DB::beginTransaction();
        try {
            $proposal = SaleProposalIssue::where('id', $id)->sharedLock()->first();
            if ($proposal->status != ProposalIssueStatusEnum::PENDING) {
                DB::rollBack();
                return $response
                    ->setError()
                    ->setMessage(__('Đơn hàng đã duyệt hoặc từ chối !'));

            }
            $proposal->update([
                'status' => ProposalIssueStatusEnum::APPOROVED,
                'invoice_confirm_name' => Auth::user()->name,
                'date_confirm' => Carbon::now()->format('Y-m-d'),
            ]);
            $totalQuantity = array_reduce($requestData['product'], function ($carry, $item) {
                return $carry + intval($item['quantity']);
            }, 0);

            $lastIssue = SaleIssue::orderByDesc('id')->first();
            $issueCode = $lastIssue ? (int) $lastIssue->issue_code + 1 : 1;

            $dataSaleIssue = [
                'proposal_id' => $proposal->id,
                'warehouse_issue_id' => $proposal->warehouse_issue_id,
                'warehouse_name' => $proposal->warehouse_name,
                'warehouse_address' => $proposal->warehouse_address,
                'issuer_id' => Auth::user()->id,
                'invoice_issuer_name' => Auth::user()->name,
                'warehouse_id' => $proposal->warehouse_id,
                'warehouse_type' => $proposal->warehouse_type,
                'quantity' => $totalQuantity,
                'title' => $proposal->title,
                'description' => $requestData['description'],
                'expected_date' => $expectedDate,
                'general_order_code' => $proposal->general_order_code,
                'status' => 'pending',
                'issue_code' => $issueCode,
            ];
            $saleIssue = SaleIssue::query()->create($dataSaleIssue);
            if (isset($requestData['product'])) {
                foreach ($requestData['product'] as $key => $products) {
                    $quantityInStock = SaleProduct::where(['product_id' => $key, 'warehouse_id' => $proposal->warehouse_issue_id])->first();
                    if ($products['quantity'] > $quantityInStock) {
                        return $response
                            ->setError()
                            ->setMessage('Số lượng sản phẩm ' . $products->name . ' không còn đủ trong kho!!');
                    }
                    $dataProductIssueDetail = [
                        'sale_isue_id' => $saleIssue->id,
                        'product_id' => $key,
                        'quantity' => $products['quantity'],
                    ];
                    SaleIssueDetail::query()->create($dataProductIssueDetail);
                }
            }
            $arrNoti = [
                'action' => 'tạo',
                'permission' => "sale-issue.confirm",
                'route' => route('sale-issue.view-confirm', $saleIssue->id),
                'status' => 'tạo'
            ];
            send_notify_cms_and_tele($saleIssue, $arrNoti);
            //Notify for creator proposal
            $arrNoti = [
                'action' => 'duyệt',
                'permission' => "sale-proposal-issue.create",
                'route' => route('sale-proposal-issue.view', $proposal->id),
                'status' => 'duyệt'
            ];

            send_notify_cms_and_tele($proposal, $arrNoti);
            DB::commit();
            return $response
                ->setPreviousUrl(route('sale-proposal-issue.index'))
                ->setNextUrl(route('sale-proposal-issue.index'))
                ->setMessage(trans('core/base::notices.update_success_message'));

        } catch (Exception $e) {
            DB::rollBack();
            return $response
                ->setError()
                ->setMessage($e->getMessage());
        }
    }
    public function denied($id, Request $request, BaseHttpResponse $response)
    {
        $saleProposalIssue = SaleProposalIssue::findOrFail($id);
        DB::beginTransaction();
        try {
            $saleProposalIssue->update(['status' => ProposalIssueStatusEnum::DENIED, 'reason_cancel' => $request->input('denyReason'), 'invoice_confirm_name' => Auth::user()->name]);
            $arrNoti = [
                'action' => 'từ chối',
                'permission' => "sale-proposal-issue.create",
                'route' => route('sale-proposal-issue.view', $saleProposalIssue->id),
                'status' => 'từ chối'
            ];
            send_notify_cms_and_tele($saleProposalIssue, $arrNoti);
            DB::commit();
            return $response
                ->setPreviousUrl(route('sale-proposal-issue.view', $saleProposalIssue->id))
                ->setNextUrl(route('sale-proposal-issue.view', $saleProposalIssue->id))
                ->setError()
                ->setMessage('Từ chối đơn đề xuất kho');
        } catch (Exception $e) {
            DB::rollBack();
            return $response
                ->setError()
                ->setMessage($e->getMessage());
        }


    }
    public function proposal($proposal_id)
    {
        $proposal = SaleProposalIssue::find($proposal_id);
        $product = SaleProposalIssueDetail::where('proposal_id', $proposal_id)
            ->with([
                'product',
                'product.parentProduct',
                'product.productAttribute',
                'productStock' => function ($query) use ($proposal) {
                    $query->where('warehouse_id', $proposal->warehouse_issue_id);
                }
            ])
            ->get();
        $saleWarehouse = $proposal->warehouseIssue->saleWarehouse->id;
        return response()->json([
            'data' => $product,
            'proposal' => $proposal,
            'saleWarehouse' => $saleWarehouse,
        ], 200);
    }
}
