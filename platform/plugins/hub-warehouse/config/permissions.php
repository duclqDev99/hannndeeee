<?php

return [
    [
        'name' => 'Quản lý HUB',
        'flag' => 'hub-warehouse-management.index',
    ],
    [
        'name' => 'Danh sách HUB',
        'flag' => 'hub-warehouse.index',
        'parent_flag' => 'hub-warehouse-management.index',
    ],
    [
        'name' => 'Toàn quyền với tất cả HUB',
        'flag' => 'hub-warehouse.all-permissions',
        'parent_flag' => 'hub-warehouse.index',
    ],
    [
        'name' => 'Thêm mới',
        'flag' => 'hub-warehouse.create',
        'parent_flag' => 'hub-warehouse.index',
    ],
    [
        'name' => 'Sửa',
        'flag' => 'hub-warehouse.edit',
        'parent_flag' => 'hub-warehouse.index',
    ],
    [
        'name' => 'Xóa',
        'flag' => 'hub-warehouse.destroy',
        'parent_flag' => 'hub-warehouse.index',
    ],

    // quản lý kho tồn hub
    [
        'name' => 'Danh sách kho',
        'flag' => 'hub-stock.index',
        'parent_flag' => 'hub-warehouse-management.index',
    ],
    [
        'name' => 'Thêm mới',
        'flag' => 'hub-stock.create',
        'parent_flag' => 'hub-stock.index',
    ],
    [
        'name' => 'Sửa',
        'flag' => 'hub-stock.edit',
        'parent_flag' => 'hub-stock.index',
    ],
    [
        'name' => 'Xóa',
        'flag' => 'hub-stock.destroy',
        'parent_flag' => 'hub-stock.index',
    ],

    // Đề xuất nhập kho
    [
        'name' => 'Đề xuất nhập kho HUB',
        'flag' => 'proposal-hub-receipt.index',
        'parent_flag' => 'hub-warehouse-management.index',
    ],
    [
        'name' => 'Duyệt đơn nhập kho',
        'flag' => 'proposal-hub-receipt.approve',
        'parent_flag' => 'proposal-hub-receipt.index',
    ],
    [
        'name' => 'Thêm mới',
        'flag' => 'proposal-hub-receipt.create',
        'parent_flag' => 'proposal-hub-receipt.index',
    ],
    [
        'name' => 'Sửa',
        'flag' => 'proposal-hub-receipt.edit',
        'parent_flag' => 'proposal-hub-receipt.index',
    ],
    [
        'name' => 'Xóa',
        'flag' => 'proposal-hub-receipt.destroy',
        'parent_flag' => 'proposal-hub-receipt.index',
    ],
    // phiếu nhập kho
    [
        'name' => 'Phiếu nhập kho',
        'flag' => 'hub-receipt.index',
        'parent_flag' => 'hub-warehouse-management.index',
    ],
    [
        'name' => 'Duyệt phiếu nhập kho',
        'flag' => 'hub-receipt.confirm',
        'parent_flag' => 'hub-receipt.index',
    ],
    [
        'name' => 'Hủy phiếu nhập kho',
        'flag' => 'hub-receipt.cancel',
        'parent_flag' => 'hub-receipt.index',
    ],

    // Đề xuất xuất hub
    [
        'name' => 'Đề xuất xuất kho HUB',
        'flag' => 'proposal-hub-issue.index',
        'parent_flag' => 'hub-warehouse-management.index',
    ],
    [
        'name' => 'Duyệt đơn xuất kho',
        'flag' => 'proposal-hub-issue.approve',
        'parent_flag' => 'proposal-hub-issue.index',
    ],
    [
        'name' => 'Thêm mới',
        'flag' => 'proposal-hub-issue.create',
        'parent_flag' => 'proposal-hub-issue.index',
    ],
    [
        'name' => 'Sửa',
        'flag' => 'proposal-hub-issue.edit',
        'parent_flag' => 'proposal-hub-issue.index',
    ],
    [
        'name' => 'Xóa',
        'flag' => 'proposal-hub-issue.destroy',
        'parent_flag' => 'proposal-hub-issue.index',
    ],


    [
        'name' => 'Phiếu xuất kho',
        'flag' => 'hub-issue.index',
        'parent_flag' => 'hub-warehouse-management.index',
    ],
    [
        'name' => 'Duyệt phiếu xuất kho',
        'flag' => 'hub-issue.confirm',
        'parent_flag' => 'hub-issue.index',
    ],
    [
        'name' => 'Hủy phiếu xuất kho',
        'flag' => 'hub-issue.denied',
        'parent_flag' => 'hub-issue.index',
    ],

    // Quyền sửa thông tin phòng ban
    [
        'name' => 'Cập nhật thông tin phòng ban',
        'flag' => 'users.edit-department',
        'parent_flag' => 'users.index',
    ],
    //sản phẩm lẻ
    // [
    //     'name' => 'Quản lý sản phẩm lẻ',
    //     'flag' => 'hub-stock.detail-odd',
    //     'parent_flag' => 'hub-warehouse-management.index',
    // ],
    [
        'name' => 'Thêm mới',
        'flag' => 'hub-stock.create-product-manual',
        'parent_flag' => 'hub-stock.detail-odd',
    ],
    [
        'name' => 'Sửa',
        'flag' => 'hub-stock.reduce-quantity',
        'parent_flag' => 'hub-stock.detail-odd',
    ],

    [
        'name' => 'Thành phẩm',
        'flag' => 'hub-product.index',
        'parent_flag' => 'hub-warehouse-management.index',
    ],

];
