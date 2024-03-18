<?php
namespace Botble\ProductQrcode\Actions;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class ExportQrCodeFormViewAction implements FromView
{
    protected $collection;
    protected $headings;

    public function setCollection($collection)
    {
        $this->collection = $collection;
    }

    public function setHeadings(array $headings)
    {
        $this->headings = $headings;
    }
    public function collection() {
        return collect($this->collection);
    }

    public function headings(): array
    {
        return $this->headings;
    }
    public function view(): View
    {
        return view('plugins/product-qrcode::export-qrcode', [
            'collection' => $this->collection,
            'headings' => $this->headings,
        ]);
    }
}
