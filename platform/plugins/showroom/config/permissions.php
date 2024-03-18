<?php

return [
    [
        'name' => 'Showrooms',
        'flag' => 'showroom.management',
    ],
    /**
     * Danh sách
     */
        [
            'name' => 'Danh sách',
            'flag' => 'showroom.index',
            'parent_flag' => 'showroom.management',
        ],
        [
            'name' => 'Create',
            'flag' => 'showroom.create',
            'parent_flag' => 'showroom.index',
        ],
        [
            'name' => 'Edit',
            'flag' => 'showroom.edit',
            'parent_flag' => 'showroom.index',
        ],
        [
            'name' => 'Delete',
            'flag' => 'showroom.destroy',
            'parent_flag' => 'showroom.index',
        ],
        [
            'name' => 'Toàn quyền trên tất cả showroom',
            'flag' => 'showroom.all',
            'parent_flag' => 'showroom.management',
        ],

    /**
     * báo cáo
     */
        [
            'name' => 'Báo cáo',
            'flag' => 'showroom.report.index',
            'parent_flag' => 'showroom.management',
        ],
    /**
     * đơn hàng
     */
        [
            'name' => 'Đơn hàng',
            'flag' => 'showroom.orders.index',
            'parent_flag' => 'showroom.management',
        ],
        [
            'name' => 'Tạo đơn',
            'flag' => 'showroom.orders.create',
            'parent_flag' => 'showroom.orders.index'
        ],
        [
            'name' => 'Sửa đơn',
            'flag' => 'showroom.orders.edit',
            'parent_flag' => 'showroom.orders.index'
        ],
        [
            'name' => 'Tạo khách hàng',
            'flag' => 'showroom.orders.create.store',
            'parent_flag' => 'showroom.orders.index'
        ],
        [
            'name' => 'Thanh toán cho khách hàng',
            'flag' => 'showroom.orders.checkout-payment',
            'parent_flag' => 'showroom.orders.index'
        ],
        // Đề xuất nhập kho

        [
            'name' => 'Đề xuất nhập showroom',
            'flag' => 'proposal-showroom-receipt.index',
            'parent_flag' => 'showroom.management',
        ],
        [
            'name' => 'Duyệt đơn nhập kho',
            'flag' => 'proposal-showroom-receipt.approve',
            'parent_flag' => 'proposal-showroom-receipt.index',
        ],
        [
            'name' => 'Thêm',
            'flag' => 'proposal-showroom-receipt.create',
            'parent_flag' => 'proposal-showroom-receipt.index',
        ],
        [
            'name' => 'Sửa',
            'flag' => 'proposal-showroom-receipt.edit',
            'parent_flag' => 'proposal-showroom-receipt.index',
        ],
        [
            'name' => 'Xóa',
            'flag' => 'proposal-showroom-receipt.destroy',
            'parent_flag' => 'proposal-showroom-receipt.index',
        ],

        // Phiếu nhập kho

        [
            'name' => 'Phiếu nhập kho',
            'flag' => 'showroom-receipt.index',
            'parent_flag' => 'showroom.management',

        ],
        [
            'name' => 'Duyệt phiếu nhập kho',
            'flag' => 'showroom-receipt.confirm',
            'parent_flag' => 'showroom-receipt.index',

        ],

        // Yêu cầu trả hàng
        [
            'name' => 'Yêu cầu trả hàng',
            'flag' => 'showroom-proposal-issue.index',
            'parent_flag' => 'showroom.management',
        ],
        [
            'name' => 'Duyệt đơn trả hàng',
            'flag' => 'showroom-proposal-issue.approve',
            'parent_flag' => 'showroom-proposal-issue.index',
        ],
        [
            'name' => 'Thêm',
            'flag' => 'showroom-proposal-issue.create',
            'parent_flag' => 'showroom-proposal-issue.index',
        ],
        [
            'name' => 'Sửa',
            'flag' => 'showroom-proposal-issue.edit',
            'parent_flag' => 'showroom-proposal-issue.index',
        ],
        [
            'name' => 'Xóa',
            'flag' => 'showroom-proposal-issue.destroy',
            'parent_flag' => 'showroom-proposal-issue.index',
        ],

        // Phiếu xuất hàng trả
        [
            'name' => 'Phiếu nhập kho',
            'flag' => 'showroom-receipt.index',
            'parent_flag' => 'showroom.management',

        ],
        [
            'name' => 'Duyệt phiếu nhập kho',
            'flag' => 'showroom-receipt.confirm',
            'parent_flag' => 'showroom-receipt.index',

        ],
        [
            'name' => 'Từ chối nhập kho',
            'flag' => 'showroom-receipt.denied',
            'parent_flag' => 'showroom-receipt.index',

        ],


        [
            'name' => 'Phiếu xuất kho',
            'flag' => 'showroom-issue.index',
            'parent_flag' => 'showroom.management',
        ],
        [
            'name' => 'Duyệt phiếu xuất kho',
            'flag' => 'showroom-issue.confirm',
            'parent_flag' => 'showroom-issue.index',
        ],

        [
            'name' => 'Danh sách kho Showroom',
            'flag' => 'showroom-warehouse.index',
            'parent_flag' => 'showroom.management',
        ],
        [
            'name' => 'Thêm',
            'flag' => 'showroom-warehouse.create',
            'parent_flag' => 'showroom-warehouse.index',
        ],
        [
            'name' => 'Sửa',
            'flag' => 'showroom-warehouse.edit',
            'parent_flag' => 'showroom-warehouse.index',
        ],
        [
            'name' => 'Quét QR',
            'flag' => 'showroom-qr.check-qr',
            'parent_flag' => 'showroom.management',
        ],

        [
            'name' => 'Đổi trả hàng',
            'flag' => 'exchange-goods.index',
            'parent_flag' => 'showroom.index',
        ],
        [
            'name' => 'Tạo phiếu',
            'flag' => 'exchange-goods.create',
            'parent_flag' => 'exchange-goods.index',
        ],
        [
            'name' => 'Xem chi tiết',
            'flag' => 'exchange-goods.view',
            'parent_flag' => 'exchange-goods.index',
        ],
];
