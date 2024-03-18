<?php

return [
    [
        'name' => 'Page',
        'flag' => 'pages.index',
        'is_super' => true,
    ],
    [
        'name' => 'Create',
        'flag' => 'pages.create',
        'parent_flag' => 'pages.index',
    ],
    [
        'name' => 'Edit',
        'flag' => 'pages.edit',
        'parent_flag' => 'pages.index',
    ],
    [
        'name' => 'Delete',
        'flag' => 'pages.destroy',
        'parent_flag' => 'pages.index',
    ],
];
