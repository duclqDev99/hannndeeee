<?php

namespace Botble\SharedModule\Listeners;

use Botble\Ecommerce\Facades\OrderHelper;
use Botble\Ecommerce\Models\Address;
use Botble\Ecommerce\Models\Customer;
use Botble\SharedModule\Events\CustomerCheckoutEvent;
use Botble\SharedModule\Trait\CustomersAppTrait;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;

class CreateCustomerCheckoutListener implements ShouldQueue
{
    use InteractsWithQueue;
    use CustomersAppTrait;

    /**
     * Handle the event.
     *
     * @param  CustomerCheckoutEvent  $event
     * @return void
     */
    public function handle(CustomerCheckoutEvent $event)
    {
        $order = $event->order;
        $customerData = $event->customerData;
        if ($order->user_id == null || $order->user_id == 0) {
            $responseApp = $this->getInfoCustomerApi($customerData['phone']);
            if ($responseApp['error_code'] == 0) {
                // Cập nhật hoặc tạo mới Customer
                $data = [
                    'phone' => $customerData['phone'],
                    'name' => $responseApp['data']['fullname'],
                    'email' => $customerData['email'],
                    'vid' => $responseApp['data']['vga'],
                ];
                $customer = Customer::updateOrCreate(
                    ['phone' => $customerData['phone']], // Điều kiện tìm kiếm
                    $data // Dữ liệu cần cập nhật hoặc tạo mới
                );
                $customer->confirmed_at = $customer->created_at;

                $addressData = [
                    'province_id' => Arr::get($customerData, 'province'),
                    'district_id' => Arr::get($customerData, 'district'),
                    'ward_id' => Arr::get($customerData, 'ward'),
                    'showroom_id' => Arr::get($customerData, 'showroom'),
                    'customer_id' => $customer->id,
                    'name' => $customer->name,
                    'is_default' => true
                ];
                
                $address = Address::query()
                    ->updateOrCreate(
                        [
                            'customer_id' => $customer->id,
                            'province_id' => Arr::get($customerData, 'province'),
                            'district_id' => Arr::get($customerData, 'district'),
                            'ward_id' => Arr::get($customerData, 'ward'),
                        ],
                        $addressData
                    );

                OrderHelper::setOrderSessionData($customerData['phone'], Arr::only($customerData, [
                    'name',
                    'email',
                    'phone',
                    'province',
                    'district',
                    'showroom',
                    'ward',
                    'address',
                    'showroom',
                    'shipping_method',
                    'shipping_option',
                ]));
                session(['customer_phone_checkout' => $customerData['phone']]);
            }
        }
    }
}
