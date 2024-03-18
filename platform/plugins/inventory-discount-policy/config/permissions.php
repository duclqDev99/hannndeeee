<?php

return [
    [
        'name' => 'Chính sách giảm giá',
        'flag' => 'inventory-discount-policy.index',
        'parent_flag' => 'sale-warehouse-index.index',

    ],
    [
        'name' => 'Thêm',
        'flag' => 'inventory-discount-policy.create',
        'parent_flag' => 'inventory-discount-policy.index',
    ],
    [
        'name' => 'Sửa',
        'flag' => 'inventory-discount-policy.edit',
        'parent_flag' => 'inventory-discount-policy.index',
    ],
    [
        'name' => 'Xóa',
        'flag' => 'inventory-discount-policy.destroy',
        'parent_flag' => 'inventory-discount-policy.index',
    ],
];
