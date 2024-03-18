<?php

namespace Botble\Agent\Exports;


use Maatwebsite\Excel\Concerns\FromCollection;
use Illuminate\Support\Collection;

class ReportExport implements FromCollection
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
