<?php

namespace Botble\SaleWarehouse\Models;

use Botble\Base\Models\BaseModel;
use Botble\Ecommerce\Models\Order;
use Botble\Ecommerce\Models\Product;
use Illuminate\Database\Eloquent\Casts\Attribute;

/**
 * @method static \Botble\Base\Models\BaseQueryBuilder<static> query()
 */
class SaleActualReceipt extends BaseModel
{
    protected $table = 'sw_sale_actual_receipt';
    protected $fillable = [
        'receipt_id',
        'image',
    ];

    public function actualDetail()
    {
        return $this->hasMany(SaleActualReceiptDetail::class, 'actual_id');
    }
    public function receipt()
    {
        return $this->hasOne(SaleReceipt::class, 'id', 'receipt_id');
    }

    public function title(): Attribute
    {
        return Attribute::get(function () {
            return $this->receipt()->first()->title;
        });
    }
}
