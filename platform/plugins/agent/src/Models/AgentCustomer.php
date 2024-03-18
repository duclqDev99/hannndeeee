<?php

namespace Botble\Agent\Models;

use Botble\Base\Models\BaseModel;
use Botble\Ecommerce\Models\Customer;

/**
 * @method static \Botble\Base\Models\BaseQueryBuilder<static> query()
 */
class AgentCustomer extends BaseModel
{
    protected $table = 'agent_customers';
    protected $fillable = [
        'customer_id',
        'where_type',
        'where_id',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

}
