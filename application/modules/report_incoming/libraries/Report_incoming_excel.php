<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Report_incoming_excel
{
    public function build($model,$filters,$column=8,$direction='desc')
    {
        // PHPTemp spills cell data to temporary storage without requiring SQLite3.
        $cacheMethod=PHPExcel_CachedObjectStorage_SQLite3::cacheMethodIsAvailable()
            ? PHPExcel_CachedObjectStorageFactory::cache_to_sqlite3
            : PHPExcel_CachedObjectStorageFactory::cache_to_phpTemp;
        $cacheOptions=$cacheMethod===PHPExcel_CachedObjectStorageFactory::cache_to_phpTemp
            ? array('memoryCacheSize'=>2097152) : array();
        if(!PHPExcel_Settings::setCacheStorageMethod($cacheMethod,$cacheOptions)
            || PHPExcel_CachedObjectStorageFactory::getCacheStorageMethod()!==$cacheMethod)
            throw new RuntimeException('Cache export Excel harus dikonfigurasi sebelum workbook dibuat.');
        $book=new PHPExcel(); $sheet=$book->getActiveSheet(); $sheet->setTitle('Report Incoming');
        $sheet->mergeCells('A1:S1')->setCellValue('A1','REPORT INCOMING');
        $period=$filters['start_date']!=='' || $filters['end_date']!==''
            ? ($filters['start_date']!=='' ? $filters['start_date'] : 'Awal').' s/d '.($filters['end_date']!=='' ? $filters['end_date'] : 'Akhir')
            : 'Semua periode';
        $sheet->mergeCells('A2:S2')->setCellValueExplicit('A2','Periode Incoming: '.$period.
            ($filters['search']!=='' ? ' | Pencarian: '.$filters['search'] : ''),PHPExcel_Cell_DataType::TYPE_STRING);
        foreach(Report_incoming_model::headers() as $index=>$label) $sheet->setCellValueByColumnAndRow($index,3,$label);
        $line=4; $number=0;
        foreach($model->export_rows($filters,$column,$direction) as $row) {
            foreach($model->format_row($row,++$number) as $index=>$value) {
                if($value===null) continue;
                $cell=PHPExcel_Cell::stringFromColumnIndex($index).$line;
                if(in_array($index,array(3,8),true) && Report_incoming_model::valid_date($value)) {
                    $date=PHPExcel_Shared_Date::FormattedPHPToExcel((int)substr($value,0,4),(int)substr($value,5,2),(int)substr($value,8,2));
                    $sheet->setCellValueExplicit($cell,$date,PHPExcel_Cell_DataType::TYPE_NUMERIC);
                } elseif(is_int($value) || is_float($value)) $sheet->setCellValueExplicit($cell,$value,PHPExcel_Cell_DataType::TYPE_NUMERIC);
                else $sheet->setCellValueExplicit($cell,$value,PHPExcel_Cell_DataType::TYPE_STRING);
            }
            $line++;
        }
        $last=max(3,$line-1);
        $sheet->getStyle('A1:S3')->applyFromArray(array(
            'font'=>array('bold'=>true,'color'=>array('rgb'=>'FFFFFF')),
            'fill'=>array('type'=>PHPExcel_Style_Fill::FILL_SOLID,'color'=>array('rgb'=>'195472')),
            'alignment'=>array('horizontal'=>PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                'vertical'=>PHPExcel_Style_Alignment::VERTICAL_CENTER,'wrap'=>true)
        ));
        $sheet->getRowDimension(1)->setRowHeight(30); $sheet->getRowDimension(2)->setRowHeight(30);
        $sheet->getRowDimension(3)->setRowHeight(45);
        foreach(range(0,18) as $index) $sheet->getColumnDimension(PHPExcel_Cell::stringFromColumnIndex($index))->setWidth(22);
        foreach(array('A'=>8,'E'=>40,'F'=>65,'N'=>28,'P'=>32,'Q'=>28,'R'=>40,'S'=>55) as $letter=>$width)
            $sheet->getColumnDimension($letter)->setWidth($width);
        if($line>4) {
            foreach(array('G','H','J','K','L') as $letter)
                $sheet->getStyle($letter.'4:'.$letter.$last)->getNumberFormat()->setFormatCode('#,##0.00');
            foreach(array('D','I') as $letter)
                $sheet->getStyle($letter.'4:'.$letter.$last)->getNumberFormat()->setFormatCode('dd-mm-yyyy');
            $sheet->getStyle('A4:A'.$last)->getNumberFormat()->setFormatCode('0');
        }
        $sheet->freezePane('F4'); $sheet->setAutoFilter('A3:S'.$last);
        $note=$line+1; $sheet->mergeCells('A'.$note.':S'.$note);
        $sheet->setCellValueExplicit('A'.$note,'Catatan: Satu baris = satu detail penerimaan/lot. Total Qty Receive dan Balance adalah progress PO terkini yang diulang setiap lot; jangan dijumlahkan antarbaris. Filter tanggal tidak mengubah total receive. PIC/SJ yang ambigu ditandai Belum terverifikasi. Customer mengikuti stok historis dan dapat berubah.',PHPExcel_Cell_DataType::TYPE_STRING);
        $sheet->getStyle('A'.$note)->getAlignment()->setWrapText(true); $sheet->getRowDimension($note)->setRowHeight(45);
        return $book;
    }
}
