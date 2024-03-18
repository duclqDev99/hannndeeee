<?php

namespace Botble\Warehouse\Http\Controllers\API;

use Botble\Base\Http\Controllers\BaseController;
use Botble\Warehouse\Enums\MaterialStatusEnum;
use Botble\Warehouse\Models\MaterialWarehouse;
use Botble\Warehouse\Models\Stock;
use Botble\Warehouse\Models\Agency;
use Botble\Ecommerce\Models\Product;
use Botble\Warehouse\Enums\BaseStatusEnum;
use Botble\Warehouse\Http\Requests\ApiValidateRequest;
use Botble\Warehouse\Models\Material;
use Botble\Warehouse\Models\MaterialProposalPurchase;
use Botble\Warehouse\Models\ProposalPurchaseGoods;
use Botble\Warehouse\Models\Supplier;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MaterialApiController extends BaseController
{
    public function getAllMaterial()
    {
        $stocks = Material::where(['status' => MaterialStatusEnum::ACTIVE])->get();
        if (!empty($stocks)) {
            return $this->responseApi(0, "Thành công!", 200, $stocks);
        } else {
            return $this->responseApi(1, "Không tìm thấy bất kỳ nguyên phụ liệu nào!", 200);
        }
    }

    public function getAllSupplier()
    {
        $agency = Supplier::where(['status' => BaseStatusEnum::PUBLISHED])->get();

        if (!empty($agency)) {
            return $this->responseApi(0, "Thành công!", 200, $agency);
        } else {
            return $this->responseApi(1, "Không tìm thấy bất kỳ nhà cung cấp nào!", 200);
        }
    }

    public function checkValidateReceipt()
    {
        $requestData = json_decode($_POST['data']);

        if (!isset($requestData->warehouse_id)) {
            return $this->responseApi(1, "Vui lòng chọn kho nhập nguyên liệu!", 200);
        }

        if (empty($requestData->title)) {
            return $this->responseApi(1, "Vui lòng nhập tiêu đề đơn!", 200);
        }

        $expectedDate = date('Y-m-d', strtotime($requestData->expected_date ?? Carbon::now()));
        $currentDate = Carbon::now()->format('Y-m-d');

        if (($expectedDate) < $currentDate) {
            return $this->responseApi(1, "Vui lòng nhập ngày dự kiến lớn hơn ngày hiện tại!", 200);
        }

        $parameter = '';

        if ($requestData->type_proposal == 'stock') {
            $parameter = 'stock';

            if (!isset($requestData->detination_wh_id)) {
                return $this->responseApi(1, "Vui lòng chọn kho lấy nguyên liệu!", 200);
            }

            if (($requestData->detination_wh_id) == $requestData->warehouse_id) {
                return $this->responseApi(1, "Kho xuất nguyên liệu và kho đích không được trùng!", 200);
            }
        } else { //supplier
            $parameter = 'supplier';
        }

        foreach ($requestData->$parameter->material as $key => $value) {
            if ((int) $value->quantity < 1) {
                return $this->responseApi(1, "Vui lòng nhập đầy đủ số lượng!", 200);
            }
        }

        return $this->responseApi(0, "Thành công!", 200);
    }

    public function checkValidatePurchaseGoods()
    {
        $requestData = json_decode($_POST['data']);

        if (empty($requestData->title)) {
            return $this->responseApi(1, "Vui lòng nhập tiêu đề đơn!", 200);
        }

        $expectedDate = Carbon::createFromFormat('d-m-Y', $requestData->expected_date)->format('Y-m-d');
        $currentDate = Carbon::now()->format('Y-m-d');

        if (($expectedDate) < $currentDate) {
            return $this->responseApi(1, "Vui lòng nhập ngày dự kiến lớn hơn ngày hiện tại!", 200);
        }

        foreach ($requestData->material as $key => $value) {
            $countKey = explode('-', $key);

            if (empty($value->quantity)) {
                return $this->responseApi(1, "Vui lòng nhập đầy đủ số lượng nhập nguyên phụ liệu!", 200);
            }

            if ((int) ($value->quantity) < 1) {
                return $this->responseApi(1, "Vui lòng nhập số lượng lớn hơn hoặc bằng 1!", 200);
            }

            if (!empty(($value->price)) && (int) ($value->price) < 1) {
                return $this->responseApi(1, "Giá nguyên phụ liệu phải lớn hơn hoặc bằng 1!", 200);
            }

            if (count($countKey) === 1) {
                $materialByCode = Material::where(['code' => $value->code])->first();
                if (!empty($materialByCode) && !empty($value->code)) {
                    return $this->responseApi(1, "Mã nguyên phụ liệu:" . $value->name . " đã tồn tại!", 200);
                }

                if (empty($value->name)) {
                    return $this->responseApi(1, "Vui lòng nhập tên nguyên phụ liệu!", 200);
                }
            }
        }

        return $this->responseApi(0, "Thành công!", 200);
    }

    public function getInfoProposalById(string|int $id)
    {
        $proposal = MaterialProposalPurchase::where(['id' => $id])->with('proposalDetail', 'proposalOut')->first();

        if (!empty($proposal)) {
            return $this->responseApi(0, "Thành công!", 200, $proposal);
        } else {
            return $this->responseApi(1, "Không tìm thấy đơn đề xuất nhập kho này!", 200);
        }
    }

    public function getInfoProposalGoodsById(string|int $id)
    {
        $proposal = ProposalPurchaseGoods::where(['id' => $id])->with('proposalDetail')->first();

        if (!empty($proposal)) {
            return $this->responseApi(0, "Thành công!", 200, $proposal);
        } else {
            return $this->responseApi(1, "Không tìm thấy đơn đề xuất nhập kho này!", 200);
        }
    }

    public function responseApi($errorCode, $msg, $httpCode, $data = null)
    {
        $dataRes = [
            'error_code' => $errorCode,
            'msg' => $msg,
            'body' => $data,
        ];

        return response()->json($dataRes, $httpCode);
    }
}
