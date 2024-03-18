<?php

namespace Botble\ProductQrcode\Models;

use Botble\Base\Casts\SafeContent;
use Botble\Base\Models\BaseModel;
use Botble\Ecommerce\Models\Product;
use Botble\ProductQrcode\Enums\QRStatusEnum;

/**
 * @method static \Botble\Base\Models\BaseQueryBuilder<static> query()
 */
class QrcodeTemporary extends BaseModel
{
    protected $table = 'wfp_qrcode_temporary';

    protected $fillable = [
        'times_product_id',
        'qr_code_base64',
    ];
}
