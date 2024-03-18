<?php

return [
    [
        'name' => 'System',
        'flag' => 'core.system',
        'is_super' => true,
    ],
    [
        'name' => 'Manage license',
        'flag' => 'core.manage.license',
        'parent_flag' => 'core.system',
    ],
    [
        'name' => 'View extensions page',
        'flag' => 'extensions.index',
        'parent_flag' => 'core.system',
    ],
];
