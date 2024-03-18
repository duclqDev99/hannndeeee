<?php

namespace Botble\HubWarehouse\Repositories\Eloquent;

use Botble\HubWarehouse\Models\HubIssue;
use Botble\HubWarehouse\Models\HubWarehouse;
use Botble\HubWarehouse\Models\IssueInputTour;
use Botble\HubWarehouse\Models\QuantityProductInStock;
use Botble\Support\Repositories\Eloquent\RepositoriesAbstract;
use Botble\HubWarehouse\Repositories\Interfaces\HubIssueRepositoryInterface;
use Botble\ProductQrcode\Enums\QRStatusEnum;
use Botble\ProductQrcode\Models\ProductQrcode;
use Exception;
use Illuminate\Support\Facades\DB;

class HubIssueRepository extends RepositoriesAbstract implements HubIssueRepositoryInterface
{
    public function create(array $data)
    {
    }

    public function confirmReceiptInTour($data)
    {
        DB::beginTransaction();

        $productQrcode = $data['products'] ?? [];
        $dataInsert = [];
        $qrcodeId = [];
        $id = (int) $data['hub_issue_id'] ?? 0;
        $hubIssue = HubIssue::find($id);
        if (!$hubIssue) {
            DB::rollBack();
            return ['message' => 'Phiếu không tồn tại.', 'error' => true];
        }
        $productCounts = [];
        try {
            if (is_array($productQrcode)) {
                foreach ($productQrcode as $qrcode) {
                    $dataInsert[] = [
                        'proposal_issues_id' => $hubIssue->proposal?->id,
                        'qrcode_id' => $qrcode['id'],
                        'where_type' => HubWarehouse::class,
                        'where_id' => $qrcode['warehouse_id'],
                        'product_id' => $qrcode['reference_id'],
                    ];
                    $qrcodeId[] = $qrcode['id'];

                    $productId = $qrcode['reference_id'];
                    if (array_key_exists($productId, $productCounts)) {
                        $productCounts[$productId]++;
                    } else {
                        $productCounts[$productId] = 1;
                    }
                }
            }

            $this->insertIssueInputTour($dataInsert);
            $this->updateProductQrcodeStatus($qrcodeId);
            $this->updateProductQuantities($productCounts);

            DB::commit();
            return ['message' => 'Nhập sản phẩm thành công', 'error' => false];
        } catch (Exception $e) {
            DB::rollBack();
            return ['message' => $e->getMessage(), 'error' => true];
        }
    }

    protected function insertIssueInputTour($dataInsert)
    {
        IssueInputTour::query()->insert($dataInsert);
    }

    protected function updateProductQrcodeStatus($productQrcode)
    {
        ProductQrcode::query()->whereIn('id', $productQrcode)->update(['status' => QRStatusEnum::INSTOCK]);
    }

    protected function updateProductQuantities($productCounts)
    {
        foreach ($productCounts as $productId => $count) {
            $product = QuantityProductInStock::query()->firstOrNew(['product_id' => $productId]);
            $product->increment('quantity', $count);
        }
    }
}
