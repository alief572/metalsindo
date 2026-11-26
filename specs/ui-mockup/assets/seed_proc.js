/* Data contoh alur Pengadaan Non Material sampai Pembayaran (fiktif; struktur mengikuti tabel modul aplikasi):
   rutin_non_planning_header/detail (PR Departemen), asset_planning, tran_pr_header/detail (PR Asset), material_planning_base_on_produksi (PR Stock),
   tr_purchase_order_non_material + dt_trans_po_non_material + tr_top_po (PO), warehouse_adjustment (incoming), tr_invoice_po / receive invoice AP,
   request_payment, payment_approve (pembayaran). Tanggal acuan: 10 Nov 2026. Data hanya di memori + localStorage. */
Mock.seedR = (function () {
  var A = function (lvl, date, reason) { return {st:'Y', by:['', 'Hendra Wijaya', 'Sari Utami', 'Bambang Hartono'][lvl], date:date, reason:reason || ''}; };
  var N = {1:null, 2:null, 3:null};
  var it = function (iid, nm, spec, sat, qty, harga, qpo, ket) { return {iid:iid, nm:nm, spec:spec, satuan:sat, qty:qty, harga:harga, qty_po:qpo || 0, ket:ket || ''}; };
  var R = {
    me: 'Rama',
    approver: {1:'Hendra Wijaya', 2:'Sari Utami', 3:'Bambang Hartono'},
    dept: [{id:20, nama:'Accounting'}, {id:21, nama:'Production'}, {id:22, nama:'PPIC'}, {id:23, nama:'QC'}, {id:24, nama:'Purchasing'}, {id:25, nama:'Marketing'}, {id:26, nama:'HR & GA'}, {id:27, nama:'Maintenance'}],
    cc: [{id:'CC2000001', dept:21, nama:'Slitting'}, {id:'CC2000002', dept:21, nama:'Sharing'}, {id:'CC2000003', dept:27, nama:'Workshop'}, {id:'CC2000004', dept:26, nama:'Umum'}, {id:'CC2000005', dept:22, nama:'Gudang'}, {id:'CC2000006', dept:23, nama:'Laboratorium'}, {id:'CC2000007', dept:20, nama:'Kantor Pusat'}],
    satuan: ['PCS', 'UNIT', 'SET', 'BOX', 'LOT', 'KG', 'ROLL', 'LTR', 'PAIR'],
    coaNR: [{coa:'6101-01', nama:'Biaya ATK'}, {coa:'6102-01', nama:'Biaya Perbaikan & Pemeliharaan Mesin'}, {coa:'6103-01', nama:'Biaya Perlengkapan Pabrik'}, {coa:'6104-02', nama:'Biaya Jasa Profesional'}, {coa:'6105-01', nama:'Biaya Rumah Tangga Kantor'}],
    coaAsset: [{coa:'1301-01', akum:'1301-91', nama:'Mesin & Peralatan'}, {coa:'1302-01', akum:'1302-91', nama:'Peralatan Kantor'}, {coa:'1303-01', akum:'1303-91', nama:'Kendaraan'}, {coa:'1304-01', akum:'1304-91', nama:'Perlengkapan Gudang'}],
    bank: [{id:'1101-02', nama:'Bank BCA - 123.456.7890'}, {id:'1101-03', nama:'Bank Mandiri - 140.000.123.456'}, {id:'1101-04', nama:'Bank BNI - 0123.4567.89'}],
    supplier: [
      {id:'S2600001', nama:'PT. SINAR TEKNIK ABADI', loi:'Lokal', top:'30 Hari setelah invoice', bank:'BCA', norek:'0123456789'},
      {id:'S2600002', nama:'CV. MITRA OFFICE SUPPLY', loi:'Lokal', top:'14 Hari setelah invoice', bank:'Mandiri', norek:'1400011223344'},
      {id:'S2600003', nama:'PT. INDO MESIN PRESISI', loi:'Lokal', top:'30 Hari setelah invoice', bank:'BNI', norek:'0099887766'},
      {id:'S2600004', nama:'PT. KARYA SPAREPART JAYA', loi:'Lokal', top:'Cash Before Delivery', bank:'BCA', norek:'5550123456'},
      {id:'S2600005', nama:'PT. BANGUN ASET UTAMA', loi:'Lokal', top:'DP 30% + Termin', bank:'Mandiri', norek:'1200099887766'}
    ],
    /* ---- PR Departemen (non_rutin) ---- */
    prDept: [
      {no_pengajuan:'PRN26100001', no_pr:'PRN26100001', id_dept:21, tgl:'2026-10-20', tgl_dibutuhkan:'2026-11-05', coa:'6103-01', project:'', reason:'Kebutuhan APD lini slitting bulan November', tingkat_pr:'Normal', by:'Rama', doc:'', closed:null, rej:null,
        app:{1:A(1, '2026-10-20 14:10'), 2:A(2, '2026-10-21 09:30'), 3:A(3, '2026-10-22 10:05')},
        items:[it('D1a', 'Sarung tangan katun', 'Putih, tebal, lapis karet', 'PAIR', 200, 8000, 200, 'Ganti tiap 2 minggu'), it('D1b', 'Masker KN95', 'Isi 50 pcs per box', 'BOX', 20, 85000, 10, '')]},
      {no_pengajuan:'PRN26110001', no_pr:'PRN26110001', id_dept:27, tgl:'2026-11-02', tgl_dibutuhkan:'2026-11-12', coa:'6102-01', project:'', reason:'Penggantian bearing dan pelumasan mesin slitting', tingkat_pr:'Urgent', by:'Dewi Lestari', doc:'', closed:null, rej:null,
        app:{1:A(1, '2026-11-02 15:00'), 2:A(2, '2026-11-03 08:40'), 3:A(3, '2026-11-03 13:20')},
        items:[it('D2a', 'Bearing 6205 ZZ', 'SKF / setara', 'PCS', 24, 65000, 24, ''), it('D2b', 'Grease SKF LGMT2 1 kg', 'Kaleng 1 kg', 'PCS', 6, 210000, 6, '')]},
      {no_pengajuan:'PRN26110002', no_pr:'PRN26110002', id_dept:26, tgl:'2026-11-04', tgl_dibutuhkan:'2026-11-30', coa:'6104-02', project:'', reason:'Kalibrasi tahunan timbangan lantai gudang', tingkat_pr:'Normal', by:'Budi Santoso', doc:'', closed:null, rej:null,
        app:{1:A(1, '2026-11-04 10:15'), 2:A(2, '2026-11-05 09:00'), 3:A(3, '2026-11-05 16:45')},
        items:[it('D3a', 'Jasa kalibrasi timbangan', 'Timbangan lantai 3 ton, sertifikat KAN', 'LOT', 2, 3500000, 0, '')]},
      {no_pengajuan:'PRN26110003', no_pr:'PRN26110003', id_dept:21, tgl:'2026-11-06', tgl_dibutuhkan:'2026-11-20', coa:'6103-01', project:'', reason:'Perlengkapan pengemasan coil', tingkat_pr:'Normal', by:'Rama', doc:'', closed:null, rej:null,
        app:{1:A(1, '2026-11-06 11:00'), 2:null, 3:null},
        items:[it('D4a', 'Strapping band PP 15 mm', 'Warna hitam, 10 kg per roll', 'ROLL', 30, 180000), it('D4b', 'Stretch film 500 mm', 'Hand roll 2.5 kg', 'ROLL', 40, 62000)]},
      {no_pengajuan:'PRN26110004', no_pr:'PRN26110004', id_dept:25, tgl:'2026-11-09', tgl_dibutuhkan:'2026-11-25', coa:'6101-01', project:'', reason:'Katalog dan brosur produk untuk pameran', tingkat_pr:'Normal', by:'Dewi Lestari', doc:'', closed:null, rej:null,
        app:N,
        items:[it('D5a', 'Cetak brosur A4', 'Art paper 150 gr, full color', 'PCS', 500, 4500)]},
      {no_pengajuan:'PRN26110005', no_pr:'PRN26110005', id_dept:20, tgl:'2026-11-05', tgl_dibutuhkan:'2026-11-18', coa:'6101-01', project:'', reason:'Kebutuhan kertas dan toner kantor pusat', tingkat_pr:'Normal', by:'Budi Santoso', doc:'', closed:null, rej:null,
        app:{1:A(1, '2026-11-05 13:30'), 2:A(2, '2026-11-06 10:10'), 3:null},
        items:[it('D6a', 'Kertas HVS A4 80 gr', 'Rim 500 lembar', 'BOX', 20, 245000), it('D6b', 'Toner printer HP 79A', 'Original', 'PCS', 4, 1150000)]},
      {no_pengajuan:'PRN26110006', no_pr:'PRN26110006', id_dept:23, tgl:'2026-11-03', tgl_dibutuhkan:'2026-11-15', coa:'6104-02', project:'', reason:'Jasa uji komposisi pihak ketiga', tingkat_pr:'Normal', by:'Rama', doc:'', closed:null,
        rej:{lvl:2, by:'Sari Utami', date:'2026-11-04 09:20', reason:'Harga estimasi terlalu tinggi, mohon lampirkan 2 penawaran pembanding.'},
        app:{1:A(1, '2026-11-03 15:20'), 2:{st:'D', by:'Sari Utami', date:'2026-11-04 09:20', reason:'Harga estimasi terlalu tinggi, mohon lampirkan 2 penawaran pembanding.'}, 3:null},
        items:[it('D7a', 'Uji komposisi OES', 'Per sampel, laporan resmi', 'LOT', 12, 650000)]}
    ],
    /* ---- Asset Planning ---- */
    planAsset: [
      {code_plan:'AP2609001', id_dept:20, cc:'CC2000007', nama_asset:'UPS 3 kVA Ruang Server', ket:'Ganti UPS lama yang baterainya rusak', qty:1, budget:32000000, budget_pr:2000000, budget_po:2500000, bulan:'09', tahun:'2026', coa:'1302-01', coa_akum:'1302-91', status:'Y', reason:'', by:'Budi Santoso', date:'2026-09-02 10:00', pr:'PRA26100001'},
      {code_plan:'AP2610001', id_dept:21, cc:'CC2000001', nama_asset:'Forklift Elektrik 2 Ton', ket:'Penambahan unit untuk area slitting', qty:1, budget:450000000, budget_pr:25000000, budget_po:30000000, bulan:'10', tahun:'2026', coa:'1301-01', coa_akum:'1301-91', status:'Y', reason:'', by:'Rama', date:'2026-10-05 09:15', pr:'PRA26110001'},
      {code_plan:'AP2610002', id_dept:23, cc:'CC2000006', nama_asset:'Hardness Tester Digital', ket:'Uji kekerasan material masuk', qty:1, budget:85000000, budget_pr:3000000, budget_po:85000000, bulan:'10', tahun:'2026', coa:'1301-01', coa_akum:'1301-91', status:'Y', reason:'', by:'Dewi Lestari', date:'2026-10-07 11:20', pr:'PRA26110002'},
      {code_plan:'AP2610003', id_dept:22, cc:'CC2000005', nama_asset:'Rak Gudang Heavy Duty', ket:'Rak untuk area spare part', qty:10, budget:60000000, budget_pr:0, budget_po:60000000, bulan:'10', tahun:'2026', coa:'1304-01', coa_akum:'1304-91', status:'Y', reason:'', by:'Budi Santoso', date:'2026-10-08 14:00', pr:'PRA26110003'},
      {code_plan:'AP2610004', id_dept:22, cc:'CC2000005', nama_asset:'Timbangan Digital Gudang 1 Ton', ket:'Pengganti timbangan analog', qty:2, budget:24000000, budget_pr:24000000, budget_po:24000000, bulan:'11', tahun:'2026', coa:'1301-01', coa_akum:'1301-91', status:'Y', reason:'', by:'Budi Santoso', date:'2026-10-12 08:40', pr:''},
      {code_plan:'AP2610005', id_dept:20, cc:'CC2000007', nama_asset:'Printer Label Barcode', ket:'Label barcode coil di gudang', qty:2, budget:18000000, budget_pr:1000000, budget_po:18000000, bulan:'11', tahun:'2026', coa:'1302-01', coa_akum:'1302-91', status:'Y', reason:'', by:'Dewi Lestari', date:'2026-10-14 10:10', pr:'PRA26110004'},
      {code_plan:'AP2610006', id_dept:27, cc:'CC2000003', nama_asset:'Genset 100 kVA', ket:'Cadangan listrik area produksi', qty:1, budget:380000000, budget_pr:380000000, budget_po:380000000, bulan:'12', tahun:'2026', coa:'1301-01', coa_akum:'1301-91', status:'Y', reason:'', by:'Rama', date:'2026-10-15 13:45', pr:'PRA26110005'},
      {code_plan:'AP2611001', id_dept:26, cc:'CC2000004', nama_asset:'AC Split 2 PK', ket:'Ruang meeting dan ruang direksi', qty:3, budget:36000000, budget_pr:36000000, budget_po:36000000, bulan:'12', tahun:'2026', coa:'1302-01', coa_akum:'1302-91', status:'N', reason:'', by:'Budi Santoso', date:'2026-11-03 09:00', pr:''},
      {code_plan:'AP2611002', id_dept:20, cc:'CC2000007', nama_asset:'Laptop Staff Accounting', ket:'Pengganti laptop berumur lebih dari 5 tahun', qty:2, budget:30000000, budget_pr:30000000, budget_po:30000000, bulan:'12', tahun:'2026', coa:'1302-01', coa_akum:'1302-91', status:'N', reason:'', by:'Dewi Lestari', date:'2026-11-05 10:30', pr:''},
      {code_plan:'AP2611003', id_dept:21, cc:'CC2000002', nama_asset:'Overhead Crane Hoist 3 Ton', ket:'Area sharing', qty:1, budget:275000000, budget_pr:275000000, budget_po:275000000, bulan:'12', tahun:'2026', coa:'1301-01', coa_akum:'1301-91', status:'D', reason:'Belum masuk RAB 2026, ajukan ulang pada perencanaan 2027.', by:'Rama', date:'2026-11-04 16:10', pr:''}
    ],
    /* ---- PR Asset (pr_asset) ---- */
    prAsset: [
      {no_pr:'PRA26100001', tgl:'2026-10-10', by:'Budi Santoso', rej:null, app:{1:A(1, '2026-10-10 15:00'), 2:A(2, '2026-10-11 09:00'), 3:A(3, '2026-10-12 11:30')},
        items:[{iid:'A0a', plan:'AP2609001', nm:'UPS 3 kVA Ruang Server', id_dept:20, cc:'CC2000007', satuan:'UNIT', qty:1, harga:30000000, qty_po:1, spec:'Online double conversion', ket:''}]},
      {no_pr:'PRA26110001', tgl:'2026-10-28', by:'Rama', rej:null, app:{1:A(1, '2026-10-28 14:00'), 2:A(2, '2026-10-29 10:00'), 3:A(3, '2026-10-30 09:40')},
        items:[{iid:'A1a', plan:'AP2610001', nm:'Forklift Elektrik 2 Ton', id_dept:21, cc:'CC2000001', satuan:'UNIT', qty:1, harga:425000000, qty_po:1, spec:'Kapasitas 2 ton, mast 3 m, baterai lithium', ket:''}]},
      {no_pr:'PRA26110002', tgl:'2026-11-05', by:'Dewi Lestari', rej:null, app:{1:A(1, '2026-11-05 11:00'), 2:null, 3:null},
        items:[{iid:'A2a', plan:'AP2610002', nm:'Hardness Tester Digital', id_dept:23, cc:'CC2000006', satuan:'UNIT', qty:1, harga:82000000, qty_po:0, spec:'Rockwell, layar sentuh', ket:''}]},
      {no_pr:'PRA26110003', tgl:'2026-11-09', by:'Budi Santoso', rej:null, app:N,
        items:[{iid:'A3a', plan:'AP2610003', nm:'Rak Gudang Heavy Duty', id_dept:22, cc:'CC2000005', satuan:'UNIT', qty:10, harga:6000000, qty_po:0, spec:'Beban 800 kg per level', ket:''}]},
      {no_pr:'PRA26110004', tgl:'2026-11-06', by:'Dewi Lestari', rej:null, app:{1:A(1, '2026-11-06 09:50'), 2:A(2, '2026-11-07 10:30'), 3:null},
        items:[{iid:'A4a', plan:'AP2610005', nm:'Printer Label Barcode', id_dept:20, cc:'CC2000007', satuan:'UNIT', qty:2, harga:8500000, qty_po:0, spec:'Thermal transfer 203 dpi', ket:''}]},
      {no_pr:'PRA26110005', tgl:'2026-11-04', by:'Rama',
        rej:{lvl:1, by:'Hendra Wijaya', date:'2026-11-04 15:30', reason:'Spesifikasi daya belum final, konfirmasi dengan Maintenance.'},
        app:{1:{st:'D', by:'Hendra Wijaya', date:'2026-11-04 15:30', reason:'Spesifikasi daya belum final, konfirmasi dengan Maintenance.'}, 2:null, 3:null},
        items:[{iid:'A5a', plan:'AP2610006', nm:'Genset 100 kVA', id_dept:27, cc:'CC2000003', satuan:'UNIT', qty:1, harga:375000000, qty_po:0, spec:'Silent type, ATS', ket:''}]}
    ],
    /* ---- PR Stock (app_pr_stock; material planning berbasis produksi) ---- */
    prStock: [
      {so:'SO-26-0412', no_pr:'PRS26110002', asal:'Sales Order', untuk:'Stock Produksi', by:'Dewi Lestari', tgl:'2026-11-03', due:'2026-11-25', tgl_dibutuhkan:'2026-11-20', customer:'PT. ABC METALINDO', tingkat_pr:'Normal', rej:null, app:N,
        items:[{iid:'T1a', nm:'Pallet kayu 1 x 1,2 m', satuan:'PCS', estimasi:120, free:40, use:40, min:20, max:200, moq:10, pr:80, rev:80, harga:95000, st:'', qty_po:0},
               {iid:'T1b', nm:'Strapping band PP 15 mm', satuan:'ROLL', estimasi:30, free:6, use:6, min:5, max:60, moq:5, pr:24, rev:24, harga:180000, st:'', qty_po:0}]},
      {so:'REQ-26-0087', no_pr:'PRS26110003', asal:'Request Produksi', untuk:'Stock Gudang', by:'Rama', tgl:'2026-11-04', due:'2026-11-28', tgl_dibutuhkan:'2026-11-22', customer:'-', tingkat_pr:'Urgent', rej:null, app:{1:A(1, '2026-11-05 09:00'), 2:null, 3:null},
        items:[{iid:'T2a', nm:'Interleaf paper 80 gsm', satuan:'ROLL', estimasi:60, free:10, use:10, min:10, max:100, moq:10, pr:50, rev:50, harga:420000, st:'', qty_po:0}]},
      {so:'SO-26-0398', no_pr:'PRS26110004', asal:'Sales Order', untuk:'Stock Produksi', by:'Budi Santoso', tgl:'2026-11-02', due:'2026-11-24', tgl_dibutuhkan:'2026-11-18', customer:'PT. DELTA PRESISI', tingkat_pr:'Normal', rej:null, app:{1:A(1, '2026-11-03 10:00'), 2:A(2, '2026-11-04 11:30'), 3:null},
        items:[{iid:'T3a', nm:'Wooden crate 120 x 80 cm', satuan:'PCS', estimasi:40, free:5, use:5, min:5, max:60, moq:5, pr:35, rev:35, harga:350000, st:'', qty_po:0},
               {iid:'T3b', nm:'Desiccant 1 kg', satuan:'PCS', estimasi:200, free:50, use:50, min:50, max:400, moq:50, pr:150, rev:150, harga:12000, st:'', qty_po:0}]},
      {so:'SO-26-0371', no_pr:'PRS26110001', asal:'Sales Order', untuk:'Stock Produksi', by:'Dewi Lestari', tgl:'2026-10-26', due:'2026-11-15', tgl_dibutuhkan:'2026-11-12', customer:'PT. MEGA LOGAM', tingkat_pr:'Normal', rej:null, app:{1:A(1, '2026-10-27 09:00'), 2:A(2, '2026-10-28 10:00'), 3:A(3, '2026-10-29 14:10')},
        items:[{iid:'T4a', nm:'Pallet besi 1,2 x 1,2 m', satuan:'PCS', estimasi:25, free:5, use:5, min:5, max:40, moq:5, pr:20, rev:20, harga:780000, st:'Y', qty_po:0},
               {iid:'T4b', nm:'Plastik pembungkus coil 1 m', satuan:'ROLL', estimasi:12, free:2, use:2, min:2, max:20, moq:2, pr:10, rev:10, harga:540000, st:'Y', qty_po:0}]},
      {so:'REQ-26-0079', no_pr:'PRS26110005', asal:'Request Produksi', untuk:'Stock Gudang', by:'Rama', tgl:'2026-10-30', due:'2026-11-20', tgl_dibutuhkan:'2026-11-14', customer:'-', tingkat_pr:'Normal', 
        rej:{lvl:3, by:'Bambang Hartono', date:'2026-11-02 16:00', reason:'Stok masih cukup sampai akhir bulan, ajukan ulang minggu depan.'},
        app:{1:A(1, '2026-10-31 09:00'), 2:A(2, '2026-11-01 11:00'), 3:{st:'D', by:'Bambang Hartono', date:'2026-11-02 16:00', reason:'Stok masih cukup sampai akhir bulan, ajukan ulang minggu depan.'}},
        items:[{iid:'T5a', nm:'Label coil 10 x 15 cm', satuan:'ROLL', estimasi:80, free:30, use:30, min:20, max:120, moq:10, pr:50, rev:50, harga:75000, st:'', qty_po:0}]}
    ],
    /* ---- Purchase Order Non Material ---- */
    po: [
      {no_po:'PO2610001', no_surat:'PO-NM/26/X/0001', tanggal:'2026-10-14', id_suplier:'S2600005', loi:'Lokal', cif:'LOCO', matauang:'IDR', kurs:1, delivery_date:'2026-10-28', pic:'Gudang - Andi', note:'', kirim:0, status:2, reject_reason:'', revisi:0, close_po:null, by:'Rama', created:'2026-10-14 10:00',
        items:[{iid:'A0a', no_pr:'PRA26100001', tipe_pr:'Asset', nm:'UPS 3 kVA Ruang Server', spec:'Online double conversion', satuan:'UNIT', qty_pr:1, qty:1, harga:29500000, disc:0, ppn:11, qty_in:1, ket:''}],
        top:[{group:'Incoming', persen:100, ket:'Dibayar setelah barang diterima'}]},
      {no_po:'PO2611001', no_surat:'PO-NM/26/XI/0001', tanggal:'2026-10-26', id_suplier:'S2600002', loi:'Lokal', cif:'LOCO', matauang:'IDR', kurs:1, delivery_date:'2026-11-06', pic:'Gudang - Andi', note:'Kirim bertahap', kirim:0, status:2, reject_reason:'', revisi:0, close_po:null, by:'Rama', created:'2026-10-26 11:00',
        items:[{iid:'D1a', no_pr:'PRN26100001', tipe_pr:'Department', nm:'Sarung tangan katun', spec:'Putih, tebal, lapis karet', satuan:'PAIR', qty_pr:200, qty:200, harga:8000, disc:0, ppn:11, qty_in:200, ket:''},
               {iid:'D1b', no_pr:'PRN26100001', tipe_pr:'Department', nm:'Masker KN95', spec:'Isi 50 pcs per box', satuan:'BOX', qty_pr:20, qty:10, harga:85000, disc:0, ppn:11, qty_in:10, ket:''}],
        top:[{group:'DP', persen:30, ket:'Uang muka'}, {group:'Incoming', persen:70, ket:'Pelunasan setelah barang diterima'}]},
      {no_po:'PO2611002', no_surat:'PO-NM/26/XI/0002', tanggal:'2026-11-06', id_suplier:'S2600004', loi:'Lokal', cif:'LOCO', matauang:'IDR', kurs:1, delivery_date:'2026-11-13', pic:'Gudang - Andi', note:'', kirim:50000, status:1, reject_reason:'', revisi:0, close_po:null, by:'Rama', created:'2026-11-06 14:20',
        items:[{iid:'D2a', no_pr:'PRN26110001', tipe_pr:'Department', nm:'Bearing 6205 ZZ', spec:'SKF / setara', satuan:'PCS', qty_pr:24, qty:24, harga:64000, disc:0, ppn:11, qty_in:0, ket:''}],
        top:[{group:'Incoming', persen:100, ket:'Cash setelah barang diterima'}]},
      {no_po:'PO2611003', no_surat:'PO-NM/26/XI/0003', tanggal:'2026-11-02', id_suplier:'S2600005', loi:'Lokal', cif:'LOCO', matauang:'IDR', kurs:1, delivery_date:'2026-11-20', pic:'Maintenance - Yusuf', note:'Termasuk pelatihan operator', kirim:2500000, status:2, reject_reason:'', revisi:0, close_po:null, by:'Rama', created:'2026-11-02 09:30',
        items:[{iid:'A1a', no_pr:'PRA26110001', tipe_pr:'Asset', nm:'Forklift Elektrik 2 Ton', spec:'Kapasitas 2 ton, mast 3 m, baterai lithium', satuan:'UNIT', qty_pr:1, qty:1, harga:420000000, disc:0, ppn:11, qty_in:0, ket:''}],
        top:[{group:'DP', persen:30, ket:'Uang muka'}]},
      {no_po:'PO2611004', no_surat:'PO-NM/26/XI/0004', tanggal:'2026-11-07', id_suplier:'S2600003', loi:'Lokal', cif:'LOCO', matauang:'IDR', kurs:1, delivery_date:'2026-11-14', pic:'Gudang - Andi', note:'', kirim:0, status:1, reject_reason:'Harga di atas penawaran terakhir (Rp 195.000). Mohon negosiasi ulang.', revisi:1, close_po:null, by:'Rama', created:'2026-11-07 10:10',
        items:[{iid:'D2b', no_pr:'PRN26110001', tipe_pr:'Department', nm:'Grease SKF LGMT2 1 kg', spec:'Kaleng 1 kg', satuan:'PCS', qty_pr:6, qty:6, harga:215000, disc:0, ppn:11, qty_in:0, ket:''}],
        top:[{group:'Incoming', persen:100, ket:'30 hari setelah invoice'}]}
    ],
    /* ---- Incoming (warehouse_adjustment) ---- */
    incoming: [
      {kode_trans:'TRS2611001', kind:'dept', tanggal:'2026-11-02', no_po:'PO2611001', sj:'SJ/MOS/2611-017', pic:'Andi', receiver:'Gudang - Andi', note:'Pengiriman tahap 1', dept:'', cc:'', doc:'', invoiced:'',
        items:[{iid:'D1a', nm:'Sarung tangan katun', qty_order:200, qty_in:120, ket:'Kondisi baik'}]},
      {kode_trans:'TRS2611002', kind:'dept', tanggal:'2026-11-05', no_po:'PO2611001', sj:'SJ/MOS/2611-021', pic:'Andi', receiver:'Gudang - Andi', note:'Pengiriman tahap 2 (pelunasan)', dept:'', cc:'', doc:'', invoiced:'IV2611002',
        items:[{iid:'D1a', nm:'Sarung tangan katun', qty_order:80, qty_in:80, ket:''}, {iid:'D1b', nm:'Masker KN95', qty_order:10, qty_in:10, ket:'2 box penyok, tetap diterima'}]},
      {kode_trans:'TRA2610001', kind:'asset', tanggal:'2026-10-27', no_po:'PO2610001', sj:'SJ/BAU/2610-005', pic:'Eko', receiver:'Accounting - Eko', note:'UPS terpasang di ruang server', dept:20, cc:'CC2000007', doc:'', invoiced:'IV2611003', assets:['AST-2610-0001'],
        items:[{iid:'A0a', nm:'UPS 3 kVA Ruang Server', qty_order:1, qty_in:1, ket:'Baik', status:'OK'}]}
    ],
    /* ---- Receive Invoice (purchase_order_payment): satu tabel untuk tipe DP / Incoming / Progress; PO Non Material (src nm) dan PO Material (src mat).
       lines[] = baris per PO: gross (nilai dasar), pot_dp (potongan DP proporsional), pot_prog, net (nilai ditagih). Nilai dalam mata uang PO (cur), idr = net x kurs. ---- */
    invoice: [
      {id:'IV2611001', tipe:'DP', tgl:'2026-10-28', no_invoice:'DP/MOS/2610/011', no_faktur:'010.000-26.00123400', tgl_faktur:'2026-10-28', sup:'CV. MITRA OFFICE SUPPLY', cur:'IDR', kurs:1, bank:'Mandiri', no_bank:'1400011223344', note:'Uang muka PO2611001', doc:'', requested:'RP2611001',
        lines:[{src:'nm', no_po:'PO2611001', no_surat:'PO-NM/26/XI/0001', top_idx:0, kode:'', gross:815850, pot_dp:0, pot_prog:0, net:815850}], value:815850, idr:815850},
      {id:'IV2611002', tipe:'Incoming', tgl:'2026-11-08', no_invoice:'INV/MOS/2611/0098', no_faktur:'010.000-26.00123456', tgl_faktur:'2026-11-08', sup:'CV. MITRA OFFICE SUPPLY', cur:'IDR', kurs:1, bank:'Mandiri', no_bank:'1400011223344', note:'Pelunasan PO2611001 tahap 2', doc:'', requested:'RP2611002',
        lines:[{src:'nm', no_po:'PO2611001', no_surat:'PO-NM/26/XI/0001', top_idx:-1, kode:'TRS2611002', gross:1653900, pot_dp:496170, pot_prog:0, net:1157730}], value:1157730, idr:1157730},
      {id:'IV2611003', tipe:'Incoming', tgl:'2026-11-03', no_invoice:'INV/BAU/2610/0044', no_faktur:'010.000-26.00123499', tgl_faktur:'2026-11-03', sup:'PT. BANGUN ASET UTAMA', cur:'IDR', kurs:1, bank:'Mandiri', no_bank:'1200099887766', note:'UPS ruang server', doc:'', requested:'RP2611008',
        lines:[{src:'nm', no_po:'PO2610001', no_surat:'PO-NM/26/X/0001', top_idx:-1, kode:'TRA2610001', gross:32745000, pot_dp:0, pot_prog:0, net:32745000}], value:32745000, idr:32745000},
      {id:'IV2611004', tipe:'DP', tgl:'2026-11-06', no_invoice:'DP/MICS/2611/003', no_faktur:'', tgl_faktur:'', sup:'PT.MICS STEEL INDONESIA', cur:'IDR', kurs:1, bank:'', no_bank:'', note:'Uang muka PO Material', doc:'', requested:'RP2611011',
        lines:[{src:'mat', no_po:'P2600997', no_surat:'PO-MP/26/X/0997', top_idx:0, kode:'', gross:60591614, pot_dp:0, pot_prog:0, net:60591614}], value:60591614, idr:60591614}
    ],
    /* ---- Request Payment (request_payment): app = status Checker / Management ---- */
    reqPay: [
      {id:'RP2611001', no_doc:'RP2611001', tipe:'po', ref:'IV2611001', no_invoice:'DP/MOS/2610/011', supplier:'CV. MITRA OFFICE SUPPLY', requester:'Rama', tgl:'2026-10-29', keperluan:'Uang muka PO2611001', jumlah:815850, tgl_bayar:'2026-11-12', ket_po:'PO-NM/26/XI/0001 (DP 30%)', chk:A(1, '2026-10-30 10:00'), mgm:null, rej:null, pay_id:''},
      {id:'RP2611002', no_doc:'RP2611002', tipe:'po', ref:'IV2611002', no_invoice:'INV/MOS/2611/0098', supplier:'CV. MITRA OFFICE SUPPLY', requester:'Rama', tgl:'2026-11-08', keperluan:'Pelunasan PO2611001 tahap 2', jumlah:1157730, tgl_bayar:'2026-11-20', ket_po:'PO-NM/26/XI/0001 (Incoming TRS2611002, potongan DP proporsional 30%)', chk:null, mgm:null, rej:null, pay_id:''},
      {id:'RP2611003', no_doc:'RP2611003', tipe:'expense', ref:'', no_invoice:'', supplier:'', requester:'Dewi Lestari', tgl:'2026-11-07', keperluan:'Perjalanan dinas ke Surabaya (kunjungan customer)', jumlah:3850000, tgl_bayar:'2026-11-13', ket_po:'', chk:null, mgm:null, rej:null, pay_id:''},
      {id:'RP2611004', no_doc:'RP2611004', tipe:'kasbon', ref:'', no_invoice:'', supplier:'', requester:'Budi Santoso', tgl:'2026-11-09', keperluan:'Kasbon operasional pameran', jumlah:7500000, tgl_bayar:'2026-11-12', ket_po:'', chk:null, mgm:null, rej:null, pay_id:''},
      {id:'RP2611005', no_doc:'RP2611005', tipe:'transportasi', ref:'', no_invoice:'', supplier:'', requester:'Rama', tgl:'2026-11-06', keperluan:'Ongkos kirim sampel ke customer (Cikarang)', jumlah:1250000, tgl_bayar:'2026-11-11', ket_po:'', chk:null, mgm:null, rej:null, pay_id:''},
      {id:'RP2611006', no_doc:'RP2611006', tipe:'periodik', ref:'', no_invoice:'', supplier:'PT. GLOBAL OFFICE RENTAL', requester:'Budi Santoso', tgl:'2026-11-01', keperluan:'Sewa mesin fotokopi bulan November', jumlah:4200000, tgl_bayar:'2026-11-10', ket_po:'', chk:A(1, '2026-11-02 09:00'), mgm:null, rej:null, pay_id:''},
      {id:'RP2611007', no_doc:'RP2611007', tipe:'direct_payment', ref:'', no_invoice:'', supplier:'PT. PLN (PERSERO)', requester:'Budi Santoso', tgl:'2026-10-30', keperluan:'Tagihan listrik Oktober 2026', jumlah:128450000, tgl_bayar:'2026-11-05', ket_po:'', chk:A(1, '2026-10-31 10:00'), mgm:A(3, '2026-11-02 11:00'), rej:null, pay_id:''},
      {id:'RP2611008', no_doc:'RP2611008', tipe:'po', ref:'IV2611003', no_invoice:'INV/BAU/2610/0044', supplier:'PT. BANGUN ASET UTAMA', requester:'Budi Santoso', tgl:'2026-10-30', keperluan:'Pelunasan UPS ruang server', jumlah:32745000, tgl_bayar:'2026-11-09', ket_po:'PO-NM/26/X/0001', chk:A(1, '2026-10-31 14:00'), mgm:A(3, '2026-11-02 15:00'), rej:null, pay_id:''},
      {id:'RP2611009', no_doc:'RP2611009', tipe:'expense', ref:'', no_invoice:'', supplier:'', requester:'Dewi Lestari', tgl:'2026-10-28', keperluan:'Biaya perjalanan dinas Semarang', jumlah:2100000, tgl_bayar:'2026-11-04', ket_po:'', chk:A(1, '2026-10-29 09:30'), mgm:A(3, '2026-10-30 10:30'), rej:null, pay_id:'PAYM2611001'},
      {id:'RP2611010', no_doc:'RP2611010', tipe:'expense', ref:'', no_invoice:'', supplier:'', requester:'Rama', tgl:'2026-11-05', keperluan:'Konsumsi tamu audit eksternal', jumlah:950000, tgl_bayar:'2026-11-10', ket_po:'', chk:{st:'D', by:'Hendra Wijaya', date:'2026-11-06 09:00', reason:'Bukti pembayaran belum dilampirkan.'}, mgm:null,
        rej:{lvl:1, by:'Hendra Wijaya', date:'2026-11-06 09:00', reason:'Bukti pembayaran belum dilampirkan.'}, pay_id:''},
      {id:'RP2611012', no_doc:'CSH-26-0007', tipe:'cash', ref:'', no_invoice:'', supplier:'', requester:'Andi', tgl:'2026-11-07', keperluan:'PR Cash - PRN26110001 - PR Departemen', jumlah:2400000, tgl_bayar:'2026-11-12', ket_po:'', chk:null, mgm:null, rej:null, pay_id:''},
      {id:'RP2611011', no_doc:'RP2611011', tipe:'po', ref:'IV2611004', no_invoice:'DP/MICS/2611/003', supplier:'PT.MICS STEEL INDONESIA', requester:'Rama', tgl:'2026-11-07', keperluan:'Uang muka PO-MP/26/X/0997 (DP 30%)', jumlah:60591614, tgl_bayar:'2026-11-14', ket_po:'PO-MP/26/X/0997 (DP 30%)', chk:{st:'D', by:'Hendra Wijaya', date:'2026-11-08 09:30', reason:'Faktur pajak uang muka belum dilampirkan.'}, mgm:null,
        rej:{lvl:1, by:'Hendra Wijaya', date:'2026-11-08 09:30', reason:'Faktur pajak uang muka belum dilampirkan.'}, pay_id:''}
    ],
    /* ---- Payment (pembayaran_material) ---- */
    payment: [
      {id:'PAYM2611001', tgl:'2026-11-04', bank:'1101-02', penerima:'Dewi Lestari', reqs:['RP2611009'], nilai:2100000, admin:0, ket:'Biaya perjalanan dinas Semarang', jenis:'nonpr', matauang:'IDR', kurs:1}
    ]
  };
  return R;
})();
Mock.persistRestore('seedR');
