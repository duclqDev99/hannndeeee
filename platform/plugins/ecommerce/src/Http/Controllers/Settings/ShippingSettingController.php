<?php

namespace Botble\Ecommerce\Http\Controllers\Settings;

use Botble\Base\Facades\Assets;
use Botble\Ecommerce\Facades\EcommerceHelper;
use Botble\Ecommerce\Forms\Settings\ShippingSettingForm;
use Botble\Ecommerce\Http\Requests\Settings\ShippingSettingRequest;
use Botble\Ecommerce\Models\Shipping;
use Botble\Showroom\Models\Showroom;
use Botble\Showroom\Models\ShowroomPickAddress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ShippingSettingController extends SettingController
{
    public function edit()
    {
        $this->pageTitle(trans('plugins/ecommerce::setting.shipping.name'));


        Assets::addStylesDirectly(['vendor/core/plugins/ecommerce/css/ecommerce.css'])
            ->addScriptsDirectly(['vendor/core/plugins/ecommerce/js/shipping.js'])
            ->addScriptsDirectly(['vendor/core/plugins/ecommerce/js/setting-pick-address.js'])

            ->addScripts(['input-mask']);

        $form = ShippingSettingForm::create();

        $shipping = Shipping::query()
            ->with([
                'rules' => function ($query) {
                    $query->withCount(['items']);
                },
            ])
            ->get();

        $showrooms = Showroom::whereNotNull('code')->pluck('name', 'code');

        return view('plugins/ecommerce::settings.shipping', compact('shipping', 'form', 'showrooms'));
    }

    public function getSettingPickAddressItem(Request $request){
        $pickAddress = ShowroomPickAddress::query()
            ->where('showroom_code', $request->showroom_code)
            ->where('service_type', $request->service_type)
            ->first();

        return view('plugins/ecommerce::shipping.pick-address-form-item', compact('pickAddress'));
    }

    public function pickAddress(Request $request)
    {
        $responseResult =  $this->httpResponse();
        
        $rules =  [
            'showroom_code' => 'required',
            'service_type' => 'required',
        ];

        if($request->service_type == 'ghtk'){
            $rules['pick_address_id']  = 'required';
        }

        if($request->service_type == 'viettel_post'){
            $rules['province_id']  = 'required';
            $rules['district_id']  = 'required';
            $rules['ward_id']  = 'required';
        }

        $validator = Validator::make($request->all(), $rules); 

        if($validator->fails()){
            $responseResult->setError()->setMessage($validator->errors()->first());
            return $responseResult;
        }

        $pickAddress = ShowroomPickAddress::query()
            ->where('showroom_code', $request->showroom_code)
            ->where('service_type', $request->service_type)
            ->first();

        if ($pickAddress) {
            $data = $request->except('_token');
            $pickAddress->update($data);
            $responseResult->setMessage('Cập nhật thành công');
        }

        if (!$pickAddress) {
            ShowroomPickAddress::create($data);
            $responseResult->setMessage('Tạo mới thành công');
        }
        return $responseResult;
    }

    public function update(ShippingSettingRequest $request)
    {
        return $this->performUpdate($request->validated());
    }
}
