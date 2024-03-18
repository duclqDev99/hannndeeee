<?php

return [
    [
        'name' => 'QrCode sản phẩm',
        'flag' => 'qrcodeProductPlugin',
    ],
        [
            'name' => 'Xem',
            'flag' => 'product-qrcode.index',
            'parent_flag' => 'qrcodeProductPlugin',
        ],
        [
            'name' => 'Chi tiết',
            'flag' => 'product-qrcode.detail',
            'parent_flag' => 'qrcodeProductPlugin',
        ],
        [
            'name' => 'Thêm mới',
            'flag' => 'product-qrcode.create',
            'parent_flag' => 'qrcodeProductPlugin',
        ],
        [
            'name' => 'Cập nhật',
            'flag' => 'product-qrcode.edit',
            'parent_flag' => 'qrcodeProductPlugin',
        ],
        [
            'name' => 'Xóa',
            'flag' => 'product-qrcode.destroy',
            'parent_flag' => 'qrcodeProductPlugin',
        ],
        [
            'name' => 'Xuất file',
            'flag' => 'product-qrcode.export-qrcodes',
            'parent_flag' => 'qrcodeProductPlugin',
        ],
        [
            'name' => 'Tạo QrCode',
            'flag' => 'product-qrcode.export-temporary',
            'parent_flag' => 'qrcodeProductPlugin',
        ],
        [
            'name' => 'In QrCode',
            'flag' => 'product-qrcode.get-qrcode-by-id',
            'parent_flag' => 'qrcodeProductPlugin',
        ],
        [
            'name' => 'In-supper QrCode',
            'flag' => 'product-qrcode.in-super',
            'parent_flag' => 'qrcodeProductPlugin',
        ],
];
