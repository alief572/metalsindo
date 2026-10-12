# PRD — Modul Incoming (Penerimaan Barang Material)

> Modul: `application/modules/incoming`
> Dibuat: 2026-09-10.

## Tujuan
Mencatat penerimaan barang material dari supplier (berdasarkan PO), menambah stok ke gudang, dan menjadi sumber data untuk penerimaan hutang (Receive Invoice AP) serta jurnal akuntansi.

## Stakeholder
- **Logistik / PIC gudang** — input dokumen incoming, timbang, terima barang.
- **Bagian Hutang (AP)** — menerima invoice supplier berdasarkan incoming.
- **Akuntansi** — jurnal inventory / unbilled / AP.
- **Marketing / Delivery** — mengambil stok hasil incoming untuk dikirim ke customer.

## Alur Bisnis Utama
1. PO disetujui (`tr_purchase_order.status=2`).
2. Barang datang → dibuat dokumen Incoming (`SaveNew`): header `tr_incoming` + detail per lot `dt_incoming`.
3. Stok bertambah otomatis di `stock_material` (per lot).
4. Progress PO diperbarui (`dt_trans_po.berat_terima`, `sts_incoming`).
5. Logistik mengisi biaya/kurs (`SaveLogistik`), lalu status_logistik=1.
6. Dokumen bisa dijurnal (status_jurnal=1) dan/atau di-receive ke Invoice AP (rec_ap=1).
7. Stok hasil incoming dipakai untuk pengiriman (Delivery Order) / produksi (SPK/slitting).

## Entitas Kunci
- **No Dokumen**: `IC-xxx/MP-mm/yyyy` (mis. `IC-065/MP-06/2026`).
- **Lot No**: penanda unik gulungan/lembar material (mis. `V530164-110626-321219-1`) — kunci pelacakan sampai barang keluar.

## Aturan / Batasan Penting
- Satu item incoming = satu lot = satu baris stok. Menghapus item = harus ikut mengurus stok & turunannya.
- Modul **tidak menyediakan fitur hapus item yang aman**; edit bersifat append (berisiko duplikat). Perubahan data historis harus lewat prosedur manual bertransaksi + verifikasi hilir.
- Kondisi "aman diubah": dokumen **belum** di-receive AP (`rec_ap=0`), **belum** dijurnal (`status_jurnal=0`), dan lot **belum** dibooking/dikirim/diproduksi.

## Kasus Terkait (2026-09)
Laporan user: dokumen `IC-065/MP-06/2026` ada item salah input (lot `V530164-110626-321219-1`) yang diminta dihapus karena memengaruhi nilai Receive Invoice AP. Lihat laporan analisis risiko: `md/incoming/ANALISIS_RISIKO_IC-065.pdf`.
