---
inclusion: always
---

# Aturan Dokumentasi Modul

## Wajib Baca Dokumentasi Sebelum Perubahan

Ketika user meminta perubahan (tambah fitur, fix bug, refactor, atau modifikasi apapun) pada suatu modul di project ini, **WAJIB** lakukan langkah berikut:

### 1. Cek Dokumentasi Modul

Cek apakah folder `/md/{nama_modul}/` sudah ada dan berisi:

- `PRD.md` — Product Requirements Document
- `DESIGN.md` — Dokumen Desain Teknis
- `ERD.md` — Entity Relationship Diagram

### 2. Jika Dokumentasi Sudah Ada

**WAJIB baca ketiga file tersebut terlebih dahulu** sebelum melakukan perubahan apapun. Ini untuk memastikan:

- Memahami konteks bisnis dan alur yang sudah berjalan
- Mengetahui struktur tabel dan relasi antar entitas
- Tidak merusak fitur yang sudah ada
- Perubahan konsisten dengan arsitektur yang sudah dibangun

### 3. Jika Dokumentasi Belum Ada

**WAJIB buatkan dokumentasi lengkap terlebih dahulu** sebelum melakukan perubahan:

1. Explore controller, model, dan views modul tersebut
2. Query database untuk mendapatkan struktur tabel terkait
3. Buat `PRD.md` (fitur, alur bisnis, stakeholder, perhitungan)
4. Buat `DESIGN.md` (arsitektur, endpoint, sequence diagram, state machine)
5. Buat `ERD.md` (struktur tabel, relasi, diagram visual)
6. Simpan di folder `/md/{nama_modul}/`
7. Baru kemudian lakukan perubahan yang diminta

### 4. Update Dokumentasi Setelah Perubahan

Setelah perubahan selesai, **update dokumentasi** yang relevan agar tetap sinkron dengan kondisi terkini modul.

## Lokasi Dokumentasi

```
/md/{nama_modul}/
├── PRD.md        # Product Requirements Document
├── DESIGN.md     # Desain Teknis
└── ERD.md        # Entity Relationship Diagram
```

## Contoh Modul yang Sudah Didokumentasi

- `/md/wt_invoicing/` — Modul Invoicing

## Catatan

- Nama folder modul mengikuti nama folder di `application/modules/`
- Dokumentasi ditulis dalam Bahasa Indonesia
- ERD menggunakan format tabel markdown + diagram ASCII art
- Jika ada perubahan schema database, update ERD.md
- Jika ada fitur baru, update PRD.md dan DESIGN.md
