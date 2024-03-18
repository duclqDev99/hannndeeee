<?php

namespace Botble\Warehouse\Actions;
use Botble\Table\Supports\TableExportHandler;
use Botble\Warehouse\Models\Material;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
class ExportMaterialAction extends TableExportHandler
{ 

    public function collection() {
        $select = ['code','name','unit','min','description'];
        $materialCollect = Material::select($select)->get();
        return collect($materialCollect);
    }

    public function headings(): array
    {
        
        $headings = [
            'Mã hàng',
            'Tên hàng',
            'Đơn vị tính',
            'Số lượng tối thiểu',
            'Mô tả',
            
        ];
        return $headings;
    }
    protected function afterSheet(AfterSheet $event)
    {
        parent::afterSheet($event);
        $highestRowAndColumn = $event->sheet->getHighestRowAndColumn();
        $event->sheet->getDelegate()->getStyle('A1:' . $highestRowAndColumn['column'] . $highestRowAndColumn['row'])->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

    }

}

