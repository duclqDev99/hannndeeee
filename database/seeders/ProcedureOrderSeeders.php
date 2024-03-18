<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ProcedureOrderSeeders extends Seeder
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
                    'next_step' => '{
                        "tk_01": {
                          "td_01": "accept"
                        },
                        "r_01": {
                          "td_01": "accept"
                        },
                         "admin_001": {
                          "tk_01": "success",
                          "r_01": "success"
                        },
                         "kh_01": {
                          "admin_001": "success"
                        }
                      }',
                    'location' => '
                        {
                            "offsetX": 300,
                            "offsetY": 110
                        }
                      ',
                    'group_code' => 'LDH_001',
                    'main_thread_status' => 'main_branch',
                ],
                [
                    'name' => 'thiết kế',
                    'code' => 'tk_01',
                    'roles_join' => '[1, 2]',
                    'cycle_point' => '',
                    'parent_id' => 'td_01',
                    'next_step' => '{
                        "admin_001": {
                          "tk_01": "success"
                        }
                      }',
                    'location' => '
                      {
                        "offsetX": 35,
                        "offsetY": 300
                       }
                      ',
                    'group_code' => 'LDH_001',
                    'main_thread_status' => 'main_branch',
                ],
                [
                    'name' => 'rập',
                    'code' => 'r_01',
                    'roles_join' => '{"warehouse.index":true,"material-receipt-confirm.index":true,"material-receipt-confirm.create":true,"material-receipt-confirm.edit":true,"material-receipt-confirm.destroy":true,"material-receipt-confirm.confirm":true,"material-receipt-confirm.printQrCode":true,"superuser":0,"manage_supers":0}',
                    'cycle_point' => '',
                    'parent_id' => 'td_01',
                    'next_step' => '{
                        "admin_001": {
                          "r_01": "success"
                        }
                      }
                      ',
                    'location' => '
                      {
                        "offsetX": 200,
                        "offsetY": 350
                       }
                      ',
                    'group_code' => 'LDH_001',
                    'main_thread_status' => 'main_branch',
                ],
                [
                    'name' => 'admin kiểm duyệt',
                    'code' => 'admin_001',
                    'roles_join' => '{"warehouse.index":true,"material-receipt-confirm.index":true,"material-receipt-confirm.create":true,"material-receipt-confirm.edit":true,"material-receipt-confirm.destroy":true,"material-receipt-confirm.confirm":true,"material-receipt-confirm.printQrCode":true,"superuser":0,"manage_supers":0}',
                    'cycle_point' => '',
                    'parent_id' => 'r_01',
                    'next_step' => '{
                        "td_01": {
                          "tk_01": "approve",
                          "r_01": "approve"
                        },
                        "r_01": {
                          "r_01": "reject"
                        },
                        "tk_01": {
                          "tk_01": "reject"
                        },
                        "mh_01": {
                          "r_01": "approve",
                          "tk_01": "approve",
                          "rd_01": "approve"
                        }
                      }
                      ',
                    'location' => '
                      {
                        "offsetX": 550,
                        "offsetY": 250
                       }
                      ',
                    'group_code' => 'LDH_001',
                    'main_thread_status' => 'main_branch',
                ],
                [
                    'name' => 'khách hàng',
                    'code' => 'kh_01',
                    'roles_join' => '{"warehouse.index":true,"material-receipt-confirm.index":true,"material-receipt-confirm.create":true,"material-receipt-confirm.edit":true,"material-receipt-confirm.destroy":true,"material-receipt-confirm.confirm":true,"material-receipt-confirm.printQrCode":true,"superuser":0,"manage_supers":0}',
                    'cycle_point' => '',
                    'parent_id' => 'sale_01',
                    'next_step' => '{
                        "td_01": {
                          "td_01": "true"
                        }
                      }
                      ',
                    'location' => '
                      {
                        "offsetX": 750,
                        "offsetY": 110
                       }
                      ',
                    'group_code' => 'LDH_001',
                    'main_thread_status' => 'main_branch',
                ],
                [
                    'name' => 'mua hàng',
                    'code' => 'mh_01',
                    'roles_join' => '{"warehouse.index":true,"material-receipt-confirm.index":true,"material-receipt-confirm.create":true,"material-receipt-confirm.edit":true,"material-receipt-confirm.destroy":true,"material-receipt-confirm.confirm":true,"material-receipt-confirm.printQrCode":true,"superuser":0,"manage_supers":0}',
                    'cycle_point' => '',
                    'parent_id' => 'admin_001',
                    'next_step' => '{
                        "k_01": {
                          "admin_001": "approve"
                        }
                      }
                      ',
                    'location' => '
                      {
                        "offsetX": 550,
                        "offsetY": 420
                       }
                      ',
                    'group_code' => 'LDH_001',
                    'main_thread_status' => 'main_branch',
                ],
                [
                    'name' => 'kho',
                    'code' => 'k_01',
                    'roles_join' => '{"warehouse.index":true,"material-receipt-confirm.index":true,"material-receipt-confirm.create":true,"material-receipt-confirm.edit":true,"material-receipt-confirm.destroy":true,"material-receipt-confirm.confirm":true,"material-receipt-confirm.printQrCode":true,"superuser":0,"manage_supers":0}',
                    'cycle_point' => '',
                    'parent_id' => 'mh_01',
                    'next_step' => '{
                        "gc_01": {
                          "mh_01": "approve"
                        }
                      }
                      ',
                    'location' => '
                      {
                        "offsetX": 780,
                        "offsetY": 420
                       }
                      ',
                    'group_code' => 'LDH_001',
                    'main_thread_status' => 'main_branch',
                ],
                [
                    'name' => 'gia công',
                    'code' => 'gc_01',
                    'roles_join' => '{"warehouse.index":true,"material-receipt-confirm.index":true,"material-receipt-confirm.create":true,"material-receipt-confirm.edit":true,"material-receipt-confirm.destroy":true,"material-receipt-confirm.confirm":true,"material-receipt-confirm.printQrCode":true,"superuser":0,"manage_supers":0}',
                    'cycle_point' => '',
                    'parent_id' => 'k_01',
                    'next_step' => '{
                        "ktp_01": {
                          "k_01": "approve"
                        }
                      }',
                    'location' => '
                      {
                        "offsetX": 1000,
                        "offsetY": 420
                       }
                      ',
                    'group_code' => 'LDH_001',
                    'main_thread_status' => 'main_branch',
                ],
                [
                    'name' => 'kho thành phẩm',
                    'code' => 'ktp_01',
                    'roles_join' => '{"warehouse.index":true,"material-receipt-confirm.index":true,"material-receipt-confirm.create":true,"material-receipt-confirm.edit":true,"material-receipt-confirm.destroy":true,"material-receipt-confirm.confirm":true,"material-receipt-confirm.printQrCode":true,"superuser":0,"manage_supers":0}',
                    'cycle_point' => '',
                    'parent_id' => 'gc_01',
                    'next_step' => '{
                        "kh_02": {
                          "gc_01": "approve"
                        }
                      }',
                    'location' => '
                      {
                        "offsetX": 1200,
                        "offsetY": 420
                       }
                      ',
                    'group_code' => 'LDH_001',
                    'main_thread_status' => 'main_branch',
                ],

                [
                    'name' => 'khach_hang',
                    'code' => 'kh_02',
                    'roles_join' => '{"warehouse.index":true,"material-receipt-confirm.index":true,"material-receipt-confirm.create":true,"material-receipt-confirm.edit":true,"material-receipt-confirm.destroy":true,"material-receipt-confirm.confirm":true,"material-receipt-confirm.printQrCode":true,"superuser":0,"manage_supers":0}',
                    'cycle_point' => 'end',
                    'parent_id' => 'gc_01',
                    'next_step' => '',
                    'location' => '
                    {
                        "offsetX": 1450,
                        "offsetY": 420
                       }
                    ',
                    'group_code' => 'LDH_001',
                    'main_thread_status' => 'main_branch',
                ],
            ]
        );
    }
}
