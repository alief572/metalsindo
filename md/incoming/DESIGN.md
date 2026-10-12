# DESIGN — Modul Incoming

> Modul: `application/modules/incoming`
> Controller utama: `controllers/Incoming.php` (±2857 baris)
> Model: `models/Pr_model.php`
> Dibuat: 2026-09-10.

## Method Kunci di `Incoming.php`

| Baris | Method | Fungsi |
|---|---|---|
| 1105 | `SaveNew()` | **Simpan dokumen incoming baru.** Insert `tr_incoming`, `dt_incoming_po`, `dt_incoming`, `stock_material`; update `dt_trans_po`; lalu `akumulasi_stock()` + `kartu_stok()`. |
| 1353 | `SaveUpdate()` | Edit incoming. Hanya UPDATE `keterangan` header, lalu **INSERT baru** ke `dt_incoming` & `stock_material` per item (tidak menghapus baris lama). |
| 1019 | `delDetail()` | **BUKAN** hapus item incoming — men-soft-delete `ms_dimensi` (dimensi material). Tidak relevan. |
| 1440 | `SaveEdit()` | Warisan untuk purchase_order (bukan incoming). |
| 2292 | `SaveTimbang()` | Proses timbang. |
| 2388 | `SaveLogistik()` | Proses logistik (kurs, biaya, set status_logistik). |
| 2655 | `jurnal_ap()` | Terkait jurnal AP. |
| 2694 | `saveIncomingCustomer()` | Incoming milik customer (titipan). |

## Temuan Desain Kritis

1. **Tidak ada endpoint aman untuk "hapus satu item".**
   Method `delDetail()` yang namanya menjanjikan ternyata menyentuh `ms_dimensi`, bukan `dt_incoming`. Tidak ada rute yang: menghapus baris `dt_incoming` + membalik `stock_material` + memperbaiki `dt_trans_po.berat_terima` secara atomik.

2. **Edit (`SaveUpdate`) bersifat append, bukan replace.**
   Menyimpan ulang dokumen incoming lewat edit akan **menduplikasi** baris `dt_incoming` dan `stock_material` (stok jadi dobel). Ini efek samping berbahaya bila dipakai untuk "memperbaiki" item.

3. **Insert stok tidak reversibel dari UI.**
   `SaveNew()` menambah qty ke `stock_material`. Tidak ada aksi UI yang mengurangi/menonaktifkan baris stok tsb bila item incoming dibatalkan.

4. **Relasi antar tabel manual (tanpa FK).**
   Konsistensi dijaga oleh logika aplikasi, bukan constraint DB. Perubahan data harus manual & lengkap agar tidak menggantung.

## State / Status Penting
- `tr_incoming.status_logistik` (0/1), `status_jurnal` (0/1), `rec_ap` (0/1).
- `stock_material.aktif` (Y/N), `status_do` (OPN/CLS), `deleted` (soft delete), `booking`, `total_kirim`.
- Sekali `rec_ap=1` (masuk Receive Invoice AP) atau `status_jurnal=1` (sudah dijurnal), perubahan item berdampak ke keuangan/akuntansi.

## Prosedur Aman yang Direkomendasikan (untuk perubahan data)
Karena tidak ada tombol hapus item yang aman, perubahan harus dilakukan lewat **SQL manual bertransaksi** dengan urutan:
1. Backup baris terkait ke tabel arsip.
2. Cek hilir (booking/DO/SPK/AP/jurnal). Bila sudah terpakai → JANGAN hapus, gunakan mekanisme retur/koreksi.
3. Bila belum terpakai: hapus `dt_incoming` item, non-aktifkan/hapus baris `stock_material` (lotno), sesuaikan `dt_trans_po.berat_terima`.
4. Verifikasi ulang total dokumen & stok.
