<?php

return [
    [
        'name' => 'Customer book orders',
        'flag' => 'customer-book-order.index',
    ],
    [
        'name' => 'Create',
        'flag' => 'customer-book-order.create',
        'parent_flag' => 'customer-book-order.index',
    ],
    [
        'name' => 'Edit',
        'flag' => 'customer-book-order.edit',
        'parent_flag' => 'customer-book-order.index',
    ],
    [
        'name' => 'Delete',
        'flag' => 'customer-book-order.destroy',
        'parent_flag' => 'customer-book-order.index',
    ],
];
