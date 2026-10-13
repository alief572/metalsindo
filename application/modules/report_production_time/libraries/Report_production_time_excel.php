<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Report_production_time_excel
{
    public function save($book,$path)
    {
        $lock=null;
        // PCLZip's adapter uses shared XML filenames in /tmp; serialize fallback writers.
        if (!class_exists('ZipArchive')) {
            $lockPath=sys_get_temp_dir().'/report-production-time-export.lock';
            $lock=fopen($lockPath,file_exists($lockPath)?'r':'c');
            if (!$lock || !flock($lock,LOCK_EX)) throw new RuntimeException('Tidak dapat menyiapkan export Excel.');
        }
        try { PHPExcel_IOFactory::createWriter($book,'Excel2007')->save($path); }
        finally { if ($lock) { flock($lock,LOCK_UN); fclose($lock); } }
    }

    private function write($sheet,$column,$line,$value)
    {
        if ($value===null || $value==='') return;
        $cell=PHPExcel_Cell::stringFromColumnIndex($column).$line;
        if ($column===2 && Report_production_time_model::valid_date($value)) {
            $value=PHPExcel_Shared_Date::FormattedPHPToExcel((int)substr($value,0,4),(int)substr($value,5,2),(int)substr($value,8,2));
            $sheet->setCellValueExplicit($cell,$value,PHPExcel_Cell_DataType::TYPE_NUMERIC);
            $sheet->getStyle($cell)->getNumberFormat()->setFormatCode('dd-mm-yyyy');
        } elseif (is_int($value) || is_float($value)) {
            $sheet->setCellValueExplicit($cell,$value,PHPExcel_Cell_DataType::TYPE_NUMERIC);
            $sheet->getStyle($cell)->getNumberFormat()->setFormatCode($column===10?'0':'#,##0.00');
        } elseif (($column===12 || $column===13) && preg_match('/^[0-9]{2}:[0-9]{2}$/D',$value)) {
            $sheet->setCellValueExplicit($cell,((int)substr($value,0,2)*60+(int)substr($value,3,2))/1440,PHPExcel_Cell_DataType::TYPE_NUMERIC);
            $sheet->getStyle($cell)->getNumberFormat()->setFormatCode('hh.mm');
        } else $sheet->setCellValueExplicit($cell,(string)$value,PHPExcel_Cell_DataType::TYPE_STRING);
    }

    public function build($groups)
    {
        if (!class_exists('ZipArchive')) PHPExcel_Settings::setZipClass(PHPExcel_Settings::PCLZIP);
        $book=new PHPExcel(); $sheet=$book->getActiveSheet();
        $sheet->setTitle('Report Waktu Pekerjaan (Final)');
        $sheet->mergeCells('A1:J1')->mergeCells('K1:P1')->mergeCells('Q1:R1');
        $sheet->setCellValue('A1','Info')->setCellValue('K1','Progress Pekerjaan')->setCellValue('Q1','Summary');
        foreach (Report_production_time_model::headers() as $column=>$label) $this->write($sheet,$column,2,$label);
        $line=3;
        foreach ($groups as $group) {
            $first=$line; $last=$line+count($group['progress'])-1;
            foreach (array_merge(range(0,9),array(16,17)) as $column) {
                $letter=PHPExcel_Cell::stringFromColumnIndex($column);
                if ($last>$first) $sheet->mergeCells($letter.$first.':'.$letter.$last);
                $value=$column<10?$group['info'][$column]:($group['invalid_total']?'Tidak tersedia':$group['summary'][$column-16]);
                $this->write($sheet,$column,$first,$value);
            }
            foreach ($group['progress'] as $index=>$progress) {
                $values=array($index+1,$progress['progress'],$progress['start'],$progress['finish'],
                    $progress['invalid_total']?'Tidak valid: '.$progress['raw_total']:$progress['total'],$progress['keterangan']);
                foreach ($values as $offset=>$value) $this->write($sheet,10+$offset,$line,$value);
                $sheet->getRowDimension($line)->setRowHeight(30);
                if ($progress['invalid_total']) $sheet->getStyle('O'.$line)->getFont()->getColor()->setRGB('A12622');
                $line++;
            }
            $sheet->getStyle('A'.$first.':R'.$last)->getBorders()->getOutline()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
        }
        $sheet->getStyle('A1:R2')->applyFromArray(array('font'=>array('bold'=>true,'color'=>array('rgb'=>'FFFFFF')),
            'fill'=>array('type'=>PHPExcel_Style_Fill::FILL_SOLID,'color'=>array('rgb'=>'195472')),
            'alignment'=>array('horizontal'=>PHPExcel_Style_Alignment::HORIZONTAL_CENTER,'vertical'=>PHPExcel_Style_Alignment::VERTICAL_CENTER,'wrap'=>true)));
        if ($line>3) $sheet->getStyle('A3:R'.($line-1))->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER)->setWrapText(true);
        $sheet->getRowDimension(1)->setRowHeight(26); $sheet->getRowDimension(2)->setRowHeight(54);
        foreach (range(0,17) as $column) $sheet->getColumnDimension(PHPExcel_Cell::stringFromColumnIndex($column))->setWidth(18);
        foreach (array('A'=>28,'B'=>32,'D'=>38,'E'=>48,'K'=>7,'L'=>25,'M'=>12,'N'=>12,'P'=>44,'Q'=>24,'R'=>24) as $column=>$width)
            $sheet->getColumnDimension($column)->setWidth($width);
        $note=$line+1; $sheet->mergeCells('A'.$note.':R'.$note);
        $this->write($sheet,0,$note,'Catatan: Total memakai menit tersimpan di LHP. Tanggal adalah tanggal aktual LHP terbaru per SPK. Durasi tidak valid membuat summary tidak tersedia. SPK dengan lot sama dapat memiliki catatan waktu yang sama; jangan menjumlahkannya sebagai total waktu mesin.');
        $sheet->getStyle('A'.$note)->getAlignment()->setWrapText(true);
        $sheet->getRowDimension($note)->setRowHeight(42);
        return $book;
    }
}
