# Checkpoint Fase 5

Status: selesai dan terverifikasi.

## Perubahan / alasan
Installer local-only transaksional, anchor Kartu Stock/Warehouse Material, ownership guard dan rollback. Menu/permission telah dipasang pada metalsindo_dev saja.

## Pemeriksaan
PASS apply/reapply/rollback fixture, konflik/anchor/target rejection; actual apply installed, reapply already installed. PASS Menu_generator nyata lokal: report once tepat setelah Kartu Stock, admin default, non-admin memerlukan permission parent existing + report.

## Risiko / pekerjaan berikutnya
Fase 6: acceptance, mutation, lint/review dan dokumentasi. DB aktif nonlokal tidak dipasang; parent permission requirement berasal dari Menu_generator existing.

## File
- scripts/report_incoming_checkpoint.ps1
- scripts/test_report_incoming.ps1
- scripts/register_report_incoming.php
- scripts/install_report_incoming_local.ps1
- assets/css/report_incoming.css
- application\modules\report_incoming\controllers\Report_incoming.php
- application\modules\report_incoming\libraries\Report_incoming_excel.php
- application\modules\report_incoming\libraries\Report_incoming_registration.php
- application\modules\report_incoming\models\Report_incoming_model.php
- application\modules\report_incoming\views\index.php
- tests\report_incoming\bootstrap.php
- tests\report_incoming\capacity.php
- tests\report_incoming\controller.php
- tests\report_incoming\excel.php
- tests\report_incoming\fixtures.php
- tests\report_incoming\migration.php
- tests\report_incoming\performance.php
- tests\report_incoming\regression.php
- tests\report_incoming\sidebar.php
- tests\report_incoming\ui_state.mjs
- tests\report_incoming\ui_syntax.mjs
- md\report_incoming\SPEC.md

Manifest SHA256 tersimpan pada JSON fase yang sama. Verifikasi checkpoint sebelum memulai fase berikutnya.
