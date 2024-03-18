<?php

namespace Botble\SaleWarehouse\Repositories\Eloquent;

use Botble\HubWarehouse\Models\IssueInputTour;
use Botble\Support\Repositories\Eloquent\RepositoriesAbstract;
use Botble\SaleWarehouse\Repositories\Interfaces\SaleIssueRepositoryInterface;
use Botble\ProductQrcode\Enums\QRStatusEnum;
use Botble\ProductQrcode\Models\ProductQrcode;
use Botble\SaleWarehouse\Models\SaleIssue;
use Botble\SaleWarehouse\Models\SaleProduct;
use Botble\SaleWarehouse\Models\SaleWarehouse;
use Exception;
use Illuminate\Support\Facades\DB;

class SaleIssueRepository extends RepositoriesAbstract implements SaleIssueRepositoryInterface
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
        $id = (int) $data['issue_id'] ?? 0;
        $saleIssue = SaleIssue::find($id);
        if (!$saleIssue) {
            DB::rollBack();
            return ['message' => 'Phiếu không tồn tại.', 'error' => true];
        }
        $productCounts = [];
        try {
            if (is_array($productQrcode)) {
                foreach ($productQrcode as $qrcode) {
                    $dataInsert[] = [
                        'proposal_issues_id' => $saleIssue->proposal?->id,
                        'qrcode_id' => $qrcode['id'],
                        'where_type' => SaleWarehouse::class,
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
            $product = SaleProduct::query()->firstOrNew(['product_id' => $productId]);
            $product->increment('quantity', $count);
        }
    }
}
