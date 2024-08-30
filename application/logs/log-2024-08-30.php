<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

ERROR - 2024-08-30 04:23:37 --> 404 Page Not Found: /index
ERROR - 2024-08-30 04:41:46 --> Severity: Warning --> mysqli::real_connect(): (HY000/1045): Access denied for user 'root'@'localhost' (using password: YES) C:\xampp56\htdocs\metalsindo\system\database\drivers\mysqli\mysqli_driver.php 202
ERROR - 2024-08-30 04:41:46 --> Unable to connect to the database
ERROR - 2024-08-30 09:42:09 --> Severity: Notice --> Undefined variable: datgroupmenu C:\xampp56\htdocs\metalsindo\application\modules\menus\views\menus_form.php 70
ERROR - 2024-08-30 09:43:21 --> Severity: Notice --> Undefined variable: datgroupmenu C:\xampp56\htdocs\metalsindo\application\modules\menus\views\menus_form.php 70
ERROR - 2024-08-30 04:45:06 --> 404 Page Not Found: ../modules/purchase_order_non_material/controllers//index
ERROR - 2024-08-30 10:02:07 --> Query error: Table 'metalsindo_db.new_supplier' doesn't exist - Invalid query: 
		SELECT 
			a.*, 
			b.nm_lengkap as nm_create, 
			d.so_number,
			f.no_pr as no_pr_material,
			e.no_pr as no_pr_depart,
			h.nama as nm_supplier,
			IF(SUM(j.jumlahharga) IS NULL, 0, SUM(j.jumlahharga)) as harga_po
		FROM 
			tr_purchase_order as a 
			LEFT JOIN users b ON b.id_user = a.created_by 
			LEFT JOIN dt_trans_po c ON c.no_po = a.no_po 
			LEFT JOIN material_planning_base_on_produksi_detail d ON d.id = c.idpr AND (c.tipe IS NULL OR c.tipe = '')
			LEFT JOIN material_planning_base_on_produksi f ON f.so_number = d.so_number AND (c.tipe IS NULL OR c.tipe = '')
			LEFT JOIN rutin_non_planning_detail e ON e.id = c.idpr AND c.tipe = 'pr depart'
			LEFT JOIN rutin_non_planning_header g ON g.no_pengajuan = e.no_pengajuan
			LEFT JOIN new_supplier h ON h.kode_supplier = a.id_suplier
			LEFT JOIN dt_trans_po j ON j.no_po = a.no_po
		WHERE
			a.close_po IS NULL AND
			(SELECT COUNT(aa.id) FROM dt_trans_po aa WHERE aa.no_po = a.no_po) > 0
		GROUP BY a.no_po
		ORDER BY a.no_po DESC
	
ERROR - 2024-08-30 10:02:16 --> Severity: Notice --> Undefined variable: datgroupmenu C:\xampp56\htdocs\metalsindo\application\modules\menus\views\menus_form.php 70
ERROR - 2024-08-30 10:14:53 --> Query error: Table 'metalsindo_db.master_suplier' doesn't exist - Invalid query: 
		SELECT 
			a.*, 
			b.nm_lengkap as nm_create, 
			d.so_number,
			f.no_pr as no_pr_material,
			e.no_pr as no_pr_depart,
			h.name_suplier as nm_supplier,
			IF(SUM(j.jumlahharga) IS NULL, 0, SUM(j.jumlahharga)) as harga_po
		FROM 
			tr_purchase_order_non_material as a 
			LEFT JOIN users b ON b.id_user = a.created_by 
			LEFT JOIN dt_trans_po_non_material c ON c.no_po = a.no_po 
			LEFT JOIN material_planning_base_on_produksi_detail d ON d.id = c.idpr AND (c.tipe IS NULL OR c.tipe = '')
			LEFT JOIN material_planning_base_on_produksi f ON f.so_number = d.so_number AND (c.tipe IS NULL OR c.tipe = '')
			LEFT JOIN rutin_non_planning_detail e ON e.id = c.idpr AND c.tipe = 'pr depart'
			LEFT JOIN rutin_non_planning_header g ON g.no_pengajuan = e.no_pengajuan
			LEFT JOIN master_suplier h ON h.id_suplier = a.id_suplier
			LEFT JOIN dt_trans_po_non_material j ON j.no_po = a.no_po
		WHERE
			a.close_po IS NULL AND
			(SELECT COUNT(aa.id) FROM dt_trans_po_non_material aa WHERE aa.no_po = a.no_po) > 0
		GROUP BY a.no_po
		ORDER BY a.no_po DESC
	
ERROR - 2024-08-30 05:15:11 --> 404 Page Not Found: ../modules/purchase_order/controllers/Purchase_order/addPurchaseorder
ERROR - 2024-08-30 10:16:44 --> Query error: Table 'metalsindo_db.customer' doesn't exist - Invalid query: SELECT *
FROM `customer`
ERROR - 2024-08-30 10:19:32 --> Query error: Table 'metalsindo_db.customer' doesn't exist - Invalid query: SELECT *
FROM `customer`
ERROR - 2024-08-30 10:19:53 --> Query error: Table 'metalsindo_db.tran_pr_header' doesn't exist - Invalid query: 
			SELECT 
				a.so_number as so_number,
				a.no_pr as no_pr,
				a.tgl_so as tgl_so, 
				b.nm_lengkap as nama_user,
				"pr material" as tipe_pr
			FROM
				material_planning_base_on_produksi a
				LEFT JOIN users b ON b.id_user = a.booking_by
			WHERE
				(SELECT COUNT(x.id) as hitung FROM material_planning_base_on_produksi_detail x WHERE x.so_number = a.so_number ) > 0 AND 
				a.metode_pembelian = "1" AND 
				a.close_pr IS NULL

			UNION ALL

			SELECT 
				a.no_pengajuan as so_number,
				a.no_pr as no_pr,
				DATE_FORMAT(a.created_date, "%Y-%m-%d") as tgl_so,
				b.nm_lengkap as nama_user,
				"pr depart" as tipe_pr
			FROM
				rutin_non_planning_header a
				LEFT JOIN users b ON b.id_user = a.created_by
			WHERE
				a.sts_app = "Y" AND
				a.metode_pembelian = "1" AND
				a.close_pr IS NULL

			UNION ALL

			SELECT 
				a.id as so_number,
				a.no_pr as no_pr,
				DATE_FORMAT(a.created_date, "%Y-%m-%d") as tgl_so,
				b.nm_lengkap as nama_user,
				"pr asset" as tipe_pr
			FROM
				tran_pr_header a
				LEFT JOIN users b ON b.id_user = a.created_by
			WHERE
				a.app_status_3 = "Y" AND
				a.metode_pembelian = "1" AND
				a.close_pr IS NULL
		
ERROR - 2024-08-30 08:14:25 --> 404 Page Not Found: /index
ERROR - 2024-08-30 08:14:29 --> 404 Page Not Found: /index
ERROR - 2024-08-30 13:14:58 --> Severity: Notice --> Undefined variable: datgroupmenu C:\xampp56\htdocs\metalsindo\application\modules\menus\views\menus_form.php 70
ERROR - 2024-08-30 13:22:01 --> Severity: Notice --> Undefined variable: datgroupmenu C:\xampp56\htdocs\metalsindo\application\modules\menus\views\menus_form.php 70
ERROR - 2024-08-30 13:22:17 --> Severity: Notice --> Undefined variable: datgroupmenu C:\xampp56\htdocs\metalsindo\application\modules\menus\views\menus_form.php 70
ERROR - 2024-08-30 13:22:31 --> Severity: Notice --> Undefined variable: datgroupmenu C:\xampp56\htdocs\metalsindo\application\modules\menus\views\menus_form.php 70
ERROR - 2024-08-30 13:22:43 --> Severity: Notice --> Undefined variable: datgroupmenu C:\xampp56\htdocs\metalsindo\application\modules\menus\views\menus_form.php 70
ERROR - 2024-08-30 13:22:53 --> Severity: Notice --> Undefined variable: datgroupmenu C:\xampp56\htdocs\metalsindo\application\modules\menus\views\menus_form.php 70
ERROR - 2024-08-30 08:23:02 --> 404 Page Not Found: /index
ERROR - 2024-08-30 08:23:36 --> 404 Page Not Found: ../modules/metode_pembelian/controllers/Metode_pembelian/index
ERROR - 2024-08-30 08:24:05 --> 404 Page Not Found: ../modules/metode_pembelian/controllers/Metode_pembelian/index
ERROR - 2024-08-30 08:24:16 --> Severity: Error --> Call to undefined function history() C:\xampp56\htdocs\metalsindo\application\modules\metode_pembelian\models\Metode_pembelian_model.php 22
ERROR - 2024-08-30 08:24:20 --> 404 Page Not Found: ../modules/metode_pembelian/controllers/Metode_pembelian/index
ERROR - 2024-08-30 13:24:25 --> Severity: Notice --> Undefined variable: datgroupmenu C:\xampp56\htdocs\metalsindo\application\modules\menus\views\menus_form.php 70
ERROR - 2024-08-30 13:24:56 --> Severity: Notice --> Undefined variable: datgroupmenu C:\xampp56\htdocs\metalsindo\application\modules\menus\views\menus_form.php 70
ERROR - 2024-08-30 08:25:01 --> Severity: Error --> Call to undefined function history() C:\xampp56\htdocs\metalsindo\application\modules\metode_pembelian\models\Metode_pembelian_model.php 22
ERROR - 2024-08-30 08:27:23 --> Query error: Table 'metalsindo_db.tran_pr_detail' doesn't exist - Invalid query: SELECT *
FROM `tran_pr_detail`
WHERE `no_pr_group` = 'PRN24080001'
ERROR - 2024-08-30 08:27:29 --> Query error: Table 'metalsindo_db.tran_pr_detail' doesn't exist - Invalid query: SELECT *
FROM `tran_pr_detail`
WHERE `no_pr_group` = 'PRN24080001'
ERROR - 2024-08-30 08:28:28 --> Query error: Table 'metalsindo_db.tran_pr_detail' doesn't exist - Invalid query: SELECT *
FROM `tran_pr_detail`
WHERE `no_pr_group` = 'PRN24080001'
ERROR - 2024-08-30 08:28:45 --> Query error: Table 'metalsindo_db.tran_pr_detail' doesn't exist - Invalid query: SELECT *
FROM `tran_pr_detail`
WHERE `no_pr_group` = 'PRN24080001'
ERROR - 2024-08-30 08:28:51 --> Query error: Table 'metalsindo_db.new_supplier' doesn't exist - Invalid query: SELECT kode_supplier AS id_supplier, nama AS nm_supplier FROM new_supplier ORDER BY nm_supplier ASC 
ERROR - 2024-08-30 08:29:55 --> Query error: Table 'metalsindo_db.tran_pr_header' doesn't exist - Invalid query: 
				SELECT
					a.no_pr as no_pr,
					a.tgl_so as tgl_pr,
					b.nm_lengkap as request_by,
					a.created_date as request_date,
					IF(a.category = "pr stok", "stok", "material") as category,
					a.so_number as so_number
				FROM
					material_planning_base_on_produksi a
					LEFT JOIN users b ON b.id_user = a.created_by
				WHERE
					a.category IN ("pr material", "pr stok") AND
					a.close_pr IS NULL AND
					a.metode_pembelian IS NULL AND (
						a.no_pr LIKE "%%" OR
						a.tgl_so LIKE "%%" OR
						b.nm_lengkap LIKE "%%" OR
						a.created_date LIKE "%%"
					)
				
				UNION ALL
	
				SELECT
					a.no_pr as no_pr,
					a.created_date as tgl_pr,
					b.nm_lengkap as request_by,
					a.created_date as request_date,
					"departemen" as category,
					"" as so_number
				FROM
					rutin_non_planning_header a
					LEFT JOIN users b ON b.id_user = a.created_by
				WHERE
						a.metode_pembelian IS NULL AND
						a.close_pr IS NULL AND
						(
							a.no_pr LIKE "%%" OR
							a.created_date LIKE "%%" OR
							b.nm_lengkap LIKE "%%"
						)
				
				UNION ALL

				SELECT
					a.no_pr as no_pr,
					a.created_date as tgl_pr,
					b.nm_lengkap as request_by,
					a.created_date as request_date,
					"asset" as category,
					"" as so_number
				FROM
					tran_pr_header a
					LEFT JOIN users b ON b.id_user = a.created_by
				WHERE
					a.metode_pembelian IS NULL AND
					a.close_pr IS NULL AND
					(
						a.no_pr LIKE "%%" OR
						a.created_date LIKE "%%" OR
						b.nm_lengkap LIKE "%%"
					)


			
ERROR - 2024-08-30 08:30:24 --> Query error: Table 'metalsindo_db.tran_pr_header' doesn't exist - Invalid query: 
				SELECT
					a.no_pr as no_pr,
					a.tgl_so as tgl_pr,
					b.nm_lengkap as request_by,
					a.created_date as request_date,
					IF(a.category = "pr stok", "stok", "material") as category,
					a.so_number as so_number
				FROM
					material_planning_base_on_produksi a
					LEFT JOIN users b ON b.id_user = a.created_by
				WHERE
					a.category IN ("pr material", "pr stok") AND
					a.close_pr IS NULL AND
					a.metode_pembelian IS NULL AND (
						a.no_pr LIKE "%%" OR
						a.tgl_so LIKE "%%" OR
						b.nm_lengkap LIKE "%%" OR
						a.created_date LIKE "%%"
					)
				
				UNION ALL
	
				SELECT
					a.no_pr as no_pr,
					a.created_date as tgl_pr,
					b.nm_lengkap as request_by,
					a.created_date as request_date,
					"departemen" as category,
					"" as so_number
				FROM
					rutin_non_planning_header a
					LEFT JOIN users b ON b.id_user = a.created_by
				WHERE
						a.metode_pembelian IS NULL AND
						a.close_pr IS NULL AND
						(
							a.no_pr LIKE "%%" OR
							a.created_date LIKE "%%" OR
							b.nm_lengkap LIKE "%%"
						)
				
				UNION ALL

				SELECT
					a.no_pr as no_pr,
					a.created_date as tgl_pr,
					b.nm_lengkap as request_by,
					a.created_date as request_date,
					"asset" as category,
					"" as so_number
				FROM
					tran_pr_header a
					LEFT JOIN users b ON b.id_user = a.created_by
				WHERE
					a.metode_pembelian IS NULL AND
					a.close_pr IS NULL AND
					(
						a.no_pr LIKE "%%" OR
						a.created_date LIKE "%%" OR
						b.nm_lengkap LIKE "%%"
					)


			
ERROR - 2024-08-30 08:31:57 --> Query error: Table 'metalsindo_db.tran_pr_header' doesn't exist - Invalid query: 
				SELECT
					a.no_pr as no_pr,
					a.tgl_so as tgl_pr,
					b.nm_lengkap as request_by,
					a.created_date as request_date,
					IF(a.category = "pr stok", "stok", "material") as category,
					a.so_number as so_number
				FROM
					material_planning_base_on_produksi a
					LEFT JOIN users b ON b.id_user = a.created_by
				WHERE
					a.category IN ("pr material", "pr stok") AND
					a.close_pr IS NULL AND
					a.metode_pembelian IS NULL AND (
						a.no_pr LIKE "%%" OR
						a.tgl_so LIKE "%%" OR
						b.nm_lengkap LIKE "%%" OR
						a.created_date LIKE "%%"
					)
				
				UNION ALL
	
				SELECT
					a.no_pr as no_pr,
					a.created_date as tgl_pr,
					b.nm_lengkap as request_by,
					a.created_date as request_date,
					"departemen" as category,
					"" as so_number
				FROM
					rutin_non_planning_header a
					LEFT JOIN users b ON b.id_user = a.created_by
				WHERE
						a.metode_pembelian IS NULL AND
						a.close_pr IS NULL AND
						(
							a.no_pr LIKE "%%" OR
							a.created_date LIKE "%%" OR
							b.nm_lengkap LIKE "%%"
						)
				
				UNION ALL

				SELECT
					a.no_pr as no_pr,
					a.created_date as tgl_pr,
					b.nm_lengkap as request_by,
					a.created_date as request_date,
					"asset" as category,
					"" as so_number
				FROM
					tran_pr_header a
					LEFT JOIN users b ON b.id_user = a.created_by
				WHERE
					a.metode_pembelian IS NULL AND
					a.close_pr IS NULL AND
					(
						a.no_pr LIKE "%%" OR
						a.created_date LIKE "%%" OR
						b.nm_lengkap LIKE "%%"
					)


			
ERROR - 2024-08-30 08:32:10 --> Query error: Table 'metalsindo_db.tran_pr_header' doesn't exist - Invalid query: 
				SELECT
					a.no_pr as no_pr,
					a.tgl_so as tgl_pr,
					b.nm_lengkap as request_by,
					a.created_date as request_date,
					IF(a.category = "pr stok", "stok", "material") as category,
					a.so_number as so_number
				FROM
					material_planning_base_on_produksi a
					LEFT JOIN users b ON b.id_user = a.created_by
				WHERE
					a.category IN ("pr material", "pr stok") AND
					a.close_pr IS NULL AND
					a.metode_pembelian IS NULL AND (
						a.no_pr LIKE "%%" OR
						a.tgl_so LIKE "%%" OR
						b.nm_lengkap LIKE "%%" OR
						a.created_date LIKE "%%"
					)
				
				UNION ALL
	
				SELECT
					a.no_pr as no_pr,
					a.created_date as tgl_pr,
					b.nm_lengkap as request_by,
					a.created_date as request_date,
					"departemen" as category,
					"" as so_number
				FROM
					rutin_non_planning_header a
					LEFT JOIN users b ON b.id_user = a.created_by
				WHERE
						a.metode_pembelian IS NULL AND
						a.close_pr IS NULL AND
						(
							a.no_pr LIKE "%%" OR
							a.created_date LIKE "%%" OR
							b.nm_lengkap LIKE "%%"
						)
				
				UNION ALL

				SELECT
					a.no_pr as no_pr,
					a.created_date as tgl_pr,
					b.nm_lengkap as request_by,
					a.created_date as request_date,
					"asset" as category,
					"" as so_number
				FROM
					tran_pr_header a
					LEFT JOIN users b ON b.id_user = a.created_by
				WHERE
					a.metode_pembelian IS NULL AND
					a.close_pr IS NULL AND
					(
						a.no_pr LIKE "%%" OR
						a.created_date LIKE "%%" OR
						b.nm_lengkap LIKE "%%"
					)


			
ERROR - 2024-08-30 13:34:06 --> Query error: Table 'metalsindo_db.so_internal' doesn't exist - Invalid query: SELECT `a`.*, `b`.`due_date`, `c`.`nm_customer`
FROM `material_planning_base_on_produksi` `a`
LEFT JOIN `so_internal` `b` ON `a`.`so_number`=`b`.`so_number`
LEFT JOIN `customer` `c` ON `a`.`id_customer`=`c`.`id_customer`
WHERE `a`.`so_number` = 'P240800001'
