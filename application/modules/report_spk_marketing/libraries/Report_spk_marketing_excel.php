<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Report_spk_marketing_excel
{
    public function build($headers, $rows)
    {
        $book = new PHPExcel();
        $sheet = $book->getActiveSheet();
        $sheet->setTitle('Report SPK Marketing (final)');
        $sheet->mergeCells('A1:F1')->mergeCells('G1:P1');
        $sheet->setCellValue('A1', 'Info SPK')->setCellValue('G1', 'Product / Item')->setCellValue('Q1', 'Summary');
        foreach ($headers as $column => $label) $sheet->setCellValueByColumnAndRow($column, 2, $label);
        $line = 3;
        foreach ($rows as $row) {
            foreach ($row as $column => $value) {
                $cell = PHPExcel_Cell::stringFromColumnIndex($column).$line;
                if (in_array($column, array(2, 3, 4, 15), true) && $value !== '') {
                    $sheet->setCellValueExplicit($cell, PHPExcel_Shared_Date::FormattedPHPToExcel(
                        (int) substr($value, 0, 4), (int) substr($value, 5, 2), (int) substr($value, 8, 2)), PHPExcel_Cell_DataType::TYPE_NUMERIC);
                    $sheet->getStyle($cell)->getNumberFormat()->setFormatCode('yyyy-mm-dd');
                } elseif (is_int($value) || is_float($value)) {
                    $sheet->setCellValueExplicit($cell, $value, PHPExcel_Cell_DataType::TYPE_NUMERIC);
                    $sheet->getStyle($cell)->getNumberFormat()->setFormatCode($column === 6 ? '0' : '#,##0.00');
                } else {
                    // Document numbers and customer text must never become Excel formulas.
                    $sheet->setCellValueExplicit($cell, (string) $value, PHPExcel_Cell_DataType::TYPE_STRING);
                }
            }
            $line++;
        }
        $sheet->getStyle('A1:Q2')->applyFromArray(array(
            'font'=>array('bold'=>true),
            'fill'=>array('type'=>PHPExcel_Style_Fill::FILL_SOLID, 'color'=>array('rgb'=>'DCE6F1')),
            'alignment'=>array('horizontal'=>PHPExcel_Style_Alignment::HORIZONTAL_CENTER, 'vertical'=>PHPExcel_Style_Alignment::VERTICAL_CENTER, 'wrap'=>true)
        ));
        $sheet->getRowDimension(2)->setRowHeight(42);
        foreach (range(0, 16) as $column) $sheet->getColumnDimension(PHPExcel_Cell::stringFromColumnIndex($column))->setWidth(18);
        $sheet->getColumnDimension('A')->setWidth(28);
        $sheet->getColumnDimension('B')->setWidth(40);
        $sheet->getColumnDimension('F')->setWidth(28);
        $sheet->getColumnDimension('H')->setWidth(25);
        $sheet->getColumnDimension('G')->setWidth(8);
        $sheet->freezePane('C3');
        $sheet->setAutoFilter('A2:Q'.max(2, $line - 1));
        return $book;
    }
}
