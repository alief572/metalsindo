/* Mockup alur Pengadaan Non Material sampai Pembayaran: inti (registry halaman, bar alur antar modul, mesin approval 3 level, fungsi hitung).
   Builder halaman ada di proc_pr.js, proc_po.js, proc_fin.js. Data: Mock.seedR (seed_proc.js), tersimpan di localStorage lewat M.saveFlow / M.delFlow. */
(function (M) {
  'use strict';
  var R = M.seedR, esc = M.esc, fmt = M.fmt;
  var P = M.proc = {R:R, build:{}};
  P.TODAY = '2026-11-10'; P.NOW = '2026-11-10 09:00'; P.YM = '2611';
  P.num = function (v) { return fmt(v || 0, 0); };
  P.idr = function (v) { return M.idr(v || 0); };
  P.pad = function (n, l) { return String(n).padStart(l, '0'); };
  P.qs = function (k) { var m = new RegExp('[?&]' + k + '=([^&]*)').exec(location.search); return m ? decodeURIComponent(m[1]) : ''; };
  P.dept = function (id) { var d = R.dept.filter(function (x) { return String(x.id) === String(id); })[0]; return d ? d.nama : '-'; };
  P.cc = function (id) { var d = R.cc.filter(function (x) { return x.id === id; })[0]; return d ? d.nama : '-'; };
  P.sup = function (id) { return R.supplier.filter(function (x) { return x.id === id; })[0] || {nama:'-', loi:'', top:''}; };
  P.up = function (s) { return String(s == null ? '' : s).toUpperCase(); };
  P.nextNo = function (prefix, rows, field, len) {
    var mx = 0; rows.forEach(function (r) { var v = String(r[field] || ''); if (v.indexOf(prefix) === 0) { var n = parseInt(v.slice(prefix.length), 10); if (n > mx) mx = n; } });
    return prefix + P.pad(mx + 1, len);
  };
  P.btn = function (cls, a, id, label, title) { return ' <button type="button" class="btn btn-sm ' + cls + '" data-a="' + a + '" data-id="' + esc(id) + '" title="' + esc(title || '') + '">' + label + '</button>'; };
  P.pill = function (t, c) { return M.pill(t, c); };
  P.linkBtn = function (href, label, cls) { return '<a class="btn btn-sm ' + (cls || 'btn-default') + '" href="' + href + '">' + label + '</a>'; };

  /* ---------- Registry halaman: [key = nama file, label menu, modul/halaman asli, grup, stage alur, sub judul] ---------- */
  var STAGES = ['PR', 'Approval PR', 'Metode Pembelian', 'Purchase Order', 'Approval PO', 'Penerimaan', 'Invoice', 'Request Payment', 'Pembayaran'];
  var PG = [
    ['non_rutin_index', 'PR Departemen', 'non_rutin/index', 'PR Departemen', 0, 'Daftar PR Departemen (non rutin)'],
    ['non_rutin_approval_head', 'Approval Head Department', 'non_rutin/approval_head', 'PR Departemen', 1, 'Approval PR Departemen - Head Department'],
    ['non_rutin_approval_cost_control', 'Approval Cost Control', 'non_rutin/approval_cost_control', 'PR Departemen', 1, 'Approval PR Departemen - Cost Control'],
    ['non_rutin_approval_management', 'Approval Management', 'non_rutin/approval_management', 'PR Departemen', 1, 'Approval PR Departemen - Management'],
    ['asset_planning_index_asset', 'Planning Asset', 'asset_planning/index_asset', 'PR Asset', 0, 'Budget planning asset per departemen'],
    ['asset_planning_index_asset_approve', 'Approval Planning Asset', 'asset_planning/index_asset/approve', 'PR Asset', 1, 'Approval budget planning asset'],
    ['pr_asset_pr', 'PR Asset', 'pr_asset/pr', 'PR Asset', 0, 'PR Asset dari planning yang sudah Approved'],
    ['pr_asset_approval_head', 'Approval Head', 'pr_asset/approval_head', 'PR Asset', 1, 'Approval PR Asset - Head'],
    ['pr_asset_approval_cost_control', 'Approval Cost Control', 'pr_asset/approval_cost_control', 'PR Asset', 1, 'Approval PR Asset - Cost Control'],
    ['pr_asset_approval_management', 'Approval Management', 'pr_asset/approval_management', 'PR Asset', 1, 'Approval PR Asset - Management'],
    ['app_pr_stock_approval_head', 'Approval Head', 'app_pr_stock/approval_head', 'PR Stock', 1, 'Approval PR Stock - Head'],
    ['app_pr_stock_approval_cost_control', 'Approval Cost Control', 'app_pr_stock/approval_cost_control', 'PR Stock', 1, 'Approval PR Stock - Cost Control'],
    ['app_pr_stock_approval_management', 'Approval Management', 'app_pr_stock/approval_management', 'PR Stock', 1, 'Approval PR Stock - Management'],
    ['metode_pembelian_pr', 'Metode Pembelian - PR', 'metode_pembelian/pr', 'Pembelian Non Material', 2, 'PR Approved (Departemen, Asset, Stok) siap dibeli'],
    ['purchase_order_non_material', 'Purchase Order Non Material', 'purchase_order_non_material', 'Pembelian Non Material', 3, 'Daftar PO dan pembuatan PO dari PR Approved'],
    ['approval_po_non_material', 'Approval PO Non Material', 'approval_po_non_material', 'Pembelian Non Material', 4, 'Persetujuan PO Non Material'],
    ['incoming_departemen', 'Incoming Departemen', 'incoming_departemen', 'Penerimaan', 5, 'Penerimaan barang / jasa departemen'],
    ['incoming_asset_asset', 'Incoming Asset', 'incoming_asset/asset', 'Penerimaan', 5, 'Penerimaan asset berdasarkan PO'],
    ['purchase_order_payment', 'Receive Invoice (DP / Incoming)', 'purchase_order_payment', 'Hutang & Pembayaran', 6, 'Satu modul Receive Invoice untuk semua PO (Non Material dan Material): DP dan Incoming'],
    ['request_payment_list_approve_checker', 'Request Payment - Checker', 'request_payment/list_approve_checker', 'Hutang & Pembayaran', 7, 'Approval Request Payment oleh Checker'],
    ['request_payment_list_approve_management', 'Request Payment - Management', 'request_payment/list_approve_management', 'Hutang & Pembayaran', 7, 'Approval Request Payment oleh Management'],
    ['request_payment_index', 'Dashboard Pembayaran (Finance)', 'request_payment/index', 'Hutang & Pembayaran', 7, 'Ringkasan jumlah dan nominal pengajuan pembayaran per status, kategori, dan jatuh tempo (baca-saja)'],
    ['pembayaran_material_payment_list', 'Payment List', 'pembayaran_material/payment_list', 'Hutang & Pembayaran', 8, 'Pembayaran atas Request Payment yang sudah Approved']
  ];
  var REG = {}; PG.forEach(function (g) { REG[g[0]] = {key:g[0], label:g[1], mod:g[2], grp:g[3], stage:g[4], sub:g[5], href:g[0] + '.html'}; });
  P.reg = REG; P.pages = PG; P.stages = STAGES;
  /* halaman mockup lama yang ikut tersambung */
  REG.purchase_order = {key:'purchase_order', label:'Purchase Order (Material)', href:'purchase_order.html', ext:true};
  REG.asset = {key:'asset', label:'Asset (master)', href:'asset.html', ext:true};

  /* ---------- Fungsi hitung bersama ---------- */
  P.LV = {1:'Head', 2:'Cost Control', 3:'Management'};
  P.waitLvl = function (d) { if (d.closed || d.rej) return 0; return !d.app[1] ? 1 : !d.app[2] ? 2 : !d.app[3] ? 3 : 0; };
  P.docState = function (d, kind) {
    var lv = kind === 'dept' ? {1:'Head Department', 2:'Cost Control', 3:'Management'} : P.LV;
    if (d.closed) return {t:'Closed', c:'gray'};
    if (d.rej) return {t:'Rejected By ' + lv[d.rej.lvl], c:'off'};
    var w = P.waitLvl(d); if (w) return {t:'Waiting Approval ' + lv[w], c:'blue'};
    return {t:'Approved', c:'ok'};
  };
  P.decide = function (d, lvl, ok, reason, kind) {
    var who = R.approver[lvl], rec = {st:ok ? 'Y' : 'D', by:who, date:P.NOW, reason:reason || ''};
    d.app[lvl] = rec;
    if (!ok) { d.rej = {lvl:lvl, by:who, date:P.NOW, reason:reason}; return; }
    if (lvl === 3) {

      if (kind === 'stock') { d.items.forEach(function (i) { if (i.st !== 'D') { i.st = 'Y'; i.qty = i.rev; } }); }
    }
  };
  P.resubmit = function (d) { d.app = {1:null, 2:null, 3:null}; d.rej = null; };
  /* tabel jejak approval (dipanggil lewat P.trailHtml agar kolom bisa diisi html) */
  P.trailHtml = function (d, kind) {
    var lv = kind === 'dept' ? {1:'Head Department', 2:'Cost Control', 3:'Management'} : P.LV;
    return M.stable([
      {label:'Approval By', html:function (r) { return esc(r.lv) + (r.a ? ' <small>(' + esc(r.a.by) + ')</small>' : ''); }},
      {label:'Status', html:function (r) { return r.a ? (r.a.st === 'Y' ? P.pill('Approved', 'ok') : P.pill('Rejected', 'off')) : P.pill('Waiting', 'blue'); }},
      {label:'Tgl Approve / Reject', html:function (r) { return r.a ? esc(r.a.date) : '-'; }},
      {label:'Alasan / Keterangan', html:function (r) { return r.a && r.a.reason ? esc(r.a.reason) : '-'; }}
    ], [1, 2, 3].map(function (l) { return {l:l, a:d.app[l], lv:lv[l]}; }));
  };

  /* PR Approved dari tiga sumber (Departemen, Asset, Stok) = sumber PO Non Material dan Metode Pembelian */
  P.pool = function () {
    var out = [];
    R.prDept.forEach(function (d) { if (d.app[3] && d.app[3].st === 'Y' && !d.closed && !d.rej) out.push({no_pr:d.no_pr, tgl:d.app[3].date.slice(0, 10), cat:'departemen', tipe:'Department', dept:P.dept(d.id_dept), by:d.by, created:d.app[3].date, req:d.tgl_dibutuhkan,
      items:d.items.map(function (i) { return {ref:i, iid:i.iid, nm:i.nm, spec:i.spec, satuan:i.satuan, qty:i.qty, harga:i.harga, ket:i.ket}; }), src:d, kind:'dept'}); });
    R.prAsset.forEach(function (d) { if (d.app[3] && d.app[3].st === 'Y' && !d.rej) out.push({no_pr:d.no_pr, tgl:d.app[3].date.slice(0, 10), cat:'asset', tipe:'Asset', dept:P.dept(d.items[0].id_dept), by:d.by, created:d.app[3].date, req:'',
      items:d.items.map(function (i) { return {ref:i, iid:i.iid, nm:i.nm, spec:i.spec, satuan:i.satuan, qty:i.qty, harga:i.harga, ket:i.ket}; }), src:d, kind:'asset'}); });
    R.prStock.forEach(function (d) { if (d.app[3] && d.app[3].st === 'Y' && !d.rej) out.push({no_pr:d.no_pr, tgl:d.app[3].date.slice(0, 10), cat:'stok', tipe:'Stok', dept:d.untuk, by:d.by, created:d.app[3].date, req:d.tgl_dibutuhkan,
      items:d.items.filter(function (i) { return i.st !== 'D'; }).map(function (i) { return {ref:i, iid:i.iid, nm:i.nm, spec:'', satuan:i.satuan, qty:i.rev, harga:i.harga, ket:''}; }), src:d, kind:'stock'}); });
    return out.sort(function (a, b) { return a.created < b.created ? 1 : -1; });
  };
  P.prProgress = function (e) { // Outstanding / Partial / Clear menurut qty yang sudah masuk PO
    var q = 0, u = 0; e.items.forEach(function (i) { q += i.qty; u += Math.min(i.qty, i.ref.qty_po || 0); });
    return u <= 0 ? {t:'Outstanding', c:'blue', code:'out'} : u >= q ? {t:'Clear', c:'ok', code:'clear'} : {t:'Partial', c:'warn', code:'part'};
  };
  P.posOfPr = function (no_pr) { return R.po.filter(function (p) { return p.items.some(function (i) { return i.no_pr === no_pr; }); }); };
  P.poCalc = function (p) {
    var sub = 0, disc = 0, ppn = 0; p.items.forEach(function (i) { var a = i.qty * i.harga, d = a * (i.disc || 0) / 100; sub += a; disc += d; ppn += (a - d) * (i.ppn || 0) / 100; });
    var kirim = +p.kirim || 0; return {sub:sub, disc:disc, ppn:ppn, kirim:kirim, total:sub - disc + ppn + kirim};
  };
  P.poState = function (p) {
    if (p.close_po) return {t:'Closed', c:'gray'};
    if (p.status === 1 && p.reject_reason) return {t:'Rejected', c:'off'};
    return p.status === 1 ? {t:'Waiting Approval', c:'blue'} : {t:'Approved', c:'ok'};
  };
  P.poRecv = function (p) { var q = 0, r = 0; p.items.forEach(function (i) { q += i.qty; r += Math.min(i.qty, i.qty_in || 0); }); return q ? Math.round(r / q * 100) : 0; };
  P.poByNo = function (no) { return R.po.filter(function (p) { return p.no_po === no; })[0] || null; };
  P.poIsAsset = function (p) { return p.items.length > 0 && p.items.every(function (i) { return i.tipe_pr === 'Asset'; }); };
  P.incValue = function (inc) { // nilai incoming (sebelum PPN) dan PPN dari harga PO
    var po = P.poByNo(inc.no_po), sub = 0, ppn = 0;
    if (po) inc.items.forEach(function (i) { var pi = po.items.filter(function (x) { return x.iid === i.iid; })[0]; if (pi) { var a = i.qty_in * pi.harga * (1 - (pi.disc || 0) / 100); sub += a; ppn += a * (pi.ppn || 0) / 100; } });
    return {sub:sub, ppn:ppn, total:sub + ppn};
  };
  P.incSupplier = function (inc) { var po = P.poByNo(inc.no_po); return po ? P.sup(po.id_suplier).nama : (inc.supplier || '-'); };
  P.reqState = function (r) {
    if (r.rej) return {t:'Rejected ' + (r.rej.lvl === 1 ? 'Checker' : 'Management'), c:'off'};
    if (r.pay_id) return {t:'Sudah Dibayar', c:'ok'};
    if (r.mgm) return {t:'Approved - Menunggu Pembayaran', c:'info'};
    if (r.chk) return {t:'Waiting Management', c:'blue'};
    return {t:'Waiting Checker', c:'warn'};
  };
  P.TIPE_REQ = {po:'Pembayaran PO (Invoice)', cash:'Cash (PR Non-PO)', expense:'Expense', kasbon:'Kasbon', transportasi:'Transportasi', periodik:'Periodik', direct_payment:'Direct Payment'};

  /* ---------- Hitungan badge (jumlah yang menunggu) ---------- */
  function cntW(arr, l) { return arr.filter(function (d) { return P.waitLvl(d) === l; }).length; }
  P.cnt = {
    non_rutin_approval_head: function () { return cntW(R.prDept, 1); }, non_rutin_approval_cost_control: function () { return cntW(R.prDept, 2); }, non_rutin_approval_management: function () { return cntW(R.prDept, 3); },
    pr_asset_approval_head: function () { return cntW(R.prAsset, 1); }, pr_asset_approval_cost_control: function () { return cntW(R.prAsset, 2); }, pr_asset_approval_management: function () { return cntW(R.prAsset, 3); },
    app_pr_stock_approval_head: function () { return cntW(R.prStock, 1); }, app_pr_stock_approval_cost_control: function () { return cntW(R.prStock, 2); }, app_pr_stock_approval_management: function () { return cntW(R.prStock, 3); },
    asset_planning_index_asset_approve: function () { return R.planAsset.filter(function (a) { return a.status === 'N'; }).length; },
    pr_asset_pr: function () { return R.planAsset.filter(function (a) { return a.status === 'Y' && !a.pr; }).length; },
    metode_pembelian_pr: function () { return P.pool().filter(function (e) { return P.prProgress(e).code !== 'clear'; }).length; },
    purchase_order_non_material: function () { return R.po.filter(function (p) { return !p.close_po && p.status === 1 && p.reject_reason; }).length; },
    approval_po_non_material: function () { return R.po.filter(function (p) { return !p.close_po && p.status === 1 && !p.reject_reason; }).length; },
    incoming_departemen: function () { return R.po.filter(function (p) { return p.status === 2 && !p.close_po && !P.poIsAsset(p) && P.poRecv(p) < 100; }).length; },
    incoming_asset_asset: function () { return R.po.filter(function (p) { return p.status === 2 && !p.close_po && P.poIsAsset(p) && P.poRecv(p) < 100; }).length; },
    request_payment_list_approve_checker: function () { return R.reqPay.filter(function (r) { return !r.rej && !r.chk; }).length; },
    request_payment_list_approve_management: function () { return R.reqPay.filter(function (r) { return !r.rej && r.chk && !r.mgm; }).length; },
    pembayaran_material_payment_list: function () { return R.reqPay.filter(function (r) { return r.mgm && !r.pay_id; }).length; }
  };
  /* Dari / Lanjut ke: sambungan antar halaman */
  var LK = {
    non_rutin_index: {prev:[], next:['non_rutin_approval_head', 'metode_pembelian_pr']},
    non_rutin_approval_head: {prev:['non_rutin_index'], next:['non_rutin_approval_cost_control']},
    non_rutin_approval_cost_control: {prev:['non_rutin_approval_head'], next:['non_rutin_approval_management']},
    non_rutin_approval_management: {prev:['non_rutin_approval_cost_control'], next:['metode_pembelian_pr', 'purchase_order_non_material']},
    asset_planning_index_asset: {prev:[], next:['asset_planning_index_asset_approve', 'pr_asset_pr']},
    asset_planning_index_asset_approve: {prev:['asset_planning_index_asset'], next:['pr_asset_pr']},
    pr_asset_pr: {prev:['asset_planning_index_asset_approve'], next:['pr_asset_approval_head']},
    pr_asset_approval_head: {prev:['pr_asset_pr'], next:['pr_asset_approval_cost_control']},
    pr_asset_approval_cost_control: {prev:['pr_asset_approval_head'], next:['pr_asset_approval_management']},
    pr_asset_approval_management: {prev:['pr_asset_approval_cost_control'], next:['metode_pembelian_pr', 'purchase_order_non_material']},
    app_pr_stock_approval_head: {prev:[], next:['app_pr_stock_approval_cost_control']},
    app_pr_stock_approval_cost_control: {prev:['app_pr_stock_approval_head'], next:['app_pr_stock_approval_management']},
    app_pr_stock_approval_management: {prev:['app_pr_stock_approval_cost_control'], next:['metode_pembelian_pr', 'purchase_order_non_material']},
    metode_pembelian_pr: {prev:['non_rutin_approval_management', 'pr_asset_approval_management', 'app_pr_stock_approval_management'], next:['purchase_order_non_material']},
    purchase_order_non_material: {prev:['metode_pembelian_pr'], next:['approval_po_non_material']},
    approval_po_non_material: {prev:['purchase_order_non_material'], next:['incoming_departemen', 'incoming_asset_asset', 'purchase_order_payment']},
    incoming_departemen: {prev:['approval_po_non_material'], next:['purchase_order_payment']},
    incoming_asset_asset: {prev:['approval_po_non_material'], next:['asset', 'purchase_order_payment']},
    purchase_order_payment: {prev:['approval_po_non_material', 'purchase_order', 'incoming_departemen', 'incoming_asset_asset'], next:['request_payment_list_approve_checker']},
    request_payment_list_approve_checker: {prev:['purchase_order_payment'], next:['request_payment_list_approve_management']},
    request_payment_list_approve_management: {prev:['request_payment_list_approve_checker'], next:['pembayaran_material_payment_list']},
    request_payment_index: {prev:[], next:['request_payment_list_approve_checker', 'request_payment_list_approve_management', 'pembayaran_material_payment_list']},
    pembayaran_material_payment_list: {prev:['request_payment_list_approve_management'], next:['request_payment_index']}
  };
  function chip(k) {
    var g = REG[k]; if (!g) return ''; var n = P.cnt[k] ? P.cnt[k]() : 0;
    return '<a class="tag chain" href="' + g.href + '">' + esc(g.label) + (g.mod ? ' <small>' + esc(g.mod) + '</small>' : '') + (n ? ' <span class="lbl warn">' + n + '</span>' : '') + '</a>';
  }
  P.flowHtml = function (key) {
    var g = REG[key], L = LK[key] || {prev:[], next:[]};
    var steps = '<ol class="steps">' + STAGES.map(function (s, i) { return '<li class="' + (i === g.stage ? 'on' : i < g.stage ? 'done' : '') + '">' + esc(s) + '</li>'; }).join('') + '</ol>';
    return '<div class="flowbar" id="flowbar">' + steps +
      (L.prev.length ? '<div><b>Dari:</b> ' + L.prev.map(chip).join('') + '</div>' : '') +
      (L.next.length ? '<div><b>Lanjut ke:</b> ' + L.next.map(chip).join('') + '</div>' : '') +
      '<div><a href="flow_pengadaan.html">Peta alur lengkap &raquo;</a></div></div>';
  };
  P.refreshFlow = function (key) { var f = document.getElementById('flowbar'); if (f) f.outerHTML = P.flowHtml(key); };

  /* ---------- Entry halaman ---------- */
  P.page = function (key) {
    var g = REG[key]; document.title = g.label; var root = M.shell({key:key, title:g.label, sub:g.sub, module:g.mod, crumb:g.grp});
    root.innerHTML = P.flowHtml(key) + '<div id="pg"></div>';
    P.cur = key; var host = document.getElementById('pg');
    P.build[key](host, key);
    M.onPerm(function () { P.refreshFlow(key); });
    return host;
  };
  P.after = function () { P.refreshFlow(P.cur); }; // dipanggil setelah aksi yang mengubah hitungan badge

  /* ---------- Util tampilan ---------- */
  P.bullets = function (arr) { return arr.map(function (x) { return '&bull; ' + esc(x); }).join('<br>'); };
  P.itemsTable = function (items, o) {
    o = o || {};
    return M.stable([{label:'#', html:function (r, i) { return i + 1; }}, {label:'Nama Barang/Jasa', html:function (r) { return esc(P.up(r.nm)); }}, {label:'Spec / Requirement', html:function (r) { return esc(r.spec || '-'); }},
      {label:'Qty', cls:'num', html:function (r) { return fmt(r.qty, 2).replace(/\.00$/, ''); }}, {label:'Satuan', html:function (r) { return esc(r.satuan || '-'); }},
      {label:'Est Harga', cls:'num', html:function (r) { return P.num(r.harga); }}, {label:'Est Total Harga', cls:'num', html:function (r) { return P.num(r.qty * r.harga); }}, {label:'Keterangan', html:function (r) { return esc(r.ket || '-'); }}], items,
      '<tfoot><tr><td colspan="6" style="text-align:right"><b>Total</b></td><td class="num"><b>' + P.num(items.reduce(function (a, r) { return a + r.qty * r.harga; }, 0)) + '</b></td><td></td></tr></tfoot>');
  };
  P.printPreview = function (title, html) {
    var b = M.modal('Print Preview - ' + title, '<div class="printdoc">' + html + '</div><div class="form-actions"><button type="button" class="btn btn-primary" id="prnow">🖨 Print</button></div>', {size:'xl'});
    b.querySelector('#prnow').onclick = function () { window.print(); };
  };
  P.reasonModal = function (title, label, okText, cb) { // modal alasan (Close PR / Close PO / Reject)
    var b = M.modal(title, '<form novalidate>' + M.fg(label + ' *', '<textarea name="r" rows="3" required></textarea>') + '<div class="form-actions"><button class="btn btn-danger" type="submit">' + okText + '</button></div></form>', {size:'sm'});
    var f = b.querySelector('form'); f.onsubmit = function (e) { e.preventDefault(); if (!M.validate(f)) return; cb(f.r.value.trim()); };
  };
  P.fileName = function (inp) { return inp && inp.files && inp.files[0] ? inp.files[0].name : ''; };
  P.q = function (v) { return Number(v || 0).toLocaleString('en-US', {maximumFractionDigits:2}); };
  P.tbl = function (host, rows, cols, on) { return M.table(host, {rows:rows, cols:cols, onAction:on}); };
  P.addBtn = function (wrapId, label, fn) { // tombol Add mengikuti simulasi hak akses
    function draw() { var w = document.getElementById(wrapId); if (!w) return; w.innerHTML = M.perm.Add ? '<button type="button" class="btn btn-success btn-sm" id="' + wrapId + 'b">' + label + '</button>' : ''; if (M.perm.Add) document.getElementById(wrapId + 'b').onclick = fn; }
    draw(); M.onPerm(draw);
  };
  P.warn = function (text) { M.swal({title:'Perhatian', text:text, type:'warning'}); };
  P.dl = function (pairs) { return '<dl class="dl">' + pairs.map(function (p) { return '<dt>' + esc(p[0]) + '</dt><dd>' + (p[2] ? p[1] : esc(p[1] == null || p[1] === '' ? '-' : p[1])) + '</dd>'; }).join('') + '</dl>'; };
  P.alert = function (kind, text) { return '<div class="' + kind + '">' + text + '</div>'; };

  /* Editor baris (tambah / hapus / ubah) untuk form: defs = [{k,label,type:text|number|select,opts,w}], o.total = tampilkan qty x harga */
  P.rowsEditor = function (el, items, defs, mk, o) {
    o = o || {};
    function tot() { var g = el.querySelector('#gt'); if (g) g.textContent = P.num(items.reduce(function (a, r) { return a + (+r.qty || 0) * (+r.harga || 0); }, 0)); }
    function draw() {
      var h = '<table class="mini"><thead><tr><th width="30">#</th>' + defs.map(function (d) { return '<th' + (d.w ? ' style="width:' + d.w + '"' : '') + '>' + esc(d.label) + '</th>'; }).join('') + (o.total ? '<th>Est Total Harga</th>' : '') + '<th width="40"></th></tr></thead><tbody>';
      items.forEach(function (it, i) {
        h += '<tr><td>' + (i + 1) + '</td>' + defs.map(function (d) {
          var a = ' data-i="' + i + '" data-k="' + d.k + '"', v = it[d.k] == null ? '' : it[d.k];
          if (d.type === 'select') return '<td><select' + a + '>' + d.opts.map(function (x) { return '<option' + (x === v ? ' selected' : '') + '>' + esc(x) + '</option>'; }).join('') + '</select></td>';
          return '<td><input type="' + (d.type === 'number' ? 'number' : 'text') + '"' + a + ' value="' + esc(v) + '"' + (d.type === 'number' ? ' min="0" step="any"' : '') + '></td>'; }).join('') +
          (o.total ? '<td class="num" id="tt' + i + '">' + P.num((+it.qty || 0) * (+it.harga || 0)) + '</td>' : '') + '<td><button type="button" class="btn btn-sm btn-danger" data-rm="' + i + '" title="Hapus baris">🗑</button></td></tr>';
      });
      h += '</tbody></table>' + (o.total ? '<div style="text-align:right;margin:6px 0"><b>Total Estimasi: <span id="gt"></span></b></div>' : '') + '<button type="button" class="btn btn-sm btn-success" data-add="1">＋ Tambah Baris</button>';
      el.innerHTML = h; tot();
    }
    el.oninput = el.onchange = function (e) {
      var t = e.target; if (t.dataset.k == null) return; var it = items[+t.dataset.i]; it[t.dataset.k] = t.type === 'number' ? (parseFloat(t.value) || 0) : t.value;
      if (o.total) { var c = el.querySelector('#tt' + t.dataset.i); if (c) c.textContent = P.num((+it.qty || 0) * (+it.harga || 0)); tot(); }
    };
    el.onclick = function (e) {
      var b = e.target.closest('button'); if (!b) return;
      if (b.dataset.add) { items.push(mk()); draw(); } else if (b.dataset.rm != null) { if (items.length > 1) { items.splice(+b.dataset.rm, 1); draw(); } else P.warn('Minimal satu baris.'); }
    };
    draw();
  };

  /* Bar keputusan approval: riwayat 3 level + alasan + tombol Approve / Reject / Back.
     o = {d, kind, lvl, collect(), back(), act(true|false)}; act=false untuk mode lihat saja */
  P.decisionBar = function (el, o) {
    var lvName = o.kind === 'dept' ? {1:'Head Department', 2:'Cost Control', 3:'Management'} : P.LV, canAct = o.act !== false && P.waitLvl(o.d) === o.lvl;
    el.innerHTML = P.trailHtml(o.d, o.kind) +
      (canAct ? '<form novalidate>' + M.fg('Alasan / Keterangan', '<textarea name="reason" rows="2" placeholder="Wajib diisi bila Reject"></textarea>') +
        '<div class="form-actions"><button type="button" class="btn btn-default" id="dback">« Back</button> <button type="button" class="btn btn-danger" id="drej">✕ Reject</button> <button type="button" class="btn btn-success" id="dok">✓ Approve</button></div></form>'
        : '<div class="form-actions"><button type="button" class="btn btn-default" id="dback">« Back</button></div>' +
          (o.act !== false ? P.alert('note', 'Dokumen ini tidak sedang menunggu approval ' + esc(lvName[o.lvl]) + '.') : ''));
    el.querySelector('#dback').onclick = o.back;
    if (!canAct) return;
    var f = el.querySelector('form');
    function done() { if (P.after) P.after(); o.back(); }
    el.querySelector('#dok').onclick = function () {
      if (o.check && !o.check()) return;
      M.saveFlow({text:'Dokumen akan disetujui sebagai ' + lvName[o.lvl] + '.', okText:'Ya, Approve!', done:'Dokumen berhasil di-approve.', save:function () { if (o.collect) o.collect(); P.decide(o.d, o.lvl, true, f.reason.value.trim(), o.kind); }, after:done});
    };
    el.querySelector('#drej').onclick = function () {
      var r = f.reason.value.trim(); f.reason.classList.toggle('err', !r);
      if (!r) { P.warn('Alasan wajib diisi untuk Reject.'); return; }
      M.saveFlow({text:'Dokumen akan ditolak dan kembali ke pembuat untuk direvisi.', okText:'Ya, Reject!', done:'Dokumen berhasil di-reject.', save:function () { P.decide(o.d, o.lvl, false, r, o.kind); }, after:done});
    };
  };
  /* Tabel item yang qty dan harganya boleh direvisi approver; mengembalikan fungsi collect() */
  P.reviseTable = function (el, items) {
    var rev = items.map(function (i) { return {qty:i.qty, harga:i.harga}; });
    function tot() { el.querySelector('#rt').textContent = P.num(rev.reduce(function (a, r) { return a + r.qty * r.harga; }, 0)); }
    el.innerHTML = '<table class="mini"><thead><tr><th>#</th><th>Nama Barang/Jasa</th><th>Spec / Requirement</th><th width="110">Qty</th><th>Satuan</th><th width="140">Est Harga</th><th>Est Total Harga</th><th>Keterangan</th></tr></thead><tbody>' +
      items.map(function (i, n) { return '<tr><td>' + (n + 1) + '</td><td>' + esc(P.up(i.nm)) + '</td><td>' + esc(i.spec || '-') + '</td><td><input type="number" min="0" step="any" data-n="' + n + '" data-f="qty" value="' + i.qty + '"></td><td>' + esc(i.satuan) + '</td><td><input type="number" min="0" step="any" data-n="' + n + '" data-f="harga" value="' + i.harga + '"></td><td class="num" id="rr' + n + '">' + P.num(i.qty * i.harga) + '</td><td>' + esc(i.ket || '-') + '</td></tr>'; }).join('') +
      '</tbody></table><div style="text-align:right;margin:6px 0"><b>Total: <span id="rt"></span></b></div>';
    el.oninput = function (e) { var t = e.target; if (t.dataset.n == null) return; var r = rev[+t.dataset.n]; r[t.dataset.f] = parseFloat(t.value) || 0; el.querySelector('#rr' + t.dataset.n).textContent = P.num(r.qty * r.harga); tot(); };
    tot();
    return function () { items.forEach(function (i, n) { i.qty = rev[n].qty; i.harga = rev[n].harga; }); };
  };
  /* Panduan (tombol Panduan di topbar) untuk halaman alur pengadaan; dipanggil guide.js bila halaman tidak punya tour sendiri */
  P.guide = function (file) {
    var key = file.replace(/\.html$/, ''), g = REG[key]; if (!g) return null;
    var st = function (el, title, text, side) { return {el:el, title:title, text:text, side:side}; };
    var isApp = /approval|approve|list_approve/.test(key);
    return [
      st(null, g.label, g.sub + '. Modul asli: ' + (g.mod || '-') + '. Tahap alur: ' + STAGES[g.stage] + '.'),
      st('#flowbar', 'Alur antar modul', 'Baris langkah menunjukkan posisi halaman ini dalam alur PR sampai Pembayaran. "Dari" dan "Lanjut ke" adalah tautan ke halaman asal dan tujuan; angka kuning = dokumen yang menunggu tindakan di halaman itu.', 'bottom'),
      st('#addw button, #pg .btn-success.btn-sm', 'Tombol tambah', 'Membuat dokumen baru. Tombol ini hilang bila Add dimatikan di menu Simulasi hak akses.', 'bottom'),
      st('.tabs', 'Tab', 'Pindah antar jenis dokumen. Angka di tab = jumlah dokumen yang menunggu.', 'bottom'),
      st('.qq', 'Cari dan urutkan', 'Kolom Search menyaring seluruh baris; klik judul kolom untuk mengurutkan.', 'bottom'),
      st('.tbl', 'Daftar dokumen', isApp ? 'Dokumen ' + 'yang menunggu persetujuan. Pilih Approve untuk membuka detail, ubah angka bila perlu, lalu Approve atau Reject (Reject wajib beralasan).' : 'Status tiap dokumen berwarna: biru menunggu, hijau selesai, merah ditolak. Kolom Option / Action berisi View, Edit, Print, dan aksi lanjutan.', 'top'),
      st('.note', 'Catatan alur', 'Menjelaskan dari mana dokumen berasal dan ke halaman mana ia berlanjut setelah diproses. Hasil simpan tersimpan di browser (localStorage); menu "Reset data mockup" mengembalikan data awal.', 'top')
    ];
  };
})(window.Mock);
