<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

ERROR - 2024-08-29 05:25:24 --> Severity: Warning --> mysqli::real_connect(): (HY000/1049): Unknown database 'metalsindo_d' C:\xampp56\htdocs\metalsindo\system\database\drivers\mysqli\mysqli_driver.php 202
ERROR - 2024-08-29 05:25:24 --> Unable to connect to the database
ERROR - 2024-08-29 05:25:33 --> 404 Page Not Found: /index
ERROR - 2024-08-29 05:25:38 --> Query error: The user specified as a definer ('root'@'%') does not exist - Invalid query: SELECT `a`.`nama` AS `nm_lv2`, `a`.`id_category2` AS `category_lv2`, `b`.`nama` AS `nm_lv1`, `a`.`aktif` AS `status`, `c`.`berat`
FROM `ms_inventory_category2` `a`
LEFT JOIN `ms_inventory_category1` `b` ON `a`.`id_category1`=`b`.`id_category1`
LEFT JOIN `stock_lv2` `c` ON `a`.`id_category2`=`c`.`id2`
WHERE `a`.`deleted` = '0'
ERROR - 2024-08-29 05:26:00 --> Query error: The user specified as a definer ('root'@'%') does not exist - Invalid query: SELECT `a`.`nama` AS `nm_lv2`, `a`.`id_category2` AS `category_lv2`, `b`.`nama` AS `nm_lv1`, `a`.`aktif` AS `status`, `c`.`berat`
FROM `ms_inventory_category2` `a`
LEFT JOIN `ms_inventory_category1` `b` ON `a`.`id_category1`=`b`.`id_category1`
LEFT JOIN `stock_lv2` `c` ON `a`.`id_category2`=`c`.`id2`
WHERE `a`.`deleted` = '0'
ERROR - 2024-08-29 11:37:47 --> Severity: Notice --> Undefined variable: datgroupmenu C:\xampp56\htdocs\metalsindo\application\modules\menus\views\menus_form.php 70
ERROR - 2024-08-29 11:39:10 --> Severity: Notice --> Undefined variable: datgroupmenu C:\xampp56\htdocs\metalsindo\application\modules\menus\views\menus_form.php 70
ERROR - 2024-08-29 11:39:47 --> Severity: Notice --> Undefined variable: datgroupmenu C:\xampp56\htdocs\metalsindo\application\modules\menus\views\menus_form.php 70
ERROR - 2024-08-29 11:40:09 --> Severity: Notice --> Undefined variable: datgroupmenu C:\xampp56\htdocs\metalsindo\application\modules\menus\views\menus_form.php 70
ERROR - 2024-08-29 11:40:21 --> Severity: Notice --> Undefined variable: datgroupmenu C:\xampp56\htdocs\metalsindo\application\modules\menus\views\menus_form.php 70
ERROR - 2024-08-29 11:40:24 --> Severity: Notice --> Undefined variable: datgroupmenu C:\xampp56\htdocs\metalsindo\application\modules\menus\views\menus_form.php 70
ERROR - 2024-08-29 06:40:54 --> 404 Page Not Found: /index
ERROR - 2024-08-29 06:40:57 --> 404 Page Not Found: /index
ERROR - 2024-08-29 06:41:51 --> Query error: Table 'metalsindo_db.ms_department' doesn't exist - Invalid query: SELECT *
FROM `ms_department`
WHERE `deleted_by` IS NULL
ERROR - 2024-08-29 11:44:09 --> Severity: Notice --> Undefined variable: datgroupmenu C:\xampp56\htdocs\metalsindo\application\modules\menus\views\menus_form.php 70
ERROR - 2024-08-29 11:44:56 --> Severity: Notice --> Undefined variable: datgroupmenu C:\xampp56\htdocs\metalsindo\application\modules\menus\views\menus_form.php 70
ERROR - 2024-08-29 06:45:01 --> 404 Page Not Found: /index
ERROR - 2024-08-29 11:45:45 --> Severity: Notice --> Undefined variable: datgroupmenu C:\xampp56\htdocs\metalsindo\application\modules\menus\views\menus_form.php 70
ERROR - 2024-08-29 11:45:49 --> Query error: Table 'metalsindo_db.ms_department' doesn't exist - Invalid query: SELECT *
FROM `ms_department`
WHERE `deleted_date` IS NULL
ERROR - 2024-08-29 11:46:08 --> Severity: Error --> Call to undefined function history() C:\xampp56\htdocs\metalsindo\application\modules\department\controllers\Department.php 45
ERROR - 2024-08-29 11:47:56 --> Query error: Table 'metalsindo_db.customer' doesn't exist - Invalid query: SELECT `a`.*, `b`.`nm_customer`, `e`.`nm_lengkap` as `request_by`, DATE_FORMAT(a.created_date, "%d %M %Y") as request_date
FROM `material_planning_base_on_produksi` `a`
LEFT JOIN `customer` `b` ON `b`.`id_customer` = `a`.`id_customer`
LEFT JOIN `material_planning_base_on_produksi_detail` `c` ON `c`.`so_number` = `a`.`so_number`
LEFT JOIN `accessories` `d` ON `d`.`id` = `c`.`id_material`
LEFT JOIN `users` `e` ON `e`.`id_user` = `a`.`created_by`
WHERE `a`.`category` = 'pr stok'
AND `a`.`booking_date` IS NOT NULL
AND `a`.`close_pr` IS NULL
GROUP BY `a`.`so_number`
ERROR - 2024-08-29 11:49:26 --> Query error: Table 'metalsindo_db.customer' doesn't exist - Invalid query: SELECT `a`.*, `b`.`nm_customer`, `e`.`nm_lengkap` as `request_by`, DATE_FORMAT(a.created_date, "%d %M %Y") as request_date
FROM `material_planning_base_on_produksi` `a`
LEFT JOIN `customer` `b` ON `b`.`id_customer` = `a`.`id_customer`
LEFT JOIN `material_planning_base_on_produksi_detail` `c` ON `c`.`so_number` = `a`.`so_number`
LEFT JOIN `accessories` `d` ON `d`.`id` = `c`.`id_material`
LEFT JOIN `users` `e` ON `e`.`id_user` = `a`.`created_by`
WHERE `a`.`category` = 'pr stok'
AND `a`.`booking_date` IS NOT NULL
AND `a`.`close_pr` IS NULL
GROUP BY `a`.`so_number`
ERROR - 2024-08-29 11:54:10 --> Query error: Table 'metalsindo_db.master_customer' doesn't exist - Invalid query: SELECT `a`.*, `b`.`name_customer`, `e`.`nm_lengkap` as `request_by`, DATE_FORMAT(a.created_date, "%d %M %Y") as request_date
FROM `material_planning_base_on_produksi` `a`
LEFT JOIN `master_customer` `b` ON `b`.`id_customer` = `a`.`id_customer`
LEFT JOIN `material_planning_base_on_produksi_detail` `c` ON `c`.`so_number` = `a`.`so_number`
LEFT JOIN `accessories` `d` ON `d`.`id` = `c`.`id_material`
LEFT JOIN `users` `e` ON `e`.`id_user` = `a`.`created_by`
WHERE `a`.`category` = 'pr stok'
AND `a`.`booking_date` IS NOT NULL
AND `a`.`close_pr` IS NULL
GROUP BY `a`.`so_number`
ERROR - 2024-08-29 11:56:25 --> Query error: Table 'metalsindo_db.master_customer' doesn't exist - Invalid query: SELECT `a`.*, `b`.`name_customer`, `e`.`nm_lengkap` as `request_by`, DATE_FORMAT(a.created_date, "%d %M %Y") as request_date
FROM `material_planning_base_on_produksi` `a`
LEFT JOIN `master_customer` `b` ON `b`.`id_customer` = `a`.`id_customer`
LEFT JOIN `material_planning_base_on_produksi_detail` `c` ON `c`.`so_number` = `a`.`so_number`
LEFT JOIN `accessories` `d` ON `d`.`id` = `c`.`id_material`
LEFT JOIN `users` `e` ON `e`.`id_user` = `a`.`created_by`
WHERE `a`.`category` = 'pr stok'
AND `a`.`booking_date` IS NOT NULL
AND `a`.`close_pr` IS NULL
GROUP BY `a`.`so_number`
ERROR - 2024-08-29 11:56:51 --> Severity: Error --> Call to undefined function history() C:\xampp56\htdocs\metalsindo\application\modules\request_pr_stok\controllers\Request_pr_stok.php 44
ERROR - 2024-08-29 11:57:34 --> Severity: Error --> Call to undefined function get_list_inventory_lv1() C:\xampp56\htdocs\metalsindo\application\modules\request_pr_stok\views\index.php 30
ERROR - 2024-08-29 11:59:20 --> Severity: Error --> Call to undefined function get_list_inventory_lv1() C:\xampp56\htdocs\metalsindo\application\modules\request_pr_stok\views\index.php 30
ERROR - 2024-08-29 11:59:52 --> Severity: Error --> Call to undefined function get_kebutuhanPerMonth() C:\xampp56\htdocs\metalsindo\application\modules\request_pr_stok\models\Request_pr_stok_model.php 407
ERROR - 2024-08-29 11:59:55 --> Severity: Error --> Call to undefined function get_kebutuhanPerMonth() C:\xampp56\htdocs\metalsindo\application\modules\request_pr_stok\models\Request_pr_stok_model.php 407
ERROR - 2024-08-29 12:00:01 --> Query error: You have an error in your SQL syntax; check the manual that corresponds to your MariaDB server version for the right syntax to use near 'LIMIT  ,' at line 16 - Invalid query: SELECT
              (@row:=@row+1) AS nomor,
              a.*,
              b.nm_category AS category_type
            FROM
              accessories a  
              LEFT JOIN accessories_category b ON a.id_category=b.id,
              (SELECT @row:=0) r
              WHERE 1=1 AND a.status = '1'  AND a.deleted_date IS NULL
              
            AND (
              a.id_stock LIKE '%%'
              OR a.stock_name LIKE '%%'
              OR b.nm_category LIKE '%%'
                )
		       ORDER BY a.id ASC,    LIMIT  , 
ERROR - 2024-08-29 12:02:41 --> Severity: Error --> Call to undefined function get_kebutuhanPerMonth() C:\xampp56\htdocs\metalsindo\application\modules\request_pr_stok\models\Request_pr_stok_model.php 407
ERROR - 2024-08-29 12:03:04 --> Query error: You have an error in your SQL syntax; check the manual that corresponds to your MariaDB server version for the right syntax to use near 'LIMIT  ,' at line 16 - Invalid query: SELECT
              (@row:=@row+1) AS nomor,
              a.*,
              b.nm_category AS category_type
            FROM
              accessories a  
              LEFT JOIN accessories_category b ON a.id_category=b.id,
              (SELECT @row:=0) r
              WHERE 1=1 AND a.status = '1'  AND a.deleted_date IS NULL
              
            AND (
              a.id_stock LIKE '%%'
              OR a.stock_name LIKE '%%'
              OR b.nm_category LIKE '%%'
                )
		       ORDER BY a.id ASC,    LIMIT  , 
ERROR - 2024-08-29 13:17:16 --> Query error: You have an error in your SQL syntax; check the manual that corresponds to your MariaDB server version for the right syntax to use near 'LIMIT  ,' at line 16 - Invalid query: SELECT
              (@row:=@row+1) AS nomor,
              a.*,
              b.nm_category AS category_type
            FROM
              accessories a  
              LEFT JOIN accessories_category b ON a.id_category=b.id,
              (SELECT @row:=0) r
              WHERE 1=1 AND a.status = '1'  AND a.deleted_date IS NULL
              
            AND (
              a.id_stock LIKE '%%'
              OR a.stock_name LIKE '%%'
              OR b.nm_category LIKE '%%'
                )
		       ORDER BY a.id ASC,    LIMIT  , 
ERROR - 2024-08-29 13:17:19 --> Query error: You have an error in your SQL syntax; check the manual that corresponds to your MariaDB server version for the right syntax to use near 'LIMIT  ,' at line 16 - Invalid query: SELECT
              (@row:=@row+1) AS nomor,
              a.*,
              b.nm_category AS category_type
            FROM
              accessories a  
              LEFT JOIN accessories_category b ON a.id_category=b.id,
              (SELECT @row:=0) r
              WHERE 1=1 AND a.status = '1'  AND a.deleted_date IS NULL
              
            AND (
              a.id_stock LIKE '%%'
              OR a.stock_name LIKE '%%'
              OR b.nm_category LIKE '%%'
                )
		       ORDER BY a.id ASC,    LIMIT  , 
ERROR - 2024-08-29 13:17:45 --> Query error: You have an error in your SQL syntax; check the manual that corresponds to your MariaDB server version for the right syntax to use near 'LIMIT  ,' at line 16 - Invalid query: SELECT
              (@row:=@row+1) AS nomor,
              a.*,
              b.nm_category AS category_type
            FROM
              accessories a  
              LEFT JOIN accessories_category b ON a.id_category=b.id,
              (SELECT @row:=0) r
              WHERE 1=1 AND a.status = '1'  AND a.deleted_date IS NULL
              
            AND (
              a.id_stock LIKE '%%'
              OR a.stock_name LIKE '%%'
              OR b.nm_category LIKE '%%'
                )
		       ORDER BY a.id ASC,    LIMIT  , 
ERROR - 2024-08-29 13:20:07 --> Query error: You have an error in your SQL syntax; check the manual that corresponds to your MariaDB server version for the right syntax to use near 'LIMIT  ,' at line 16 - Invalid query: SELECT
              (@row:=@row+1) AS nomor,
              a.*,
              b.nm_category AS category_type
            FROM
              accessories a  
              LEFT JOIN accessories_category b ON a.id_category=b.id,
              (SELECT @row:=0) r
              WHERE 1=1 AND a.status = '1'  AND a.deleted_date IS NULL
              
            AND (
              a.id_stock LIKE '%%'
              OR a.stock_name LIKE '%%'
              OR b.nm_category LIKE '%%'
                )
		       ORDER BY a.id ASC,    LIMIT  , 
ERROR - 2024-08-29 13:21:18 --> Query error: You have an error in your SQL syntax; check the manual that corresponds to your MariaDB server version for the right syntax to use near '' at line 16 - Invalid query: SELECT
              (@row:=@row+1) AS nomor,
              a.*,
              b.nm_category AS category_type
            FROM
              accessories a  
              LEFT JOIN accessories_category b ON a.id_category=b.id,
              (SELECT @row:=0) r
              WHERE 1=1 AND a.status = '1'  AND a.deleted_date IS NULL
              
            AND (
              a.id_stock LIKE '%%'
              OR a.stock_name LIKE '%%'
              OR b.nm_category LIKE '%%'
                )
		       ORDER BY a.id ASC,   
ERROR - 2024-08-29 13:21:43 --> Severity: Error --> Call to undefined function get_kebutuhanPerMonth() C:\xampp56\htdocs\metalsindo\application\modules\request_pr_stok\models\Request_pr_stok_model.php 407
ERROR - 2024-08-29 13:22:22 --> Severity: Error --> Call to undefined function get_kebutuhanPerMonth() C:\xampp56\htdocs\metalsindo\application\modules\request_pr_stok\models\Request_pr_stok_model.php 407
ERROR - 2024-08-29 13:27:27 --> Query error: Table 'metalsindo_db.budget_rutin_detail' doesn't exist - Invalid query: SELECT SUM(kebutuhan_month) AS sum_keb, `id_barang`
FROM `budget_rutin_detail`
GROUP BY `id_barang`
ERROR - 2024-08-29 13:30:23 --> Query error: Table 'metalsindo_db.warehouse_stock' doesn't exist - Invalid query: SELECT `a`.`id_material`, SUM(a.qty_stock) AS qty_stock, `b`.`konversi`
FROM `warehouse_stock` `a`
JOIN `accessories` `b` ON `a`.`id_material`=`b`.`id`
WHERE `a`.`id_gudang` IN(17, 19, 21)
GROUP BY `a`.`id_material`
ERROR - 2024-08-29 13:32:08 --> Query error: Unknown column 'code' in 'field list' - Invalid query: SELECT code FROM ms_satuan WHERE id='0' LIMIT 1
ERROR - 2024-08-29 13:36:43 --> Severity: Error --> Call to undefined function generateNoPR() C:\xampp56\htdocs\metalsindo\application\modules\request_pr_stok\controllers\Request_pr_stok.php 189
ERROR - 2024-08-29 13:36:51 --> Severity: Error --> Call to undefined function generateNoPR() C:\xampp56\htdocs\metalsindo\application\modules\request_pr_stok\controllers\Request_pr_stok.php 189
ERROR - 2024-08-29 13:37:30 --> Query error: Table 'metalsindo_db.customer' doesn't exist - Invalid query: SELECT a.*, b.nm_customer, b.alamat, c.name as country_name, d.nm_pic, d.hp, d.email_pic, b.fax FROM material_planning_base_on_produksi as a LEFT JOIN material_planning_base_on_produksi x ON x.so_number = a.so_number LEFT JOIN customer b ON b.id_customer = a.id_customer LEFT JOIN country_all c ON c.iso3 = b.country_code LEFT JOIN customer_pic d ON d.id_pic = b.id_pic WHERE a.so_number = 'P240800001' 
ERROR - 2024-08-29 13:37:38 --> Query error: Table 'metalsindo_db.so_internal' doesn't exist - Invalid query: SELECT `a`.*, `b`.`due_date`, `c`.`nm_customer`
FROM `material_planning_base_on_produksi` `a`
LEFT JOIN `so_internal` `b` ON `a`.`so_number`=`b`.`so_number`
LEFT JOIN `customer` `c` ON `a`.`id_customer`=`c`.`id_customer`
WHERE `a`.`so_number` = 'P240800001'
ERROR - 2024-08-29 13:40:16 --> Severity: Error --> Call to undefined function get_inventory_lv4() C:\xampp56\htdocs\metalsindo\application\modules\request_pr_stok\controllers\Request_pr_stok.php 290
ERROR - 2024-08-29 13:45:27 --> Query error: Table 'metalsindo_db.customer' doesn't exist - Invalid query: SELECT a.*, b.nm_customer, b.alamat, c.name as country_name, d.nm_pic, d.hp, d.email_pic, b.fax FROM material_planning_base_on_produksi as a LEFT JOIN material_planning_base_on_produksi x ON x.so_number = a.so_number LEFT JOIN customer b ON b.id_customer = a.id_customer LEFT JOIN country_all c ON c.iso3 = b.country_code LEFT JOIN customer_pic d ON d.id_pic = b.id_pic WHERE a.so_number = 'P240800001' 
ERROR - 2024-08-29 13:47:45 --> Query error: Table 'metalsindo_db.country_all' doesn't exist - Invalid query: SELECT a.*, b.name_customer as nm_customer, b.alamat, c.name as country_name, d.nm_pic, d.hp, d.email_pic, b.fax FROM material_planning_base_on_produksi as a LEFT JOIN material_planning_base_on_produksi x ON x.so_number = a.so_number LEFT JOIN master_customers b ON b.id_customer = a.id_customer LEFT JOIN country_all c ON c.iso3 = b.country_code LEFT JOIN customer_pic d ON d.id_pic = b.id_pic WHERE a.so_number = 'P240800001' 
ERROR - 2024-08-29 13:48:21 --> Query error: Table 'metalsindo_db.customer_pic' doesn't exist - Invalid query: SELECT a.*, b.name_customer as nm_customer, b.alamat, d.nm_pic, d.hp, d.email_pic, b.fax FROM material_planning_base_on_produksi as a LEFT JOIN material_planning_base_on_produksi x ON x.so_number = a.so_number LEFT JOIN master_customers b ON b.id_customer = a.id_customer LEFT JOIN customer_pic d ON d.id_pic = b.id_pic WHERE a.so_number = 'P240800001' 
ERROR - 2024-08-29 13:48:45 --> Query error: Unknown column 'b.alamat' in 'field list' - Invalid query: SELECT a.*, b.name_customer as nm_customer, b.alamat,  b.fax FROM material_planning_base_on_produksi as a LEFT JOIN material_planning_base_on_produksi x ON x.so_number = a.so_number LEFT JOIN master_customers b ON b.id_customer = a.id_customer WHERE a.so_number = 'P240800001' 
ERROR - 2024-08-29 13:49:09 --> Query error: Unknown column 'b.adress_office' in 'field list' - Invalid query: SELECT a.*, b.name_customer as nm_customer, b.adress_office as alamat, b.fax FROM material_planning_base_on_produksi as a LEFT JOIN material_planning_base_on_produksi x ON x.so_number = a.so_number LEFT JOIN master_customers b ON b.id_customer = a.id_customer WHERE a.so_number = 'P240800001' 
ERROR - 2024-08-29 13:49:16 --> Query error: Table 'metalsindo_db.new_inventory_4' doesn't exist - Invalid query: SELECT * FROM new_inventory_4 WHERE code_lv4 = '12' 
ERROR - 2024-08-29 13:51:14 --> Query error: Table 'metalsindo_db.new_inventory_4' doesn't exist - Invalid query: SELECT * FROM new_inventory_4 WHERE code_lv4 = '12' 
ERROR - 2024-08-29 13:51:31 --> Query error: Table 'metalsindo_db.new_inventory_4' doesn't exist - Invalid query: SELECT * FROM new_inventory_4 WHERE code_lv4 = '12' 
ERROR - 2024-08-29 13:53:16 --> Severity: error --> Exception: ERROR n°6 : Impossible to load the image https://localhost/metalsindo/assets/images/logo_metalsindo.jpeg C:\xampp56\htdocs\metalsindo\assets\html2pdf\html2pdf\html2pdf.class.php 1319
ERROR - 2024-08-29 13:54:08 --> Severity: error --> Exception: ERROR n°6 : Impossible to load the image https://localhost/metalsindo/assets/images/logo_metalsindo.jpeg C:\xampp56\htdocs\metalsindo\assets\html2pdf\html2pdf\html2pdf.class.php 1319
ERROR - 2024-08-29 13:54:11 --> Severity: error --> Exception: ERROR n°6 : Impossible to load the image https://localhost/metalsindo/assets/images/logo_metalsindo.jpeg C:\xampp56\htdocs\metalsindo\assets\html2pdf\html2pdf\html2pdf.class.php 1319
ERROR - 2024-08-29 08:54:35 --> Severity: Error --> Call to undefined function history() C:\xampp56\htdocs\metalsindo\application\modules\non_rutin\controllers\Non_rutin.php 68
ERROR - 2024-08-29 14:00:08 --> Severity: Notice --> Undefined variable: datgroupmenu C:\xampp56\htdocs\metalsindo\application\modules\menus\views\menus_form.php 70
ERROR - 2024-08-29 14:00:58 --> Severity: Notice --> Undefined variable: datgroupmenu C:\xampp56\htdocs\metalsindo\application\modules\menus\views\menus_form.php 70
ERROR - 2024-08-29 14:01:44 --> Severity: Notice --> Undefined variable: datgroupmenu C:\xampp56\htdocs\metalsindo\application\modules\menus\views\menus_form.php 70
ERROR - 2024-08-29 14:01:48 --> Severity: Error --> Call to undefined function history() C:\xampp56\htdocs\metalsindo\application\modules\unit\controllers\Unit.php 33
ERROR - 2024-08-29 14:02:54 --> Severity: Error --> Call to undefined function history() C:\xampp56\htdocs\metalsindo\application\modules\unit\controllers\Unit.php 88
ERROR - 2024-08-29 14:03:01 --> Severity: Error --> Call to undefined function history() C:\xampp56\htdocs\metalsindo\application\modules\unit\controllers\Unit.php 88
ERROR - 2024-08-29 14:03:36 --> Severity: Error --> Call to undefined function history() C:\xampp56\htdocs\metalsindo\application\modules\unit_packing\controllers\Unit_packing.php 33
ERROR - 2024-08-29 14:03:37 --> Severity: Error --> Call to undefined function history() C:\xampp56\htdocs\metalsindo\application\modules\unit_packing\controllers\Unit_packing.php 33
ERROR - 2024-08-29 09:06:04 --> Severity: Error --> Call to undefined function history() C:\xampp56\htdocs\metalsindo\application\modules\non_rutin\controllers\Non_rutin.php 68
ERROR - 2024-08-29 09:08:00 --> Query error: Table 'metalsindo_db.coa_category' doesn't exist - Invalid query: SELECT a.coa,b.nama FROM coa_category a join gl_metalsindo.coa_master b on a.coa=b.no_perkiraan WHERE a.tipe='NONRUTIN' order by a.coa
ERROR - 2024-08-29 09:09:24 --> Query error: Table 'gl_metalsindo.coa_master' doesn't exist - Invalid query: SELECT a.coa,b.nama FROM coa_category a join gl_metalsindo.coa_master b on a.coa=b.no_perkiraan WHERE a.tipe='NONRUTIN' order by a.coa
ERROR - 2024-08-29 09:24:46 --> The upload path does not appear to be valid.
ERROR - 2024-08-29 09:24:55 --> The upload path does not appear to be valid.
ERROR - 2024-08-29 16:11:23 --> Severity: Notice --> Undefined variable: datgroupmenu C:\xampp56\htdocs\metalsindo\application\modules\menus\views\menus_form.php 70
ERROR - 2024-08-29 16:12:11 --> Severity: Notice --> Undefined variable: datgroupmenu C:\xampp56\htdocs\metalsindo\application\modules\menus\views\menus_form.php 70
ERROR - 2024-08-29 16:13:29 --> Severity: Notice --> Undefined variable: datgroupmenu C:\xampp56\htdocs\metalsindo\application\modules\menus\views\menus_form.php 70
ERROR - 2024-08-29 16:14:37 --> Severity: Notice --> Undefined variable: datgroupmenu C:\xampp56\htdocs\metalsindo\application\modules\menus\views\menus_form.php 70
ERROR - 2024-08-29 16:15:33 --> Severity: Notice --> Undefined variable: datgroupmenu C:\xampp56\htdocs\metalsindo\application\modules\menus\views\menus_form.php 70
ERROR - 2024-08-29 16:16:22 --> Severity: Notice --> Undefined variable: datgroupmenu C:\xampp56\htdocs\metalsindo\application\modules\menus\views\menus_form.php 70
ERROR - 2024-08-29 11:17:00 --> Severity: Notice --> Undefined variable: cabang C:\xampp56\htdocs\metalsindo\application\modules\users\views\users_form.php 68
ERROR - 2024-08-29 11:17:00 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp56\htdocs\metalsindo\application\modules\users\views\users_form.php 68
ERROR - 2024-08-29 16:17:19 --> Severity: Notice --> Undefined variable: datgroupmenu C:\xampp56\htdocs\metalsindo\application\modules\menus\views\menus_form.php 70
ERROR - 2024-08-29 16:17:29 --> Severity: Notice --> Undefined variable: datgroupmenu C:\xampp56\htdocs\metalsindo\application\modules\menus\views\menus_form.php 70
ERROR - 2024-08-29 16:17:56 --> Severity: Notice --> Undefined variable: datgroupmenu C:\xampp56\htdocs\metalsindo\application\modules\menus\views\menus_form.php 70
ERROR - 2024-08-29 16:18:13 --> Severity: Notice --> Undefined variable: datgroupmenu C:\xampp56\htdocs\metalsindo\application\modules\menus\views\menus_form.php 70
ERROR - 2024-08-29 16:18:27 --> Severity: Notice --> Undefined variable: datgroupmenu C:\xampp56\htdocs\metalsindo\application\modules\menus\views\menus_form.php 70
ERROR - 2024-08-29 11:22:40 --> Query error: Unknown column 'department_id' in 'field list' - Invalid query: SELECT `department_id`
FROM `users`
WHERE `id_user` = '1'
ERROR - 2024-08-29 11:23:27 --> Severity: Notice --> Undefined index: tanda C:\xampp56\htdocs\metalsindo\application\modules\non_rutin\models\Non_rutin_model.php 182
ERROR - 2024-08-29 11:23:27 --> Severity: Notice --> Undefined index: search C:\xampp56\htdocs\metalsindo\application\modules\non_rutin\models\Non_rutin_model.php 183
ERROR - 2024-08-29 11:23:27 --> Severity: Notice --> Undefined index: order C:\xampp56\htdocs\metalsindo\application\modules\non_rutin\models\Non_rutin_model.php 184
ERROR - 2024-08-29 11:23:27 --> Severity: Notice --> Undefined index: order C:\xampp56\htdocs\metalsindo\application\modules\non_rutin\models\Non_rutin_model.php 185
ERROR - 2024-08-29 11:23:27 --> Severity: Notice --> Undefined index: start C:\xampp56\htdocs\metalsindo\application\modules\non_rutin\models\Non_rutin_model.php 186
ERROR - 2024-08-29 11:23:27 --> Severity: Notice --> Undefined index: length C:\xampp56\htdocs\metalsindo\application\modules\non_rutin\models\Non_rutin_model.php 187
ERROR - 2024-08-29 11:23:27 --> Query error: Unknown column 'department_id' in 'field list' - Invalid query: SELECT `department_id`
FROM `users`
WHERE `id_user` = '1'
ERROR - 2024-08-29 11:23:27 --> Severity: Warning --> Cannot modify header information - headers already sent by (output started at C:\xampp56\htdocs\metalsindo\system\core\Exceptions.php:272) C:\xampp56\htdocs\metalsindo\system\core\Common.php 573
ERROR - 2024-08-29 11:26:22 --> Severity: Notice --> Undefined index: tanda C:\xampp56\htdocs\metalsindo\application\modules\non_rutin\models\Non_rutin_model.php 182
ERROR - 2024-08-29 11:26:22 --> Severity: Notice --> Undefined index: search C:\xampp56\htdocs\metalsindo\application\modules\non_rutin\models\Non_rutin_model.php 183
ERROR - 2024-08-29 11:26:22 --> Severity: Notice --> Undefined index: order C:\xampp56\htdocs\metalsindo\application\modules\non_rutin\models\Non_rutin_model.php 184
ERROR - 2024-08-29 11:26:22 --> Severity: Notice --> Undefined index: order C:\xampp56\htdocs\metalsindo\application\modules\non_rutin\models\Non_rutin_model.php 185
ERROR - 2024-08-29 11:26:22 --> Severity: Notice --> Undefined index: start C:\xampp56\htdocs\metalsindo\application\modules\non_rutin\models\Non_rutin_model.php 186
ERROR - 2024-08-29 11:26:22 --> Severity: Notice --> Undefined index: length C:\xampp56\htdocs\metalsindo\application\modules\non_rutin\models\Non_rutin_model.php 187
ERROR - 2024-08-29 11:26:22 --> Query error: Unknown column 'department_id' in 'field list' - Invalid query: SELECT `department_id`
FROM `users`
WHERE `id_user` = '1'
ERROR - 2024-08-29 11:26:22 --> Severity: Warning --> Cannot modify header information - headers already sent by (output started at C:\xampp56\htdocs\metalsindo\system\core\Exceptions.php:272) C:\xampp56\htdocs\metalsindo\system\core\Common.php 573
ERROR - 2024-08-29 11:27:31 --> Severity: Notice --> Undefined index: tanda C:\xampp56\htdocs\metalsindo\application\modules\non_rutin\models\Non_rutin_model.php 182
ERROR - 2024-08-29 11:27:31 --> Severity: Notice --> Undefined index: search C:\xampp56\htdocs\metalsindo\application\modules\non_rutin\models\Non_rutin_model.php 183
ERROR - 2024-08-29 11:27:31 --> Severity: Notice --> Undefined index: order C:\xampp56\htdocs\metalsindo\application\modules\non_rutin\models\Non_rutin_model.php 184
ERROR - 2024-08-29 11:27:31 --> Severity: Notice --> Undefined index: order C:\xampp56\htdocs\metalsindo\application\modules\non_rutin\models\Non_rutin_model.php 185
ERROR - 2024-08-29 11:27:31 --> Severity: Notice --> Undefined index: start C:\xampp56\htdocs\metalsindo\application\modules\non_rutin\models\Non_rutin_model.php 186
ERROR - 2024-08-29 11:27:31 --> Severity: Notice --> Undefined index: length C:\xampp56\htdocs\metalsindo\application\modules\non_rutin\models\Non_rutin_model.php 187
ERROR - 2024-08-29 11:27:31 --> Query error: Unknown column 'department_id' in 'field list' - Invalid query: SELECT `department_id`
FROM `users`
WHERE `id_user` = '1'
ERROR - 2024-08-29 11:27:31 --> Severity: Warning --> Cannot modify header information - headers already sent by (output started at C:\xampp56\htdocs\metalsindo\system\core\Exceptions.php:272) C:\xampp56\htdocs\metalsindo\system\core\Common.php 573
ERROR - 2024-08-29 11:27:51 --> Severity: Notice --> Undefined index: search C:\xampp56\htdocs\metalsindo\application\modules\non_rutin\models\Non_rutin_model.php 183
ERROR - 2024-08-29 11:27:51 --> Severity: Notice --> Undefined index: order C:\xampp56\htdocs\metalsindo\application\modules\non_rutin\models\Non_rutin_model.php 184
ERROR - 2024-08-29 11:27:51 --> Severity: Notice --> Undefined index: order C:\xampp56\htdocs\metalsindo\application\modules\non_rutin\models\Non_rutin_model.php 185
ERROR - 2024-08-29 11:27:51 --> Severity: Notice --> Undefined index: start C:\xampp56\htdocs\metalsindo\application\modules\non_rutin\models\Non_rutin_model.php 186
ERROR - 2024-08-29 11:27:51 --> Severity: Notice --> Undefined index: length C:\xampp56\htdocs\metalsindo\application\modules\non_rutin\models\Non_rutin_model.php 187
ERROR - 2024-08-29 11:27:51 --> Query error: Unknown column 'department_id' in 'field list' - Invalid query: SELECT `department_id`
FROM `users`
WHERE `id_user` = '1'
ERROR - 2024-08-29 11:27:51 --> Severity: Warning --> Cannot modify header information - headers already sent by (output started at C:\xampp56\htdocs\metalsindo\system\core\Exceptions.php:272) C:\xampp56\htdocs\metalsindo\system\core\Common.php 573
ERROR - 2024-08-29 11:29:40 --> Query error: Unknown column 'department_id' in 'field list' - Invalid query: SELECT `department_id`
FROM `users`
WHERE `id_user` = '1'
ERROR - 2024-08-29 11:29:46 --> Severity: Notice --> Undefined index: tanda C:\xampp56\htdocs\metalsindo\application\modules\non_rutin\models\Non_rutin_model.php 182
ERROR - 2024-08-29 11:29:46 --> Severity: Notice --> Undefined index: search C:\xampp56\htdocs\metalsindo\application\modules\non_rutin\models\Non_rutin_model.php 183
ERROR - 2024-08-29 11:29:46 --> Severity: Notice --> Undefined index: order C:\xampp56\htdocs\metalsindo\application\modules\non_rutin\models\Non_rutin_model.php 184
ERROR - 2024-08-29 11:29:46 --> Severity: Notice --> Undefined index: order C:\xampp56\htdocs\metalsindo\application\modules\non_rutin\models\Non_rutin_model.php 185
ERROR - 2024-08-29 11:29:46 --> Severity: Notice --> Undefined index: start C:\xampp56\htdocs\metalsindo\application\modules\non_rutin\models\Non_rutin_model.php 186
ERROR - 2024-08-29 11:29:46 --> Severity: Notice --> Undefined index: length C:\xampp56\htdocs\metalsindo\application\modules\non_rutin\models\Non_rutin_model.php 187
ERROR - 2024-08-29 11:29:46 --> Query error: Unknown column 'department_id' in 'field list' - Invalid query: SELECT `department_id`
FROM `users`
WHERE `id_user` = '1'
ERROR - 2024-08-29 11:29:46 --> Severity: Warning --> Cannot modify header information - headers already sent by (output started at C:\xampp56\htdocs\metalsindo\system\core\Exceptions.php:272) C:\xampp56\htdocs\metalsindo\system\core\Common.php 573
ERROR - 2024-08-29 11:30:04 --> Severity: Notice --> Undefined index: tanda C:\xampp56\htdocs\metalsindo\application\modules\non_rutin\models\Non_rutin_model.php 182
ERROR - 2024-08-29 11:30:04 --> Severity: Notice --> Undefined index: search C:\xampp56\htdocs\metalsindo\application\modules\non_rutin\models\Non_rutin_model.php 183
ERROR - 2024-08-29 11:30:04 --> Severity: Notice --> Undefined index: order C:\xampp56\htdocs\metalsindo\application\modules\non_rutin\models\Non_rutin_model.php 184
ERROR - 2024-08-29 11:30:04 --> Severity: Notice --> Undefined index: order C:\xampp56\htdocs\metalsindo\application\modules\non_rutin\models\Non_rutin_model.php 185
ERROR - 2024-08-29 11:30:04 --> Severity: Notice --> Undefined index: start C:\xampp56\htdocs\metalsindo\application\modules\non_rutin\models\Non_rutin_model.php 186
ERROR - 2024-08-29 11:30:04 --> Severity: Notice --> Undefined index: length C:\xampp56\htdocs\metalsindo\application\modules\non_rutin\models\Non_rutin_model.php 187
ERROR - 2024-08-29 11:30:04 --> Query error: Unknown column 'department_id' in 'field list' - Invalid query: SELECT `department_id`
FROM `users`
WHERE `id_user` = '1'
ERROR - 2024-08-29 11:30:04 --> Severity: Warning --> Cannot modify header information - headers already sent by (output started at C:\xampp56\htdocs\metalsindo\system\core\Exceptions.php:272) C:\xampp56\htdocs\metalsindo\system\core\Common.php 573
ERROR - 2024-08-29 11:30:32 --> Query error: Unknown column 'department_id' in 'field list' - Invalid query: SELECT `department_id`
FROM `users`
WHERE `id_user` = '1'
ERROR - 2024-08-29 11:30:50 --> Query error: Unknown column 'department_id' in 'field list' - Invalid query: SELECT `department_id`
FROM `users`
WHERE `id_user` = '1'
ERROR - 2024-08-29 11:33:10 --> Query error: Unknown column 'department_id' in 'field list' - Invalid query: SELECT `department_id`
FROM `users`
WHERE `id_user` = '1'
ERROR - 2024-08-29 11:36:30 --> Query error: Unknown column 'department_id' in 'field list' - Invalid query: SELECT `department_id`
FROM `users`
WHERE `id_user` = '1'
ERROR - 2024-08-29 11:36:33 --> Query error: Unknown column 'department_id' in 'field list' - Invalid query: SELECT `department_id`
FROM `users`
WHERE `id_user` = '1'
ERROR - 2024-08-29 11:37:51 --> Severity: Notice --> Undefined index: tanda C:\xampp56\htdocs\metalsindo\application\modules\non_rutin\models\Non_rutin_model.php 352
ERROR - 2024-08-29 11:37:51 --> Severity: Notice --> Undefined index: search C:\xampp56\htdocs\metalsindo\application\modules\non_rutin\models\Non_rutin_model.php 353
ERROR - 2024-08-29 11:37:51 --> Severity: Notice --> Undefined index: order C:\xampp56\htdocs\metalsindo\application\modules\non_rutin\models\Non_rutin_model.php 354
ERROR - 2024-08-29 11:37:51 --> Severity: Notice --> Undefined index: order C:\xampp56\htdocs\metalsindo\application\modules\non_rutin\models\Non_rutin_model.php 355
ERROR - 2024-08-29 11:37:51 --> Severity: Notice --> Undefined index: start C:\xampp56\htdocs\metalsindo\application\modules\non_rutin\models\Non_rutin_model.php 356
ERROR - 2024-08-29 11:37:51 --> Severity: Notice --> Undefined index: length C:\xampp56\htdocs\metalsindo\application\modules\non_rutin\models\Non_rutin_model.php 357
ERROR - 2024-08-29 11:37:51 --> Query error: Unknown column 'department_id' in 'field list' - Invalid query: SELECT `department_id`
FROM `users`
WHERE `id_user` = '1'
ERROR - 2024-08-29 11:37:51 --> Severity: Warning --> Cannot modify header information - headers already sent by (output started at C:\xampp56\htdocs\metalsindo\system\core\Exceptions.php:272) C:\xampp56\htdocs\metalsindo\system\core\Common.php 573
ERROR - 2024-08-29 11:38:30 --> Severity: Notice --> Undefined index: tanda C:\xampp56\htdocs\metalsindo\application\modules\non_rutin\models\Non_rutin_model.php 352
ERROR - 2024-08-29 11:38:30 --> Severity: Notice --> Undefined index: search C:\xampp56\htdocs\metalsindo\application\modules\non_rutin\models\Non_rutin_model.php 353
ERROR - 2024-08-29 11:38:30 --> Severity: Notice --> Undefined index: order C:\xampp56\htdocs\metalsindo\application\modules\non_rutin\models\Non_rutin_model.php 354
ERROR - 2024-08-29 11:38:30 --> Severity: Notice --> Undefined index: order C:\xampp56\htdocs\metalsindo\application\modules\non_rutin\models\Non_rutin_model.php 355
ERROR - 2024-08-29 11:38:30 --> Severity: Notice --> Undefined index: start C:\xampp56\htdocs\metalsindo\application\modules\non_rutin\models\Non_rutin_model.php 356
ERROR - 2024-08-29 11:38:30 --> Severity: Notice --> Undefined index: length C:\xampp56\htdocs\metalsindo\application\modules\non_rutin\models\Non_rutin_model.php 357
ERROR - 2024-08-29 11:38:30 --> Query error: Unknown column 'department_id' in 'field list' - Invalid query: SELECT `department_id`
FROM `users`
WHERE `id_user` = '1'
ERROR - 2024-08-29 11:38:30 --> Severity: Warning --> Cannot modify header information - headers already sent by (output started at C:\xampp56\htdocs\metalsindo\system\core\Exceptions.php:272) C:\xampp56\htdocs\metalsindo\system\core\Common.php 573
ERROR - 2024-08-29 11:39:00 --> Severity: Notice --> Undefined index: tanda C:\xampp56\htdocs\metalsindo\application\modules\non_rutin\models\Non_rutin_model.php 352
ERROR - 2024-08-29 11:39:00 --> Severity: Notice --> Undefined index: search C:\xampp56\htdocs\metalsindo\application\modules\non_rutin\models\Non_rutin_model.php 353
ERROR - 2024-08-29 11:39:00 --> Severity: Notice --> Undefined index: order C:\xampp56\htdocs\metalsindo\application\modules\non_rutin\models\Non_rutin_model.php 354
ERROR - 2024-08-29 11:39:00 --> Severity: Notice --> Undefined index: order C:\xampp56\htdocs\metalsindo\application\modules\non_rutin\models\Non_rutin_model.php 355
ERROR - 2024-08-29 11:39:00 --> Severity: Notice --> Undefined index: start C:\xampp56\htdocs\metalsindo\application\modules\non_rutin\models\Non_rutin_model.php 356
ERROR - 2024-08-29 11:39:00 --> Severity: Notice --> Undefined index: length C:\xampp56\htdocs\metalsindo\application\modules\non_rutin\models\Non_rutin_model.php 357
ERROR - 2024-08-29 11:39:00 --> Query error: Unknown column 'department_id' in 'field list' - Invalid query: SELECT `department_id`
FROM `users`
WHERE `id_user` = '1'
ERROR - 2024-08-29 11:39:00 --> Severity: Warning --> Cannot modify header information - headers already sent by (output started at C:\xampp56\htdocs\metalsindo\system\core\Exceptions.php:272) C:\xampp56\htdocs\metalsindo\system\core\Common.php 573
ERROR - 2024-08-29 11:39:32 --> Query error: Unknown column 'department_id' in 'field list' - Invalid query: SELECT `department_id`
FROM `users`
WHERE `id_user` = '1'
ERROR - 2024-08-29 16:42:24 --> Severity: Notice --> Undefined variable: datgroupmenu C:\xampp56\htdocs\metalsindo\application\modules\menus\views\menus_form.php 70
ERROR - 2024-08-29 16:42:33 --> Severity: Notice --> Undefined variable: datgroupmenu C:\xampp56\htdocs\metalsindo\application\modules\menus\views\menus_form.php 70
ERROR - 2024-08-29 16:43:11 --> Severity: Notice --> Undefined variable: datgroupmenu C:\xampp56\htdocs\metalsindo\application\modules\menus\views\menus_form.php 70
ERROR - 2024-08-29 16:43:43 --> Severity: Notice --> Undefined variable: datgroupmenu C:\xampp56\htdocs\metalsindo\application\modules\menus\views\menus_form.php 70
ERROR - 2024-08-29 16:45:35 --> Severity: Notice --> Undefined variable: datgroupmenu C:\xampp56\htdocs\metalsindo\application\modules\menus\views\menus_form.php 70
ERROR - 2024-08-29 16:46:10 --> Severity: Notice --> Undefined variable: datgroupmenu C:\xampp56\htdocs\metalsindo\application\modules\menus\views\menus_form.php 70
ERROR - 2024-08-29 16:46:50 --> Severity: Notice --> Undefined variable: datgroupmenu C:\xampp56\htdocs\metalsindo\application\modules\menus\views\menus_form.php 70
ERROR - 2024-08-29 16:46:55 --> Severity: Error --> Call to undefined function get_list_inventory_lv1() C:\xampp56\htdocs\metalsindo\application\modules\app_pr_stock\views\approval_head.php 30
ERROR - 2024-08-29 16:46:58 --> Severity: Notice --> Undefined variable: datgroupmenu C:\xampp56\htdocs\metalsindo\application\modules\menus\views\menus_form.php 70
ERROR - 2024-08-29 16:46:59 --> Severity: Error --> Call to undefined function get_list_inventory_lv1() C:\xampp56\htdocs\metalsindo\application\modules\app_pr_stock\views\approval_head.php 30
ERROR - 2024-08-29 16:47:01 --> Severity: Notice --> Undefined variable: datgroupmenu C:\xampp56\htdocs\metalsindo\application\modules\menus\views\menus_form.php 70
ERROR - 2024-08-29 16:47:05 --> Severity: Error --> Call to undefined function get_list_inventory_lv1() C:\xampp56\htdocs\metalsindo\application\modules\app_pr_stock\views\approval_head.php 30
ERROR - 2024-08-29 16:48:11 --> Severity: Error --> Call to undefined function get_list_inventory_lv1() C:\xampp56\htdocs\metalsindo\application\modules\app_pr_stock\views\approval_head.php 30
ERROR - 2024-08-29 16:48:48 --> Query error: Table 'metalsindo_db.customer' doesn't exist - Invalid query: SELECT
              (@row:=@row+1) AS nomor,
              a.*,
              b.nm_customer
            FROM
              material_planning_base_on_produksi a
              INNER JOIN material_planning_base_on_produksi_detail z ON a.so_number=z.so_number
              LEFT JOIN customer b ON a.id_customer=b.id_customer,
              (SELECT @row:=0) r
            WHERE 1=1 AND a.category='pr stok' AND a.booking_date IS NOT NULL AND z.status_app = 'N' AND a.close_pr IS NULL AND a.rejected IS NULL  AND a.app_post IS NULL AND (
              b.nm_customer LIKE '%%'
              OR a.so_number LIKE '%%'
              OR a.project LIKE '%%'
              OR a.no_pr LIKE '%%'
            )
            GROUP BY a.so_number
            
ERROR - 2024-08-29 16:49:52 --> Query error: Table 'metalsindo_db.customer' doesn't exist - Invalid query: SELECT
              (@row:=@row+1) AS nomor,
              a.*,
              b.nm_customer
            FROM
              material_planning_base_on_produksi a
              INNER JOIN material_planning_base_on_produksi_detail z ON a.so_number=z.so_number
              LEFT JOIN customer b ON a.id_customer=b.id_customer,
              (SELECT @row:=0) r
            WHERE 1=1 AND a.category='pr stok' AND a.booking_date IS NOT NULL AND z.status_app = 'N' AND a.close_pr IS NULL AND a.rejected IS NULL  AND a.app_post IS NULL AND (
              b.nm_customer LIKE '%%'
              OR a.so_number LIKE '%%'
              OR a.project LIKE '%%'
              OR a.no_pr LIKE '%%'
            )
            GROUP BY a.so_number
            
