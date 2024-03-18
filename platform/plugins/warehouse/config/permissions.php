<?php

return [
    [
        'name' => 'Quản lý kho',
        'flag' => 'warehouse.index',
    ],
    [
        'name' => 'Kho nguyên phụ liệu',
        'flag' => 'warehouse-material-main.index',
        'parent_flag' => 'warehouse.index',
    ],
    [
        'name' => 'Nguyên phụ liệu',
        'flag' => 'material.index',
        'parent_flag' => 'warehouse-material-main.index',
    ],
    [
        'name' => 'Thêm mới',
        'flag' => 'material.create',
        'parent_flag' => 'material.index',
    ],

    [
        'name' => 'Sửa',
        'flag' => 'material.edit',
        'parent_flag' => 'material.index',
    ],
    [
        'name' => 'Xoá',
        'flag' => 'material.destroy',
        'parent_flag' => 'material.index',
    ],

    [
        'name' => 'Loại nguyên phụ liệu',
        'flag' => 'type_material.index',
        'parent_flag' => 'warehouse-material-main.index',
    ],
    [
        'name' => 'Thêm mới',
        'flag' => 'type_material.create',
        'parent_flag' => 'type_material.index',
    ],
    [
        'name' => 'Sửa',
        'flag' => 'type_material.edit',
        'parent_flag' => 'type_material.index',
    ],
    [
        'name' => 'Xoá',
        'flag' => 'type_material.destroy',
        'parent_flag' => 'type_material.index',
    ],

    [
        'name' => 'Nhà cung cấp',
        'flag' => 'supplier.index',
        'parent_flag' => 'warehouse.index',
    ],
    [
        'name' => 'Thêm mới',
        'flag' => 'supplier.create',
        'parent_flag' => 'supplier.index',
    ],
    [
        'name' => 'Sửa',
        'flag' => 'supplier.edit',
        'parent_flag' => 'supplier.index',
    ],
    [
        'name' => 'Xoá',
        'flag' => 'supplier.destroy',
        'parent_flag' => 'supplier.index',
    ],

    [
        'name' => 'Kho nguyên phụ liệu',
        'flag' => 'warehouse-material.index',
        'parent_flag' => 'warehouse-material-main.index',
    ],
    [
        'name' => 'Thêm mới',
        'flag' => 'warehouse-material.create',
        'parent_flag' => 'warehouse-material.index',
    ],
    [
        'name' => 'Sửa',
        'flag' => 'warehouse-material.edit',
        'parent_flag' => 'warehouse-material.index',
    ],
    [
        'name' => 'Xoá',
        'flag' => 'warehouse-material.destroy',
        'parent_flag' => 'warehouse-material.index',
    ],
    [
        'name' => 'Chi tiết kho',
        'flag' => 'warehouse-material.detail',
        'parent_flag' => 'warehouse-material.index',
    ],

    //Material proposal purchase
    [
        'name' => 'Đề xuất nhập kho',
        'flag' => 'material-proposal-purchase.index',
        'parent_flag' => 'warehouse-material-main.index',
    ],
    [
        'name' => 'Thêm mới',
        'flag' => 'material-proposal-purchase.create',
        'parent_flag' => 'material-proposal-purchase.index',
    ],
    [
        'name' => 'Sửa',
        'flag' => 'material-proposal-purchase.edit',
        'parent_flag' => 'material-proposal-purchase.index',
    ],
    [
        'name' => 'Xoá',
        'flag' => 'material-proposal-purchase.destroy',
        'parent_flag' => 'material-proposal-purchase.index',
    ],
    [
        'name' => 'Duyệt đơn đề',
        'flag' => 'material-proposal-purchase.receipt',
        'parent_flag' => 'material-proposal-purchase.index',
    ],
    //Receipt confirm
    [
        'name' => 'Phiếu nhập kho',
        'flag' => 'material-receipt-confirm.index',
        'parent_flag' => 'warehouse-material-main.index',
    ],
    [
        'name' => 'Xác nhận nhập kho',
        'flag' => 'material-receipt-confirm.confirm',
        'parent_flag' => 'material-receipt-confirm.index',
    ],
    [
        'name' => 'Print QR Code',
        'flag' => 'material-receipt-confirm.printQrCode',
        'parent_flag' => 'material-receipt-confirm.index',
    ],
    // Receipt confirm
    [
        'name' => 'Phiếu xuất kho',
        'flag' => 'goods-issue-receipt.index',
        'parent_flag' => 'warehouse-material-main.index',
    ],
    [
        'name' => 'Xác nhận xuất kho',
        'flag' => 'goods-issue-receipt.confirm',
        'parent_flag' => 'goods-issue-receipt.index',
    ],

    //Material proposal purchase
    [
        'name' => 'Đề xuất xuất kho',
        'flag' => 'proposal-goods-issue.index',
        'parent_flag' => 'warehouse-material-main.index',
    ],
    [
        'name' => 'Thêm mới',
        'flag' => 'proposal-goods-issue.create',
        'parent_flag' => 'proposal-goods-issue.index',
    ],
    [
        'name' => 'Sửa',
        'flag' => 'proposal-goods-issue.edit',
        'parent_flag' => 'proposal-goods-issue.index',
    ],
    [
        'name' => 'Xoá',
        'flag' => 'proposal-goods-issue.destroy',
        'parent_flag' => 'proposal-goods-issue.index',
    ],
    [
        'name' => 'Duyệt đơn đề xuất xuất kho',
        'flag' => 'proposal-goods-issue.receipt',
        'parent_flag' => 'proposal-goods-issue.index',
    ],


    //Material batch
    [
        'name' => 'Lô hàng trong kho',
        'flag' => 'material-batch.index',
        'parent_flag' => 'warehouse-material-main.index',
    ],
    [
        'name' => 'Chi tiết lô trong kho',
        'flag' => 'material-batch.detail',
        'parent_flag' => 'material-batch.index',
    ],

    //Đè xuất mua hàng
    [
        'name' => 'Đề xuất mua hàng',
        'flag' => 'proposal-purchase-goods.index',
        'parent_flag' => 'warehouse-material-main.index',
    ],
    [
        'name' => 'Thêm mới',
        'flag' => 'proposal-purchase-goods.create',
        'parent_flag' => 'proposal-purchase-goods.index',
    ],
    [
        'name' => 'Sửa',
        'flag' => 'proposal-purchase-goods.edit',
        'parent_flag' => 'proposal-purchase-goods.index',
    ],
    [
        'name' => 'Xoá',
        'flag' => 'proposal-purchase-goods.destroy',
        'parent_flag' => 'proposal-purchase-goods.index',
    ],
    [
        'name' => 'Duyệt đơn đề xuất mua hàng',
        'flag' => 'receipt-purchase-goods.receipt',
        'parent_flag' => 'proposal-purchase-goods.index',
    ],

    //Mua hàng
    [
        'name' => 'Phiếu mua hàng',
        'flag' => 'receipt-purchase-goods.index',
        'parent_flag' => 'warehouse-material-main.index',
    ],
    [
        'name' => 'Xác nhận mua hàng',
        'flag' => 'receipt-purchase-goods.confirm',
        'parent_flag' => 'receipt-purchase-goods.index',
    ],
    [
        'name' => 'Nhà gia công',
        'flag' => 'processing_house.index',
        'parent_flag' => 'warehouse.index',
    ],
    [
        'name' => 'Thêm mới',
        'flag' => 'processing_house.create',
        'parent_flag' => 'processing_house.index',
    ],
    [
        'name' => 'Sửa',
        'flag' => 'processing_house.edit',
        'parent_flag' => 'processing_house.index',
    ],
    [
        'name' => 'Xoá',
        'flag' => 'processing_house.destroy',
        'parent_flag' => 'processing_house.index',
    ],

];
