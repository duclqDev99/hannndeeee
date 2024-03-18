<?php

namespace Botble\Warehouse\Actions;

use Botble\GolfCaddie\Models\GolfCaddie;
use Botble\Warehouse\Models\Material;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;


class ImportMaterialAction extends AbstractAction
{

    public function run(array $materialData): array
    {
        DB::beginTransaction();
        foreach ($materialData as $key => $material) {
            if (!is_array($material)) {
                continue;
            }
            $material = self::convertKeysToDashAndLowercase($material);
            $messages = [
                'required' => ":attribute thứ " . $key + 1 . " là bắt buộc.",
                'string' => ":attribute :values phải là chuỗi.",
                'numeric' => ":attribute :values phải là số.",
                'unique' => ":attribute :input đã tồn tại.",
            ];
            $validator = Validator::make($material, [
                'ma_hang' => 'required|string|unique:wh_materials,code|max:220',
                'ten_hang' => 'required|string|max:220',
                'don_vi_tinh' => 'required|string|max:20',
                'so_luong_toi_thieu' => 'nullable|numeric|min:1',
                // 'gia_tien' => 'nullable|numeric|min:1',
            ],$messages);
            $validator->setAttributeNames([
                'ma_hang' => 'Mã hàng',
                'ten_hang' => 'Tên hàng',
                'don_vi_tinh' => 'Đơn vị tính',
                'so_luong_toi_thieu' => 'Số lượng tối thiểu',
                'mo_ta' => 'Mô tả',
                // 'gia_tien' => 'Giá tiền',
              ]);
            if (isset($material['status']) && is_array($material['status'])) {
                $material['status'] = $material['status']['value'];
            }
            if ($validator->fails()) {
                DB::rollBack();

                return $this->error($validator->messages()->first());
            }
            $oldKeysToNewKeys = [
                'ma_hang' => 'code',
                'ten_hang' => 'name',
                'don_vi_tinh' => 'unit',
                'so_luong_toi_thieu' => 'min',
                'mo_ta' => 'description',
                // 'gia_tien' => 'price',
            ];
            foreach ($oldKeysToNewKeys as $oldKey => $newKey) {
                if (array_key_exists($oldKey, $material)) {
                    $material[$newKey] = $material[$oldKey];
                    unset($material[$oldKey]);
                }
            }
            $material['created_by'] = Auth::id();
            $item = Material::query()->create($material);
            if (!$item) {
                DB::rollBack();

                return $this->error();
            }
        }
        DB::commit();

        return $this->success();
    }
    public function convertKeysToDashAndLowercase($array)
    {
        $newArray = array();
        $unicode = array(
            'a' => 'á|à|ả|ã|ạ|ă|ắ|ặ|ằ|ẳ|ẵ|â|ấ|ầ|ẩ|ẫ|ậ',
            'd' => 'đ',
            'e' => 'é|è|ẻ|ẽ|ẹ|ê|ế|ề|ể|ễ|ệ',
            'i' => 'í|ì|ỉ|ĩ|ị',
            'o' => 'ó|ò|ỏ|õ|ọ|ô|ố|ồ|ổ|ỗ|ộ|ơ|ớ|ờ|ở|ỡ|ợ',
            'u' => 'ú|ù|ủ|ũ|ụ|ư|ứ|ừ|ử|ữ|ự',
            'y' => 'ý|ỳ|ỷ|ỹ|ỵ',
        );
        foreach ($array as $key => $value) {
            // Convert the key to lowercase and replace spaces with dashes
            $newKey = str_replace(' ', '_', mb_strtolower($key, 'UTF-8'));
            foreach ($unicode as $nonUnicode => $uni) {
                $newKey = preg_replace("/($uni)/i", $nonUnicode, $newKey);
            }

            $newArray[$newKey] = $value;
        }
        return $newArray;
    }
}
