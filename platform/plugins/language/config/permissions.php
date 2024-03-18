<?php

return [
    [
        'name' => 'Languages',
        'flag' => 'languages.index',
        'is_super' => true,
    ],
    [
        'name' => 'Create',
        'flag' => 'languages.create',
        'parent_flag' => 'languages.index',
    ],
    [
        'name' => 'Edit',
        'flag' => 'languages.edit',
        'parent_flag' => 'languages.index',
    ],
    [
        'name' => 'Delete',
        'flag' => 'languages.destroy',
        'parent_flag' => 'languages.index',
    ],
];
