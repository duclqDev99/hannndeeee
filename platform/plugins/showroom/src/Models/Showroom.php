<?php

namespace Botble\Showroom\Models;

use App\Models\User;
use Botble\Base\Casts\SafeContent;
use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Models\BaseModel;
use Botble\Ecommerce\Models\Order;
use Botble\HubWarehouse\Models\HubWarehouse;
use Botble\Showroom\Traits\ShowroomPickAddressTrait;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * @method static \Botble\Base\Models\BaseQueryBuilder<static> query()
 */
class Showroom extends BaseModel
{
    use ShowroomPickAddressTrait;
    
    protected $table = 'showrooms';

    protected $fillable = [
        'name',
        'phone_number',
        'description',
        'address',
        'hub_id',
        'status',
        'provider_banking',
        'code',
        'lat',
        'lon'
    ];

    protected $casts = [
        'status' => BaseStatusEnum::class,
        'name' => SafeContent::class,
    ];

    public function showroomOrders()
    {
        return $this->morphMany(ShowroomOrder::class, 'where');
    }


    public function orders()
    {
        return $this->morphMany(Order::class, 'where', 'where_type', 'where_id');
    }
    public function users()
    {
        return $this->belongsToMany(User::class, ShowroomUser::class);
    }
    public function hub()
    {
        return $this->belongsTo(HubWarehouse::class, 'hub_id', 'id');

    }
    public function showroomWarehouses()
    {
        // ???
        return $this->belongsToMany(ShowroomWarehouse::class, 'id', 'showroom_id');
    }

    public function warehouses():HasMany
    {
        return $this->hasMany(ShowroomWarehouse::class, 'showroom_id');
    }
}
