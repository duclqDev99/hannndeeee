<?php

namespace Botble\Agent\Models;

use Botble\Agent\Enums\AgentStatusEnum;
use Botble\Base\Casts\SafeContent;
use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Models\BaseModel;
use Botble\Ecommerce\Models\Product;

/**
 * @method static \Botble\Base\Models\BaseQueryBuilder<static> query()
 */
class AgentWarehouse extends BaseModel
{
    protected $table = 'agent_warehouse';

    protected $fillable = [
        'name',
        'agent_id',
        'description',
        'address',
        'status',
    ];

    protected $casts = [
        'status' => AgentStatusEnum::class,
        'name' => SafeContent::class,
    ];

    public function agent()
    {
        return $this->belongsTo(Agent::class, 'agent_id');
    }

    public function agentProduct()
    {
        return $this->hasMany(AgentProduct::class, 'warehouse_id');

    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'wfp_product_in_stock', 'stock_id')
            ->withPivot('quantity');
    }

    public function totalProductInStock($stock_id)
    {
        return AgentProductBatch::where(['warehouse_id' => $stock_id, 'warehouse_type' => AgentWarehouse::class])->sum('quantity');
    }

    // public function warehouseUsers()
    // {
    //     return $this->belongsToMany(
    //         User::class,
    //         'wfp_user_warehouse',
    //         'warehouse_id',
    //         'user_id'
    //     );
    // }
}
