<?php

namespace Botble\ProductQrcode\Exports;


use Maatwebsite\Excel\Concerns\FromCollection;
use Illuminate\Support\Collection;

class ProductQrcodeExport implements FromCollection
{
    protected $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function collection()
    {
        return new Collection($this->data);
    }
}
