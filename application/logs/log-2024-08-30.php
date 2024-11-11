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
ERROR - 2024-08-30 08:46:10 --> Query error: Table 'metalsindo_db.tran_pr_detail' doesn't exist - Invalid query: SELECT `id`
FROM `tran_pr_detail`
WHERE `checklist` = '1'
AND `checklist_by` IS NULL
AND `no_rfq` IS NULL
ERROR - 2024-08-30 13:48:44 --> Query error: Unknown column 'tipe' in 'where clause' - Invalid query: SELECT *
FROM `dt_trans_po`
WHERE `idpr` = '3'
AND `tipe` = 'pr depart'
ERROR - 2024-08-30 14:50:40 --> Query error: Unknown column 'tipe' in 'where clause' - Invalid query: SELECT *
FROM `dt_trans_po`
WHERE `idpr` = '3'
AND `tipe` = 'pr depart'
ERROR - 2024-08-30 14:51:47 --> Query error: Table 'metalsindo_db.tr_po_checked_pr' doesn't exist - Invalid query: SELECT *
FROM `tr_po_checked_pr`
WHERE `no_pr` = 'PLN2408001'
AND `id_user` = '1'
ERROR - 2024-08-30 09:53:13 --> 404 Page Not Found: ../modules/purchase_order/controllers/Purchase_order/proses
ERROR - 2024-08-30 14:53:47 --> Query error: Table 'metalsindo_db.new_inventory_4' doesn't exist - Invalid query: 
			SELECT 
				a.id as id,
				a.so_number as so_number,
				a.id_material as id_material,
				a.propose_purchase as propose_purchase,
				(b.qty_stock - b.qty_booking) AS avl_stock, 
				IF(c.code = '' OR c.code IS NULL, d.id_stock, c.code) as code, 
				'' as code1, 
				IF(c.nama = '' OR c.nama IS NULL, d.stock_name, c.nama) as nm_material,
				'' as tipe_pr,
				e.code as packing_unit,	
				f.code as packing_unit2,
				IF(g.code IS NOT NULL, g.code, h.code) as unit_measure
			FROM
				material_planning_base_on_produksi_detail a
				LEFT JOIN warehouse_stock b ON b.id_material = a.id_material
				LEFT JOIN new_inventory_4 c ON c.code_lv4 = a.id_material 
				LEFT JOIN accessories d ON d.id = a.id_material
				LEFT JOIN ms_satuan e ON e.id = c.id_unit_packing
				LEFT JOIN ms_satuan f ON f.id = d.id_unit_gudang
				LEFT JOIN ms_satuan g ON g.id = c.id_unit
				LEFT JOIN ms_satuan h ON h.id = d.id_unit
			WHERE
				a.so_number IN ('PLN2408001')
				AND a.status_app = 'Y'
			GROUP BY a.id_material

			UNION ALL

			SELECT
				a.id as id,
				a.no_pengajuan as so_number,
				'' as id_material,
				a.qty as propose_purchase,
				'0' as avl_stock,
				a.nm_barang as code,
				'' as code1,
				a.nm_barang as nm_material,
				'pr depart' as tipe_pr,
				b.code as packing_unit,
				'' as packing_unit2,
				b.code as unit_measure
			FROM
				rutin_non_planning_detail a 
				LEFT JOIN ms_satuan b ON b.id = a.satuan
			WHERE
				a.no_pengajuan IN ('PLN2408001')
				
			GROUP BY a.id

			UNION ALL

			SELECT
				a.id as id,
				a.code_plan as so_number,
				'' as id_material,
				a.rev_qty as propose_purchase,
				0 as avl_stock,
				a.nama_asset as code,
				'' as code1,
				a.nama_asset as nm_material,
				'pr asset' as tipe_pr,
				'Pcs' as packing_unit,
				'' as packing_unit2,
				'Pcs' as unit_measure
			FROM
				asset_planning a 
			WHERE
				a.code_plan IN ('PLN2408001')
		
ERROR - 2024-08-30 14:54:58 --> Query error: Table 'metalsindo_db.asset_planning' doesn't exist - Invalid query: 
			SELECT 
				a.id as id,
				a.so_number as so_number,
				a.id_material as id_material,
				a.propose_purchase as propose_purchase,
				(b.qty_stock - b.qty_booking) AS avl_stock, 
				d.id_stock as code, 
				'' as code1, 
				d.stock_name as nm_material,
				'' as tipe_pr,
				e.code as packing_unit,	
				f.code as packing_unit2,
				h.code as unit_measure
			FROM
				material_planning_base_on_produksi_detail a
				LEFT JOIN warehouse_stock b ON b.id_material = a.id_material
				LEFT JOIN accessories d ON d.id = a.id_material
				LEFT JOIN ms_satuan f ON f.id = d.id_unit_gudang
				LEFT JOIN ms_satuan h ON h.id = d.id_unit
			WHERE
				a.so_number IN ('PLN2408001')
				AND a.status_app = 'Y'
			GROUP BY a.id_material

			UNION ALL

			SELECT
				a.id as id,
				a.no_pengajuan as so_number,
				'' as id_material,
				a.qty as propose_purchase,
				'0' as avl_stock,
				a.nm_barang as code,
				'' as code1,
				a.nm_barang as nm_material,
				'pr depart' as tipe_pr,
				b.code as packing_unit,
				'' as packing_unit2,
				b.code as unit_measure
			FROM
				rutin_non_planning_detail a 
				LEFT JOIN ms_satuan b ON b.id = a.satuan
			WHERE
				a.no_pengajuan IN ('PLN2408001')
				
			GROUP BY a.id

			UNION ALL

			SELECT
				a.id as id,
				a.code_plan as so_number,
				'' as id_material,
				a.rev_qty as propose_purchase,
				0 as avl_stock,
				a.nama_asset as code,
				'' as code1,
				a.nama_asset as nm_material,
				'pr asset' as tipe_pr,
				'Pcs' as packing_unit,
				'' as packing_unit2,
				'Pcs' as unit_measure
			FROM
				asset_planning a 
			WHERE
				a.code_plan IN ('PLN2408001')
		
ERROR - 2024-08-30 14:55:08 --> Query error: Unknown column 'e.code' in 'field list' - Invalid query: 
			SELECT 
				a.id as id,
				a.so_number as so_number,
				a.id_material as id_material,
				a.propose_purchase as propose_purchase,
				(b.qty_stock - b.qty_booking) AS avl_stock, 
				d.id_stock as code, 
				'' as code1, 
				d.stock_name as nm_material,
				'' as tipe_pr,
				e.code as packing_unit,	
				f.code as packing_unit2,
				h.code as unit_measure
			FROM
				material_planning_base_on_produksi_detail a
				LEFT JOIN warehouse_stock b ON b.id_material = a.id_material
				LEFT JOIN accessories d ON d.id = a.id_material
				LEFT JOIN ms_satuan f ON f.id = d.id_unit_gudang
				LEFT JOIN ms_satuan h ON h.id = d.id_unit
			WHERE
				a.so_number IN ('PLN2408001')
				AND a.status_app = 'Y'
			GROUP BY a.id_material

			UNION ALL

			SELECT
				a.id as id,
				a.no_pengajuan as so_number,
				'' as id_material,
				a.qty as propose_purchase,
				'0' as avl_stock,
				a.nm_barang as code,
				'' as code1,
				a.nm_barang as nm_material,
				'pr depart' as tipe_pr,
				b.code as packing_unit,
				'' as packing_unit2,
				b.code as unit_measure
			FROM
				rutin_non_planning_detail a 
				LEFT JOIN ms_satuan b ON b.id = a.satuan
			WHERE
				a.no_pengajuan IN ('PLN2408001')
				
			GROUP BY a.id
		
ERROR - 2024-08-30 14:55:40 --> Query error: Table 'metalsindo_db.customer' doesn't exist - Invalid query: SELECT *
FROM `customer`
WHERE `deleted_by` IS NULL
ERROR - 2024-08-30 14:56:45 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp56\htdocs\metalsindo\application\modules\purchase_order_non_material\views\add_purchaseorder.php 27
ERROR - 2024-08-30 14:58:55 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp56\htdocs\metalsindo\application\modules\purchase_order_non_material\views\add_purchaseorder.php 27
ERROR - 2024-08-30 14:59:39 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp56\htdocs\metalsindo\application\modules\purchase_order_non_material\views\add_purchaseorder.php 27
ERROR - 2024-08-30 14:59:47 --> Query error: Table 'metalsindo_db.list_help' doesn't exist - Invalid query: SELECT *
FROM `list_help`
WHERE `group_by` = 'top'
AND `sts` = 'Y'
ERROR - 2024-08-30 15:00:43 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp56\htdocs\metalsindo\application\modules\purchase_order_non_material\views\add_purchaseorder.php 27
ERROR - 2024-08-30 15:00:53 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp56\htdocs\metalsindo\application\modules\purchase_order_non_material\views\add_purchaseorder.php 27
ERROR - 2024-08-30 15:05:01 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp56\htdocs\metalsindo\application\modules\purchase_order_non_material\views\add_purchaseorder.php 27
ERROR - 2024-08-30 15:08:44 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp56\htdocs\metalsindo\application\modules\purchase_order_non_material\views\add_purchaseorder.php 27
ERROR - 2024-08-30 10:10:52 --> 404 Page Not Found: ../modules/purchase_order/controllers/Purchase_order/CariPPN
ERROR - 2024-08-30 10:11:01 --> 404 Page Not Found: ../modules/purchase_order/controllers/Purchase_order/CariPPN
ERROR - 2024-08-30 15:11:55 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp56\htdocs\metalsindo\application\modules\purchase_order_non_material\views\add_purchaseorder.php 27
ERROR - 2024-08-30 15:12:17 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp56\htdocs\metalsindo\application\modules\purchase_order_non_material\views\add_purchaseorder.php 27
ERROR - 2024-08-30 15:12:33 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp56\htdocs\metalsindo\application\modules\purchase_order_non_material\views\add_purchaseorder.php 27
ERROR - 2024-08-30 10:12:40 --> 404 Page Not Found: ../modules/purchase_order/controllers/Purchase_order/CariPPN
ERROR - 2024-08-30 15:13:20 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp56\htdocs\metalsindo\application\modules\purchase_order_non_material\views\add_purchaseorder.php 27
ERROR - 2024-08-30 15:14:04 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp56\htdocs\metalsindo\application\modules\purchase_order_non_material\views\add_purchaseorder.php 27
ERROR - 2024-08-30 15:14:56 --> Query error: Table 'metalsindo_db.new_inventory_4' doesn't exist - Invalid query: SELECT `code_lv4`, `nama`
FROM `new_inventory_4`
WHERE `code_lv4` = ''
OR `id` = ''
ERROR - 2024-08-30 15:15:03 --> Query error: Table 'metalsindo_db.new_inventory_4' doesn't exist - Invalid query: SELECT `code_lv4`, `nama`
FROM `new_inventory_4`
WHERE `code_lv4` = ''
OR `id` = ''
ERROR - 2024-08-30 15:23:56 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp56\htdocs\metalsindo\application\modules\purchase_order_non_material\views\add_purchaseorder.php 27
ERROR - 2024-08-30 15:25:42 --> Query error: Table 'metalsindo_db.new_inventory_4' doesn't exist - Invalid query: SELECT `code_lv4`, `nama`
FROM `new_inventory_4`
WHERE `code_lv4` = ''
OR `id` = ''
ERROR - 2024-08-30 15:25:50 --> Query error: Table 'metalsindo_db.new_inventory_4' doesn't exist - Invalid query: SELECT `code_lv4`, `nama`
FROM `new_inventory_4`
WHERE `code_lv4` = ''
OR `id` = ''
ERROR - 2024-08-30 15:26:46 --> Query error: Table 'metalsindo_db.tr_incoming_check' doesn't exist - Invalid query: SELECT `a`.`kode_trans`
FROM `tr_incoming_check` `a`
WHERE `a`.`no_ipp` LIKE '%P2400378%' ESCAPE '!'
ERROR - 2024-08-30 15:29:28 --> Query error: Table 'metalsindo_db.tr_incoming_check' doesn't exist - Invalid query: SELECT `a`.`kode_trans`
FROM `tr_incoming_check` `a`
WHERE `a`.`no_ipp` LIKE '%P2400378%' ESCAPE '!'
ERROR - 2024-08-30 15:29:54 --> Query error: Table 'metalsindo_db.tr_incoming_check' doesn't exist - Invalid query: SELECT `a`.`kode_trans`
FROM `tr_incoming_check` `a`
WHERE `a`.`no_ipp` LIKE '%P2400378%' ESCAPE '!'
ERROR - 2024-08-30 15:30:57 --> Query error: Unknown column 'aa.tipe' in 'where clause' - Invalid query: 
							SELECT
								b.no_pr as no_pr
							FROM
								material_planning_base_on_produksi_detail a
								JOIN material_planning_base_on_produksi b ON b.so_number = a.so_number
							WHERE
								a.id IN (SELECT aa.idpr FROM dt_trans_po aa WHERE aa.no_po = 'P2400378' AND (aa.tipe IS NULL OR aa.tipe = ''))
							GROUP BY b.no_pr

							UNION ALL 

							SELECT
								b.no_pr as no_pr
							FROM
								rutin_non_planning_detail a
								JOIN rutin_non_planning_header b ON b.no_pengajuan = a.no_pengajuan
							WHERE
								a.id IN (SELECT aa.idpr FROM dt_trans_po aa WHERE aa.no_po = 'P2400378' AND aa.tipe = 'pr depart')
							GROUP BY b.no_pr
						
