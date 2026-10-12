# Eksperimen Go — material_planing

Folder ini hasil copy dari `application/modules/material_planing`
ke `application/modules/material_planing_experiment` biar PHP asli aman.
Backend Go ada di subfolder `backend-go/` (satu-satunya dependency luar: `github.com/go-sql-driver/mysql`).
Frontend copy-an (`views/index.php`) sudah diarahkan ke Go via flag `USE_GO`.

## Asal PHP -> Go

| PHP (CodeIgniter) | Go | Keterangan |
|---|---|---|
| `Inventory_4_model::get_data_material_planning()` | `GET/POST /api/material-planning`, `/api/material-planning/list` | DataTables server-side: `{draw,start,length,search[value]}` -> `{draw,recordsTotal,recordsFiltered,data[]}`. SQL asli: `SELECT * FROM view_material_planning`. |
| `Material_planing::EditHeader()` | `POST /api/material-planning/edit-header` `{id,id_material,width,view}` | Balikan JSON `{header,stok,stok_fg,stok_book,get_po_pr,viewx}`. SQL asli: `dt_spkmarketing JOIN tr_spk_marketing` + `stock_material` gudang `1` (WIP) & `3` (FG) + `SUM(dt_trans_pr)-SUM(dt_incoming)`. |
| `Material_planing::Approve()` | `POST /api/material-planning/approve` `{id}` | `UPDATE dt_spkmarketing SET status_lanjutan='2'`. |
| `Material_planing::Close()` | `POST /api/material-planning/close` `{id}` | `UPDATE dt_spkmarketing SET status_lanjutan='3'`. |
| `Material_planing::Tutup()` | `POST /api/material-planning/tutup` `{id}` | `UPDATE dt_spkmarketing SET status_close_planning='CLS'`. |
| `Material_planing::SaveEditHeaderNew()` | `POST /api/material-planning/save-fg` | Body `{id_material,id_customer,width,id_dt_spkmarketing,no_surat,detail[{id,id2,berat,width,qty,weight,keterangan}]}`. Asli: `insert_batch stock_material_customer` + `update_batch stock_material`. |
| — | `GET /health` | Cek hidup. |

Format `data[]` list disamakan dengan `views/index.php:DataTables()`:
`no,no_spk,customer,kode_material,no_aloy,thickness,width,length,delivery_date,total_weight,total_sheet,total_spk,fg,action`.

## Jalanin

```powershell
cd application\modules\material_planing_experiment\backend-go
go vet ./...
go run .
# server di :8081
```

Build:

```powershell
go build -o material-planning-go.exe .
.\material-planning-go.exe
```

Env (lihat `.env.example`):

- `PORT` default `8081` (PHP pakai `8080`, XAMPP `80`, jadi tidak tabrakan).
- `USE_MOCK=true` -> paksa mock 3 SPK. Kalau unset/kosong -> pakai DB asli, gagal -> fallback mock (lihat log `fallback mock`).
- `DB_HOST/DB_PORT/DB_USER/DB_PASS/DB_NAME` default = **DB yang sama dengan modul PHP asli**:
  `206.84.97.106:3306 / metalsindo_dev` (lihat `application/config/development/database.php`,
  `ENVIRONMENT=development`). Terbukti: `recordsTotal=5456` sama dengan `SELECT COUNT(*) FROM view_material_planning`.

## MySQL lokal / offline (opsional)

Kalau remote tidak terjangkau, ada skema minimal + seed 3 baris:

```powershell
# import sekali saja (MySQL XAMPP, user root tanpa password)
Get-Content schema.sql | & "C:\xampp56\mysql\bin\mysql.exe" -u root
Get-Content seed.sql | & "C:\xampp56\mysql\bin\mysql.exe" -u root

# jalan dengan DB lokal
$env:DB_HOST="localhost"; $env:DB_USER="root"; $env:DB_PASS=""; $env:DB_NAME="material_planning_dev"
go run .
# /health -> {"useDB":true,"mock":false}
```

Tanpa env apa pun Go langsung nempel DB asli (`go run .` -> `/health useDB:true`).
File `schema.sql` = tabel minimal (`view_material_planning` dibuat sebagai TABLE agar jalan lokal;
di `metalsindo_dev` aslinya itu VIEW — query SELECT-nya sama). `seed.sql` = 3 SPK + stok + PR/incoming contoh.

Catatan skema asli: `length_val` bertipe varchar (di-parse defensif ke float),
`delivery_date` bertipe date (diforce string via `DATE_FORMAT`), view juga membawa
`id_customer` (dipakai `edit-header`, bukan lagi hardcode).

## Frontend copy-an -> Go

`application/modules/material_planing_experiment/` adalah modul CI yang hidup
(controller `Material_planing_experiment`, permission sama `Material_Planing.*`
jadi role yang ada langsung bisa buka, tanpa ubah DB). Buka di browser:

- Asli: `http://localhost/metalsindo_2/material_planing` (judul "Material Planing")
- Experiment: `http://localhost/metalsindo_2/material_planing_experiment` (judul "Material Planing (GO Experiment)")

Keduanya redirect ke login dulu (wajar — `Admin_Controller`). Modul experiment
tidak muncul di sidebar (menu diambil dari DB) jadi akses via URL langsung.
`views/index.php` copy-an punya flag:

```js
var USE_GO = true;
var GO_BASE = 'http://localhost:8081/api/material-planning';
```

- `true` -> DataTables (`/list`), Edit (`/edit-header`), Approve (`/approve`), Tutup (`/tutup`), Close (`/close`) lari ke Go.
- `false` -> balik ke PHP `siteurl + 'material_planing/...'` seperti semula.
- Catatan: Go `edit-header` mengembalikan JSON (bukan HTML seperti PHP), jadi modal dirender ringkas
  (`no_surat`, jumlah lot WIP/FG, `get_po_pr`, + `<pre>` JSON). Full port tampilan `EditHeader.php` belum dikerjakan.

## Contoh curl (PowerShell `Invoke-WebRequest`)

```powershell
(Invoke-WebRequest http://localhost:8081/health -UseBasicParsing).Content

$b='{"draw":1,"start":0,"length":10,"search":{"value":""}}'
(Invoke-WebRequest http://localhost:8081/api/material-planning/list -Method POST -Body $b -ContentType "application/json" -UseBasicParsing).Content

$b='{"id":"P2600001-1","id_material":"SUS304-2B-0.8","width":1219,"view":"edit"}'
(Invoke-WebRequest http://localhost:8081/api/material-planning/edit-header -Method POST -Body $b -ContentType "application/json" -UseBasicParsing).Content

$b='{"id":"P2600001-1"}'
(Invoke-WebRequest http://localhost:8081/api/material-planning/approve -Method POST -Body $b -ContentType "application/json" -UseBasicParsing).Content

$b='{"id_material":"SUS304-2B-0.8","id_customer":"CUST-001","id_dt_spkmarketing":"P2600001-1","no_surat":"001/MP/SPK/IX/2026","detail":[{"id2":"STK-FG-001","berat":1200,"width":1219,"qty":5,"weight":240}]}'
(Invoke-WebRequest http://localhost:8081/api/material-planning/save-fg -Method POST -Body $b -ContentType "application/json" -UseBasicParsing).Content
```

Form-encoded ala jQuery DataTables / `views/index.php` juga bisa (tanpa JSON):

```powershell
(Invoke-WebRequest http://localhost:8081/api/material-planning/list -Method POST -Body "draw=1&start=0&length=10&search[value]=SUS304" -ContentType "application/x-www-form-urlencoded" -UseBasicParsing).Content
```

## Status verifikasi

- `go vet ./...` OK, `go build` OK
- DB asli `metalsindo_dev` (default, tanpa env): `health useDB:true`,
  `list recordsTotal=recordsFiltered=5456` (sama dengan COUNT di DB),
  `edit-header` SPK `P2401123-1` balikin header + lot stok + `get_po_pr:29064.40` asli (read-only).
- Mock (`USE_MOCK=true`): `health`, `list`, `edit-header`, `approve`, `close`, `tutup`, `save-fg` semua 200.
- DB lokal `material_planning_dev`: `approve` beneran `UPDATE dt_spkmarketing` (dicek via mysql, lalu di-restore).
- `php -l` untuk `views/index.php` copy-an OK; `material_planing/` asli tidak diubah (cek `git status`).
