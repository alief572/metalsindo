# 04 — Pengujian

## 1. Vektor uji perhitungan (fungsi pajak)

Jadikan tabel ini sebagai uji unit (atau skrip uji) untuk fungsi pajak. Pembulatan di setiap langkah, setengah naik, IDR.

| Nilai | DPP = Nilai × 11/12 | PPn = DPP × 12% | Nilai Invoice |
|---:|---:|---:|---:|
| 735,000 | 673,750 | 80,850 | 754,600 |
| 1,950,000 | 1,787,500 | 214,500 | 2,002,000 |
| 2,000,000 | 1,833,333 | 220,000 | 2,053,333 |
| 3,200,000 | 2,933,333 | 352,000 | 3,285,333 |
| 4,550,000 | 4,170,833 | 500,500 | 4,671,333 |
| 4,800,000 | 4,400,000 | 528,000 | 4,928,000 |
| 5,000,000 | 4,583,333 | 550,000 | 5,133,333 |
| 1,043,000 | 956,083 | 114,730 | 1,070,813 |
| 0 | 0 | 0 | 0 |

Skenario penuh (Case 1 di FAT): PO 100 × 65,000 → Sub Total 6,500,000, PPN 715,000, Total 7,215,000. DP 30% → Nilai 1,950,000 → Invoice 2,002,000. Incoming 100 (nilai 6,500,000), potongan DP 30% = 1,950,000, Nilai 4,550,000 → Invoice 4,671,333. Σ Nilai = 6,500,000 = Nilai PO sebelum PPN; Σ Invoice dibayar = 6,673,333.

Skenario bertahap (Case 2): PO 200 × 50,000 (10,000,000), DP 20% → Nilai 2,000,000. Incoming 120 → dasar 6,000,000, potongan 1,200,000, Nilai 4,800,000. Incoming 80 → dasar 4,000,000, potongan 800,000, Nilai 3,200,000. Σ Nilai = 10,000,000.

## 2. Aturan yang wajib diuji

- Tipe Progress dan Retensi tidak ada di UI maupun `checkbx`.
- DP kosong pada PO menyimpan tanpa error dan tidak memunculkan baris di panel DP; DP > 100 ditolak.
- Satu PO tidak bisa memiliki dua invoice DP.
- Total invoice (DP + Incoming) per PO tidak melebihi Nilai PO sebelum PPN.
- Gabung incoming dari supplier, mata uang, atau kurs berbeda ditolak.
- Server menghitung ulang nilai; mengubah nilai tersembunyi di form tidak mengubah hasil simpan.
- Nomor PR sama sebelum dan sesudah approval tiga level, setelah reject lalu edit, dan di Metode Pembelian.
- Fase 2: edit invoice saat Waiting Checker memperbarui request yang sama; setelah Checker approve invoice terkunci; hapus invoice menarik request dan membebaskan incoming; request ditolak → perbaiki dan kirim ulang membuat request baru dan request lama tidak dihitung ganda.

## 3. Dokumen FAT

`specs/input/PR To Payment/FAT_PR-To-Payment HFG (26 Nov 2026).xlsx` (format standar FAT HFG).

| Sheet | Skenario | Status |
|---|---|---|
| FAT PR-PAY Case 1 | PO DP 30% + Incoming penuh, sampai Payment | Dijalankan pada **mockup**: 18 Passed (TC-PO-006 semula Potential, diperbaiki 2026-11-26) |
| FAT PR-PAY Case 2 | PO DP 20% + Incoming bertahap | Belum dijalankan |
| FAT PR-PAY Case 3 | PO tanpa DP + uji negatif | Belum dijalankan |
| Data & Rumus | Hitungan yang diharapkan, prasyarat data | – |

Hasil "Passed" pada Case 1 berarti **mockup** sesuai expected result, bukan aplikasi asli. Programmer menjalankan ulang FAT pada aplikasi hasil modifikasi dan mengisi kolom Actual Result, Status, Tester, dan Test Date.

Pada aplikasi asli, jurnal belum termasuk dalam FAT (belum ada spesifikasi akuntansi).

## 4. Cara memeriksa data

Gunakan skrip SQL baca-saja pada `hfg_dev` (tidak ada infrastruktur uji PHP). Contoh pemeriksaan batas total per PO setelah simpan:

```sql
SELECT d.no_po, SUM(d.nilai) AS total_nilai
FROM tr_invoice_po_detail d
GROUP BY d.no_po;   -- bandingkan dengan nilai PO sebelum PPN
```
