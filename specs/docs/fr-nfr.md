# FR / NFR — Product Costing (To Be)

Spesifikasi kebutuhan fungsional (FR) dan non-fungsional (NFR) untuk **Product Costing sampai Penawaran**.

- **Sumber:** `input/Product Costing/` (diagram `D001-BPM-Costing To Be.png`, revisi `D002-BPM-Costing To Be.png`, dan workbook `New Structure Material&Product.xlsx`, 10 sheet), ditambah konfirmasi user 2026-11-10 revisi 2026-11-12 (K35), dan revisi 2026-11-18 (K38 sampai K46) (lihat *Keputusan yang sudah dikonfirmasi*).
- **Pendamping:** ERD `docs/diagrams/erd.drawio`. Kolom *Entitas* pada tiap FR menunjuk tabel di ERD tersebut.
- **Status:** draf untuk review. Hal yang belum pasti ada di *Pertanyaan Terbuka* (OQ-PC-xx; 1 tersisa); tidak ada aturan yang saya tambahkan di luar sumber di atas.
- **Penomoran:** `FR-PC-nn`, `NFR-PC-nn`, `OQ-PC-nn` (awalan PC agar tidak tertukar dengan penomoran lama yang sudah dikosongkan).

## Cakupan

**Dalam cakupan:** Master Material (struktur baru Roll dan Sheet), Price Reference (Purchase dan Costing), Rate (Master Rate Umum, termasuk Rate Man Power), Flow Process dan Cycletime, perhitungan Product Costing sampai Bottom Price, Pricelist beserta approval Management, sambungan Bottom Price ke Penawaran (Pricelist Roundup, Price Quotation, Diskon, dan Approval Diskon oleh Direktur), dan notifikasi lonceng untuk Price Reference yang kedaluwarsa.

**Di luar cakupan:** Logistic Cost (dikonfirmasi tidak termasuk), Rate Machine per jam (dikonfirmasi diabaikan; Rate Machine memakai Rp/Kg), Total Man Hour dan pengaruh Cycletime terhadap Man Power Cost (tidak dipakai lagi, K14 digantikan K35), komponen gaji/BPJS Rate Man Power, struktur/desain Product Costing Rev.1 yang lama (dibuang), edit Master Material Roll, dan alur SPK Produksi Non SPK (sheet *Temuan Tambahan* poin 1 dan 4 dinyatakan tidak perlu).

## Aktor

Mengikuti lane pada diagram BPM:

| Aktor | Peran dalam alur |
|---|---|
| Engineering | Mengembangkan material/varian baru; menyusun Flow Process dan Cycletime |
| Finance | Memperbarui Kurs dan Price Reference |
| Costing | Menetapkan seluruh Rate (termasuk Rate Man Power Rp/Kg) |
| Accounting | Menghitung Material Cost sampai Bottom Price |
| Sales | Menyusun Price List, mengajukan approval, dan memakai pricelist yang dirilis pada Penawaran |
| Management | Menyetujui atau menolak Pricelist |
| Direktur | Menyetujui atau menolak Diskon Penawaran yang melebihi batas (K43) |

## Keputusan yang sudah dikonfirmasi (user, 2026-11-10)

| # | Keputusan |
|---|---|
| K1 | Rate Machine memakai **Rp/Kg**; catatan rate mesin per jam diabaikan |
| K2 | Cycletime berstatus **menit/Kg** |
| K3 | FOH dan Waste berjalan paralel pada diagram; keduanya menjumlah ke HPP. **Waste = Rate % Waste × Biaya Material** |
| K4 | Label formula Biaya Admin di sheet *Konsep-Sim Man.Sup,Other Cost* adalah salah ketik |
| K5 | Modul Price Reference menyediakan **4 pilihan masa berlaku**: 1 Bulan, 3 Bulan, Semester, Tahunan |
| K6 | Logistic Cost tidak termasuk cakupan |
| K7 | Setiap simpan Master Rate Umum (termasuk Rate Man Power sejak K35) membuat **versi baru** (riwayat dipertahankan, versi terbaru aktif) |
| K8 | Kolom Machine pada Flow Process dipilih dari **master Asset berkategori Machine** |
| K9 | Bottom Price pada Penawaran diambil dari **pricelist yang sudah dirilis Management** |
| K10 | Pricelist berlaku untuk **semua customer** |
| K11 | Price Reference Purchase **tidak memakai approval** |
| K12 | Flow Process **tanpa versi** (cukup waktu update terakhir) |
| K13 | Status pricelist: DRAFT → DIAJUKAN → DISETUJUI → DIRILIS; bila ditolak, kembali ke DRAFT dan keputusan tercatat di log |
| K14 | ~~Biaya Man Power = Cycletime × Rate Man Power~~ **digantikan K35.** Total Man Hour tidak dipakai lagi |
| K15 | Price Reference yang **kedaluwarsa memblokir costing** dan sistem memberi **notifikasi lonceng** |
| K16 | Costing dihitung **lewat tombol "Update Costing"**; perubahan rate, Price Reference, atau Flow Process tidak memicu hitung otomatis |
| K17 | Pricelist lama (Book Price dan LME) beserta field Komisi dan Profit % pada Penawaran **digantikan** oleh pricelist yang dirilis |
| K18 | File Evidence dan Note pada Price Reference Purchase **tidak wajib** |
| K19 | **Material Ekuivalen** dipakai modul Produksi: bila stok material yang dipesan habis, produksi boleh memakai material ekuivalennya |
| K20 | **Price Reference Costing diturunkan dari Higher Price Purchase** (diganti K40: dari Costing Price) |
| K21 | Pricelist **boleh dirilis parsial** |
| K22 | Costing **dihitung sebagian** bila ada Rate yang belum terisi (sejak K35 Flow Process tidak lagi menjadi syarat) |
| K23 | **Material Ekuivalen bersifat dua arah dan berantai** (A=B dan B=C berarti A=C), (disempurnakan K36) |
| K24 | **Satuan pricelist seluruhnya Rp/Kg.** Nilai penawaran Sheet = Pricelist Rp/Kg × Berat/Sheet × Jumlah order sheet; Roll = Pricelist Rp/Kg × Jumlah order Kg (disempurnakan K42: Pricelist Rp/Kg diganti Price Quotation) |
| K25 | Kolom **Man Power** (jumlah orang) pada Flow Process tetap disimpan sebagai informasi |
| K26 | **Update Costing berlaku untuk semua material** sekaligus |
| K27 | Notifikasi lonceng diterima oleh **PIC yang memiliki akses modul tersebut** dan **muncul ketika Update Costing diklik** |
| K28 | **Price Reference Costing diturunkan otomatis** dari Higher Price Purchase (diganti K40: dari Costing Price) |
| K29 | Costing berstatus SEBAGIAN **tidak boleh** masuk Pricelist; harus **LENGKAP** |
| K30 | Management **boleh menyetujui sebagian baris** pricelist |
| K31 | **Masa berlaku Price Reference Costing mengikuti** baris Purchase sumbernya |
| K32 | Baris pricelist yang **tidak disetujui kembali ke DRAFT** |
| K33 | **Material Sheet tanpa Maker dan Surface**; **Hardness tidak wajib diisi**. Nama Material Sheet = Spesifikasi + Tebal + Panjang + Lebar + Type Alloy (**diganti K37**) |
| K34 | **Masa berlaku Price Reference Purchase dapat diubah** tanpa memasukkan harga baru (**diganti K39**: masa berlaku kini global per tipe material) |
| K36 | **(2026-11-12)** Material Ekuivalen berasal dari **dua sumber**: (a) **otomatis dari Master Spesifikasi** (material yang spesifikasinya sama; kelompok spesifikasi ekuivalen **dihapus oleh K44**), dan (b) **relasi manual** yang ditambahkan Engineering. **Setiap relasi, termasuk yang otomatis, dapat dihapus**, dan material lain dapat ditambahkan, dari material mana pun (berlaku dua arah). Daftar ekuivalen saat ini bersifat sementara. Kelompok spesifikasi ekuivalen (mis. SPCC = SPC270C = JSC270C) belum diverifikasi tim material |
| K37 | **(2026-11-12, Master Material Level 4)** (1) **Sheet tanpa Komposisi Material.** (2) **Nama Material Sheet otomatis** = Spesifikasi + Hardness (dilewati bila kosong) + Thickness + Width + Length + Type Alloy (dari master Level 3), dan **tetap dapat diedit** pengguna. **Nama Material Roll juga otomatis tetapi dapat diedit** (2026-11-12). (3) **Ekuivalen otomatis tidak hanya sampai spesifikasi, tetapi juga thickness yang sama** (diganti K44: Spesifikasi sama, lalu Thickness sama). (4) Untuk Sheet, **Density, Thickness, Width, dan Length wajib diisi** karena menentukan rumus Berat/Sheet |
| K35 | **(Revisi D002, 2026-11-12)** **Man Power Cost = Rate Man Power (Rp/Kg) yang diinput langsung**; tidak lagi dipengaruhi Cycletime. Rate Man Power diinput di **Master Rate Umum** (modul Rate), menu Rate Man Power terpisah (komponen gaji, BPJS, Faktor Indirect Labor) ditiadakan. **Cycletime dan Flow Process dibiarkan** (tidak dihapus) tetapi tidak ikut menghitung costing |
| K38 | **(2026-11-18)** Daftar Price Reference Purchase **dikelompokkan berdasarkan Level 2** (Ferrous dan Non Ferrous) |
| K39 | **(2026-11-18)** **Masa berlaku Price Reference bersifat global per tipe material**: satu untuk **Ferrous** dan satu untuk **Non Ferrous**, dengan pilihan tetap (1 Bulan, 3 Bulan, Semester, Tahunan). **Berlaku s/d** setiap material = tanggal update harga terbaru material itu + masa berlaku tipenya. Mengubah masa berlaku global menghitung ulang seluruh material pada tipe tersebut. Menggantikan K34 (masa berlaku per material) |
| K40 | **(2026-11-18)** Price Reference Purchase memakai **Last Purchase** dan **Costing Price** sebagai pengganti Lower Price dan Higher Price. **Last Purchase** diambil dari **History Pembelian terakhir**, yaitu PO material yang **sudah incoming** (Roll per Maker, Sheet tanpa Maker), dalam **USD** (Rp = USD × kurs), disimpan sebagai **snapshot** di riwayat, dan **dikosongkan bila belum ada riwayat**. **Costing Price** diinput bebas dalam **USD** (Rp = USD × kurs) dan **harus lebih besar dari Last Purchase**. Price Reference Costing diturunkan dari Costing Price (menggantikan K20 dan K28) |
| K41 | **(2026-11-18)** **Material Ekuivalen otomatis didasari Spesifikasi, Thickness, dan Maker** (**syarat Maker dicabut oleh K44**). **Sheet boleh ekuivalen dengan Roll karena Roll dapat diolah menjadi Sheet, tetapi Sheet tidak dapat menjadi Roll**, sehingga relasi otomatis Roll dan Sheet **searah** (searah Roll dan Sheet tetap berlaku) |
| K42 | **(2026-11-18, Penawaran)** **Pricelist Roundup** = Pricelist Rp/Kg dibulatkan **ke atas ke kelipatan Rp100**, hanya dihitung di Penawaran (pricelist dirilis tidak berubah). **Price Quotation** (Rp/Kg) **default-nya Pricelist Roundup dan dapat diedit**, termasuk di bawah Bottom Price |
| K43 | **(2026-11-18, Penawaran)** **Diskon dihitung per penawaran**, diinput sebagai **persen atau nominal Rp** yang saling terkonversi, terhadap Total Quotation. **Diskon lebih dari 5%** (dan item dengan Price Quotation di bawah Bottom Price) memerlukan **Approval Direktur**. Bila **ditolak**, penawaran dikembalikan ke Penawaran untuk direvisi |
| K44 | **(2026-11-18)** **Aturan Material Ekuivalen disederhanakan**: (1) **Master Spesifikasi tidak lagi memiliki isian Spesifikasi Ekuivalen** (kelompok seperti SPCC = SPC270C ditiadakan); (2) ekuivalen otomatis divalidasi **berurutan: Spesifikasi sama, lalu Thickness sama**; (3) **Maker tidak menjadi syarat** (opsional, beda Maker dibiarkan); (4) kolom Maker pada daftar ekuivalen **diambil dari isian Maker material, bukan dari Supplier**. Searah Roll dan Sheet (K41) dan relasi manual (K36) tetap |
| K45 | **(2026-11-18)** Rumus **Berat/Sheet = Thickness (mm) × Width (mm) × Length (mm) × Berat jenis (g/cm³) ÷ 1.000.000**; Berat jenis adalah isian **Density** pada material Sheet |
| K46 | **(2026-11-18, dari aplikasi berjalan `penawaran/detail`; dipindah ke Price Reference Purchase)** Tabel **PRICELIST** (Book Price dan LME 10 Hari / 30 Hari / Spot) **tidak ditampilkan di Penawaran**, tetapi di **Price Reference Purchase pada setiap material** sebagai **referensi bagi Finance menentukan Costing Price**. **Non Ferrous**: baris Kurs Terbaru, Kurs 10 Hari, Kurs 30 Hari (LME dalam USD × kurs; Book Price sudah Rp) dan baris Dolar; **Ferrous**: hanya Book Price (Rp); bila belum ada, tampil "Price List Untuk Material Ini Belum Terinput". **Informasi pembanding saja**: Costing Price tetap diinput Finance, dan Bottom Price serta Price Quotation tetap dari pricelist dirilis (K17 tidak berubah) |

## Rumus Costing (ringkasan dari sheet *Draft Element Costing* dan *Simulasi Costing*)

Satuan harga: **Rp/Kg**. Untuk Sheet, nilai per sheet = Rp/Kg × Berat/Sheet.

| No | Elemen | Rumus | Satuan |
|---|---|---|---|
| 1 | Material | Price Reference Costing (Rp/Kg) × Berat | Rp/Kg (Sheet: Rp/Sheet dan Rp/Kg) |
| 2.1 | Man Power | Rate Man Power (Rp/Kg) diinput langsung (K35). Contoh simulasi: Rp 2.442,81 | Rp/Kg |
| 2.2 | Machine | Rate (Rp/Kg) | Rp/Kg |
| 2.3 | Consumable | Rate (Rp/Kg); rate = Biaya Consumable ÷ total Kg tahun lalu | Rp/Kg |
| 3 | FOH | Rate (Rp/Kg); rate = Biaya Kepala 5 ÷ total Kg tahun lalu (sudah termasuk logistik) | Rp/Kg |
| 4 | Waste | Rate % Waste × Biaya Material (K3) | Rp/Kg |
| — | **HPP** | Material + Man Power + Machine + Consumable + FOH + Waste (K3, sesuai angka simulasi) | Rp/Kg |
| 5 | Biaya Admin | Rate (Rp/Kg); rate = Biaya Kepala 6 ÷ total Kg tahun lalu | Rp/Kg |
| — | **Cost of Sales** | HPP + Biaya Admin | Rp/Kg |
| 6 | Profit | Rate % × Cost of Sales | Rp/Kg |
| — | **Sales Price** | Cost of Sales + Profit | Rp/Kg |
| 7 | Negotiation Allowance | Rate % × Sales Price | Rp/Kg |
| 8 | Commission Fee | Rate (Rp/Kg) | Rp/Kg |
| — | **Bottom Price** | Sales Price + Negotiation Allowance + Commission Fee (nilai yang muncul pada quotation) | Rp/Kg |

Nilai penawaran **Roll** = Pricelist Rp/Kg × Jumlah order Kg. Nilai penawaran **Sheet** = Pricelist Rp/Kg × Berat/Sheet × Jumlah order sheet (K24).

Rate Man Power **tidak lagi dihitung di sistem** (K35): nilainya Rp/Kg diinput langsung di Master Rate Umum; perhitungan asalnya dilakukan di luar sistem (sheet *Konsep-Sim Biaya MP* hanya sebagai referensi). **Berat/Sheet (kg) = Thickness (mm) × Width (mm) × Length (mm) × Berat jenis (g/cm³) ÷ 1.000.000** (Berat jenis = isian Density pada material, K45).

## Kebutuhan Fungsional

Kolom *Sumber*: **BPM** = langkah pada diagram, **WB** = sheet workbook, **K** = keputusan terkonfirmasi di atas.

### A. Master Material (aktor: Engineering)

| ID | Kebutuhan | Sumber | Entitas |
|---|---|---|---|
| FR-PC-01 | Engineering dapat menambah dan mengubah material baru atau varian material, baik **Roll** maupun **Sheet**. | BPM "Develop New Material atau New Varian Material"; WB *Struktur Roll*, *Struktur Sheet* | ms_inventory_category3 |
| FR-PC-02 | Material Roll memiliki atribut: Inventory Type, Ferrous/Non Ferrous (dipilih), Type Alloy (dipilih), Spesifikasi (dipilih dari Master Spesifikasi), Maker (teks bebas), Density (angka), Hardness (teks, **opsional**), Thickness (angka). | WB *Struktur Roll* | ms_inventory_category3, ms_spesifikasi |
| FR-PC-03 | Material Sheet memiliki atribut: Inventory Type, Ferrous/Non Ferrous (otomatis Ferrous), Type Alloy, Spesifikasi, Density, Thickness, Length, Width. **Tanpa Maker, Surface, dan Komposisi Material; Hardness opsional** (K33, K37). **Density, Thickness, Length, dan Width wajib diisi** (K37). | WB *Struktur Sheet* | ms_inventory_category3, ms_spesifikasi |
| FR-PC-04 | Sistem membentuk **Nama Material** otomatis: Roll = Type Alloy + Spesifikasi + Hardness + Thickness; Sheet = Spesifikasi + Hardness + Thickness + Width + Length + Type Alloy (K37). Bagian yang kosong (mis. Hardness) dilewati. Nama Material (Roll maupun Sheet) otomatis tetapi **dapat diedit** pengguna (K37). | WB *Struktur Roll*, *Struktur Sheet* | ms_inventory_category3 |
| FR-PC-05 | Sistem menghitung **Berat per Sheet** otomatis dengan rumus pada bagian Rumus Costing. | WB *Struktur Sheet* | ms_inventory_category3 (berat_sheet) |
| FR-PC-06 | Daftar **Spesifikasi** dikelola per Type Alloy dan menjadi pilihan pada material. Spesifikasi **tidak** memiliki isian ekuivalen; kesamaan Spesifikasi dipakai langsung pada Material Ekuivalen (FR-PC-07, K44). | WB *Struktur Roll* ("Select Master Spesifikasi") | ms_spesifikasi, ms_inventory_category2 |
| FR-PC-07 | **Material Ekuivalen** dibentuk dari dua sumber (K36): (a) **otomatis**, divalidasi **berurutan**: **Spesifikasi sama**, lalu **Thickness sama**; **Maker tidak menjadi syarat** (opsional, beda Maker dibiarkan) dan kolom Maker pada daftar diambil dari **isian Maker material, bukan dari Supplier** (K44); (b) **relasi manual**, yaitu Engineering memilih material lain. Relasi otomatis **searah antara Roll dan Sheet** (K41): untuk material **Sheet**, ekuivalennya adalah Sheet lain dan **Roll** dengan Spesifikasi dan Thickness yang sama, karena Roll dapat diolah menjadi Sheet; untuk material **Roll**, **Sheet tidak** menjadi ekuivalen. Relasi manual tetap **dua arah** (A ekuivalen B berarti B ekuivalen A) dan **berantai** (A=B dan B=C berarti A=C; ditampilkan dengan tanda *Berantai*). **Setiap relasi, termasuk yang otomatis, dapat dihapus** dari daftar sebuah material dan material lain dapat ditambahkan; penghapusan relasi otomatis dicatat agar tidak muncul kembali. Data tersedia bagi modul Produksi untuk memakai material ekuivalen bila stok material yang dipesan habis (modul Produksi di luar cakupan dokumen ini). | WB *Struktur Roll*, *Struktur Sheet*; K19, K23, K36, K41, K44 | ms_material_ekuivalen (jenis MANUAL atau HAPUS), ms_inventory_category3 |

### B. Price Reference (aktor: Finance)

| ID | Kebutuhan | Sumber | Entitas |
|---|---|---|---|
| FR-PC-08 | Finance memperbarui **Kurs** sebagai dasar konversi USD ke Rp. | BPM "Update Kurs" | perubahan_kurs |
| FR-PC-09 | Finance mengelola **Price Reference Purchase** dengan struktur Header dan Detail: untuk Roll per **Maker**, untuk Sheet **tanpa Maker**. Daftar **dikelompokkan berdasarkan Level 2** (Ferrous dan Non Ferrous) (K38). | WB *Price Reference Purchase*; K11 | ms_price_ref_purchase, dt_price_ref_purchase |
| FR-PC-10 | Detail Price Reference Purchase berisi **Last Purchase** dan **Costing Price**, masing-masing dalam USD/Kg dan Rp/Kg (Rp = USD × kurs). **Last Purchase** diambil otomatis dari **History Pembelian terakhir**, yaitu baris PO material tersebut yang **sudah incoming** (Roll per Maker; Sheet tanpa Maker), disimpan sebagai **snapshot** pada riwayat beserta rujukan baris PO sumbernya, dan **dikosongkan bila belum ada riwayat pembelian**. **Costing Price** diinput bebas dalam USD dan **harus lebih besar dari Last Purchase** (bila Last Purchase ada). Setiap perubahan menambah baris detail sebagai riwayat. File Evidence dan Note boleh dikosongkan (K18). | WB *Price Reference Purchase*; BPM "History Pembelian"; K18, K40 | dt_price_ref_purchase, dt_trans_po, perubahan_kurs |
| FR-PC-11 | **Masa berlaku Price Reference bersifat global per tipe material (Level 2)**: satu untuk **Ferrous** dan satu untuk **Non Ferrous**, dengan 4 pilihan: 1 Bulan, 3 Bulan, Semester, Tahunan. **Berlaku s/d** setiap material = tanggal update harga terbaru material itu + masa berlaku tipenya. Mengubah masa berlaku global menghitung ulang Berlaku s/d seluruh material pada tipe tersebut tanpa memasukkan harga baru, dan Price Reference Costing turunan ikut berubah (K31). Masa berlaku tidak lagi diatur per material (K34 diganti K39). | K5, K31, K39; WB *Price Reference Purchase/Costing* | ms_price_ref_setting, dt_price_ref_purchase, dt_price_ref_costing |
| FR-PC-12 | **Price Reference Costing** (Roll per Maker, Sheet tanpa Maker) hanya memuat **Costing Price** (USD/Kg dan Rp/Kg) untuk keperluan Costing, dan **diturunkan otomatis dari Costing Price Purchase** (bukan diinput terpisah). Setiap baris costing mencatat baris purchase sumbernya dan **masa berlakunya mengikuti baris purchase tersebut**. | BPM "Price Reference Costing Rp/Kg"; WB *Price Reference Costing*; K31, K40 | ms_price_ref_costing, dt_price_ref_costing, dt_price_ref_purchase |
| FR-PC-13 | Harga Price Reference **selalu Rp/Kg**; harga per sheet dihitung dengan mengalikan Berat/Sheet saat Costing. | WB *Price Reference Purchase/Costing* | dt_price_ref_costing |
| FR-PC-47 | Pada form Update Price Reference Purchase setiap material, sistem menampilkan tabel **PRICELIST referensi** dari data pricelist lama (Book Price dan LME 10 Hari / 30 Hari / Spot): Non Ferrous per baris Kurs Terbaru, Kurs 10 Hari, Kurs 30 Hari, dan Dolar; Ferrous hanya Book Price. Tabel ini **referensi bagi Finance menentukan Costing Price** (FR-PC-10) dan tidak mengisi atau membatasi nilainya. Tidak ditampilkan di Penawaran. | K46; aplikasi `penawaran/detail` (screenshot `input/Sales/SS Penawaran muncul pricelist LME.png`) | ms_pricelistfr, ms_pricelistnfr, perubahan_kurs, ms_inventory_category3 |

### C. Rate (aktor: Costing)

| ID | Kebutuhan | Sumber | Entitas |
|---|---|---|---|
| FR-PC-14 | Rate Man Power ditetapkan dalam **Rp/Kg** dan **diinput langsung** pada **Master Rate Umum** (FR-PC-16); tidak dihitung dari Cycletime, gaji, atau BPJS. | BPM D002 "Penetapan Rate Man Power (Rp/Kg)"; K35 | ms_rate_costing, dt_rate_costing |
| FR-PC-15 | *Dihapus (K35).* Pengelolaan komponen Rate Man Power terpisah ditiadakan; versi Rate Man Power ikut versi Master Rate Umum (FR-PC-17). | K35 | — |
| FR-PC-16 | Costing mengelola **Master Rate Umum** (berlaku semua produk): **Man Power (Rp/Kg)**, Machine (Rp/Kg), Consumable (Rp/Kg), FOH (Rp/Kg), Waste (%), Biaya Admin (Rp/Kg), Profit (%), Negotiation Allowance (%), Commission Fee (Rp/Kg). Nilai rate diinput; perhitungan asalnya (mis. biaya ÷ total Kg tahun lalu) dilakukan di luar sistem. | BPM "Penetapan Rate Man Power/Machine/Consumables/FOH/Waste/Biaya Admin/Profit/Negotiation Allowance/Commission Fee"; WB *Konsep-Sim Man.Sup,Other Cost*; K1, K4 | ms_rate_costing, dt_rate_costing |
| FR-PC-17 | Setiap penyimpanan Master Rate Umum menghasilkan **versi baru** (riwayat dipertahankan, versi terbaru aktif). | K7 | ms_rate_costing |

### D. Flow Process dan Cycletime (aktor: Engineering)

| ID | Kebutuhan | Sumber | Entitas |
|---|---|---|---|
| FR-PC-18 | Engineering menyusun **Flow Process** per material: daftar proses dengan Cost Centre/Area, Nama Proses, **Cycletime (menit/Kg)**, jumlah Man Power (disimpan sebagai informasi, K25), dan Machine. Flow Process dan Cycletime **dibiarkan** (K35) tetapi tidak dipakai menghitung costing. | BPM "Develop Flow Process & Cycletime per Produk/Material"; WB *Konsep-Sim Biaya MP*; K2 | ms_flow_process, dt_flow_process |
| FR-PC-19 | **Total Cycletime** per material adalah jumlah Cycletime seluruh baris proses; hanya informasi dan **tidak memengaruhi Man Power Cost** (K35). Total Man Hour tidak dipakai lagi. | WB *Konsep-Sim Biaya MP*; K14, K35 | dt_flow_process, ms_flow_process |
| FR-PC-20 | Kolom **Machine** dipilih dari master Asset berkategori **Machine** (modul `asset_category` dan `asset`). | K8 | dt_flow_process, asset, asset_category |
| FR-PC-21 | Flow Process hanya menyimpan waktu update terakhir (tanpa versi). | K12 | ms_flow_process |

### E. Perhitungan Product Costing (aktor: Accounting)

| ID | Kebutuhan | Sumber | Entitas |
|---|---|---|---|
| FR-PC-22 | Sistem menghitung **Material Cost** dari Price Reference Costing (Rp/Kg) × Berat. | BPM "1. Perhitungan Material Cost"; WB *Draft Element Costing* | tr_product_costing, dt_price_ref_costing |
| FR-PC-23 | Sistem menghitung **Biaya Proses** = Man Power (Rate Man Power Rp/Kg, K35) + Machine + Consumable, seluruhnya dari Master Rate Umum. | BPM "2. Perhitungan Biaya Proses", "Man Power Cost", "Machine Cost", "Cost Bahan Pendukung Khusus" | tr_product_costing, dt_product_costing |
| FR-PC-24 | Sistem menghitung **FOH Cost** dan **Waste Cost** (Rate % Waste × Biaya Material), lalu menjumlahkannya ke **HPP** bersama Material dan Biaya Proses. | BPM "FOH Cost", "Waste Cost", "Perhitungan Biaya HPP"; K3 | tr_product_costing |
| FR-PC-25 | Sistem menghitung berurutan: **Cost of Sales**, **Sales Price**, dan **Bottom Price** sesuai tabel Rumus Costing. | BPM "Perhitungan Cost Of Sales", "Sales Price", "Bottom Price" | tr_product_costing |
| FR-PC-26 | Untuk material **Sheet**, setiap elemen dihitung sebagai Rate (Rp/Kg) × Berat/Sheet dan ditampilkan dalam **Rp/Sheet dan Rp/Kg**. | WB *Draft Element Costing* | dt_product_costing |
| FR-PC-27 | Hasil costing menyimpan **rincian per elemen** (rate, satuan, nilai Rp/Kg, nilai Rp/Sheet) dan **versi rate, Price Reference, yang dipakai**. | WB *Simulasi Costing*; K7 | tr_product_costing, dt_product_costing |
| FR-PC-28 | Tersedia **Report Costing Detail per Produk**. | BPM data object "Report Costing Detail/Product" | tr_product_costing, dt_product_costing |
| FR-PC-36 | Perhitungan costing dijalankan lewat tombol **Update Costing**, yang berlaku untuk **semua material** sekaligus. Perubahan rate atau Price Reference **tidak** memicu hitung otomatis. | K16, K26 | tr_product_costing |
| FR-PC-37 | Saat **Update Costing** diklik, material yang **Price Reference Costing-nya kedaluwarsa** (melewati masa berlaku) **diblokir** (tidak dihitung) dan sistem membuat **notifikasi lonceng** yang diterima oleh PIC yang memiliki akses modul terkait. Material lain tetap dihitung. | K15, K27; FR-PC-11 | tr_notifikasi, dt_price_ref_costing |
| FR-PC-38 | Bila ada **Rate yang belum terisi**, costing **dihitung sebagian**: elemen yang datanya belum ada tidak dihitung dan hasilnya ditandai **SEBAGIAN** (lengkap = **LENGKAP**). | K22 | tr_product_costing (kelengkapan), dt_product_costing |

### F. Pricelist dan Approval (aktor: Sales, Management)

| ID | Kebutuhan | Sumber | Entitas |
|---|---|---|---|
| FR-PC-29 | Sales menyusun **Draft Pricelist** dari Bottom Price hasil costing. Hanya costing berstatus **LENGKAP** yang dapat masuk pricelist. Satuan pricelist seluruhnya **Rp/Kg**. | BPM "Perhitungan Price List"; K24, K29 | tr_pricelist, dt_pricelist |
| FR-PC-30 | Sales **mengajukan approval** Pricelist kepada Management. | BPM "Pengajuan Approval Pricelist" | tr_pricelist (status DIAJUKAN) |
| FR-PC-31 | Management **menyetujui atau menolak**, dan boleh **menyetujui sebagian baris** pricelist. Keputusan dan catatan tercatat, dan tiap baris menyimpan keputusan yang menetapkannya. Baris yang tidak disetujui, atau seluruh pricelist yang ditolak, **kembali ke DRAFT**. | BPM gateway "Approval Pricelist"; K13, K30, K32 | tr_pricelist_approval, dt_pricelist (id_approval, status) |
| FR-PC-32 | Pricelist yang disetujui **dirilis** sebagai pricelist dasar dan **berlaku untuk semua customer**. | BPM "Release Pricelist dasar"; K10 | tr_pricelist (status DIRILIS) |
| FR-PC-39 | Pricelist **boleh dirilis parsial**: tidak harus memuat seluruh material, dan hanya baris yang disetujui Management yang dirilis (baris lain kembali ke DRAFT). Harga yang berlaku untuk sebuah material adalah baris pricelist dirilis terbaru yang memuat material itu. | K21, K30 | tr_pricelist, dt_pricelist |

### G. Penawaran (aktor: Sales)

| ID | Kebutuhan | Sumber | Entitas |
|---|---|---|---|
| FR-PC-33 | Pada tambah/ubah item penawaran, Sales memilih **Bentuk Material terlebih dahulu**, lalu memilih **Material yang terfilter berdasarkan bentuk**. | WB *Temuan Tambahan* poin 2 | child_penawaran, ms_bentuk |
| FR-PC-34 | Field mengikuti bentuk: **Roll** — Width diinput; **Sheet** — Width dan Length terisi otomatis dari material, **Qty (Sheet)** diinput. | WB *Temuan Tambahan* poin 2 | child_penawaran |
| FR-PC-35 | **Bottom Price** pada Penawaran diambil otomatis dari **pricelist yang sudah dirilis Management**, sehingga item penawaran menyimpan rujukan ke baris pricelist tersebut. Nilai ini kemudian dibulatkan menjadi Pricelist Roundup (FR-PC-42). | WB *Temuan Tambahan* poin 3; K9 | child_penawaran (id_dt_pricelist), dt_pricelist |
| FR-PC-40 | Pricelist lama (Book Price dan LME 10 Hari / 30 Hari / Spot) beserta pilihan Dasar Harga, Komisi, dan Profit % pada form item penawaran **digantikan** oleh pricelist yang dirilis. | K17 | child_penawaran |
| FR-PC-41 | Nilai item penawaran dihitung dari **Price Quotation** Rp/Kg (default Pricelist Roundup, FR-PC-43): **Roll** = Price Quotation Rp/Kg × Jumlah order Kg; **Sheet** = Price Quotation Rp/Kg × Berat/Sheet × Jumlah order sheet. | K24, K42 | child_penawaran, dt_pricelist |
| FR-PC-42 | Setiap item penawaran menampilkan **Pricelist Roundup**, yaitu Pricelist Rp/Kg yang dirilis **dibulatkan ke atas ke kelipatan Rp100** (mis. Rp 342.532,87 menjadi Rp 342.600). Pembulatan **hanya dihitung di Penawaran**; pricelist yang dirilis tidak berubah. | K42 | child_penawaran (pricelist_roundup), dt_pricelist |
| FR-PC-43 | **Price Quotation** (Rp/Kg) item penawaran **default-nya Pricelist Roundup**, tetapi **dapat diedit** Sales, termasuk menjadi lebih rendah dari Bottom Price. Item dengan Price Quotation di bawah Bottom Price **ditandai** dan ikut Approval Direktur (FR-PC-45). | K42, K43 | child_penawaran (harga_penawaran, di_bawah_bottom) |
| FR-PC-44 | Sales mengisi **Diskon per penawaran** (bukan per item) sebagai **persen atau nominal Rp**; kedua nilai **saling terkonversi** (mengisi persen menampilkan nilai Rp, dan sebaliknya) terhadap **Total Quotation**, yaitu jumlah nilai seluruh item yang dihitung dari Price Quotation. Sistem menampilkan total setelah diskon. | K43 | tr_penawaran (total_quotation, diskon_persen, diskon_rp) |
| FR-PC-45 | Bila **Diskon lebih dari 5%** atau ada **item di bawah Bottom Price**, penawaran **memerlukan Approval Direktur**; Sales mengajukan approval dan statusnya tercatat (tidak perlu, menunggu, disetujui, atau ditolak). Diskon sampai dengan 5% tanpa item di bawah Bottom Price tidak memerlukan approval. | K43 | tr_penawaran (status_approval_diskon) |
| FR-PC-46 | **Direktur menyetujui atau menolak** approval diskon dengan catatan; keputusan, pemutus, dan waktunya tercatat. Bila **ditolak**, penawaran **dikembalikan ke Penawaran untuk direvisi** oleh Sales. | K43 | tr_penawaran_approval_diskon, tr_penawaran |

## Kebutuhan Non-Fungsional

*Kriteria uji* wajib terukur. Baris bertanda **[usulan]** adalah angka yang saya usulkan karena sumber tidak menyebut angka; perlu dikonfirmasi sebelum dianggap kebutuhan.

| ID | Kebutuhan | Cakupan | Kriteria uji | Asal |
|---|---|---|---|---|
| NFR-PC-01 | **Keterlacakan costing.** Setiap hasil costing dapat ditelusuri ke versi rate dan Price Reference yang dipakai. | FR-PC-27 | 100% baris `tr_product_costing` memiliki kedua rujukan terisi; menghitung ulang dari rujukan itu menghasilkan Bottom Price dengan selisih Rp 0. | K7 |
| NFR-PC-02 | **Riwayat tidak ditimpa.** Master Rate Umum (termasuk Rate Man Power) tidak diubah di tempat. | FR-PC-17 | Setelah *n* kali simpan tersedia tepat *n* versi; isi versi lama tidak berubah setelah simpan berikutnya. | K7 |
| NFR-PC-03 | *Dihapus (K35).* Pembulatan Rate Direct Labor tidak lagi relevan karena Rate Man Power diinput langsung. | — | — | K35 |
| NFR-PC-04 | **Konsistensi satuan.** Harga acuan disimpan dalam Rp/Kg; nilai per sheet = Rp/Kg × Berat/Sheet. | FR-PC-13, FR-PC-26 | Pengujian contoh Sheet 1 × 1000 × 1000 mm, density 8,94 menghasilkan 8,94 kg per sheet. | WB *Struktur Sheet* |
| NFR-PC-05 | **Kurs konsisten.** Nilai Rp pada Price Reference (Last Purchase dan Costing Price) = nilai USD × kurs yang dirujuk. | FR-PC-10 | Contoh 10,5 USD × kurs 18.000 = Rp 189.000, selisih 0. | WB *Price Reference Purchase* |
| NFR-PC-06 | **Hak akses.** Setiap halaman modul baru memakai pengecekan hak akses Add / View / Manage / Delete seperti modul yang ada. | seluruh FR | Akses tanpa hak yang sesuai ditolak pada 100% rute yang diuji. | pola aplikasi (`has_permission`) |
| NFR-PC-07 | **Jejak audit.** Setiap tabel baru menyimpan pembuat dan waktu buat serta pengubah dan waktu ubah; Last Update dan Update By tampil pada Rate dan Flow Process. | seluruh FR | 100% baris memiliki `created_by/on`; baris yang diubah memiliki `modified_by/on`. | WB (kolom Last Update, Update By) |
| NFR-PC-08 | **Kinerja daftar.** Halaman daftar (Price Reference, Rate, Flow, Costing, Pricelist) tetap responsif pada data besar. | FR-PC-09 s.d. FR-PC-32 | **[usulan]** Daftar 10 baris tampil di bawah 2 detik (persentil 95) pada 5.000 baris. | usulan |
| NFR-PC-09 | **Kinerja Update Costing.** Perhitungan lewat tombol selesai dalam waktu wajar. | FR-PC-22 s.d. FR-PC-25, FR-PC-36 | **[usulan]** Update Costing untuk seluruh material (K26), diuji pada 1.000 material, selesai di bawah 60 detik. | usulan |
| NFR-PC-10 | **Pembulatan Pricelist Roundup.** Pricelist Roundup selalu kelipatan Rp100 dan tidak lebih rendah dari Pricelist. | FR-PC-42 | Pada 100% kasus uji nilai Pricelist Roundup kelipatan 100 dan selisihnya dengan Pricelist kurang dari Rp100; contoh Rp 342.532,87 menjadi Rp 342.600. | K42 |
| NFR-PC-11 | **Ambang approval diskon.** Ambang 5% diterapkan tepat. | FR-PC-45 | Diskon 5,00% tidak memicu approval dan 5,01% memicu approval pada 100% kasus uji. | K43 |

## Pertanyaan Terbuka

Belum ada jawaban; tidak dianggap kebutuhan sampai dikonfirmasi. Pertanyaan lain sebelumnya sudah terjawab dan tercatat sebagai K14 sampai K32.

| ID | Pertanyaan | Dampak |
|---|---|---|
| OQ-PC-12 | Notifikasi lonceng diterima "PIC yang memiliki akses modul tersebut": **modul yang dimaksud** Price Reference (karena yang perlu bertindak memperbarui harga) atau Product Costing? Apakah satu notifikasi dibuat **per material** yang diblokir, atau satu ringkasan per klik Update Costing? | FR-PC-37; kolom `tr_notifikasi.modul_tujuan` |

## Penelusuran Diagram BPM ke FR

| Lane | Langkah pada BPM | FR |
|---|---|---|
| Engineering | Develop New Material atau New Varian Material | FR-PC-01 s.d. FR-PC-07 |
| Engineering | Develop Flow Process & Cycletime per Produk/Material | FR-PC-18 s.d. FR-PC-21 |
| Finance | Update Kurs; Update Price Reference Costing USD/Kg; Price Reference Costing Rp/Kg | FR-PC-08, FR-PC-12, FR-PC-13 |
| Finance | History Pembelian (sumber data, hanya dibaca; menjadi Last Purchase) | FR-PC-10, FR-PC-12 |
| Costing (Rate) | Penetapan Rate Man Power (Rp/Kg) | FR-PC-14 |
| Costing (Rate) | Penetapan Rate Machine, Consumables, FOH, Waste, Biaya Admin, Profit, Negotiation Allowance, Commission Fee | FR-PC-16, FR-PC-17 |
| Accounting | Perhitungan Material Cost, Biaya Proses, Man Power, Machine, Bahan Pendukung, FOH, Waste | FR-PC-22 s.d. FR-PC-24 |
| Accounting | Perhitungan Biaya HPP, Cost Of Sales, Sales Price, Bottom Price | FR-PC-24, FR-PC-25 |
| Sales | Perhitungan Price List; Report Costing Detail/Product | FR-PC-28, FR-PC-29 |
| Sales | Pengajuan Approval Pricelist | FR-PC-30 |
| Management | Approval Pricelist (Approve / Reject) | FR-PC-31 |
| Sales | Release Pricelist dasar, kemudian proses Sales | FR-PC-32, FR-PC-35 |
| Sales | Penawaran: Pricelist Roundup, Price Quotation, Diskon (revisi 2026-11-18, bukan dari BPM D002) | FR-PC-41 s.d. FR-PC-45 |
| Direktur | Approval Diskon Penawaran (revisi 2026-11-18, bukan dari BPM D002) | FR-PC-46 |

## Penelusuran FR ke Use Case

Use Case ada di `docs/workbook.xlsx` (sheet *UC Non-BPMN*, rute FR → UC). Tiap UC menunjuk tepat satu halaman pada sheet *Pages*.

| Use Case | FR |
|---|---|
| UC-N01 Kelola Spesifikasi Material | FR-PC-06 |
| UC-N02 Kelola Material Roll dan Sheet | FR-PC-01 s.d. FR-PC-05 |
| UC-N03 Kelola Material Ekuivalen | FR-PC-07 |
| UC-N04 Update Kurs | FR-PC-08 |
| UC-N05 Kelola Price Reference Purchase (termasuk masa berlaku global) | FR-PC-09 s.d. FR-PC-13, FR-PC-47 |
| UC-N06 Pantau Price Reference Costing | FR-PC-12, FR-PC-13 |
| UC-N08 Kelola Master Rate Umum (termasuk Rate Man Power) | FR-PC-14, FR-PC-16, FR-PC-17 |
| UC-N09 Kelola Flow Process dan Cycletime | FR-PC-18 s.d. FR-PC-21 |
| UC-N10 Update Costing | FR-PC-22 s.d. FR-PC-27, FR-PC-36 s.d. FR-PC-38 |
| UC-N11 Lihat Report Costing Detail | FR-PC-28 |
| UC-N12 Lihat Notifikasi Costing | FR-PC-37 |
| UC-N13 Susun dan Ajukan Pricelist | FR-PC-29, FR-PC-30 |
| UC-N14 Putuskan Approval Pricelist | FR-PC-31, FR-PC-32, FR-PC-39 |
| UC-N15 Tambah Item Penawaran dari Pricelist | FR-PC-33 s.d. FR-PC-35, FR-PC-40 s.d. FR-PC-43 |
| UC-N16 Atur Diskon dan Ajukan Approval Penawaran | FR-PC-44, FR-PC-45 |
| UC-N17 Putuskan Approval Diskon Penawaran | FR-PC-46 |

Uji penutupan: seluruh 47 FR terhubung ke minimal satu UC. NFR tidak diturunkan menjadi UC (tidak punya aktor).
