<?php

return [
    [
        'name' => 'Đơn hàng Retail',
        'flag' => 'retail.index',
    ],

    // Tiến độ đơn hàng
    [
        'name' => 'Xem tiến độ',
        'flag' => 'retail.view-progress',
        'parent_flag' => 'retail.index',
    ],

    // Sale
    [
        'name' => 'Sale',
        'flag' => 'retail.sale.index',
        'parent_flag' => 'retail.index',
    ],
    // Sale YCSX
    [
        'name' => 'Yêu cầu sản xuất',
        'flag' => 'retail.sale.purchase-order.index',
        'parent_flag' => 'retail.sale.index',
    ],
    [
        'name' => 'Thêm',
        'flag' => 'retail.sale.purchase-order.create',
        'parent_flag' => 'retail.sale.purchase-order.index',
    ],
    [
        'name' => 'Sửa',
        'flag' => 'retail.sale.purchase-order.edit',
        'parent_flag' => 'retail.sale.purchase-order.index',
    ],
    [
        'name' => 'Gửi phê duyệt',
        'flag' => 'retail.sale.purchase-order.send',
        'parent_flag' => 'retail.sale.purchase-order.index',
    ],
    [
        'name' => 'Duyệt',
        'flag' => 'retail.sale.purchase-order.confirm',
        'parent_flag' => 'retail.sale.purchase-order.index',
    ],
    [
        'name' => 'Xóa',
        'flag' => 'retail.sale.purchase-order.destroy',
        'parent_flag' => 'retail.sale.purchase-order.index',
    ],

    // Sale quotation
    [
        'name' => 'Báo giá',
        'flag' => 'retail.sale.quotation.index',
        'parent_flag' => 'retail.sale.index',
    ],
    [
        'name' => 'Thêm',
        'flag' => 'retail.sale.quotation.create',
        'parent_flag' => 'retail.sale.quotation.index',
    ],
    [
        'name' => 'Sửa',
        'flag' => 'retail.sale.quotation.edit',
        'parent_flag' => 'retail.sale.quotation.index',
    ],
    [
        'name' => 'Gửi phê duyệt',
        'flag' => 'retail.sale.quotation.send',
        'parent_flag' => 'retail.sale.quotation.index',
    ],
    [
        'name' => 'Duyệt',
        'flag' => 'retail.sale.quotation.confirm',
        'parent_flag' => 'retail.sale.quotation.index',
    ],
    [
        'name' => 'Xóa',
        'flag' => 'retail.sale.quotation.destroy',
        'parent_flag' => 'retail.sale.quotation.index',
    ],

    [
        'name' => 'Kí hợp đồng với KH',
        'flag' => 'retail.sale.quotation.sign_contact',
        'parent_flag' => 'retail.sale.quotation.index',
    ],

       // Sale production
       [
        'name' => 'Đơn đặt hàng',
        'flag' => 'retail.sale.production.index',
        'parent_flag' => 'retail.sale.index',
    ],
    [
        'name' => 'Thêm',
        'flag' => 'retail.sale.production.create',
        'parent_flag' => 'retail.sale.production.index',
    ],
    [
        'name' => 'Sửa',
        'flag' => 'retail.sale.production.edit',
        'parent_flag' => 'retail.sale.production.index',
    ],
    [
        'name' => 'Gửi phê duyệt',
        'flag' => 'retail.sale.production.send',
        'parent_flag' => 'retail.sale.production.index',
    ],
    [
        'name' => 'Duyệt',
        'flag' => 'retail.sale.production.confirm',
        'parent_flag' => 'retail.sale.production.index',
    ],
    [
        'name' => 'Xóa',
        'flag' => 'retail.sale.production.destroy',
        'parent_flag' => 'retail.sale.production.index',
    ],


    //Accountant

    [
        'name' => 'Kế toán',
        'flag' => 'retail.accountant.index',
        'parent_flag' => 'retail.index',
    ],
    // Accountant quotation
    [
        'name' => 'Báo giá',
        'flag' => 'retail.accountant.quotation.index',
        'parent_flag' => 'retail.accountant.index',
    ],
    [
        'name' => 'Duyệt',
        'flag' => 'retail.accountant.quotation.confirm',
        'parent_flag' => 'retail.accountant.quotation.index',
    ],
];
