<?php

namespace Botble\OrderRetail\Models;

use Botble\Base\Models\BaseModel;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Arr;

class Contract extends BaseModel
{
    protected $table = 'retail_contracts';

    protected $fillable = [
        'quotation_id',
        'url',
        'extras',
    ];

    protected $casts = [
        'extras' => 'json',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(OrderProduct::class, 'retail_product_id')->withDefault();
    }

    protected function fileName(): Attribute
    {
        return Attribute::get(fn () => Arr::get($this->extras, 'name', ''));
    }

    protected function fileSize(): Attribute
    {
        return Attribute::get(fn () => Arr::get($this->extras, 'size', ''));
    }

    protected function mimeType(): Attribute
    {
        return Attribute::get(fn () => Arr::get($this->extras, 'mime_type', ''));
    }

    protected function fileExtension(): Attribute
    {
        return Attribute::get(fn () => Arr::get($this->extras, 'extension', ''));
    }

    protected function basename(): Attribute
    {
        return Attribute::get(fn () => $this->file_name . ($this->file_extension ? '.' . $this->file_extension : ''));
    }

    protected function isExternalLink(): Attribute
    {
        return Attribute::get(fn () => Arr::get($this->extras, 'is_external', false));
    }
}
