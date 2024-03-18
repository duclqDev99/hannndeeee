<?php

return [
    [
        'name' => 'Quản lý đại lý',
        'flag' => 'agent.manager',
    ],
    [
        'name' => 'Đại lý',
        'flag' => 'agent.index',
        'parent_flag' => 'agent.manager',

    ],
    [
        'name' => 'Thêm',
        'flag' => 'agent.create',
        'parent_flag' => 'agent.index',
    ],
    [
        'name' => 'Sửa',
        'flag' => 'agent.edit',
        'parent_flag' => 'agent.index',
    ],
    [
        'name' => 'Xóa',
        'flag' => 'agent.destroy',
        'parent_flag' => 'agent.index',
    ],

    [
        'name' => 'Toàn quyền trên tất cả đại lý',
        'flag' => 'agent.all',
        'parent_flag' => 'agent.index',
    ],

    [
        'name' => 'Kho đại lý',
        'flag' => 'agent-warehouse.index',
        'parent_flag' => 'agent.manager',

    ],
    [
        'name' => 'Thêm',
        'flag' => 'agent-warehouse.create',
        'parent_flag' => 'agent-warehouse.index',
    ],
    [
        'name' => 'Sửa',
        'flag' => 'agent-warehouse.edit',
        'parent_flag' => 'agent-warehouse.index',
    ],
    [
        'name' => 'Xóa',
        'flag' => 'agent-warehouse.destroy',
        'parent_flag' => 'agent-warehouse.index',
    ],

    [
        'name' => 'Đề xuất nhập kho',
        'flag' => 'proposal-agent-receipt.index',
        'parent_flag' => 'agent.manager'
    ],
    [
        'name' => 'Thêm',
        'flag' => 'proposal-agent-receipt.create',
        'parent_flag' => 'proposal-agent-receipt.index',
    ],
    [
        'name' => 'Sửa',
        'flag' => 'proposal-agent-receipt.edit',
        'parent_flag' => 'proposal-agent-receipt.index',
    ],
    [
        'name' => 'Xóa',
        'flag' => 'proposal-agent-receipt.destroy',
        'parent_flag' => 'proposal-agent-receipt.index',
    ],
    [
        'name' => 'Phiếu nhập kho',
        'flag' => 'agent-receipt.index',
        'parent_flag' => 'agent.manager'
    ],
    [
        'name' => 'Duyệt phiếu nhập kho',
        'flag' => 'agent-receipt.confirm',
        'parent_flag' => 'agent-receipt.index',
    ],
    [
        'name' => 'Từ chối nhập kho',
        'flag' => 'agent-receipt.denied',
        'parent_flag' => 'agent-receipt.index',
    ],
    [
        'name' => 'Sản phẩm lô',
        'flag' => 'agent-warehouse.detail-batch',
        'parent_flag' => 'agent-warehouse.index',
    ],
    // [
    //     'name' => 'Sản phẩm lẻ',
    //     'flag' => 'agent-warehouse.detail-odd',
    //     'parent_flag' => 'agent-warehouse.index',
    // ],
    [
        'name' => 'Thêm',
        'flag' => 'agent-warehouse.create-product-manual',
        'parent_flag' => 'agent-warehouse.detail-odd',
    ],
    [
        'name' => 'Sửa',
        'flag' => 'agent-warehouse.reduce-quantity',
        'parent_flag' => 'agent-warehouse.detail-odd',
    ],
    //report

    [
        'name' => 'Thống kê',
        'flag' => 'agent.report.index',
        'parent_flag' => 'agent.manager'
    ],
    //order
    [
        'name' => 'Thanh toán',
        'flag' => 'agent.orders.index',
        'parent_flag' => 'agent.manager'
    ],
        [
            'name' => 'Tạo đơn',
            'flag' => 'agent.orders.create',
            'parent_flag' => 'agent.orders.index'
        ],
    // Tạo Đơn đề xuất
        [
            'name' => 'Đề xuất xuất kho',
            'flag' => 'agent-proposal-issue.index',
            'parent_flag' => 'agent.manager'
        ],
        [
            'name' => 'Thêm',
            'flag' => 'agent-proposal-issue.create',
            'parent_flag' => 'agent-proposal-issue.index',
        ],
        [
            'name' => 'Sửa',
            'flag' => 'agent-proposal-issue.edit',
            'parent_flag' => 'agent-proposal-issue.index',
        ],
        [
            'name' => 'Xóa',
            'flag' => 'agent-proposal-issue.destroy',
            'parent_flag' => 'agent-proposal-issue.index',
        ],
        [
            'name' => 'Duyệt đơn xuất kho',
            'flag' => 'agent-proposal-issue.approve',
            'parent_flag' => 'agent-proposal-issue.index',
        ],


        [
            'name' => 'Phiếu xuất kho',
            'flag' => 'agent-issue.index',
            'parent_flag' => 'agent.manager'
        ],
        [
            'name' => 'Duyệt phiếu xuất kho',
            'flag' => 'agent-issue.confirm',
            'parent_flag' => 'agent-issue.index',
        ],

];
