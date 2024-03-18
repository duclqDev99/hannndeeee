<?php

return [
    [
        'name' => 'Order transactions',
        'flag' => 'order-transaction.index',
    ],
    [
        'name' => 'Create',
        'flag' => 'order-transaction.create',
        'parent_flag' => 'order-transaction.index',
    ],
    [
        'name' => 'Edit',
        'flag' => 'order-transaction.edit',
        'parent_flag' => 'order-transaction.index',
    ],
    [
        'name' => 'Delete',
        'flag' => 'order-transaction.destroy',
        'parent_flag' => 'order-transaction.index',
    ],
];
