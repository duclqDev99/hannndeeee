<?php

namespace Database\Seeders;

use Botble\OrderStepSetting\Enums\ActionEnum;
use Botble\OrderStepSetting\Enums\ActionStatusEnum;
use Botble\OrderStepSetting\Models\ActionSetting;
use Botble\OrderStepSetting\Models\StepSetting;
use Botble\Sales\Enums\OrderStepStatusEnum;
use Botble\Sales\Enums\StepActionEnum;
use Botble\Sales\Models\StepInfo;
use Botble\Sales\Models\StepInfoDetail;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StepSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('hd_step_setting')->truncate();
        DB::table('hd_action_setting')->truncate();

        // Step1
        $step1 = StepSetting::create([
            'title' => 'Yêu cầu sản xuất',
            'index' => 1,
            'is_init' => true
        ]);
        ActionSetting::insert([
            [
                'title' => 'Tạo yêu cầu sản xuất',
                'action_code' => ActionEnum::RETAIL_SALE_CREATE_ORDER,
                'step_index' => $step1->index,
                'department_code' => 'retail_sale',
                'is_show' => false,
                'action_type' => 'purchase_order',
                'valid_status' => 'created',
                'update_relate_actions' => json_encode([
                    'next' => [
                        ActionEnum::RETAIL_SALE_REQUESTING_APPROVE_ORDER => ActionStatusEnum::PENDING,
                    ]   
                ])
            ],
            [
                'title' => 'Gửi phê duyệt YCSX',
                'department_code' => 'retail_sale',
                'is_show' => false,
                'step_index' => $step1->index,
                'action_code' => ActionEnum::RETAIL_SALE_REQUESTING_APPROVE_ORDER,
                'action_type' => 'purchase_order',
                'valid_status' => 'sended',
                'update_relate_actions' => json_encode([
                    'next' => [
                        ActionEnum::RETAIL_SALE_MANAGER_CONFIRM_ORDER => ActionStatusEnum::PENDING,
                    ]
                ])
            ],
            [
                'title' => 'Quản lý sale duyệt YCSX',
                'department_code' => 'retail_sale',
                'is_show' => true,
                'step_index' => $step1->index,
                'action_code' => ActionEnum::RETAIL_SALE_MANAGER_CONFIRM_ORDER,
                'action_type' => 'purchase_order',
                'valid_status' => 'confirmed',
                'update_relate_actions' => json_encode([   
                    'prev' => [
                        ActionEnum::RETAIL_SALE_REQUESTING_APPROVE_ORDER => ActionStatusEnum::REFUSED,
                    ],
                    'next' => [
                        ActionEnum::HGF_ADMIN_CONFIRM_ORDER => ActionStatusEnum::PENDING
                    ],
                ])
            ],
            [
                'title' => 'HGF duyệt YCSX',
                'department_code' => 'hgf_admin',
                'is_show' => true,
                'step_index' => $step1->index,
                'action_code' => ActionEnum::HGF_ADMIN_CONFIRM_ORDER,
                'action_type' => 'purchase_order',
                'valid_status' => 'confirmed',
                'update_relate_actions' => json_encode([
                    'prev' => [
                        ActionEnum::RETAIL_SALE_REQUESTING_APPROVE_ORDER => ActionStatusEnum::REFUSED,
                    ]
                ])
            ]
        ]);
        // Step2
        $step2 = StepSetting::create([
            'title' => 'Báo giá',
            'index' => 2,
            'is_init' => true
        ]);
        ActionSetting::insert([
            [
                'title' => 'Retail Sale tạo báo giá',
                'department_code' => 'retail_sale',
                'is_show' => false,
                'step_index' => $step2->index,
                'action_code' => ActionEnum::RETAIL_SALE_CREATE_QUOTATION,
                'action_type' => 'quotation_order',
                'valid_status' => 'created',
                'update_relate_actions' => json_encode([
                    'next' => [
                        ActionEnum::RETAIL_SEND_QUOTATION => ActionStatusEnum::PENDING,
                    ]
                ])
            ],
            [
                'title' => 'Retail Sale gửi phê duyệt báo giá',
                'department_code' => 'retail_sale',
                'is_show' => false,
                'step_index' => $step2->index,
                'action_code' => ActionEnum::RETAIL_SEND_QUOTATION,
                'action_type' => 'quotation_order',
                'valid_status' => 'sended',
                'update_relate_actions' => json_encode([
                    'next' => [
                        ActionEnum::RETAIL_SALE_MANAGER_CONFIRM_QUOTATION => ActionStatusEnum::PENDING,
                    ]
                ])
            ],
            [
                'title' => 'Quản lý sale duyệt báo giá',
                'department_code' => 'retail_sale',
                'is_show' => true,
                'step_index' => $step2->index,
                'action_code' => ActionEnum::RETAIL_SALE_MANAGER_CONFIRM_QUOTATION,
                'action_type' => 'quotation_order',
                'valid_status' => 'confirmed',
                'update_relate_actions' => json_encode([ 
                    'prev' => [
                        ActionEnum::RETAIL_SEND_QUOTATION => ActionStatusEnum::REFUSED,
                    ],
                    'next' => [
                        ActionEnum::CUSTOMER_CONFIRM_QUOTATION => ActionStatusEnum::PENDING,
                    ],
                ])
            ],
            [
                'title' => 'Khách hàng duyệt báo giá',
                'department_code' => 'retail_sale',
                'is_show' => true,
                'step_index' => $step2->index,
                'action_code' => ActionEnum::CUSTOMER_CONFIRM_QUOTATION,
                'action_type' => 'quotation_order',
                'valid_status' => 'confirmed',
                'update_relate_actions' => json_encode([
                    'prev' => [
                        ActionEnum::CUSTOMER_SIGN_CONTRACT => ActionStatusEnum::REFUSED,
                    ],
                    'next' => [
                        ActionEnum::CUSTOMER_SIGN_CONTRACT => ActionStatusEnum::PENDING,
                    ],
                ])
            ]
        ]);
        // Step3
        $step3 = StepSetting::create([
            'title' => 'Hợp đồng',
            'index' => 3,
            'is_init' => true
        ]);
        ActionSetting::insert([
            [
                'title' => 'Khách hàng ký hợp đồng',
                'department_code' => 'retail_sale',
                'is_show' => true,
                'step_index' => $step3->index,
                'action_code' => ActionEnum::CUSTOMER_SIGN_CONTRACT,
                'action_type' => 'quotation_order',
                'valid_status' => 'signed',
                'update_relate_actions' => json_encode([
                    'next' => [
                        ActionEnum::CUSTOMER_DEPOSIT => ActionStatusEnum::PENDING,
                    ],
                ])
            ],
            [
                'title' => 'Kế toán nhận cọc',
                'department_code' => 'retail_accountant',
                'is_show' => true,
                'step_index' => $step3->index,
                'action_code' => ActionEnum::CUSTOMER_DEPOSIT,
                'action_type' => 'quotation_order',
                'valid_status' => 'confirmed',
                'update_relate_actions' => null
            ]
        ]);
        // Step4
        $step4 = StepSetting::create([
            'title' => 'Đơn đặt hàng',
            'index' => 4,
            'is_init' => true
        ]);
        ActionSetting::insert([
            [
                'title' => 'Retail sale tạo đơn đặt hàng',
                'department_code' => 'retail_sale',
                'is_show' => false,
                'step_index' => $step4->index,
                'action_code' => ActionEnum::RETAIL_SALE_CREATE_PRODUCTION,
                'action_type' => 'production_order',
                'valid_status' => 'created',
                'update_relate_actions' => json_encode([
                    'next' => [
                        ActionEnum::RETAIL_SALE_SEND_PRODUCTION => ActionStatusEnum::PENDING,
                    ],
                ])
            ],
            [
                'title' => 'Retail gửi đơn sản xuất cho HGF',
                'department_code' => 'retail_sale',
                'is_show' => false,
                'step_index' => $step4->index,
                'action_code' => ActionEnum::RETAIL_SALE_SEND_PRODUCTION,
                'action_type' => 'production_order',
                'valid_status' => 'sended',
                'update_relate_actions' => json_encode([
                    'next' => [
                        ActionEnum::HGF_ADMIN_CONFIRM_PRODUCTION => ActionStatusEnum::PENDING,
                    ],
                ])
            ],
            [
                'title' => 'HGF xác nhận sản xuất',
                'department_code' => 'hgf_admin',
                'is_show' => true,
                'step_index' => $step4->index,
                'action_code' => ActionEnum::HGF_ADMIN_CONFIRM_PRODUCTION,
                'action_type' => 'production_order',
                'valid_status' => 'confirmed',
                'update_relate_actions' => json_encode([
                    'prev' => [
                        ActionEnum::RETAIL_SALE_SEND_PRODUCTION => ActionStatusEnum::REFUSED,
                    ]
                ])
            ],
            [
                'title' => 'HGF giao hàng',
                'department_code' => 'hgf_admin',
                'is_show' => true,
                'step_index' => $step4->index,
                'action_code' => ActionEnum::HGF_ADMIN_SHIPPING,
                'action_type' => 'production_order',
                'valid_status' => 'delivered',
                'update_relate_actions' => null
            ]
        ]);
        // Step5
        $step5 = StepSetting::create([
            'title' => 'Hoàn thành',
            'index' => 5,
            'is_init' => true
        ]);
        ActionSetting::insert([
            [
                'title' => 'Sale nhận hàng',
                'department_code' => 'retail_sale',
                'is_show' => true,
                'step_index' => $step5->index,
                'action_code' => ActionEnum::RETAIL_SALE_CONFIRM_RECEIVE_PRODUCT,
                'action_type' => 'production_order',
                'valid_status' => 'received',
                'update_relate_actions' => null
            ],
            [
                'title' => 'Kế toán xác nhận thu đủ tiền',
                'department_code' => 'retail_accountant',
                'is_show' => true,
                'step_index' => $step5->index,
                'action_code' => ActionEnum::ACCOUNTANT_CONFIRM_DEBT,
                'action_type' => 'production_order',
                'valid_status' => 'confirmed',
                'update_relate_actions' => null
            ]
        ]);
        // 
    }
}
