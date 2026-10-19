<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Warehouse_stock_date_excel
{
    public function build($model,$filters,$meta,$direction='asc')
    {
        if (!class_exists('ZipArchive')) PHPExcel_Settings::setZipClass(PHPExcel_Settings::PCLZIP);
        if (!PHPExcel_Settings::setCacheStorageMethod(PHPExcel_CachedObjectStorageFactory::cache_to_sqlite3)
            || PHPExcel_CachedObjectStorageFactory::getCacheStorageMethod()!==PHPExcel_CachedObjectStorageFactory::cache_to_sqlite3)
            throw new RuntimeException('SQLite3 cache diperlukan untuk ekspor stok.');
        $names=$model->warehouses(); $headers=$model->headers($filters);
        $lastColumn=PHPExcel_Cell::stringFromColumnIndex(count($headers)-1);
        $book=new PHPExcel(); $sheet=$book->getActiveSheet(); $sheet->setTitle('Stock Gudang');
        $sheet->mergeCells('A1:'.$lastColumn.'1')->setCellValueExplicit('A1',$names[$filters['gudang']],PHPExcel_Cell_DataType::TYPE_STRING);
        $sheet->mergeCells('A2:'.$lastColumn.'2')->setCellValueExplicit('A2','Tanggal stok: '.$meta['stock_date'],PHPExcel_Cell_DataType::TYPE_STRING);
        $sheet->mergeCells('A3:'.$lastColumn.'3')->setCellValueExplicit('A3','Width: '.$filters['width'].' | Pencarian: '.$filters['search'],PHPExcel_Cell_DataType::TYPE_STRING);
        foreach ($headers as $column=>$header) $sheet->setCellValueByColumnAndRow($column,4,$header);
        $summary=$direction==='desc' ? $model->summary($filters,$meta) : null;
        $line=5; $start=0; $weight=0;
        do {
            $rows=$model->rows($filters,$meta,$start,1000,$direction);
            foreach ($rows as $index=>$row) {
                $number=$direction==='desc' ? $summary['n']-$start-$index : $start+$index+1;
                foreach ($model->values($row,$number,$meta) as $column=>$value) {
                    if ($value===null) continue;
                    $numeric=in_array($column,array(0,5,7,8),true) || ($filters['gudang']==='3' && $column===9);
                    $sheet->setCellValueExplicit(PHPExcel_Cell::stringFromColumnIndex($column).$line,$value,
                        $numeric ? PHPExcel_Cell_DataType::TYPE_NUMERIC : PHPExcel_Cell_DataType::TYPE_STRING);
                }
                if ($row['qty']!==null && $row['sisa_spk']!==null) $weight+=$row['qty']*$row['sisa_spk'];
                $line++;
            }
            $start+=count($rows);
        } while (count($rows)===1000);
        $sheet->setAutoFilter('A4:'.$lastColumn.max(4,$line-1))->freezePane('A5');
        $sheet->setCellValue('H'.$line,'TOTAL BERAT')->setCellValue('I'.$line,$weight);
        $sheet->getStyle('A1:'.$lastColumn.'4')->getFont()->setBold(true);
        $sheet->getStyle('F5:F'.$line)->getNumberFormat()->setFormatCode('#,##0.00');
        $sheet->getStyle('H5:I'.$line)->getNumberFormat()->setFormatCode('#,##0.00');
        for ($column=0;$column<count($headers);$column++) $sheet->getColumnDimension(PHPExcel_Cell::stringFromColumnIndex($column))->setWidth(24);
        $sheet->getColumnDimension('A')->setWidth(9); $sheet->getColumnDimension('D')->setWidth(40);
        return $book;
    }
}
