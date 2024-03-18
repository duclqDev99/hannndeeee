<?php

return [
    [
        'name' => 'Order step settings',
        'flag' => 'order-step-setting.index',
    ],
    [
        'name' => 'Create',
        'flag' => 'order-step-setting.create',
        'parent_flag' => 'order-step-setting.index',
    ],
    [
        'name' => 'Edit',
        'flag' => 'order-step-setting.edit',
        'parent_flag' => 'order-step-setting.index',
    ],
    [
        'name' => 'Delete',
        'flag' => 'order-step-setting.destroy',
        'parent_flag' => 'order-step-setting.index',
    ],
];
