# SPK Produksi addHeader Memory Exhausted Bugfix Design

## Overview

Bug ini menyebabkan fatal error PHP "Allowed memory size of 134217728 bytes exhausted" ketika halaman `spk_produksi/addHeader/` dibuka. Penyebabnya adalah pengambilan seluruh isi tabel `stock_material` tanpa filter melalui query `SELECT a.* FROM stock_material a` di dalam method `addHeader()`. Saat tabel sudah besar, penyusunan result set melebihi batas memori PHP sehingga halaman gagal dimuat.

Strategi perbaikan: menyelaraskan query pada `addHeader()` dengan method pembanding `addHeader_new()`, yaitu memfilter stok menjadi `WHERE a.totalweight > 0 AND id_gudang = 1`. Dengan demikian hanya subset stok relevan yang dimuat ke memori, bukan seluruh tabel.

Perbaikan ini bersifat minimal dan terfokus pada satu baris query di `addHeader()`. Perubahan tidak menyentuh method lain. Namun, karena filter `totalweight > 0` mengubah himpunan baris yang dimuat ke dropdown, design ini secara eksplisit membahas satu kasus pelestarian penting: header SPK lama yang sudah menyimpan `id_stock` dengan stok yang kini `totalweight = 0` (lihat bagian Hypothesized Root Cause, Correctness Properties Property 2, dan Fix Implementation).

## Glossary

- **Bug_Condition (C)**: Kondisi yang memicu bug - halaman `addHeader` dibuka tanpa `id` header (mode buat baru) sementara tabel `stock_material` cukup besar sehingga query tanpa filter `SELECT a.* FROM stock_material a` menghabiskan memori PHP.
- **Property (P)**: Perilaku yang diharapkan - halaman `addHeader` dimuat tanpa fatal error memory exhausted, dan dropdown `#id_stock` hanya memuat subset stok relevan (`totalweight > 0 AND id_gudang = 1`).
- **Preservation**: Perilaku yang HARUS tetap sama - rendering dropdown (`id_stock` sebagai value, `lotno` sebagai label), pemuatan `$header`/`$detail`/`$stok` untuk header yang sudah ada, serta method lain seperti `addHeader_new()` dan `addHeader_view()`.
- **addHeader()**: Method pada `application/modules/spk_produksi/controllers/Spk_produksi.php` yang menyiapkan data untuk form tambah/edit header SPK Produksi dan merender view `AddHeader`.
- **addHeader_new()**: Method pembanding pada file yang sama yang sudah menggunakan query stok terfilter dan menjadi acuan solusi.
- **$stock**: Variabel hasil query stok material yang dikirim ke view dan dipakai untuk membangun opsi dropdown `#id_stock`.
- **id_stock**: Primary key baris `stock_material`; dipakai sebagai value opsi dropdown dan tersimpan di header SPK (`tr_spk_produksi.id_stock`).
- **totalweight**: Kolom `stock_material` yang menandakan sisa berat stok; `> 0` berarti stok masih tersedia, `= 0` berarti stok sudah habis terpakai.
- **id_gudang**: Kolom `stock_material` yang menandakan gudang; filter membatasi pada `id_gudang = 1`.

## Bug Details

### Bug Condition

Bug muncul ketika method `addHeader()` mengeksekusi `$this->db->query("SELECT a.* FROM stock_material a")->result()` tanpa klausa `WHERE`. Query ini meminta seluruh baris tabel `stock_material` dan memaksa driver MySQLi menyusun result set lengkap di memori. Ketika ukuran result set melewati batas memori PHP (128MB), eksekusi berhenti dengan fatal error di `mysqli_result.php`.

**Formal Specification:**
```
FUNCTION isBugCondition(input)
  INPUT: input berupa HttpRequest ke route spk_produksi/addHeader
  OUTPUT: boolean

  RETURN input.action == 'addHeader'
         AND stockQueryHasNoFilter(input)        // query 'SELECT a.* FROM stock_material a' tanpa WHERE
         AND fullTableResultSetExceedsMemory(stock_material)  // ukuran seluruh baris > batas memori PHP tersisa
END FUNCTION
```

### Examples

- Halaman `spk_produksi/addHeader/` dibuka saat tabel `stock_material` berisi puluhan ribu baris → Diharapkan: halaman tampil. Aktual: fatal error "Allowed memory size of 134217728 bytes exhausted".
- Halaman `spk_produksi/addHeader/` dibuka saat tabel kecil → Diharapkan: halaman tampil. Aktual: tampil (bug belum termanifestasi, tetapi query tanpa filter tetap tidak efisien dan rawan seiring tabel bertambah besar).
- Halaman `spk_produksi/addHeader_new/` dibuka pada kondisi tabel besar yang sama → Diharapkan & Aktual: halaman tampil karena query sudah terfilter (`totalweight > 0 AND id_gudang = 1`). Ini membuktikan filter adalah pembeda perilaku.
- Edge case: Header SPK lama dengan `id_stock` menunjuk stok yang kini `totalweight = 0` → setelah filter diterapkan, opsi lot tersebut tidak lagi ada di himpunan `$stock` sehingga perlu penanganan khusus (lihat Correctness Properties Property 2 dan Fix Implementation).

## Expected Behavior

### Preservation Requirements

**Unchanged Behaviors:**
- Dropdown `#id_stock` tetap dirender dengan `id_stock` sebagai value opsi dan `lotno` (di-uppercase) sebagai label, persis seperti sebelumnya.
- Saat sebuah header SPK Produksi sudah tersimpan dan direferensikan `id` pada URL, data `$header`, `$detail`, dan `$stok` tetap dimuat dengan perilaku yang sama (query-query tersebut tidak diubah).
- Method lain yang juga memuat stok (`addHeader_new()`, `addHeader_view()`, `addHeader_view2()`, dan sejenisnya) tidak boleh terdampak oleh perubahan pada `addHeader()`.
- Stok dengan `totalweight > 0` dan `id_gudang = 1` tetap tersedia sebagai pilihan pada dropdown.
- Nilai `id_stock` yang sudah tersimpan pada header lama tetap dapat ditampilkan sebagai opsi terpilih (`selected`) pada dropdown, meskipun stok tersebut kini `totalweight = 0`.

**Scope:**
Semua input yang TIDAK memenuhi bug condition harus sepenuhnya tidak terpengaruh oleh perbaikan ini. Termasuk:
- Pembukaan method lain pada controller (`addHeader_new`, `addHeader_view`, `addHeaderproses`, dll).
- Proses submit/simpan header SPK Produksi (tidak menyentuh query stok yang ditampilkan).
- Pemuatan detail dan stok terkait header yang sudah ada melalui query terpisah yang tidak diubah.

**Note:** Perilaku benar yang sebenarnya (halaman termuat + dropdown berisi subset terfilter) dirumuskan pada bagian Correctness Properties (Property 1). Bagian ini fokus pada apa yang TIDAK boleh berubah.

## Hypothesized Root Cause

Berdasarkan analisis bug, penyebab paling mungkin adalah:

1. **Query Stok Tanpa Filter (penyebab utama)**: Baris `$stock = $this->db->query("SELECT a.* FROM stock_material a")->result();` mengambil seluruh tabel `stock_material` tanpa `WHERE`. Driver MySQLi (`result()` menggunakan buffered result) menyusun seluruh baris menjadi array objek di memori PHP sekaligus, sehingga konsumsi memori tumbuh linear terhadap jumlah baris tabel dan menembus batas 128MB.
   - Method `addHeader_new()` pada file yang sama memakai `WHERE a.totalweight > 0 AND id_gudang=1` dan tidak mengalami masalah → menguatkan hipotesis bahwa filter inilah pembedanya.

2. **Efek Samping Filter terhadap Header Lama (risiko regresi)**: Menambahkan `totalweight > 0` akan menghilangkan baris stok yang sudah habis (`totalweight = 0`) dari himpunan `$stock`. Jika sebuah header SPK lama menyimpan `id_stock` yang kini `totalweight = 0`, opsi lot tersebut tidak akan muncul di dropdown. Akibatnya opsi yang sebelumnya terpilih (`selected`) bisa hilang, menampilkan dropdown seolah kosong/berubah dan berpotensi mengubah nilai saat form disimpan ulang.
   - Perlu dicatat: view `AddHeader.php` tetap menjalankan query terpisah `SELECT * FROM stock_material WHERE id_stock = '...'` untuk mengambil `detail_potongan` berdasarkan `id_stock` header. Jadi sebagian data header tetap tampil, namun OPSI pada dropdown `#id_stock` khusus bergantung pada array `$stock`.

3. **Batas Memori PHP yang Terbatas**: `memory_limit = 128M` memperbesar kemungkinan exhausted. Menaikkan memory_limit bukan solusi yang tepat karena hanya menunda masalah dan tidak menyelaraskan perilaku dengan `addHeader_new()`.

## Correctness Properties

Property 1: Bug Condition - Halaman addHeader termuat dengan query stok terfilter

_For any_ input yang memenuhi bug condition (isBugCondition bernilai true), yaitu pembukaan halaman `addHeader` saat tabel `stock_material` besar, method `addHeader()` yang telah diperbaiki SHALL menjalankan query stok terfilter (`WHERE a.totalweight > 0 AND id_gudang = 1`) sehingga hanya subset stok relevan yang dimuat ke memori, dan halaman `addHeader` termuat tanpa fatal error memory exhausted sambil menampilkan dropdown pemilihan stok.

**Validates: Requirements 2.1, 2.2**

Property 2: Preservation - Perilaku dropdown, header tersimpan, dan method lain tidak berubah

_For any_ input yang TIDAK memenuhi bug condition (isBugCondition bernilai false), method yang telah diperbaiki SHALL menghasilkan perilaku yang sama dengan sebelum perbaikan, dengan ketentuan pelestarian berikut:
- Dropdown `#id_stock` tetap memakai `id_stock` sebagai value dan `lotno` sebagai label.
- Pemuatan `$header`, `$detail`, dan `$stok` untuk header yang sudah ada tidak berubah.
- Method `addHeader_new()`, `addHeader_view()`, dan method lain tidak terdampak.
- Nilai `id_stock` yang tersimpan pada header lama tetap dapat ditampilkan sebagai opsi terpilih pada dropdown meskipun stok tersebut kini `totalweight = 0` (opsi lot referensi tidak hilang akibat filter).

**Validates: Requirements 3.1, 3.2, 3.3, 3.4**

## Fix Implementation

### Changes Required

Dengan asumsi analisis akar masalah benar:

**File**: `application/modules/spk_produksi/controllers/Spk_produksi.php`

**Function**: `addHeader($id = null, $view = null)`

**Specific Changes**:

1. **Tambahkan filter pada query stok (perubahan utama)**: Ubah
   ```php
   $stock = $this->db->query("SELECT a.* FROM stock_material a")->result();
   ```
   menjadi query terfilter yang selaras dengan `addHeader_new()`:
   ```php
   $stock = $this->db->query("SELECT a.* FROM stock_material a WHERE a.totalweight > 0 AND id_gudang = 1")->result();
   ```
   Ini membatasi baris yang dimuat ke memori ke subset relevan saja dan menghilangkan penyebab memory exhausted.

2. **Penanganan header lama yang mereferensikan stok `totalweight = 0` (pencegahan regresi untuk Property 2)**: Ketika form dibuka untuk header yang sudah ada (`$id` / `$header` tidak kosong) dan `id_stock` header tersebut tidak termasuk dalam himpunan hasil query terfilter, tambahkan baris stok referensi tersebut ke `$stock` agar opsi lot terpilih tetap tampil. Pendekatan yang direkomendasikan (tetap minimal dan hanya aktif saat mengedit header lama):
   - Jika `$header` tidak kosong, ambil `id_stock` dari `$header[0]->id_stock`.
   - Jika `id_stock` tersebut tidak ada di `$stock` (karena ter-filter oleh `totalweight > 0`), jalankan query tunggal `SELECT a.* FROM stock_material a WHERE a.id_stock = ?` dan gabungkan (append) hasilnya ke `$stock`.
   - Dampak memori minimal karena hanya menambah satu baris, dan tidak aktif pada mode buat-baru (saat bug condition berlaku) sehingga tidak mengembalikan masalah memory exhausted.
   - Alternatif yang lebih longgar namun lebih boros: `WHERE (a.totalweight > 0 AND id_gudang = 1) OR a.id_stock = <id_stock_header>`. Pilih salah satu; pendekatan append lebih eksplisit dan tetap menjaga dropdown ringkas untuk mode buat-baru.

3. **Jangan ubah query lain di method ini**: Query `$header`, `$detail`, dan `$stok` dibiarkan apa adanya untuk menjaga perilaku pelestarian (Requirements 3.2).

4. **Jangan sentuh method lain**: `addHeader_new()`, `addHeader_view()`, `addHeader_view2()`, dan sejenisnya tidak diubah (Requirements 3.3).

5. **Konsistensi gaya query**: Gunakan format filter yang sama persis dengan `addHeader_new()` agar mudah dirawat dan perilaku konsisten antar method.

## Testing Strategy

### Validation Approach

Strategi pengujian mengikuti dua fase: pertama, memunculkan counterexample yang membuktikan bug pada kode yang belum diperbaiki; kedua, memverifikasi perbaikan bekerja benar dan tetap melestarikan perilaku yang ada. Karena ini aplikasi CodeIgniter/PHP tanpa kerangka uji unit controller yang mapan untuk query DB langsung, sebagian pengujian bersifat manual/terukur pada level perilaku halaman, dilengkapi pengujian berbasis properti pada logika pemfilteran stok di mana memungkinkan.

### Exploratory Bug Condition Checking

**Goal**: Memunculkan counterexample yang membuktikan bug SEBELUM perbaikan diterapkan. Mengonfirmasi atau menyangkal analisis akar masalah. Jika disangkal, akar masalah perlu dirumuskan ulang.

**Test Plan**: Buka halaman `spk_produksi/addHeader/` pada kondisi tabel `stock_material` besar (atau turunkan `memory_limit` sementara agar ambang mudah tercapai), lalu amati fatal error. Bandingkan dengan `addHeader_new()` pada kondisi yang sama. Jalankan pada kode yang BELUM diperbaiki untuk mengamati kegagalan dan memahami akar masalah.

**Test Cases**:
1. **Buka addHeader pada tabel besar**: Akses `spk_produksi/addHeader/` saat `stock_material` banyak baris (akan gagal / fatal error pada kode belum diperbaiki).
2. **Bandingkan addHeader_new pada tabel besar**: Akses `spk_produksi/addHeader_new/` pada kondisi sama (tampil normal → menguatkan bahwa filter adalah pembeda).
3. **Ukur konsumsi memori query stok**: Catat perkiraan memori hasil `SELECT a.*` tanpa filter vs dengan filter (tanpa filter menembus batas pada tabel belum diperbaiki).
4. **Edge - header lama totalweight=0**: Buka `addHeader/{id}` untuk header yang `id_stock`-nya kini `totalweight = 0`, catat bahwa dengan filter saja opsi lot terpilih akan hilang (dasar untuk penanganan pelestarian).

**Expected Counterexamples**:
- Fatal error "Allowed memory size of 134217728 bytes exhausted" di `mysqli_result.php` saat membuka `addHeader` pada tabel besar.
- Penyebab yang dikonfirmasi: query stok tanpa filter memuat seluruh tabel ke memori.

### Fix Checking

**Goal**: Memverifikasi bahwa untuk semua input yang memenuhi bug condition, method yang telah diperbaiki menghasilkan perilaku yang diharapkan.

**Pseudocode:**
```
FOR ALL input WHERE isBugCondition(input) DO
  result := addHeader_fixed(input)
  ASSERT pageLoadsWithoutMemoryError(result)
  ASSERT stockLoadedIsFilteredSubset(result)   // hanya totalweight > 0 AND id_gudang = 1
  ASSERT dropdownIdStockRendered(result)
END FOR
```

### Preservation Checking

**Goal**: Memverifikasi bahwa untuk semua input yang TIDAK memenuhi bug condition, method yang telah diperbaiki menghasilkan hasil yang sama dengan method asli.

**Pseudocode:**
```
FOR ALL input WHERE NOT isBugCondition(input) DO
  ASSERT addHeader_original(input).dropdownRendering = addHeader_fixed(input).dropdownRendering
  ASSERT addHeader_original(input).headerDetailStok   = addHeader_fixed(input).headerDetailStok
  ASSERT otherMethodsBehavior_original               = otherMethodsBehavior_fixed
  // Khusus header lama yang mereferensikan stok totalweight = 0:
  ASSERT selectedOptionForStoredIdStock(addHeader_fixed(input)) IS PRESENT
END FOR
```

**Testing Approach**: Pengujian berbasis properti disarankan untuk preservation checking karena:
- Menghasilkan banyak kasus uji secara otomatis di seluruh domain input stok.
- Menangkap edge case (mis. header lama dengan `totalweight = 0`) yang mudah terlewat oleh uji manual.
- Memberi jaminan kuat bahwa perilaku tidak berubah untuk input non-bug.

**Test Plan**: Amati perilaku pada kode BELUM diperbaiki untuk rendering dropdown, pemuatan header/detail/stok, dan opsi terpilih; lalu tulis uji berbasis properti/manual yang menangkap perilaku tersebut dan pastikan tetap sama setelah perbaikan.

**Test Cases**:
1. **Pelestarian rendering dropdown**: Amati value=`id_stock` dan label=`lotno` pada kode belum diperbaiki, pastikan tetap sama setelah perbaikan untuk stok terfilter.
2. **Pelestarian header/detail/stok tersimpan**: Amati pemuatan `$header`, `$detail`, `$stok` untuk header yang ada, pastikan tidak berubah setelah perbaikan.
3. **Pelestarian method lain**: Pastikan `addHeader_new()` dan `addHeader_view()` berperilaku sama setelah perbaikan (tidak diubah).
4. **Pelestarian opsi terpilih header lama (totalweight = 0)**: Pastikan opsi lot yang tersimpan pada header lama tetap muncul dan `selected` pada dropdown meskipun stok kini `totalweight = 0`.

### Unit Tests

- Uji bahwa query stok yang dibangun `addHeader()` mengandung filter `totalweight > 0 AND id_gudang = 1`.
- Uji bahwa untuk mode buat-baru (tanpa `$header`), himpunan `$stock` hanya berisi baris lolos filter.
- Uji bahwa untuk mode edit dengan `id_stock` header yang `totalweight = 0`, baris stok referensi tetap ada di `$stock`.

### Property-Based Tests

- Hasilkan konfigurasi tabel `stock_material` acak (variasi `totalweight` dan `id_gudang`) dan verifikasi bahwa `$stock` selalu merupakan subset `{totalweight > 0 AND id_gudang = 1}` (ditambah opsi referensi header bila mode edit).
- Hasilkan berbagai `id_stock` header dan verifikasi opsi terpilih selalu hadir pada dropdown (pelestarian Property 2).
- Verifikasi di banyak skenario bahwa jumlah baris yang dimuat untuk mode buat-baru tidak pernah mencakup seluruh tabel.

### Integration Tests

- Buka `spk_produksi/addHeader/` end-to-end pada tabel besar dan pastikan halaman termuat tanpa fatal error serta dropdown berisi stok terfilter.
- Buka `spk_produksi/addHeader/{id}` untuk header lama yang mereferensikan stok `totalweight = 0` dan pastikan opsi lot terpilih tetap tampil dan ter-select.
- Lakukan submit/simpan header dari halaman `addHeader` dan pastikan alur penyimpanan tidak terpengaruh oleh perubahan query stok.
