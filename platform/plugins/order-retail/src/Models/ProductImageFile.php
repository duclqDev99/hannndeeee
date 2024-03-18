<?php

namespace Botble\OrderRetail\Models;

use Botble\Base\Models\BaseModel;
use Botble\Media\Services\UploadsManager;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;

class ProductImageFile extends BaseModel
{
    protected $table = 'retail_product_image_files';

    protected $fillable = [
        'retail_product_id',
        'product_id',
        'url',
        'extras',
    ];

    protected $casts = [
        'extras' => 'json',
    ];

    protected static function booted(): void
    {
        self::deleted(function (ProductImageFile $file) {
            app(UploadsManager::class)->deleteFile($file);
        });
    }

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
