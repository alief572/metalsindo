/* Builder halaman PR: PR Departemen (non_rutin), Planning Asset (asset_planning), PR Asset (pr_asset), PR Stock (app_pr_stock). */
(function (M) {
  'use strict';
  var P = M.proc, R = P.R, esc = M.esc, fmt = M.fmt;
  var STAT = ['Semua Status', 'Waiting', 'Approved', 'Rejected', 'Closed'];
  var stFilter = function (sel, kind) { return function (d) { if (!sel || sel === STAT[0]) return true; var t = P.docState(d, kind).t; return t.indexOf(sel) === 0; }; };
  var pill = function (d, kind) { var s = P.docState(d, kind); return P.pill(s.t, s.c); };
  var statusSel = function () { return '<span class="pull-right"><label>Status: <select id="fs" style="padding:5px">' + STAT.map(function (s) { return '<option>' + s + '</option>'; }).join('') + '</select></label></span>'; };

  /* =====================================================  PR DEPARTEMEN (non_rutin) ===================================================== */
  function deptCols(btns) {
    return [
      {label:'No PR', html:function (r) { return esc(r.no_pr); }, text:function (r) { return r.no_pr; }},
      {label:'Departemen', html:function (r) { return esc(P.up(P.dept(r.id_dept))); }, text:function (r) { return P.dept(r.id_dept); }},
      {label:'Nama Barang/Jasa', html:function (r) { return P.bullets(r.items.map(function (i) { return P.up(i.nm); })); }, text:function (r) { return r.items.map(function (i) { return i.nm; }).join(' '); }},
      {label:'Spec / Requirement', html:function (r) { return P.bullets(r.items.map(function (i) { return P.up(i.spec); })); }, text:function (r) { return r.items.map(function (i) { return i.spec; }).join(' '); }},
      {label:'Qty', html:function (r) { return P.bullets(r.items.map(function (i) { return P.q(i.qty) + ' ' + i.satuan.toLowerCase(); })); }, text:function (r) { return r.items.length; }},
      {label:'Dibutuhkan', html:function (r) { return M.dstr(r.tgl_dibutuhkan); }, text:function (r) { return r.tgl_dibutuhkan; }},
      {label:'Keterangan', html:function (r) { return P.bullets(r.items.map(function (i) { return P.up(i.ket || '-'); })); }, text:function (r) { return r.items.map(function (i) { return i.ket; }).join(' '); }},
      {label:'Status', html:function (r) { return pill(r, 'dept'); }, text:function (r) { return P.docState(r, 'dept').t; }},
      {label:'Option', html:btns, text:function () { return ''; }, width:'150px'}
    ];
  }
  function deptDl(d) {
    var co = R.coaNR.filter(function (c) { return c.coa === d.coa; })[0];
    return P.dl([['No PR', d.no_pr], ['Departemen', P.dept(d.id_dept)], ['COA', co ? co.coa + ' - ' + co.nama : d.coa], ['Project Name', d.project], ['Tgl Dibutuhkan', M.dstr(d.tgl_dibutuhkan), 1], ['Tingkat PR', d.tingkat_pr], ['Dibuat Oleh', d.by + ' (' + M.dstr(d.tgl) + ')'], ['Reason', d.reason], ['Dokumen', d.doc || 'Tidak ada']]);
  }
  function viewDept(d) {
    var b = M.modal('Detail PR Departemen ' + d.no_pr, deptDl(d) + '<div class="sect">Detail Barang/Jasa</div>' + P.itemsTable(d.items) + '<div class="sect">Riwayat Approval</div>' + P.trailHtml(d, 'dept') +
      (d.closed ? P.alert('warnbox', 'PR di-close: ' + esc(d.closed.reason)) : ''), {size:'xl'});
    return b;
  }
  function printDept(d) {
    P.printPreview(d.no_pr, '<h3 style="text-align:center">PENGAJUAN PR NON RUTIN</h3>' + deptDl(d) + P.itemsTable(d.items) + P.trailHtml(d, 'dept'));
  }

  P.build.non_rutin_index = function (host) {
    var editable = function (d) { return !d.closed && (d.rej || (!d.app[1] && !d.app[2] && !d.app[3])); };
    function list() {
      host.innerHTML = '<div class="box primary"><div class="box-header"><span id="addw"></span>' + statusSel() + '</div><div class="box-body" id="tbl"></div></div>' +
        '<div class="note">Alur: PR dibuat Departemen, disetujui berurutan <b>Head Department → Cost Control → Management</b>. Nomor PR (PRN…) terbit saat PR dibuat dan <b>tidak berubah</b> sampai selesai; setelah approval Management PR muncul di <a href="metode_pembelian_pr.html">Metode Pembelian - PR</a> dan siap dibuat PO. PR yang ditolak dapat diedit dan diajukan ulang dari awal. <b>Close PR</b> hanya untuk PR Approved yang tidak jadi dibeli.</div>';
      var fs = M.$('#fs');
      var t = P.tbl(M.$('#tbl'), function () { return R.prDept.filter(stFilter(fs.value, 'dept')).slice().sort(function (a, b) { return a.no_pengajuan < b.no_pengajuan ? 1 : -1; }); }, deptCols(function (r) {
        var s = P.docState(r, 'dept').t;
        return P.btn('btn-warning', 'view', r.no_pengajuan, '👁', 'View') + (editable(r) ? P.btn('btn-primary', 'edit', r.no_pengajuan, '✎', 'Edit') : '') + P.btn('btn-success', 'print', r.no_pengajuan, '🖨', 'Print') + (s === 'Approved' ? P.btn('btn-danger', 'close', r.no_pengajuan, '✕', 'Close PR') : '');
      }), function (a, id) {
        var d = R.prDept.filter(function (x) { return x.no_pengajuan === id; })[0];
        if (a === 'view') viewDept(d); else if (a === 'print') printDept(d); else if (a === 'edit') form(d);
        else if (a === 'close') P.reasonModal('Closing PR ' + d.no_pr, 'Alasan Close PR', 'Close PR', function (r) { M.saveFlow({text:'PR ' + d.no_pr + ' akan di-close dan tidak dapat dibuat PO lagi.', okText:'Ya, Close!', done:'PR berhasil di-close.', save:function () { d.closed = {reason:r, date:P.NOW, by:R.me}; }, after:function () { P.after(); t.refresh(); }}); });
      });
      fs.onchange = function () { t.refresh(); };
      P.addBtn('addw', '＋ Add', function () { form(null); });
    }
    function form(d) {
      var edit = !!d, o = d ? JSON.parse(JSON.stringify(d)) : {id_dept:'', coa:'', project:'', reason:'', tgl_dibutuhkan:'', tingkat_pr:'Normal', doc:'', items:[mk()]};
      function mk() { return {nm:'', spec:'', satuan:'PCS', qty:1, harga:0, ket:''}; }
      host.innerHTML = '<div class="box primary"><div class="box-header"><h3 class="box-title">' + (edit ? 'Edit' : 'Tambah') + ' PR Departemen' + (edit ? ' ' + esc(d.no_pengajuan) : '') + '</h3></div><div class="box-body"><form novalidate id="f"><div class="grid2">' +
        M.fg('Department *', '<select name="id_dept" required>' + M.opts(R.dept.map(function (x) { return {v:String(x.id), t:x.nama.toUpperCase()}; }), String(o.id_dept), 'v', 't', 'Pilih Department') + '</select>') +
        M.fg('COA *', '<select name="coa" required>' + M.opts(R.coaNR.map(function (c) { return {v:c.coa, t:c.coa + ' - ' + c.nama}; }), o.coa, 'v', 't', 'Pilih COA') + '</select>') +
        M.fg('Project Name', '<input type="text" name="project" value="' + esc(o.project) + '">') +
        M.fg('Tgl Dibutuhkan', '<input type="text" name="tgl" placeholder="YYYY-MM-DD" value="' + esc(o.tgl_dibutuhkan) + '">') +
        M.fg('Reason *', '<textarea name="reason" rows="2" required>' + esc(o.reason) + '</textarea>') +
        M.fg('Tingkat PR', '<select name="tingkat">' + M.opts(['Normal', 'Urgent'], o.tingkat_pr) + '</select>') +
        M.fg('Upload Document', '<input type="file" name="doc">', o.doc ? 'File saat ini: ' + esc(o.doc) : '') + '</div>' +
        (edit && d.rej ? P.alert('warnbox', 'PR ditolak ' + esc(P.LV[d.rej.lvl === 1 ? 1 : d.rej.lvl]) + ' (' + esc(d.rej.by) + '): ' + esc(d.rej.reason) + '. Setelah disimpan, approval diulang dari Head Department.') : '') +
        '<div class="sect">Detail Barang / Jasa</div><div id="rows"></div><div class="form-actions"><button type="button" class="btn btn-default" id="back">« Back</button> <button class="btn btn-primary" type="submit">💾 Save</button></div></form></div></div>';
      P.rowsEditor(M.$('#rows'), o.items, [{k:'nm', label:'Nama Barang/Jasa'}, {k:'spec', label:'Spec / Requirement'}, {k:'qty', label:'Qty', type:'number', w:'90px'}, {k:'satuan', label:'Satuan', type:'select', opts:R.satuan, w:'90px'}, {k:'harga', label:'Est Harga', type:'number', w:'130px'}, {k:'ket', label:'Keterangan'}], mk, {total:true});
      M.$('#back').onclick = list;
      var f = M.$('#f');
      f.onsubmit = function (e) {
        e.preventDefault(); if (!M.validate(f)) return;
        if (o.items.some(function (i) { return !String(i.nm).trim() || !(+i.qty > 0); })) { P.warn('Nama barang wajib diisi dan Qty harus lebih dari 0.'); return; }
        M.saveFlow({text:'Data PR Departemen akan disimpan.', done:'Data PR berhasil disimpan.', save:function () {
          var nn = d ? d.no_pr : P.nextNo('PRN' + P.YM, R.prDept, 'no_pr', 4); var x = d || {no_pengajuan:nn, no_pr:nn, tgl:P.TODAY, by:R.me, closed:null, rej:null, app:{1:null, 2:null, 3:null}};
          x.id_dept = +f.id_dept.value; x.coa = f.coa.value; x.project = f.project.value; x.reason = f.reason.value; x.tgl_dibutuhkan = f.tgl.value; x.tingkat_pr = f.tingkat.value; x.doc = P.fileName(f.doc) || o.doc;
          x.items = o.items.map(function (i, n) { return {iid:i.iid || ('N' + Date.now() + n), nm:i.nm, spec:i.spec, satuan:i.satuan, qty:+i.qty, harga:+i.harga, qty_po:i.qty_po || 0, ket:i.ket}; });
          if (d && d.rej) P.resubmit(d); if (!d) R.prDept.push(x);
        }, after:function () { P.after(); list(); }});
      };
    }
    list();
  };

  /* Approval PR Departemen (3 level) */
  function deptApproval(host, lvl) {
    function list() {
      host.innerHTML = '<div class="box primary"><div class="box-header"><h3 class="box-title">Approval PR Departemen - ' + P.LV[lvl] + '</h3><span class="pull-right"><label>Tampilkan: <select id="fm" style="padding:5px"><option value="1">Menunggu saya</option><option value="0">Semua</option></select></label></span></div><div class="box-body" id="tbl"></div></div>' +
        '<div class="note">Approver ' + P.LV[lvl] + ' (<b>' + esc(R.approver[lvl]) + '</b>) dapat merevisi Qty dan Est Harga sebelum menyetujui. Reject mengembalikan PR ke departemen pembuat. ' + (lvl === 3 ? 'Setelah Approve Management, PR (nomor tetap) masuk ke <a href="metode_pembelian_pr.html">Metode Pembelian - PR</a>.' : 'Setelah approve, PR menunggu level berikutnya.') + '</div>';
      var fm = M.$('#fm');
      var t = P.tbl(M.$('#tbl'), function () { return R.prDept.filter(function (d) { return fm.value === '0' || P.waitLvl(d) === lvl; }).slice().sort(function (a, b) { return a.no_pengajuan < b.no_pengajuan ? 1 : -1; }); }, deptCols(function (r) {
        return P.btn('btn-warning', 'view', r.no_pengajuan, '👁', 'View') + (P.waitLvl(r) === lvl ? P.btn('btn-info', 'app', r.no_pengajuan, '✓ Approve', 'Approve') : '');
      }), function (a, id) { var d = R.prDept.filter(function (x) { return x.no_pengajuan === id; })[0]; if (a === 'view') viewDept(d); else detail(d); });
      fm.onchange = function () { t.refresh(); };
    }
    function detail(d) {
      host.innerHTML = '<div class="box primary"><div class="box-header"><h3 class="box-title">Approval PR Departemen ' + esc(d.no_pengajuan) + ' - ' + P.LV[lvl] + '</h3></div><div class="box-body">' + deptDl(d) + '<div class="sect">Detail Barang / Jasa (Qty dan Est Harga dapat direvisi)</div><div id="its"></div><div class="sect">Riwayat Approval</div><div id="dec"></div></div></div>';
      var collect = P.reviseTable(M.$('#its'), d.items);
      P.decisionBar(M.$('#dec'), {d:d, kind:'dept', lvl:lvl, collect:collect, back:list});
    }
    list();
  }
  P.build.non_rutin_approval_head = function (h) { deptApproval(h, 1); };
  P.build.non_rutin_approval_cost_control = function (h) { deptApproval(h, 2); };
  P.build.non_rutin_approval_management = function (h) { deptApproval(h, 3); };

  /* =====================================================  PLANNING ASSET (asset_planning/index_asset) ===================================================== */
  var BLN = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
  function planState(a) { return a.status === 'N' ? {t:'WAITING APPROVAL', c:'blue'} : a.status === 'Y' ? {t:'APPROVED', c:'ok'} : {t:'REJECTED', c:'off'}; }
  function planDl(a) {
    var c = R.coaAsset.filter(function (x) { return x.coa === a.coa; })[0];
    return P.dl([['Department', P.dept(a.id_dept)], ['Cost Center', P.cc(a.cc)], ['Nama Asset', P.up(a.nama_asset)], ['Planning', BLN[+a.bulan - 1] + ' ' + a.tahun], ['COA', c ? a.coa + ' - ' + c.nama : a.coa], ['COA Akumulasi', a.coa_akum], ['Qty', a.qty], ['Budget', P.idr(a.budget)], ['Sisa Budget PR', P.idr(a.budget_pr)], ['Sisa Budget PO', P.idr(a.budget_po)], ['Keterangan', a.ket], ['Dibuat', a.by + ' (' + a.date + ')'], ['Status', planState(a).t], ['No PR', a.pr || 'Belum dibuat PR'], ['Alasan Reject', a.reason]]);
  }
  function buildPlan(host, approve) {
    function list() {
      host.innerHTML = '<div class="box primary"><div class="box-header">' + (approve ? '<h3 class="box-title">Approval Planning Asset</h3>' : '<span id="addw"></span>') + '</div><div class="box-body" id="tbl"></div></div>' +
        '<div class="note">' + (approve ? 'Management menyetujui budget planning asset. Planning <b>Approved</b> yang belum punya PR muncul di <a href="pr_asset_pr.html">PR Asset</a> untuk dibuat PR.' : 'Budget planning asset per departemen. Edit dan Delete hanya untuk planning berstatus Waiting. Persetujuan dilakukan di <a href="asset_planning_index_asset_approve.html">Approval Planning Asset</a>.') + '</div>';
      var t = P.tbl(M.$('#tbl'), function () { return R.planAsset.slice().sort(function (a, b) { return a.code_plan < b.code_plan ? 1 : -1; }); }, [
        {label:'Department', html:function (r) { return esc(P.up(P.dept(r.id_dept))); }, text:function (r) { return P.dept(r.id_dept); }}, {key:'nama_asset', label:'Nama Asset', html:function (r) { return esc(P.up(r.nama_asset)); }}, {label:'Keterangan', html:function (r) { return esc(P.up(r.ket)); }, text:function (r) { return r.ket; }},
        {label:'Qty', html:function (r) { return r.qty; }, text:function (r) { return r.qty; }}, {label:'Budget', html:function (r) { return '<span class="num" style="display:block">' + P.num(r.budget) + '</span>'; }, text:function (r) { return r.budget; }},
        {label:'Sisa Budget PR', html:function (r) { return '<span class="num" style="display:block">' + P.num(r.budget_pr) + '</span>'; }, text:function (r) { return r.budget_pr; }}, {label:'Sisa Budget PO', html:function (r) { return '<span class="num" style="display:block">' + P.num(r.budget_po) + '</span>'; }, text:function (r) { return r.budget_po; }},
        {label:'Planning', html:function (r) { return BLN[+r.bulan - 1] + ' ' + r.tahun; }, text:function (r) { return r.tahun + r.bulan; }}, {label:'Status', html:function (r) { var s = planState(r); return P.pill(s.t, s.c); }, text:function (r) { return planState(r).t; }},
        {label:'Dibuat Oleh', key:'by'}, {label:'Dibuat Tgl', html:function (r) { return M.dstr(r.date); }, text:function (r) { return r.date; }},
        {label:'Option', html:function (r) { return r.status !== 'N' ? P.btn('btn-warning', 'view', r.code_plan, '👁', 'Detail') : approve ? P.btn('btn-info', 'app', r.code_plan, '✓ Approve', 'Approve') : P.btn('btn-primary', 'edit', r.code_plan, '✎', 'Edit') + P.btn('btn-danger', 'del', r.code_plan, '🗑', 'Delete'); }, text:function () { return ''; }, width:'130px'}
      ], function (a, id) {
        var x = R.planAsset.filter(function (p) { return p.code_plan === id; })[0];
        if (a === 'view') M.modal('Detail Planning Asset ' + x.code_plan, planDl(x), {size:'sm'});
        else if (a === 'edit') form(x); else if (a === 'app') decide(x);
        else if (a === 'del') M.delFlow('Planning asset akan dihapus.', 'Planning asset berhasil dihapus.', function () { R.planAsset.splice(R.planAsset.indexOf(x), 1); t.refresh(); });
      });
      if (!approve) P.addBtn('addw', '＋ Add Planning Asset', function () { form(null); });
    }
    function decide(x) {
      host.innerHTML = '<div class="box primary"><div class="box-header"><h3 class="box-title">Approval Planning ' + esc(x.code_plan) + '</h3></div><div class="box-body">' + planDl(x) + '<form novalidate>' + M.fg('Alasan', '<textarea name="reason" rows="2" placeholder="Wajib diisi bila Reject"></textarea>') +
        '<div class="form-actions"><button type="button" class="btn btn-default" id="b">« Back</button> <button type="button" class="btn btn-danger" id="r">✕ Reject</button> <button type="button" class="btn btn-success" id="a">✓ Approve</button></div></form></div></div>';
      var f = host.querySelector('form'); M.$('#b').onclick = list;
      M.$('#a').onclick = function () { M.saveFlow({text:'Planning asset akan disetujui.', okText:'Ya, Approve!', done:'Planning asset berhasil di-approve.', save:function () { x.status = 'Y'; x.reason = ''; }, after:function () { P.after(); list(); }}); };
      M.$('#r').onclick = function () { var r = f.reason.value.trim(); if (!r) { P.warn('Alasan wajib diisi untuk Reject.'); return; } M.saveFlow({text:'Planning asset akan ditolak.', okText:'Ya, Reject!', done:'Planning asset berhasil di-reject.', save:function () { x.status = 'D'; x.reason = r; }, after:function () { P.after(); list(); }}); };
    }
    function form(x) {
      var edit = !!x, o = x || {id_dept:'', cc:'', nama_asset:'', ket:'', qty:1, budget:'', bulan:'11', tahun:'2026', coa:''};
      host.innerHTML = '<div class="box primary"><div class="box-header"><h3 class="box-title">' + (edit ? 'Edit' : 'Tambah') + ' Planning Asset</h3></div><div class="box-body"><form novalidate><div class="grid2">' +
        M.fg('Department *', '<select name="dept" required>' + M.opts(R.dept.map(function (d) { return {v:String(d.id), t:d.nama.toUpperCase()}; }), String(o.id_dept), 'v', 't', 'Pilih Department') + '</select>') + M.fg('Cost Center', '<select name="cc"></select>') +
        M.fg('Nama Asset *', '<input type="text" name="nama" required value="' + esc(o.nama_asset) + '">') + M.fg('COA *', '<select name="coa" required>' + M.opts(R.coaAsset.map(function (c) { return {v:c.coa, t:c.coa + ' - ' + c.nama}; }), o.coa, 'v', 't', 'Pilih COA') + '</select>') +
        M.fg('Bulan *', '<select name="bulan" required>' + BLN.map(function (b, i) { var v = P.pad(i + 1, 2); return '<option value="' + v + '"' + (v === o.bulan ? ' selected' : '') + '>' + b + '</option>'; }).join('') + '</select>') + M.fg('Tahun *', '<input type="number" name="tahun" required value="' + esc(o.tahun) + '">') +
        M.fg('Budget (Rp) *', '<input type="number" name="budget" min="1" required value="' + esc(o.budget) + '">') + M.fg('Qty *', '<input type="number" name="qty" min="1" required value="' + esc(o.qty) + '">') +
        M.fg('Keterangan', '<textarea name="ket" rows="2">' + esc(o.ket) + '</textarea>') + M.fg('COA Akumulasi', '<input type="text" name="akum" readonly>', 'Otomatis dari COA') +
        '</div><div class="form-actions"><button type="button" class="btn btn-default" id="b">« Back</button> <button class="btn btn-primary" type="submit">💾 Save</button></div></form></div></div>';
      var f = host.querySelector('form'); M.$('#b').onclick = list;
      function ccs() { var l = R.cc.filter(function (c) { return String(c.dept) === f.dept.value; }); f.cc.innerHTML = '<option value="">' + (l.length ? '-- Pilih --' : 'List Empty') + '</option>' + l.map(function (c) { return '<option value="' + c.id + '"' + (c.id === o.cc ? ' selected' : '') + '>' + esc(c.nama) + '</option>'; }).join(''); }
      function akum() { var c = R.coaAsset.filter(function (z) { return z.coa === f.coa.value; })[0]; f.akum.value = c ? c.akum : ''; }
      f.dept.onchange = ccs; f.coa.onchange = akum; ccs(); akum();
      f.onsubmit = function (e) { e.preventDefault(); if (!M.validate(f)) return;
        M.saveFlow({text:'Data planning asset akan disimpan.', done:'Data planning berhasil disimpan.', save:function () {
          var z = x || {code_plan:P.nextNo('AP' + P.YM, R.planAsset, 'code_plan', 3), status:'N', reason:'', by:R.me, date:P.NOW, pr:''};
          z.id_dept = +f.dept.value; z.cc = f.cc.value; z.nama_asset = f.nama.value; z.coa = f.coa.value; z.coa_akum = f.akum.value; z.bulan = f.bulan.value; z.tahun = f.tahun.value; z.qty = +f.qty.value; z.budget = +f.budget.value; z.ket = f.ket.value;
          if (!x) { z.budget_pr = z.budget; z.budget_po = z.budget; R.planAsset.push(z); }
        }, after:function () { P.after(); list(); }}); };
    }
    list();
  }
  P.build.asset_planning_index_asset = function (h) { buildPlan(h, false); };
  P.build.asset_planning_index_asset_approve = function (h) { buildPlan(h, true); };

  /* =====================================================  PR ASSET (pr_asset) ===================================================== */
  var prTotal = function (d) { return d.items.reduce(function (a, i) { return a + i.qty * i.harga; }, 0); };
  function assetCols(btns) {
    return [{label:'No PR', html:function (r) { return esc(r.no_pr); }, text:function (r) { return r.no_pr; }}, {label:'Tanggal PR', html:function (r) { return M.dstr(r.tgl); }, text:function (r) { return r.tgl; }},
      {label:'Nama Barang', html:function (r) { return P.bullets(r.items.map(function (i) { return P.up(i.nm) + ' (' + P.q(i.qty) + ' ' + i.satuan + ')'; })); }, text:function (r) { return r.items.map(function (i) { return i.nm; }).join(' '); }},
      {label:'Est Total', html:function (r) { return '<span class="num" style="display:block">' + P.num(prTotal(r)) + '</span>'; }, text:function (r) { return prTotal(r); }},
      {label:'PR By', key:'by'}, {label:'PR Date', html:function (r) { return M.dstr(r.tgl); }, text:function (r) { return r.tgl; }},
      {label:'Status', html:function (r) { return pill(r, 'asset'); }, text:function (r) { return P.docState(r, 'asset').t; }}, {label:'Option', html:btns, text:function () { return ''; }, width:'150px'}];
  }
  function assetDl(d) {
    return P.dl([['No PR', d.no_pr], ['Tanggal PR', M.dstr(d.tgl), 1], ['PR By', d.by], ['Status', P.docState(d, 'asset').t], ['Est Total', P.idr(prTotal(d))]]);
  }
  function assetItems(items, edit) {
    return M.stable([{label:'#', html:function (r, i) { return i + 1; }}, {label:'Nama Barang', html:function (r) { return esc(P.up(r.nm)); }}, {label:'No Planning', key:'plan'}, {label:'Department', html:function (r) { return esc(P.dept(r.id_dept)); }}, {label:'Costcenter', html:function (r) { return esc(P.cc(r.cc)); }},
      {label:'Spesifikasi', html:function (r) { return esc(r.spec || '-'); }}, {label:'Qty', cls:'num', html:function (r) { return P.q(r.qty) + ' ' + esc(r.satuan); }}, {label:'Est Harga', cls:'num', html:function (r) { return P.num(r.harga); }}, {label:'Est Total', cls:'num', html:function (r) { return P.num(r.qty * r.harga); }}], items);
  }
  function viewAsset(d) { M.modal('Detail PR Asset ' + d.no_pr, assetDl(d) + '<div class="sect">Detail Asset</div>' + assetItems(d.items) + '<div class="sect">Riwayat Approval</div>' + P.trailHtml(d, 'asset'), {size:'xl'}); }
  function printAsset(d) { P.printPreview(d.no_pr, '<h3 style="text-align:center">PURCHASE REQUEST ASSET</h3>' + assetDl(d) + assetItems(d.items) + P.trailHtml(d, 'asset')); }

  P.build.pr_asset_pr = function (host) {
    function list() {
      host.innerHTML = '<div class="box primary"><div class="box-header"><span id="addw"></span></div><div class="box-body" id="tbl"></div></div>' +
        '<div class="note">PR Asset dibuat dari planning asset yang sudah <b>Approved</b> (<a href="asset_planning_index_asset_approve.html">Approval Planning Asset</a>). Setelah dibuat, PR melewati approval Head → Cost Control → Management. PR Approved masuk ke <a href="metode_pembelian_pr.html">Metode Pembelian - PR</a> dan dapat dibuat PO.</div>';
      var t = P.tbl(M.$('#tbl'), function () { return R.prAsset.slice().sort(function (a, b) { return a.no_pr < b.no_pr ? 1 : -1; }); }, assetCols(function (r) {
        return P.btn('btn-warning', 'view', r.no_pr, '👁', 'View') + P.btn('btn-success', 'print', r.no_pr, '🖨', 'Print') + (r.rej ? P.btn('btn-primary', 'again', r.no_pr, '↻ Ajukan Ulang', 'Ajukan ulang approval') : '');
      }), function (a, id) {
        var d = R.prAsset.filter(function (x) { return x.no_pr === id; })[0];
        if (a === 'view') viewAsset(d); else if (a === 'print') printAsset(d);
        else M.saveFlow({text:'PR ' + id + ' diajukan ulang dan approval diulang dari Head.', okText:'Ya, Ajukan!', done:'PR diajukan ulang.', save:function () { P.resubmit(d); }, after:function () { P.after(); t.refresh(); }});
      });
      P.addBtn('addw', '＋ Buat PR dari Planning', create);
    }
    function create() {
      var av = R.planAsset.filter(function (a) { return a.status === 'Y' && !a.pr; });
      var b = M.modal('Add PR Asset - Planning Approved', '<form novalidate>' + (av.length ? '<div class="tbl-wrap"><table class="mini"><thead><tr><th></th><th>Nama Barang</th><th>Department</th><th>Costcenter</th><th>Budget</th><th width="80">Qty</th><th width="140">Est Harga / Unit</th></tr></thead><tbody>' +
        av.map(function (a) { return '<tr><td><input type="checkbox" data-c="' + a.code_plan + '" style="width:auto"></td><td>' + esc(P.up(a.nama_asset)) + '<br><small>' + a.code_plan + '</small></td><td>' + esc(P.dept(a.id_dept)) + '</td><td>' + esc(P.cc(a.cc)) + '</td><td class="num">' + P.num(a.budget) + '</td><td><input type="number" min="1" max="' + a.qty + '" value="' + a.qty + '" data-q="' + a.code_plan + '"></td><td><input type="number" min="0" value="' + Math.round(a.budget / a.qty) + '" data-h="' + a.code_plan + '"></td></tr>'; }).join('') +
        '</tbody></table></div><div class="form-actions"><button class="btn btn-primary" type="submit">💾 Buat PR</button></div>' : P.alert('note', 'Tidak ada planning asset Approved yang belum dibuat PR. Setujui dulu di <a href="asset_planning_index_asset_approve.html">Approval Planning Asset</a>.')) + '</form>', {size:'xl'});
      var f = b.querySelector('form'); f.onsubmit = function (e) {
        e.preventDefault(); var pick = av.filter(function (a) { return f.querySelector('[data-c="' + a.code_plan + '"]').checked; });
        if (!pick.length) { P.warn('Pilih minimal satu planning.'); return; }
        for (var i = 0; i < pick.length; i++) { var q = +f.querySelector('[data-q="' + pick[i].code_plan + '"]').value; if (!(q > 0 && q <= pick[i].qty)) { P.warn('Qty ' + pick[i].nama_asset + ' harus 1 sampai ' + pick[i].qty + '.'); return; } }
        M.saveFlow({text:'PR Asset akan dibuat dan masuk approval Head.', done:'PR Asset berhasil dibuat.', save:function () {
          var d = {no_pr:P.nextNo('PRA' + P.YM, R.prAsset, 'no_pr', 4), tgl:P.TODAY, by:R.me, rej:null, app:{1:null, 2:null, 3:null}, items:[]};
          pick.forEach(function (a) { var q = +f.querySelector('[data-q="' + a.code_plan + '"]').value, h = +f.querySelector('[data-h="' + a.code_plan + '"]').value;
            d.items.push({iid:'A' + a.code_plan, plan:a.code_plan, nm:a.nama_asset, id_dept:a.id_dept, cc:a.cc, satuan:'UNIT', qty:q, harga:h, qty_po:0, spec:a.ket, ket:''}); a.pr = d.no_pr; a.budget_pr = Math.max(0, a.budget_pr - q * h); });
          R.prAsset.push(d);
        }, after:function () { P.after(); list(); }});
      };
    }
    list();
  };
  function assetApproval(host, lvl) {
    function list() {
      host.innerHTML = '<div class="box primary"><div class="box-header"><h3 class="box-title">Approval PR Asset - ' + P.LV[lvl] + '</h3><span class="pull-right"><label>Tampilkan: <select id="fm" style="padding:5px"><option value="1">Menunggu saya</option><option value="0">Semua</option></select></label></span></div><div class="box-body" id="tbl"></div></div>' +
        '<div class="note">Approver ' + P.LV[lvl] + ': <b>' + esc(R.approver[lvl]) + '</b>. ' + (lvl === 3 ? 'Setelah Approve Management, PR masuk ke <a href="metode_pembelian_pr.html">Metode Pembelian - PR</a> dan dapat dipilih di Purchase Order Non Material.' : 'Setelah approve, PR menunggu level berikutnya.') + '</div>';
      var fm = M.$('#fm');
      var t = P.tbl(M.$('#tbl'), function () { return R.prAsset.filter(function (d) { return fm.value === '0' || P.waitLvl(d) === lvl; }).slice().sort(function (a, b) { return a.no_pr < b.no_pr ? 1 : -1; }); }, assetCols(function (r) {
        return P.btn('btn-warning', 'view', r.no_pr, '👁', 'View') + (P.waitLvl(r) === lvl ? P.btn('btn-info', 'app', r.no_pr, '✓ Approve', 'Approve') : '');
      }), function (a, id) { var d = R.prAsset.filter(function (x) { return x.no_pr === id; })[0]; if (a === 'view') viewAsset(d); else detail(d); });
      fm.onchange = function () { t.refresh(); };
    }
    function detail(d) {
      host.innerHTML = '<div class="box primary"><div class="box-header"><h3 class="box-title">Approval PR Asset ' + esc(d.no_pr) + ' - ' + P.LV[lvl] + '</h3></div><div class="box-body">' + assetDl(d) + '<div class="sect">Detail Asset</div>' + assetItems(d.items) + '<div class="sect">Riwayat Approval</div><div id="dec"></div></div></div>';
      P.decisionBar(M.$('#dec'), {d:d, kind:'asset', lvl:lvl, back:list});
    }
    list();
  }
  P.build.pr_asset_approval_head = function (h) { assetApproval(h, 1); };
  P.build.pr_asset_approval_cost_control = function (h) { assetApproval(h, 2); };
  P.build.pr_asset_approval_management = function (h) { assetApproval(h, 3); };

  /* =====================================================  PR STOCK (app_pr_stock) ===================================================== */
  function stockCols(btns) {
    return [{label:'Asal Permintaan', key:'asal'}, {label:'No. Req / No SO', key:'so'}, {label:'No. PR', html:function (r) { return r.no_pr ? esc(r.no_pr) : '-'; }, text:function (r) { return r.no_pr; }}, {label:'Untuk Kebutuhan', key:'untuk'},
      {label:'Request By', key:'by'}, {label:'Request Date', html:function (r) { return M.dstr(r.tgl); }, text:function (r) { return r.tgl; }},
      {label:'Status', html:function (r) { return pill(r, 'stock'); }, text:function (r) { return P.docState(r, 'stock').t; }}, {label:'Option', html:btns, text:function () { return ''; }, width:'150px'}];
  }
  function stockDl(d) { return P.dl([['No. Request / SO', d.so], ['Asal Permintaan', d.asal], ['Due Date SO', M.dstr(d.due), 1], ['No. PR', d.no_pr], ['Tgl Dibutuhkan', M.dstr(d.tgl_dibutuhkan), 1], ['Customer', d.customer], ['Tingkat PR', d.tingkat_pr], ['Untuk Kebutuhan', d.untuk], ['Request By', d.by]]); }
  function stockTable(d, editable) {
    return '<div class="tbl-wrap"><table class="mini"><thead><tr>' + (editable ? '<th></th>' : '') + '<th>Material Name</th><th>Estimasi</th><th>Stock Free</th><th>Use Stock</th><th>Sisa Stock Free</th><th>Min Stock</th><th>Max Stock</th><th>Min Order Qty</th><th>PR Qty</th><th width="110">Rev</th><th>Status</th></tr></thead><tbody>' +
      d.items.map(function (i) { return '<tr>' + (editable ? '<td><input type="checkbox" data-ck="' + i.iid + '" style="width:auto"' + (i.st === 'D' ? '' : ' checked') + '></td>' : '') + '<td>' + esc(P.up(i.nm)) + ' <small>(' + esc(i.satuan) + ')</small></td><td class="num">' + P.q(i.estimasi) + '</td><td class="num">' + P.q(i.free) + '</td><td class="num">' + P.q(i.use) + '</td><td class="num">' + P.q(i.free - i.use) + '</td><td class="num">' + P.q(i.min) + '</td><td class="num">' + P.q(i.max) + '</td><td class="num">' + P.q(i.moq) + '</td><td class="num">' + P.q(i.pr) + '</td><td>' + (editable ? '<input type="number" min="0" data-rv="' + i.iid + '" value="' + i.rev + '">' : P.q(i.rev)) + '</td><td>' + (i.st === 'D' ? P.pill('Ditolak', 'off') : i.st === 'Y' ? P.pill('Approved', 'ok') : '-') + '</td></tr>'; }).join('') + '</tbody></table></div>';
  }
  function viewStock(d) { M.modal('Detail PR Stock ' + d.so, stockDl(d) + '<div class="sect">Material</div>' + stockTable(d, false) + '<div class="sect">Riwayat Approval</div>' + P.trailHtml(d, 'stock'), {size:'xl'}); }
  function stockApproval(host, lvl) {
    function list() {
      host.innerHTML = '<div class="box primary"><div class="box-header"><h3 class="box-title">Approval PR Stock - ' + P.LV[lvl] + '</h3><span class="pull-right"><label>Tampilkan: <select id="fm" style="padding:5px"><option value="1">Menunggu saya</option><option value="0">Semua</option></select></label></span></div><div class="box-body" id="tbl"></div></div>' +
        '<div class="note">PR Stock berasal dari planning material berbasis SO / request produksi. Approver ' + P.LV[lvl] + ': <b>' + esc(R.approver[lvl]) + '</b>. Item yang tidak dicentang ditolak, Qty Rev boleh diubah. ' + (lvl === 3 ? 'Nomor PR (PRS…) sudah ada sejak PR dibuat dan tidak berubah. Setelah Approve Management, PR masuk <a href="metode_pembelian_pr.html">Metode Pembelian - PR</a> kategori Stok.' : '') + '</div>';
      var fm = M.$('#fm');
      var t = P.tbl(M.$('#tbl'), function () { return R.prStock.filter(function (d) { return fm.value === '0' || P.waitLvl(d) === lvl; }).slice().sort(function (a, b) { return a.tgl < b.tgl ? 1 : -1; }); }, stockCols(function (r) {
        return P.btn('btn-warning', 'view', r.so, '👁', 'View') + (P.waitLvl(r) === lvl ? P.btn('btn-info', 'app', r.so, '✓ Approve', 'Approve') : '');
      }), function (a, id) { var d = R.prStock.filter(function (x) { return x.so === id; })[0]; if (a === 'view') viewStock(d); else detail(d); });
      fm.onchange = function () { t.refresh(); };
    }
    function detail(d) {
      host.innerHTML = '<div class="box primary"><div class="box-header"><h3 class="box-title">Approval PR Stock ' + esc(d.so) + ' - ' + P.LV[lvl] + '</h3></div><div class="box-body">' + stockDl(d) + '<div class="sect">Material</div><div id="mt">' + stockTable(d, true) + '</div><div class="sect">Riwayat Approval</div><div id="dec"></div></div></div>';
      var collect = function () { d.items.forEach(function (i) { var ck = M.$('[data-ck="' + i.iid + '"]').checked; i.rev = +M.$('[data-rv="' + i.iid + '"]').value || 0; i.st = ck ? '' : 'D'; }); };
      P.decisionBar(M.$('#dec'), {d:d, kind:'stock', lvl:lvl, collect:collect, back:list, check:function () {
        if (!d.items.some(function (i) { return M.$('[data-ck="' + i.iid + '"]').checked; })) { P.warn('Centang minimal satu material, atau gunakan Reject untuk menolak seluruh PR.'); return false; } return true; }});
    }
    list();
  }
  P.build.app_pr_stock_approval_head = function (h) { stockApproval(h, 1); };
  P.build.app_pr_stock_approval_cost_control = function (h) { stockApproval(h, 2); };
  P.build.app_pr_stock_approval_management = function (h) { stockApproval(h, 3); };
})(window.Mock);
