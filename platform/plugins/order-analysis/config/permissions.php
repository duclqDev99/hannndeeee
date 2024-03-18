<?php

return [
    [
        'name' => 'Order analyses',
        'flag' => 'analysis-order.index',
    ],
        [
            'name' => 'Analyses',
            'flag' => 'analyses.index',
            'parent_flag' => 'analysis-order.index',
        ],
            [
                'name' => 'Create',
                'flag' => 'analyses.create',
                'parent_flag' => 'analyses.index',
            ],
            [
                'name' => 'Edit',
                'flag' => 'analyses.edit',
                'parent_flag' => 'analyses.index',
            ],
            [
                'name' => 'Delete',
                'flag' => 'analyses.destroy',
                'parent_flag' => 'analyses.index',
            ],
//order
        [
            'name' => 'Order',
            'flag' => 'order-analyses.index',
            'parent_flag' => 'analysis-order.index',
        ],
            [
                'name' => 'Thêm',
                'flag' => 'order-analyses.create',
                'parent_flag' => 'order-analyses.index',
            ],
            [
                'name' => 'Sửa',
                'flag' => 'order-analyses.edit',
                'parent_flag' => 'order-analyses.index',
            ],
            [
                'name' => 'Xóa',
                'flag' => 'order-analyses.destroy',
                'parent_flag' => 'order-analyses.index',
            ],
            [
                'name' => 'Admin',
                'flag' => 'order-analyses.completed',
                'parent_flag' => 'order-analyses.index',
            ],
];
