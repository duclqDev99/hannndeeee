<?php

return [
    [
        'name' => 'Kho thành phẩm',
        'flag' => 'warehouse-finished-products.index',
        'parent_flag' => 'warehouse.index',
    ],
    [
        'name' => 'Danh sách thành phẩm',
        'flag' => 'finished-products.index',
        'parent_flag' => 'warehouse-finished-products.index',
    ],
    [
        'name' => 'Danh sách kho',
        'flag' => 'warehouse-finished-product.index',
        'parent_flag' => 'warehouse-finished-products.index'
    ],
    [
        'name' => 'Thêm mới',
        'flag' => 'warehouse-finished-products.create',
        'parent_flag' => 'warehouse-finished-product.index',
    ],
    [
        'name' => 'Xóa',
        'flag' => 'warehouse-finished-products.edit',
        'parent_flag' => 'warehouse-finished-product.index',
    ],
    [
        'name' => 'Sửa',
        'flag' => 'warehouse-finished-products.destroy',
        'parent_flag' => 'warehouse-finished-product.index',
    ],
    [
        'name' => 'Toàn quyền tất cả các kho',
        'flag' => 'warehouse-finished-products.warehouse-all',
        'parent_flag' => 'warehouse-finished-product.index',
    ],

    // Đơn đề xuất nhập kho thành phẩm
    [
        'name' => 'Đề xuất xuất kho',
        'flag' => 'proposal-product-issue.index',
        'parent_flag' => 'warehouse-finished-products.index',
    ],
    [
        'name' => 'Thêm mới',
        'flag' => 'proposal-product-issue.create',
        'parent_flag' => 'proposal-product-issue.index',
    ],
    [
        'name' => 'Sửa',
        'flag' => 'proposal-product-issue.edit',
        'parent_flag' => 'proposal-product-issue.index',
    ],
    [
        'name' => 'Xóa',
        'flag' => 'proposal-product-issue.destroy',
        'parent_flag' => 'proposal-product-issue.index',
    ],
    [
        'name' => 'Duyệt đơn xuất kho',
        'flag' => 'proposal-product-issue.examine',
        'parent_flag' => 'proposal-product-issue.index',
    ],
    [
        'name' => 'Chuyển đơn xuất kho',
        'flag' => 'proposal-product-issue.approve',
        'parent_flag' => 'proposal-product-issue.index',
    ],
    // Phiếu nhập kho
    [
        'name' => 'Phiếu xuất kho',
        'flag' => 'product-issue.index',
        'parent_flag' => 'warehouse-finished-products.index',
    ],
    [
        'name' => 'Xác nhận xuất kho',
        'flag' => 'product-issue.confirm',
        'parent_flag' => 'product-issue.index',
    ],
    [
        'name' => 'Từ chối xuất kho',
        'flag' => 'product-issue.denied',
        'parent_flag' => 'product-issue.index',
    ],


    // Đơn đề xuất nhập kho thành phẩm
    [
        'name' => 'Đề xuất nhập kho',
        'flag' => 'proposal-receipt-products.index',
        'parent_flag' => 'warehouse-finished-products.index',
    ],
    [
        'name' => 'Tạo mới',
        'flag' => 'proposal-receipt-products.create',
        'parent_flag' => 'proposal-receipt-products.index',
    ],
    [
        'name' => 'Sửa',
        'flag' => 'proposal-receipt-products.edit',
        'parent_flag' => 'proposal-receipt-products.index',
    ],
    [
        'name' => 'Xoá',
        'flag' => 'proposal-receipt-products.destroy',
        'parent_flag' => 'proposal-receipt-products.index',
    ],
    [
        'name' => 'Duyệt đơn nhập kho',
        'flag' => 'proposal-receipt-products.censorship',
        'parent_flag' => 'proposal-receipt-products.index',
    ],

    // Phiếu nhập kho
    [
        'name' => 'Phiếu nhập kho',
        'flag' => 'receipt-product.index',
        'parent_flag' => 'warehouse-finished-products.index',
    ],
    [
        'name' => 'Xác nhận nhập kho',
        'flag' => 'receipt-product.censorship',
        'parent_flag' => 'receipt-product.index',
    ],
    [
        'name' => 'Từ chối nhập kho',
        'flag' => 'receipt-product.cancel',
        'parent_flag' => 'receipt-product.index',
    ],
    //sản phẩm lẻ
    [
        'name' => 'Quản lý sản phẩm lẻ',
        'flag' => 'warehouse-finished-products.detail-odd',
        'parent_flag' => 'warehouse-finished-products.index'
    ],
    [
        'name' => 'Thêm mới',
        'flag' => 'warehouse-finished-products.create-product-manual',
        'parent_flag' => 'warehouse-finished-products.detail-odd',
    ],
    [
        'name' => 'Sửa',
        'flag' => 'warehouse-finished-products.reduce-quantity',
        'parent_flag' => 'warehouse-finished-products.detail-odd',
    ],
];
