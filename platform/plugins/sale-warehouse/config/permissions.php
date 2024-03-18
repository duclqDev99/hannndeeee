<?php

return [


    [
        'name' => 'Kho sale',
        'flag' => 'sale-warehouse-index.index',
    ],

    [
        'name' => 'Danh sách kho sale',
        'flag' => 'sale-warehouse.index',
        'parent_flag' => 'sale-warehouse-index.index',

    ],
    [
        'name' => 'Thêm',
        'flag' => 'sale-warehouse.create',
        'parent_flag' => 'sale-warehouse.index',
    ],
    [
        'name' => 'Sửa',
        'flag' => 'sale-warehouse.edit',
        'parent_flag' => 'sale-warehouse.index',
    ],
    [
        'name' => 'Xóa',
        'flag' => 'sale-warehouse.destroy',
        'parent_flag' => 'sale-warehouse.index',
    ],
    [
        'name' => 'Toàn quyền trên kho sale',
        'flag' => 'sale-warehouse.all',
        'parent_flag' => 'sale-warehouse.index',
    ],



    [
        'name' => 'Danh sách kho',
        'flag' => 'sale-warehouse-child.index',
        'parent_flag' => 'sale-warehouse-index.index',

    ],
    [
        'name' => 'Thêm',
        'flag' => 'sale-warehouse-child.create',
        'parent_flag' => 'sale-warehouse-child.index',
    ],
    [
        'name' => 'Xóa',
        'flag' => 'sale-warehouse-child.edit',
        'parent_flag' => 'sale-warehouse-child.index',
    ],
    [
        'name' => 'Sửa',
        'flag' => 'sale-warehouse-child.destroy',
        'parent_flag' => 'sale-warehouse-child.index',
    ],


    [
        'name' => 'Phiếu nhập kho',
        'flag' => 'sale-receipt.index',
        'parent_flag' => 'sale-warehouse-index.index',
    ],
    [
        'name' => 'Duyệt phiếu nhập kho',
        'flag' => 'sale-receipt.confirm',
        'parent_flag' => 'sale-receipt.index',
    ],
    [
        'name' => 'Hủy phiếu nhập kho',
        'flag' => 'sale-receipt.denied',
        'parent_flag' => 'sale-receipt.index',
    ],


    [
        'name' => 'Đề xuất xuất kho',
        'flag' => 'sale-proposal-issue.index',
        'parent_flag' => 'sale-warehouse-index.index',
    ],
    [
        'name' => 'Thêm',
        'flag' => 'sale-proposal-issue.create',
        'parent_flag' => 'sale-proposal-issue.index',
    ],
    [
        'name' => 'Sửa',
        'flag' => 'sale-proposal-issue.edit',
        'parent_flag' => 'sale-proposal-issue.index',
    ],
    [
        'name' => 'Xóa',
        'flag' => 'sale-proposal-issue.destroy',
        'parent_flag' => 'sale-proposal-issue.index',
    ],
    [
        'name' => 'Duyệt đơn xuất kho',
        'flag' => 'sale-proposal-issue.approve',
        'parent_flag' => 'sale-proposal-issue.index',
    ],
    [
        'name' => 'Từ chối xuất kho',
        'flag' => 'sale-proposal-issue.denied',
        'parent_flag' => 'sale-proposal-issue.index',
    ],


    [
        'name' => 'Phiếu xuất kho',
        'flag' => 'sale-issue.index',
        'parent_flag' => 'sale-warehouse-index.index',
    ],
    [
        'name' => 'Duyệt phiếu xuất kho',
        'flag' => 'sale-issue.confirm',
        'parent_flag' => 'sale-issue.index',
    ],
    [
        'name' => 'Hủy phiếu xuất kho',
        'flag' => 'sale-issue.denied',
        'parent_flag' => 'sale-issue.index',
    ],
    [
        'name' => 'Sản phẩm',
        'flag' => 'sale-product.index',
        'parent_flag' => 'sale-warehouse-index.index',
    ],
];
