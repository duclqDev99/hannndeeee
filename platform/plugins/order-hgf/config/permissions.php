<?php

return [
    [
        'name' => 'Đơn hàng HGF',
        'flag' => 'hgf.index',
    ],
    // admin
    [
        'name' => 'Admin',
        'flag' => 'hgf.admin.index',
        'parent_flag' => 'hgf.index',
    ],
    // admin YCSX
    [
        'name' => 'Yêu cầu sản xuất',
        'flag' => 'hgf.admin.purchase-order.index',
        'parent_flag' => 'hgf.admin.index',
    ],
    [
        'name' => 'Duyệt',
        'flag' => 'hgf.admin.purchase-order.confirm',
        'parent_flag' => 'hgf.admin.purchase-order.index',
    ],
   
  
       // admin production
       [
        'name' => 'Đơn đặt hàng',
        'flag' => 'hgf.admin.production.index',
        'parent_flag' => 'hgf.admin.index',
    ],
   
    [
        'name' => 'Duyệt',
        'flag' => 'hgf.admin.production.confirm',
        'parent_flag' => 'hgf.admin.production.index',
    ],
   
   
];
