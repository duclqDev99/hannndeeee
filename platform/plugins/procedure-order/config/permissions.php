<?php

return [
    [
        'name' => 'Procedure orders',
        'flag' => 'procedure-order.index',
    ],
    [
        'name' => 'Create',
        'flag' => 'procedure-order.create',
        'parent_flag' => 'procedure-order.index',
    ],
    [
        'name' => 'Edit',
        'flag' => 'procedure-order.edit',
        'parent_flag' => 'procedure-order.index',
    ],
    [
        'name' => 'Delete',
        'flag' => 'procedure-order.destroy',
        'parent_flag' => 'procedure-order.index',
    ],

    [
        'name' => 'Procedure groups',
        'flag' => 'procedure-groups.index',
    ],
    [
        'name' => 'Create',
        'flag' => 'procedure-groups.create',
        'parent_flag' => 'procedure-groups.index',
    ],
    [
        'name' => 'Edit',
        'flag' => 'procedure-groups.edit',
        'parent_flag' => 'procedure-groups.index',
    ],
    [
        'name' => 'Delete',
        'flag' => 'procedure-groups.destroy',
        'parent_flag' => 'procedure-groups.index',
    ],
];
