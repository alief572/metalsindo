<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Stock_raw_material_excel
{
    public function build($model, $filters, $meta, $direction = 'asc')
    {
        if (!class_exists('ZipArchive')) PHPExcel_Settings::setZipClass(PHPExcel_Settings::PCLZIP);
        // PHPTemp spills cell data to temporary storage without requiring a server extension.
        $cacheMethod = PHPExcel_CachedObjectStorageFactory::cache_to_phpTemp;
        if (!PHPExcel_Settings::setCacheStorageMethod($cacheMethod, array('memoryCacheSize'=>2097152))
            || PHPExcel_CachedObjectStorageFactory::getCacheStorageMethod() !== $cacheMethod)
            throw new RuntimeException('Cache ekspor harus dikonfigurasi sebelum workbook dibuat.');
        $book = new PHPExcel(); $sheet = $book->getActiveSheet(); $sheet->setTitle('Stock Raw Material');
        $sheet->mergeCells('A1:M1')->setCellValue('A1','STOCK RAW MATERIAL');
        $sheet->mergeCells('A2:M2')->setCellValueExplicit('A2','Tanggal stok: '.$meta['stock_date'],PHPExcel_Cell_DataType::TYPE_STRING);
        $sheet->mergeCells('A3:M3')->setCellValueExplicit('A3','Width: '.$filters['width'].' | Pencarian: '.$filters['search'],PHPExcel_Cell_DataType::TYPE_STRING);
        $headers = array('#','ID Material','Lot Number','Nama Material','Supplier','Width','Jumlah Item','Berat Satuan (kg)',
            'Berat Total (kg)','Keterangan','Gudang','Customer','Tanggal Stok');
        foreach ($headers as $column=>$label) $sheet->setCellValueByColumnAndRow($column,4,$label);
        $line = 5; $start = 0; $weight = 0;
        $summary = $direction === 'desc' ? $model->summary($filters,$meta) : null;
        do {
            $rows = $model->rows($filters,$meta,$start,1000,$direction);
            foreach ($rows as $index=>$row) {
                $number = $direction === 'desc' ? $summary['n']-$start-$index : $start+$index+1;
                foreach ($model->values($row,$number,$meta) as $column=>$value) {
                    if ($value === null) continue;
                    $sheet->setCellValueExplicit(PHPExcel_Cell::stringFromColumnIndex($column).$line,$value,
                        in_array($column,array(0,5,7,8),true) ? PHPExcel_Cell_DataType::TYPE_NUMERIC : PHPExcel_Cell_DataType::TYPE_STRING);
                }
                if ($row['qty'] !== null && $row['sisa_spk'] !== null) $weight += $row['qty']*$row['sisa_spk'];
                $line++;
            }
            $start += count($rows);
        } while (count($rows) === 1000);
        $last = max(4,$line-1);
        $sheet->setCellValue('H'.$line,'TOTAL BERAT')->setCellValue('I'.$line,$weight);
        $sheet->getStyle('A1:M4')->getFont()->setBold(true);
        $sheet->getStyle('F5:F'.$line)->getNumberFormat()->setFormatCode('#,##0.00');
        $sheet->getStyle('H5:I'.$line)->getNumberFormat()->setFormatCode('#,##0.00');
        foreach (range('A','M') as $letter) $sheet->getColumnDimension($letter)->setWidth(24);
        $sheet->getColumnDimension('A')->setWidth(9); $sheet->getColumnDimension('D')->setWidth(40);
        $sheet->setAutoFilter('A4:M'.$last); $sheet->freezePane('A5');
        return $book;
    }
}
