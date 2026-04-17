<?php
$invoice_date = "2026-04-16";
$timestamp = strtotime(date('Y-m-d', strtotime($invoice_date)) . ' 00:00:00 UTC');
$excelDate = 25569 + ($timestamp / 86400);
echo "Result: $excelDate\n";
