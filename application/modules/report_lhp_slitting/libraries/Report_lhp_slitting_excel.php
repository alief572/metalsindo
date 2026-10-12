<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Report_lhp_slitting_excel
{
    public function build($headers, $rows)
    {
        $book = new PHPExcel();
        $sheet = $book->getActiveSheet();
        $sheet->setTitle('Report LHP Slitting (Final)');
        $sheet->mergeCells('A1:E1')->mergeCells('F1:I1')->mergeCells('J1:R1');
        $sheet->setCellValue('A1','A. Info')->setCellValue('F1','B. Produksi')
            ->setCellValue('J1','C. Summary Produksi & Sisa')->setCellValue('S1','D. Penimbangan Mother Coil');
        foreach ($headers as $column=>$label) $sheet->setCellValueByColumnAndRow($column,2,$label);
        $line = 3;
        foreach ($rows as $row) {
            foreach ($row as $column=>$value) {
                $cell = PHPExcel_Cell::stringFromColumnIndex($column).$line;
                if ($value === null) continue;
                if ($column === 3 && Report_lhp_slitting_model::valid_date($value)) {
                    $date = PHPExcel_Shared_Date::FormattedPHPToExcel((int)substr($value,0,4),(int)substr($value,5,2),(int)substr($value,8,2));
                    $sheet->setCellValueExplicit($cell,$date,PHPExcel_Cell_DataType::TYPE_NUMERIC);
                    $sheet->getStyle($cell)->getNumberFormat()->setFormatCode('dd-mm-yyyy');
                } elseif (is_int($value) || is_float($value)) {
                    $sheet->setCellValueExplicit($cell,$value,PHPExcel_Cell_DataType::TYPE_NUMERIC);
                    $sheet->getStyle($cell)->getNumberFormat()->setFormatCode('#,##0.00');
                } else {
                    $sheet->setCellValueExplicit($cell,(string)$value,PHPExcel_Cell_DataType::TYPE_STRING);
                }
            }
            $line++;
        }
        $sheet->getStyle('A1:S2')->applyFromArray(array(
            'font'=>array('bold'=>true,'color'=>array('rgb'=>'FFFFFF')),
            'fill'=>array('type'=>PHPExcel_Style_Fill::FILL_SOLID,'color'=>array('rgb'=>'195472')),
            'alignment'=>array('horizontal'=>PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                'vertical'=>PHPExcel_Style_Alignment::VERTICAL_CENTER,'wrap'=>true)
        ));
        $sheet->getRowDimension(1)->setRowHeight(42);
        $sheet->getRowDimension(2)->setRowHeight(48);
        foreach (range(0,18) as $column) $sheet->getColumnDimension(PHPExcel_Cell::stringFromColumnIndex($column))->setWidth(20);
        foreach (array('A'=>28,'B'=>28,'C'=>40,'D'=>18,'E'=>55,'H'=>40,'S'=>28) as $column=>$width)
            $sheet->getColumnDimension($column)->setWidth($width);
        $sheet->freezePane('F3');
        $sheet->setAutoFilter('A2:S'.max(2,$line-1));
        // Keep the visible note outside the autofilter, like the supplied Final concept.
        $noteRow = $line+1;
        $sheet->mergeCells('A'.$noteRow.':S'.$noteRow);
        $sheet->setCellValue('A'.$noteRow,'Catatan: Satu baris = satu hasil slitting. Summary dan penimbangan diulang untuk setiap hasil dalam LHP yang sama. Jangan menjumlahkan nilai header antarbaris.');
        $sheet->getStyle('A'.$noteRow)->getAlignment()->setWrapText(true);
        $sheet->getRowDimension($noteRow)->setRowHeight(32);
        return $book;
    }
}
