-- Optimasi view_efaktur_invoice (halaman wt_invoicing/e_faktur timeout 30 detik)
-- Penyebab: subquery stk_sum meng-agregasi SELURUH stock_material JOIN dt_delivery_order_child
-- tanpa filter, lalu di-materialize tanpa index. Dipanggil berkali-kali per request.
-- Perbaikan: batasi subquery hanya ke invoice yang belum di-export (stat_efaktur = 0),
-- sesuai satu-satunya pemakaian view (Wt_invoicing_model::get_efaktur).
-- Catatan: view jadi hanya berisi invoice stat_efaktur = 0.

-- 1) Index pendukung (abaikan error "Duplicate key name" jika sudah ada)
ALTER TABLE stock_material ADD INDEX idx_sm_lotno (lotno);
ALTER TABLE stock_material ADD INDEX idx_sm_no_kirim (no_kirim);
ALTER TABLE dt_delivery_order_child ADD INDEX idx_doc_lotno (lotno);
ALTER TABLE tr_invoice_detail ADD INDEX idx_invd_no_invoice (no_invoice);
ALTER TABLE tr_invoice ADD INDEX idx_inv_stat_efaktur (stat_efaktur);
ALTER TABLE tr_invoice ADD INDEX idx_inv_no_invoice (no_invoice);

-- 2) View baru (DEFINER dihapus agar tidak error "definer does not exist")
CREATE OR REPLACE
  SQL SECURITY INVOKER
  VIEW view_efaktur_invoice AS
SELECT
  t.no_invoice,
  t.npwp,
  t.nama_customer,
  t.term,
  t.nomor_do,
  t.tgl_invoice,
  t.dpp_final AS nilai_dpp,
  t.ppn_final AS nilai_ppn,
  t.dpp_final + t.ppn_final AS nilai_invoice,
  t.stat_efaktur,
  t.id_customer
FROM (
  SELECT
    inv.no_surat AS no_invoice,
    cust.npwp AS npwp,
    UCASE(cust.name_customer) AS nama_customer,
    inv.note AS term,
    inv.no_do AS nomor_do,
    inv.tgl_invoice AS tgl_invoice,
    CEILING(11 / 12 * IFNULL(ds.total_awal, 0)) AS dpp_final,
    CEILING(11 / 12 * IFNULL(ds.total_awal, 0)) * 0.12 AS ppn_final,
    inv.stat_efaktur AS stat_efaktur,
    inv.id_customer AS id_customer
  FROM tr_invoice inv
  JOIN master_customers cust ON inv.id_customer = cust.id_customer
  LEFT JOIN (
    SELECT
      id_inv.no_invoice AS no_invoice,
      SUM(CASE WHEN cat.id_bentuk = 'B2000002'
               THEN id_inv.harga_satuan * IFNULL(stk_sum.total_qty_sheet, 0)
               ELSE id_inv.qty_invoice * id_inv.harga_satuan END) AS total_awal
    FROM tr_invoice_detail id_inv
    JOIN tr_invoice main_inv
      ON main_inv.no_invoice = id_inv.no_invoice
     AND main_inv.stat_efaktur = 0
    LEFT JOIN ms_inventory_category3 cat ON cat.id_category3 = id_inv.id_category3
    LEFT JOIN (
      SELECT stk.no_kirim AS no_kirim,
             child.id_material AS id_material,
             SUM(stk.qty_sheet) AS total_qty_sheet
      FROM stock_material stk
      JOIN dt_delivery_order_child child ON stk.lotno = child.lotno
      WHERE stk.no_kirim IN (SELECT id_do FROM tr_invoice WHERE stat_efaktur = 0)
      GROUP BY stk.no_kirim, child.id_material
    ) stk_sum ON stk_sum.id_material = id_inv.id_category3
             AND stk_sum.no_kirim = main_inv.id_do
    GROUP BY id_inv.no_invoice
  ) ds ON inv.no_invoice = ds.no_invoice
) t;
