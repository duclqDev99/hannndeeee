<?php

namespace Botble\Department\database\seeders;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class NewProcedureOrderSeeders extends Seeder
{
    /**
     * Run the database seeders.
     */
    public function run(): void
    {
        DB::table('procedure_orders')->insert(
            [
                [
                    'name' => 'tạo đơn',
                    'code' => 'td_01',
                    'roles_join' => '{"warehouse.index":true,"material-receipt-confirm.index":true,"material-receipt-confirm.create":true,"material-receipt-confirm.edit":true,"material-receipt-confirm.destroy":true,"material-receipt-confirm.confirm":true,"material-receipt-confirm.printQrCode":true,"superuser":0,"manage_supers":0}',
                    'parent_id' => '',
                    'cycle_point' => 'start',
                    'next_step' => json_encode([
                        'if' => '',
                        'next' => 'tk_01',
                        'prev' => ''
                    ]),
                    'group_code' => 'LDH_001',
                    'main_thread_status' => 'main_branch',
                    'department_joins' => json_encode(['sale_01', 'dp_01']),
                ],
                [
                    'name' => 'thiết kế',
                    'code' => 'tk_01',
                    'roles_join' => '{"warehouse.index":true,"material-receipt-confirm.index":true,"material-receipt-confirm.create":true,"material-receipt-confirm.edit":true,"material-receipt-confirm.destroy":true,"material-receipt-confirm.confirm":true,"material-receipt-confirm.printQrCode":true,"superuser":0,"manage_supers":0}',
                    'cycle_point' => '',
                    'parent_id' => 'td_01',
                    'next_step' => json_encode([
                        'if' => '',
                        'next' => 'admin_001',
                        'prev' => ''
                    ]),
                    'group_code' => 'LDH_001',
                    'main_thread_status' => 'main_branch',
                    'department_joins' => json_encode(['dp_01', 'tk_01']),
                ], 
                [
                    'name' => 'rập',
                    'code' => 'r_01',
                    'roles_join' => '{"warehouse.index":true,"material-receipt-confirm.index":true,"material-receipt-confirm.create":true,"material-receipt-confirm.edit":true,"material-receipt-confirm.destroy":true,"material-receipt-confirm.confirm":true,"material-receipt-confirm.printQrCode":true,"superuser":0,"manage_supers":0}',
                    'cycle_point' => '',
                    'parent_id' => 'td_01',
                    'next_step' => json_encode([
                        'if' => '',
                        'next' => 'admin_001',
                        'prev' => ''
                    ]),
                    'group_code' => 'LDH_001',
                    'main_thread_status' => 'main_branch',
                ],
                [
                    'name' => 'admin kiểm duyệt',
                    'code' => 'admin_001',
                    'roles_join' => '{"warehouse.index":true,"material-receipt-confirm.index":true,"material-receipt-confirm.create":true,"material-receipt-confirm.edit":true,"material-receipt-confirm.destroy":true,"material-receipt-confirm.confirm":true,"material-receipt-confirm.printQrCode":true,"superuser":0,"manage_supers":0}',
                    'cycle_point' => '',
                    'parent_id' => 'r_01',
                    'next_step' => json_encode([
                        'if' => '',
                        'next' => 'sale_01',
                        'prev' => ''
                    ]),
                    'group_code' => 'LDH_001',
                    'main_thread_status' => 'main_branch',
                ],
                [
                    'name' => 'sale cho khách hàng xem',
                    'code' => 'sale_01',
                    'roles_join' => '{"warehouse.index":true,"material-receipt-confirm.index":true,"material-receipt-confirm.create":true,"material-receipt-confirm.edit":true,"material-receipt-confirm.destroy":true,"material-receipt-confirm.confirm":true,"material-receipt-confirm.printQrCode":true,"superuser":0,"manage_supers":0}',
                    'cycle_point' => '',
                    'parent_id' => 'admin_001',
                    'next_step' => json_encode([
                        'if' => '',
                        'next' => 'kh_01',
                        'prev' => ''
                    ]),
                    'group_code' => 'LDH_001',
                    'main_thread_status' => 'main_branch',
                ],
                [
                    'name' => 'khách hàng',
                    'code' => 'kh_01',
                    'roles_join' => '{"warehouse.index":true,"material-receipt-confirm.index":true,"material-receipt-confirm.create":true,"material-receipt-confirm.edit":true,"material-receipt-confirm.destroy":true,"material-receipt-confirm.confirm":true,"material-receipt-confirm.printQrCode":true,"superuser":0,"manage_supers":0}',
                    'cycle_point' => '',
                    'parent_id' => 'sale_01',
                    'next_step' => json_encode([
                        'if' => '',
                        'next' => 'sale_01',
                        'prev' => ''
                    ]),
                    'group_code' => 'LDH_001',
                    'main_thread_status' => 'main_branch',
                ],



                [
                    'name' => 'mua hàng',
                    'code' => 'mh_01',
                    'roles_join' => '{"warehouse.index":true,"material-receipt-confirm.index":true,"material-receipt-confirm.create":true,"material-receipt-confirm.edit":true,"material-receipt-confirm.destroy":true,"material-receipt-confirm.confirm":true,"material-receipt-confirm.printQrCode":true,"superuser":0,"manage_supers":0}',
                    'cycle_point' => '',
                    'parent_id' => 'admin_001',
                    'next_step' => json_encode([
                        'if' => '' ?? [], //'string or array conditions',
                        'next' => 'k_01',
                        'prev' => ''
                    ]),
                    'group_code' => 'LDH_001',
                    'main_thread_status' => 'main_branch',
                ],
                [
                    'name' => 'kho',
                    'code' => 'k_01',
                    'roles_join' => '{"warehouse.index":true,"material-receipt-confirm.index":true,"material-receipt-confirm.create":true,"material-receipt-confirm.edit":true,"material-receipt-confirm.destroy":true,"material-receipt-confirm.confirm":true,"material-receipt-confirm.printQrCode":true,"superuser":0,"manage_supers":0}',
                    'cycle_point' => '',
                    'parent_id' => 'mh_01',
                    'next_step' => json_encode([
                        'if' => '',
                        'next' => 'gc_01',
                        'prev' => ''
                    ]),
                    'group_code' => 'LDH_001',
                    'main_thread_status' => 'main_branch',
                ],
                [
                    'name' => 'gia công',
                    'code' => 'gc_01',
                    'roles_join' => '{"warehouse.index":true,"material-receipt-confirm.index":true,"material-receipt-confirm.create":true,"material-receipt-confirm.edit":true,"material-receipt-confirm.destroy":true,"material-receipt-confirm.confirm":true,"material-receipt-confirm.printQrCode":true,"superuser":0,"manage_supers":0}',
                    'cycle_point' => '',
                    'parent_id' => 'k_01',
                    'next_step' => json_encode([
                        'if' => '',
                        'next' => 'ktp_01',
                        'prev' => ''
                    ]),
                    'group_code' => 'LDH_001',
                    'main_thread_status' => 'main_branch',
                ],
                [
                    'name' => 'kho thành phẩm',
                    'code' => 'ktp_01',
                    'roles_join' => '{"warehouse.index":true,"material-receipt-confirm.index":true,"material-receipt-confirm.create":true,"material-receipt-confirm.edit":true,"material-receipt-confirm.destroy":true,"material-receipt-confirm.confirm":true,"material-receipt-confirm.printQrCode":true,"superuser":0,"manage_supers":0}',
                    'cycle_point' => '',
                    'parent_id' => 'gc_01',
                    'next_step' => json_encode([
                        'if' => '',
                        'next' => '',
                        'prev' => ''
                    ]),
                    'group_code' => 'LDH_001',
                    'main_thread_status' => 'main_branch',
                ],

                [
                    'name' => 'khach_hang',
                    'code' => 'kh_02',
                    'roles_join' => '{"warehouse.index":true,"material-receipt-confirm.index":true,"material-receipt-confirm.create":true,"material-receipt-confirm.edit":true,"material-receipt-confirm.destroy":true,"material-receipt-confirm.confirm":true,"material-receipt-confirm.printQrCode":true,"superuser":0,"manage_supers":0}',
                    'cycle_point' => 'end',
                    'parent_id' => 'gc_01',
                    'next_step' => json_encode([
                        'if' => '',
                            'next' => '',
                        'prev' => ''
                    ]),
                    'group_code' => 'LDH_001',
                    'main_thread_status' => 'main_branch',
                ],
            ]
        );

        for ($i = 1; $i <= 10; $i++) {
            DB::table('order_analyses')->insert([
                'id' => $i,
                'name' => "Order Analysis Test $i",
                'code' => "OAT_00$i",
                'description' => "mô tả bản thiết kế 00$i.",
                'created_by' => 1,
                'updated_by' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('analysis_detail')->insert([
                'id' => $i,
                'analysis_material_id' => 1,
                'analysis_order_id' => $i,
                'quantity' => 10,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
