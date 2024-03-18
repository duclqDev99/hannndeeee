<?php

namespace Botble\HubWarehouse\Models;

use Botble\Ecommerce\Models\Product;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IssueInputTour extends Model
{
    use HasFactory;

    protected $table = 'hb_issue_input_tour';

    protected $fillable = [
        'proposal_issues_id',
        'qrcode_id',
        'where_type',
        'where_id',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
