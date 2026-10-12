# ERD — Modul Incoming (Penerimaan Barang Material)

> Modul: `application/modules/incoming`
> DB operasional: `metalsindo_live` (host 206.84.97.106)
> DB akuntansi: `gl_metalsindo_live`
> Dibuat: 2026-09-10 — hasil eksplorasi kode + query DB live.

## Ringkasan Alur Data

Saat sebuah dokumen Incoming **disimpan** (`Incoming::SaveNew()`), sistem menulis ke **BEBERAPA tabel sekaligus** dalam satu transaksi, lalu memanggil fungsi akumulasi stok & kartu stok. Ini berarti satu baris item incoming **tidak berdiri sendiri** — ia melahirkan baris di stok gudang dan (turunannya) di pengiriman/produksi.

```
                         Incoming::SaveNew()
                                │  (satu trans_start ... trans_complete)
      ┌──────────────┬─────────┼───────────────┬──────────────────────┐
      ▼              ▼         ▼               ▼                      ▼
 tr_incoming    dt_incoming_po  dt_trans_po   dt_incoming        stock_material
 (header)       (detail PO)   (UPDATE berat) (detail item)      (STOK GUDANG +qty)
                                                                      │
                                              Pr_model::akumulasi_stock($no_surat)
                                              Pr_model::kartu_stok($no_surat)
                                                                      │
                                              (hilir, terjadi kemudian oleh modul lain)
                                                                      ▼
                                              dt_delivery_order_child  →  tr_delivery_order
                                              (barang dikirim / dijual ke customer)
                                                                      ▼
                                              tr_receive_invoice_ap_*  (nilai hutang / AP)
```

## Tabel Inti

### `tr_incoming` — Header dokumen incoming
| Kolom | Tipe | Keterangan |
|---|---|---|
| id | int PK | auto |
| id_data | varchar | ID internal, format `IC_065_MP_06_2026` (underscore) |
| id_incoming | varchar | No dokumen tampil, format `IC-065/MP-06/2026` (slash) |
| id_suplier | varchar | FK ke master_supplier |
| id_gudang | varchar | FK ke ms_gudang |
| sj_supplier | varchar | No Surat Jalan supplier |
| tanggal | date | Tgl transaksi |
| status_logistik | enum('1','0') | Sudah diproses logistik |
| status_jurnal | enum('0','1') | Sudah dijurnal ke GL |
| no_invoice_rec_ap | varchar | No invoice saat di-receive AP |
| nilai_invoice | decimal | Nilai invoice AP |
| rec_ap | int | 1 = sudah masuk Receive Invoice AP |
| total_bayar_ap | double | |

### `dt_incoming` — Detail item incoming (INI yang berisi "item")
| Kolom | Tipe | Keterangan |
|---|---|---|
| id_dt_incoming | int PK | auto |
| id_data | varchar | = tr_incoming.id_data |
| id_incoming | varchar | = tr_incoming.id_incoming |
| id_detail_incoming | varchar | `{id_data}-{urut}` |
| id_dt_po | varchar | FK ke dt_trans_po |
| id_material | varchar | FK material |
| nama_material | varchar | |
| lotno | varchar | **No LOT — kunci pelacakan ke stok & pengiriman** |
| weight / actual_berat | double | Berat |
| qty_recive / qty_sheet | int | Kuantitas |
| id_gudang | varchar | Gudang tujuan |

### `stock_material` — Stok gudang (bertambah saat incoming disimpan)
Kolom kunci: `lotno`, `qty`, `weight`, `totalweight`, `booking`, `sisa_spk`, `delivery_book`, `total_kirim`, `no_kirim`, `status_do` (OPN/CLS), `aktif` (Y/N), `id_incoming`, `deleted`.
Relasi ke incoming: `stock_material.id_incoming = tr_incoming.id_data` dan `stock_material.lotno = dt_incoming.lotno`.

### Tabel turunan / hilir
| Tabel | Peran |
|---|---|
| dt_incoming_po | Detail PO yang diterima |
| dt_trans_po | Progress penerimaan PO (berat_terima, sts_incoming) |
| dt_delivery_order_child / tr_delivery_order | Pengiriman barang ke customer (memakai lot dari stok) |
| tr_receive_invoice_ap_header / _detail | Receive Invoice AP (hutang supplier), difilter by `id_incoming` |
| gl_metalsindo_live.jurnal | Jurnal akuntansi (dipicu status_jurnal) |
| dt_incoming_delete / tr_incoming_delete / dt_incoming_backup_202609 | Arsip / backup |

## Catatan Integritas Penting
- **Tidak ada foreign key constraint DB** (CodeIgniter mengelola relasi via query manual). Menghapus baris di satu tabel tidak otomatis membersihkan/menyesuaikan tabel lain.
- Kunci penghubung incoming → stok adalah **lotno** dan **id_incoming (=id_data)**.
- Barang yang sudah masuk stok bisa **dibooking, dikirim (DO), diproduksi (SPK/slitting)**. Setelah itu, menghapus incoming-nya akan meninggalkan referensi menggantung.
