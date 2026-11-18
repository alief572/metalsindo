/* Panduan per modul memakai driver.js (assets/driver.js.iife.js + driver.css, v1.3.1, MIT). Dibuka dari tombol "Panduan" di topbar.
   Langkah dipilih menurut halaman (nama file) dan keadaan layar (daftar / form / modal); langkah yang elemennya tidak ada dilewati. */
(function (w) {
  'use strict';
  var base = (function () { var s = document.currentScript; return s ? s.src.replace(/guide\.js.*$/, '') : 'assets/'; })();
  var $ = function (s) { return document.querySelector(s); };
  var has = function (s) { return !!$(s); };

  /* ---------- elemen umum ---------- */
  var ADD = '#addw button, #addwrap button, #add, .box-header button';
  var TBL = '.tbl';
  var SEARCH = '.qq';
  var ACT = '.tbl tbody tr:first-child td.act, .tbl tbody tr:first-child td:last-child';
  var TABS = '.tabs';
  function S(el, title, text, side) { return {el:el, title:title, text:text, side:side}; }
  var side = {
    search: S('#msq', 'Cari menu', 'Ketik nama modul untuk menyaring menu di sebelah kiri tanpa perlu menggulung.', 'right'),
    perm: S('#perm', 'Simulasi hak akses', 'Meniru has_permission(): matikan Add, View, Manage, atau Delete untuk melihat tombol mana yang hilang pada tiap level pengguna.', 'bottom')
  };
  var tSearch = S(SEARCH, 'Cari dan urutkan', 'Kolom Search menyaring seluruh baris. Klik judul kolom untuk mengurutkan; Show mengatur jumlah baris per halaman.', 'bottom');
  var tTable = function (t, x) { return S(TBL, t || 'Daftar data', x, 'top'); };
  var tAct = function (x) { return S(ACT, 'Kolom Action', x, 'left'); };
  var tAdd = function (x) { return S(ADD, 'Tombol Add', x, 'bottom'); };
  var saved = 'Hasil simpan tersimpan di browser ini, jadi bisa dilihat lagi setelah halaman dimuat ulang. Menu "Reset data mockup" mengembalikan data awal.';

  /* ---------- daftar panduan: kunci = nama file ---------- */
  var T = {};
  function simpleMaster(file, nama, add, tabel, act) {
    T[file] = function () { return [
      S(null, nama, tabel), tAdd(add), tSearch, tTable(), tAct(act || 'View melihat detail, Edit mengubah, Delete menghapus. Tombol yang tampil mengikuti hak akses.')]; };
  }
  simpleMaster('inventory_1.html', 'Level 1 - Type', 'Menambah tipe inventory (mis. Material). Isi nama, lalu Save; konfirmasi muncul sebelum data disimpan. ' + saved, 'Tipe inventory paling atas. Level 2 sampai Level 4 bergantung pada tipe ini.');
  simpleMaster('inventory_2.html', 'Level 2 - Ferrous / Non Ferrous', 'Menambah kategori di bawah Type beserta daftar komposisinya (Fe, C, Mn, ...). Pilihan Type menentukan kategori yang tersedia.', 'Kategori material: Ferrous dan Non Ferrous. Kolom komposisi dipakai untuk menghitung harga alloy dari LME.');
  simpleMaster('inventory_3.html', 'Level 3 - Alloy', 'Menambah alloy. Dropdown Level 2 mengikuti Type yang dipilih; Kode Coretax diisi di sini.', 'Alloy di bawah Level 2 (mis. SUS, C1100) lengkap dengan Kode Coretax.');
  simpleMaster('master_surface.html', 'Master Surface', 'Menambah finishing permukaan (mis. 2B, BA).', 'Daftar finishing permukaan material.');
  simpleMaster('master_bentuk.html', 'Master Bentuk', 'Menambah bentuk material (Roll, Sheet, ...). Setiap bentuk punya daftar dimensi sendiri yang diisi lewat tombol tambah dimensi di form.', 'Bentuk material beserta dimensinya. Bentuk menentukan field apa yang muncul di Level 4 - Material.');
  simpleMaster('master_spesifikasi.html', 'Master Spesifikasi', 'Menambah spesifikasi per Type Alloy (mis. C2680, 304, SPCC-SD). Spesifikasi tidak punya isian ekuivalen; kesamaan spesifikasi langsung dipakai pada Material Ekuivalen.', 'Daftar spesifikasi yang dipilih di form Level 4 - Material.');
  simpleMaster('asset_category.html', 'Asset Category', 'Menambah kategori asset. Kategori bernama Machine menjadi sumber mesin untuk Flow Process.', 'Kategori asset (Machine, Office Equipment, ...).');
  simpleMaster('asset.html', 'Asset', 'Menambah asset beserta data penyusutan. Asset berkategori Machine muncul sebagai pilihan mesin di Flow Process.', 'Daftar asset dengan nilai perolehan dan penyusutan.');

  T['inventory_4.html'] = function () { return [
    S(null, 'Level 4 - Material', 'Master material per bentuk. Dari sini seluruh transaksi (PR, PO, Penawaran, Costing) mengambil material.'),
    S(TABS, 'Pilih bentuk dahulu', 'Setiap tab adalah satu bentuk (Roll, Sheet, Bar, ...). Daftar dan form berubah mengikuti dimensi bentuk itu.', 'bottom'),
    tAdd('Form lengkap: klasifikasi, Spesifikasi, Maker, Thickness, Density, komposisi, stok, dan supplier. Pada Sheet, Berat/Sheet = Thickness x Width x Length x Berat jenis (g/cm3) / 1.000.000.'),
    tSearch, tTable('Daftar material', 'Nama material tersusun otomatis dari Alloy, Spesifikasi, Surface, dan dimensi.'),
    tAct('Edit membuka form. Di dalamnya ada daftar Material Ekuivalen: otomatis dari Spesifikasi yang sama lalu Thickness yang sama (Maker tidak jadi syarat), ditambah relasi manual. Sheet boleh ekuivalen dengan Roll, tidak sebaliknya.')]; };

  T['master_suplier.html'] = function () { return [S(null, 'Master Supplier', 'Supplier lokal dan internasional beserta kategori dan contact person. Supplier dipakai di Level 4 - Material, PR, dan PO.'), S(TABS, 'Lokal dan Internasional', 'Pisah tab menurut lokasi. Supplier berawalan LS adalah lokal, IS adalah internasional (impor).', 'bottom'), tSearch, tTable(), tAct('Lihat detail, ubah data, atau hapus supplier.')]; };
  T['master_lme.html'] = function () { return [S(null, 'Master LME', 'Harga logam harian dari London Metal Exchange (Cu, Zn, Sn, Ni, Ag, Al) dalam USD per ton.'), S(TABS, 'Riwayat dan Rate', 'Tab riwayat berisi input harian; tab Rate menampilkan rate H-30, H-10, dan saat ini yang dipakai pada PO Import dan Price Reference.', 'bottom'), tSearch, tTable()]; };
  T['master_kurs.html'] = function () { return [S(null, 'Master Kurs', 'Nilai tukar IDR per USD. Dipakai Penawaran, PO, dan Price Reference.'), S(TABS, 'Riwayat dan Rate Kurs', 'Tab pertama memuat riwayat perubahan dan tombol Update Kurs. Tab Rate menampilkan Kurs Terbaru serta rata-rata 10 dan 30 hari.', 'bottom'), tSearch, tTable()]; };
  T['bookprice_material.html'] = function () { return [S(null, 'Bookprice Material', 'Harga acuan per material yang menjadi dasar pricelist.'), S('#tpl', 'Template Excel', 'Unduh template dahulu supaya format kolom sesuai.', 'bottom'), S('#up', 'Upload Excel', 'Pilih file, lalu unggah. Mockup mensimulasikan baris baru ditambahkan dan baris yang sudah ada diperbarui; hasilnya langsung tampil di tabel.', 'bottom'), tSearch, tTable()]; };

  /* ---------- Price Reference dan Costing ---------- */
  T['price_reference_purchase.html'] = function () {
    if (has('.ov.show form')) return [
      S('.ov.show .sect', 'Referensi harga', 'Tabel Book Price dan LME (10 hari, 30 hari, spot) hanya referensi untuk menentukan Costing Price. Non Ferrous per baris Kurs Terbaru, 10 Hari, 30 Hari, dan Dolar; Ferrous hanya Book Price.', 'bottom'),
      S('.ov.show form', 'Last Purchase dan Costing Price', 'Last Purchase terisi otomatis dari PO terakhir yang sudah incoming dan tidak bisa diubah. Costing Price diisi bebas dalam USD dan harus lebih besar dari Last Purchase; Rp/Kg dihitung dari kurs.', 'left')];
    return [S(null, 'Price Reference Purchase', 'Harga pembelian acuan per material. Menjadi dasar Product Costing.'),
      S(TABS, 'Ferrous dan Non Ferrous', 'Material dikelompokkan per Level 2 dengan tab.', 'bottom'),
      S('.mb', 'Masa berlaku global', 'Satu masa berlaku untuk seluruh material Ferrous dan satu untuk Non Ferrous. Mengubahnya langsung berlaku untuk semua material pada tab itu.', 'bottom'),
      tTable('Daftar material', 'Kolom Last Purchase, Costing Price (USD dan Rp/Kg), dan Berlaku s/d. Material yang kedaluwarsa memblokir Update Costing.'),
      tAct('Update membuka form Costing Price dengan referensi LME; Riwayat menampilkan snapshot Last Purchase pada tiap perubahan.')]; };
  T['price_reference_costing.html'] = function () { return [S(null, 'Price Reference Costing', 'Costing Price turunan dari Purchase. Halaman ini hanya untuk dibaca; masa berlaku mengikuti Price Reference Purchase.'), tSearch, tTable()]; };
  T['master_rate_umum.html'] = function () { return [S(null, 'Master Rate Umum', 'Rate biaya yang dipakai semua costing: Machine, Consumable, FOH, Waste, Admin, Profit, Negotiation Allowance, Commission.'), S('#edit', 'Rate berlaku', 'Ubah nilai lalu simpan. Setiap simpan membuat versi baru, sehingga costing lama tidak berubah.', 'top'), S('#hist', 'Riwayat versi', 'Daftar versi rate sebelumnya.', 'top')]; };
  T['flow_process.html'] = function () { return [S(null, 'Flow Process', 'Rangkaian proses dan cycletime (menit/Kg) per material.'), tAdd('Tambah flow untuk material; baris proses ditambah dengan tombol tambah baris.'), S('#rows, .tbl', 'Baris proses', 'Setiap baris adalah satu proses. Mesin dipilih dari asset kategori Machine.', 'top'), tSearch, tTable()]; };
  T['product_costing.html'] = function () { return [S(null, 'Product Costing', 'Menghitung biaya produk sampai Bottom Price dari Price Reference, rate, dan flow process.'), S('#upd', 'Update Costing', 'Menghitung ulang semua material. Hasil bisa LENGKAP, SEBAGIAN, atau diblokir bila Price Reference kedaluwarsa; PIC mendapat notifikasi lonceng.', 'bottom'), S('#res', 'Ringkasan hasil', 'Jumlah material per status setelah Update Costing.', 'top'), tTable()]; };
  T['report_costing.html'] = function () { return [S(null, 'Report Costing Detail', 'Rincian elemen biaya sampai Bottom Price.'), S('#sel', 'Pilih produk', 'Pilih material untuk menampilkan laporan.', 'bottom'), S('#rep', 'Rincian biaya', 'Biaya per Kg dan per Sheet, dari bahan sampai profit.', 'top')]; };
  T['notifikasi.html'] = function () { return [S(null, 'Notifikasi Costing', 'Pemberitahuan untuk PIC, mis. Price Reference kedaluwarsa. Notifikasi yang sama juga muncul di lonceng topbar.'), S('#all', 'Tandai dibaca', 'Menandai seluruh notifikasi sebagai sudah dibaca.', 'bottom'), tTable()]; };
  T['pricelist_dasar.html'] = function () { return [S(null, 'Pricelist (dasar)', 'Pricelist dasar disusun dari costing berstatus LENGKAP (Rp/Kg, semua customer).'), tAdd('Susun pricelist baru dari hasil costing.'), S('#aju, #sub', 'Ajukan approval', 'Mengirim pricelist ke Management. Baris yang disetujui dirilis; sisanya kembali DRAFT.', 'bottom'), tTable()]; };
  T['pricelist_approval.html'] = function () { return [S(null, 'Approval Pricelist', 'Management memutuskan pricelist yang diajukan.'), tTable('Daftar pengajuan', 'Buka satu pengajuan untuk memeriksa barisnya.'), S('#tbl', 'Keputusan', 'Setujui seluruh baris, sebagian, atau tolak. Baris disetujui dirilis dan menjadi dasar Bottom Price penawaran.', 'top')]; };
  T['pricelist.html'] = function () { return [S(null, 'Pricelist (lama)', 'Pricelist modul lama: Bottom Price Ferrous (Book Price) dan Non Ferrous (Book Price + LME 10 hari, 30 hari, spot). Digantikan Pricelist dasar.'), tSearch, tTable()]; };

  /* ---------- Penjualan ---------- */
  T['transaksi_inquiry.html'] = function () { return [S(null, 'Inquiry (CRCL)', 'Permintaan customer beserta item per bentuk material.'), tAdd('Buat inquiry baru: pilih customer, lalu tambahkan item.'), S('#flt', 'Filter', 'Saring inquiry menurut kondisi tertentu.', 'bottom'), tSearch, tTable(), tAct('Lihat detail item, atau ubah inquiry.')]; };
  function penTour(file, nama, ket) {
    T[file] = function () {
      if (has('.ov.show form')) return [S('.ov.show form', 'Form item penawaran', 'Pilih Bentuk, lalu Material. Bottom Price diambil dari Pricelist yang sudah dirilis; material tanpa pricelist dirilis tidak bisa disimpan. Pricelist Roundup dibulatkan ke Rp100; Price Quotation terisi dari Roundup tetapi boleh diedit. Berat/Sheet = Thickness x Width x Length x Berat jenis / 1.000.000.', 'left')];
      if (has('#items')) return [
        S('#items', 'Item penawaran', 'Kolom Pricelist Roundup dan Price Quotation. Item dengan Price Quotation di bawah Bottom Price diberi tanda dan ikut Approval Direktur.', 'top'),
        S('#disc', 'Diskon penawaran', 'Diskon dihitung per penawaran. Isi persen atau Rupiah, keduanya saling terkonversi. Lebih dari 5% atau ada item di bawah Bottom Price perlu Approval Direktur.', 'top'),
        S('#dapv', 'Ajukan approval', 'Mengirim penawaran ke Direktur. Selama menunggu, item dan diskon terkunci. Bila ditolak, penawaran kembali untuk direvisi.', 'top'),
        S('#back', 'Kembali ke daftar', 'Kembali ke daftar penawaran.', 'bottom')];
      return [S(null, nama, ket), tAdd('Membuat header penawaran. Setelah disimpan, aplikasi langsung membuka halaman detail untuk menambah item.'), tSearch, tTable(), tAct('Detail membuka halaman item; tersedia juga edit, copy, closing, dan ajukan revisi menurut status penawaran.')]; };
  }
  penTour('penawaran.html', 'Penawaran Material', 'Penawaran harga ke customer dari Pricelist dirilis, dengan Roundup, diskon, dan approval.');
  penTour('penawaran_slitting.html', 'Penawaran Slitting', 'Sama seperti Penawaran Material, dengan tambahan qty slitting, qty coil, dan width akhir.');
  T['index_approval.html'] = function () { return [S(null, 'Approval Revisi Penawaran', 'Sales mengajukan revisi penawaran yang sudah closing; di sini disetujui atau ditolak (material dan slitting).'), tTable(), tAct('Approve membuka penawaran untuk direvisi; Reject mengembalikannya.')]; };
  T['approval_diskon.html'] = function () { return [S(null, 'Approval Diskon Penawaran', 'Antrean Direktur untuk penawaran dengan diskon lebih dari 5% atau item di bawah Bottom Price.'), tTable('Antrean approval', 'Ringkasan diskon, nilai penawaran, dan item di bawah Bottom Price.'), tAct('Direktur menyetujui, atau menolak dengan catatan; penawaran yang ditolak kembali ke Sales untuk direvisi.')]; };
  function spkTour(file, nama, ket) { T[file] = function () { return [S(null, nama, ket), tAdd('SPK dibuat dari penawaran Open: pilih customer, lalu nomor penawaran. Item terisi otomatis; isi No PO, tanggal PO, dan rencana customer.'), tSearch, tTable(), tAct('Belum di-Approve: Edit dan Approve. Sudah Approved: terkunci, perubahan lewat revisi penawaran yang disetujui. Print mencetak SPK.')]; }; }
  spkTour('spk_marketing.html', 'SPK Marketing', 'Surat perintah kerja dari penawaran yang disetujui customer.');
  spkTour('index_slitting.html', 'SPK Marketing Slitting', 'SPK Marketing untuk order slitting.');

  /* ---------- Purchasing ---------- */
  T['purchase_request.html'] = function () {
    if (has('#grid')) return [
      S('input[name=requestor]', 'Requestor', 'Isi nama pemohon. Nomor PR dan tanggal terisi otomatis.', 'bottom'),
      S('#addm', 'Tambah material', 'Setiap klik menambah satu baris material.', 'bottom'),
      S('#grid', 'Baris material', 'Pilih material; Bentuk, Width, Length, dan supplier terisi dari master. Roll memakai ID dan OD dengan Total Weight manual. Sheet memakai Qty Sheet x Weight/Sheet dan Total Weight dihitung otomatis.', 'top'),
      S('.form-actions button[type=submit]', 'Save', 'Menyimpan PR berstatus Waiting, lalu kembali ke daftar.', 'top')];
    if (has('.ov.show .md')) return [S('.ov.show .md', 'Detail PR', 'Header dan seluruh material PR. Kolom Qty Sheet dan Weight Sheet muncul bila ada material Sheet.', 'left')];
    return [S(null, 'Purchase Request', 'Permintaan pembelian material. PR yang Approved menjadi sumber Purchase Order.'),
      tAdd('Membuka form PR berupa halaman penuh.'), tSearch, tTable('Daftar PR', 'Progress PR: Waiting (kuning) atau Approved (hijau).'),
      tAct('View melihat detail. Edit dan Approve hanya pada PR Waiting; setelah Approved PR terkunci. Copy PR membuat PR baru berstatus Waiting dengan item yang sama.')]; };

  T['purchase_order.html'] = function () {
    if (has('#apvgo')) return [
      S('.ov.show .dl', 'Ringkasan PO', 'Supplier, jenis (Lokal atau Import), tanggal, dan Total Order yang akan disetujui.', 'bottom'),
      S('.ov.show .tbl', 'Referensi Last Purchase', 'Untuk tiap item: Unit Price PO dibandingkan dengan harga PO terakhir material yang sama yang sudah incoming. Selisih merah berarti lebih mahal, hijau lebih murah. Selisih hanya dihitung bila mata uangnya sama.', 'top'),
      S('#apvgo', 'Approve', 'Menyetujui PO setelah konfirmasi. Last Purchase hanya referensi dan tidak memblokir.', 'top')];
    if (has('#grid')) return [
      S('select[name=sup]', 'Supplier', 'Hanya supplier yang punya PR Approved dan belum dipakai PO. Jenis Lokal atau Import, mata uang, dan Payment Term terisi otomatis.', 'bottom'),
      S('select[name=pr]', 'Pilih PR', 'Daftar PR Approved milik supplier itu. Item PR otomatis menjadi baris PO.', 'bottom'),
      S('input[name=kurs]', 'Kurs', 'Bawaan rata-rata kurs 30 hari; bisa diubah.', 'bottom'),
      S('#grid', 'Item PO', 'Roll Import: pilih Rate LME untuk mengisi Alloy Price, tambah Fab Cost; Unit Price = Alloy Price + Fab Cost per Kg. Roll Lokal dan Sheet: Unit Price diisi manual. Isi Disc % dan Tax; Amount dihitung otomatis.', 'top'),
      S('#t_s', 'Total Order', 'Sub Total, Discount, TAX, dan Total Order dihitung dari semua baris.', 'top'),
      S('.form-actions button[type=submit]', 'Simpan', 'Menyimpan PO berstatus Waiting.', 'top')];
    if (has('.ov.show .md')) return [S('.ov.show .md', 'Detail PO', 'Header, tabel kurs dan LME, item, serta total PO.', 'left')];
    return [S(null, 'Purchase Order', 'Pesanan pembelian ke supplier dari PR yang sudah Approved.'),
      tAdd('Pilih bentuk material dahulu. Sheet memakai form tanpa Rate LME, Alloy Price, dan Fab Cost.'), tSearch, tTable('Daftar PO', 'Progress: Waiting (biru), Approved (hijau), Closed (merah, diset oleh proses penerimaan barang).'),
      tAct('View, Print. Pada PO Waiting: Edit, Approval PO (menampilkan referensi Last Purchase per item), dan Delete. PO yang sudah punya incoming tidak bisa dihapus.')]; };

  T['index.html'] = function () { return [S(null, 'Selamat datang', 'Ini daftar seluruh halaman mockup. Setiap halaman punya tombol Panduan di pojok kanan atas yang menjelaskan modul tersebut langkah demi langkah.'), S('.cards', 'Kartu modul', 'Klik satu kartu untuk membuka modulnya.', 'top'), side.search]; };
  T['daftar.html'] = T['index.html'];

  /* ---------- jalankan ---------- */
  function file() { var p = location.pathname.split('/').pop(); return p || 'index.html'; }
  function run() {
    var f = T[file()], list = f ? f() : [S(null, document.title, 'Belum ada panduan khusus untuk halaman ini.')];
    var extra = (!/^(index|daftar)\.html$/.test(file()) && !has('.ov.show')) ? [side.search, side.perm] : [];
    var st = list.concat(extra).filter(function (s) { return !s.el || has(s.el); }).map(function (s) {
      var step = {popover:{title:s.title, description:s.text}}; if (s.el) step.element = s.el; if (s.side && s.el) step.popover.side = s.side; return step; });
    var d = w.driver.js.driver({showProgress:true, allowClose:true, overlayOpacity:.6, stagePadding:6, nextBtnText:'Lanjut', prevBtnText:'Kembali', doneBtnText:'Selesai', progressText:'{{current}} dari {{total}}', steps:st});
    d.drive();
  }
  function load(cb) {
    if (w.driver && w.driver.js) return cb();
    var l = document.createElement('link'); l.rel = 'stylesheet'; l.href = base + 'driver.css'; document.head.appendChild(l);
    var s = document.createElement('script'); s.src = base + 'driver.js.iife.js'; s.onload = cb; s.onerror = function () { alert('Library driver.js tidak dapat dimuat.'); }; document.head.appendChild(s);
  }
  w.Mock = w.Mock || {};
  w.Mock.guide = {start:function () { load(run); }, tours:T};
})(window);
