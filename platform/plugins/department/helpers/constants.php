<?php

if (!defined('DEPARTMENT_MODULE_SCREEN_NAME')) {
    define('DEPARTMENT_MODULE_SCREEN_NAME', 'department');
}
if (!defined('DEPARTMENTS')) {
    define('DEPARTMENTS', collect([
        (object) ["id" => 1,"name" => 'Sale Retail', 'code' => 'retail_sale'],
        (object) ["id" => 2,"name" => 'Thiết kế Retail', 'code' => 'retail_tk'],
        (object) ["id" => 3,"name" => 'Rập HGF', 'code' => 'hgf_rap'],
        (object) ["id" => 5,"name" => 'HUB', 'code' => 'retail_hub'],
        (object) ["id" => 6,"name" => 'Đại lý', 'code' => 'agent'],
        (object) ["id" => 7,"name" => 'Kho thành phẩm', 'code' => 'retail_warehouse_product'],
        (object) ["id" => 8,"name" => 'ShowRoom', 'code' => 'show_room'],
        (object) ["id" => 9,"name" => 'Hgf Admin', 'code' => 'hgf_admin'],
        (object) ["id" => 10,"name" => 'Kế toán Retail', 'code' => 'retail_accountant'],
        (object) ["id" => 9,"name" => 'Kho sale', 'code' => 'sale_warehouse'],
    ]));
}
