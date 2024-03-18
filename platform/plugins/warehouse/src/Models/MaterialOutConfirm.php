<?php

namespace Botble\Warehouse\Models;

use Botble\Base\Casts\SafeContent;
use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Models\BaseModel;
use Botble\Warehouse\Enums\GoodsIssueEnum;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @method static \Botble\Base\Models\BaseQueryBuilder<static> query()
 */
class MaterialOutConfirm extends BaseModel
{
    protected $table = 'wh_material_out_confirm';

    protected $fillable = [
        'warehouse_id',
        'invoice_issuer_name',
        'invoice_confirm_name',
        'proposal_id',
        'document_number',
        'warehouse_name',
        'warehouse_address',
        'quantity',
        'total_amount',
        'tax_amount',
        'title',
        'description',
        'expected_date',
        'date_confirm',
        'status',
        'general_order_code',
    ];

    protected $casts = [
        'name' => SafeContent::class,
        'status' => GoodsIssueEnum::class
    ];

    public function proposalOutDetail(): HasMany
    {
        return $this->hasMany(MaterialOutConfirmDetail::class, 'out_id', 'id')->with('batch');
    }
    public function confirmOutDetail(): HasMany
    {
        return $this->hasMany(MaterialOutConfirmDetail::class, 'out_id', 'id');
    }

    public function batchMaterials(): HasManyThrough
    {
        return $this->hasManyThrough(
            MaterialBatch::class,
            MaterialOutConfirmDetail::class,
            'out_id',
            'material_code',
            'id',
            'material_code'
        )->orderBy('created_at')->where('quantity', '>', '0');
    }

    public function proposal()
    {
        return $this->belongsTo(MaterialOut::class, 'proposal_id', 'id');
    }


}
