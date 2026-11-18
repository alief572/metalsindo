/* Builder Purchase Request dan Purchase Order (as-is). Add/Edit berupa halaman penuh; View berupa modal; hasil Add/Edit/Approve/Delete/Copy tersimpan (localStorage lewat M.saveFlow/M.delFlow). */
(function (M) {
  'use strict';
  var P = M.seedP, S = M.seedS, esc = M.esc, fmt = M.fmt;
  var SHEET = 'B2000002';
  var today = function () { return new Date().toISOString().slice(0, 10); };
  var pad = function (n, l) { return String(n).padStart(l, '0'); };
  var addD = function (s, d) { var x = new Date(s + 'T00:00:00Z'); x.setUTCDate(x.getUTCDate() + d); return x.toISOString().slice(0, 10); };
  var up = function (s) { return String(s || '').toUpperCase(); };
  function matOf(id) { return P.material.filter(function (m) { return m.id === id; })[0] || null; }
  function supOf(id) { return P.supplier.filter(function (s) { return s.id === id; })[0] || null; }
  function supName(id) { var s = supOf(id); return s ? s.nama : (id || '-'); }
  function maxNo(rows) { return rows.reduce(function (a, r) { return Math.max(a, parseInt(String(r.no).slice(-5), 10) || 0); }, 0); }
  function matLabel(m) { return m.id + '|' + m.nm + '-' + (m.maker || ''); }
  function inp(attr, val, extra) { return '<input type="text" class="form-control" ' + attr + ' value="' + esc(val == null ? '' : val) + '"' + (extra || '') + '>'; }
  function btn(cls, a, id, ic, ttl) { return ' <button class="btn btn-sm ' + cls + '" data-a="' + a + '" data-id="' + esc(id) + '" title="' + ttl + '">' + ic + '</button>'; }
  var num = function (v, d) { return fmt(v || 0, d == null ? 2 : d); };
  var money2 = function (el, cb) { M.money.bind(el, cb); };

  /* ---------- kurs & LME (dari Master Kurs / Master LME) ---------- */
  function kursInfo() {
    var h = S.kurs.slice().sort(function (a, b) { return a.tgl < b.tgl ? 1 : a.tgl > b.tgl ? -1 : (b.id - a.id); });
    function avg(days) { var d0 = new Date(h[0].tgl + 'T00:00:00Z'); d0.setUTCDate(d0.getUTCDate() - days); var lim = d0.toISOString().slice(0, 10), r = h.filter(function (x) { return x.tgl >= lim; }); return r.reduce(function (a, x) { return a + x.nominal; }, 0) / r.length; }
    return {spot:h[0].nominal, d10:avg(10), d30:avg(30)};
  }
  var ELM = ['Cu', 'Zn', 'Sn', 'Ni', 'Ag', 'Al'];
  function lmeInfo() { // Rate saat ini = harga terbaru; H-10 = rata-rata 14 hari sebelumnya; H-30 = rata-rata sisanya
    var h = S.lme.slice().sort(function (a, b) { return a.tgl < b.tgl ? 1 : -1; }), now = h[0], r10 = h.slice(1, 11), r30 = h.slice(11);
    var avg = function (rows, e) { return rows.length ? rows.reduce(function (a, x) { return a + (x.v[e] || 0); }, 0) / rows.length : 0; };
    var o = {}; ELM.forEach(function (e) { o[e] = {now:now.v[e] || 0, d10:avg(r10, e), d30:avg(r30, e)}; }); return o;
  }
  function comp(m) { var n = up(m && m.nm); if (/BSR|C2[0-9]{3}/.test(n)) return {Cu:65, Zn:35}; if (/PBS|C5[0-9]{3}/.test(n)) return {Cu:92, Sn:8}; if (/\bNS\b|C7[0-9]{3}/.test(n)) return {Cu:65, Ni:18, Zn:17}; return {Cu:100}; }
  function alloyPrice(m, rate) { // USD/Kg = sum(LME komposisi x kandungan) / 1000
    var L = lmeInfo(), k = {'Hari Ini':'now', 'H-10':'d10', 'H-30':'d30'}[rate]; if (!k) return 0; var c = comp(m), s = 0;
    Object.keys(c).forEach(function (e) { s += L[e][k] * c[e] / 100; }); return Math.round(s) / 1000;
  }
  function kursTables() {
    var k = kursInfo(), L = lmeInfo();
    return '<table class="tbl" style="max-width:560px"><thead><tr><th style="text-align:center">Kurs On The Spot</th><th style="text-align:center">Kurs 10 Hari</th><th style="text-align:center">Kurs 30 Hari</th></tr></thead><tbody><tr>' +
      [k.spot, k.d10, k.d30].map(function (v) { return '<td style="text-align:center">Rp. ' + fmt(v, 2) + ' ,-</td>'; }).join('') + '</tr></tbody></table>' +
      '<div style="height:10px"></div><table class="tbl" style="max-width:560px"><thead><tr><th width="5">#</th><th>Komposisi</th><th>Rate H-30</th><th>Rate H-10</th><th>Rate Saat Ini</th></tr></thead><tbody>' +
      ELM.map(function (e, i) { return '<tr><td>' + (i + 1) + '</td><td>' + e + '</td><td>$ ' + fmt(L[e].d30, 2) + '</td><td>$ ' + fmt(L[e].d10, 2) + '</td><td>$ ' + fmt(L[e].now, 2) + '</td></tr>'; }).join('') + '</tbody></table>';
  }

  /* =====================================================================
     PURCHASE REQUEST  (modul purchase_request)
     ===================================================================== */
  function buildPR(page) {
    var root = document.createElement('div'); page.appendChild(root);
    function nextSurat(d) { var y = d.slice(0, 4), mx = 0; P.pr.forEach(function (r) { if (String(r.tanggal).slice(0, 4) === y) mx = Math.max(mx, parseInt(String(r.no_surat).slice(-4), 10) || 0); }); return 'PR' + y + d.slice(5, 7) + '-' + pad(mx + 1, 4); }
    var nextCode = function () { return 'P26' + pad(maxNo(P.pr) + 1, 5); };
    var badge = function (r) { return r.status === 2 ? M.pill('Approved', 'ok') : M.pill('Waiting', 'warn'); };

    function list() {
      root.innerHTML = '<div class="box primary"><div class="box-header"><span id="addw"></span></div><div class="box-body" id="tbl"></div></div>';
      function addBtn() { M.$('#addw').innerHTML = M.perm.Add ? '<button class="btn btn-success btn-sm" id="add">＋ Create</button>' : ''; if (M.perm.Add) M.$('#add').onclick = function () { form(null); }; }
      addBtn(); M.onPerm(addBtn);
      var t = M.table(M.$('#tbl'), {rows:function () { return P.pr.slice().sort(function (a, b) { return a.no_surat < b.no_surat ? 1 : -1; }); }, cols:[
        {key:'no_surat', label:'No.PR'}, {key:'tanggal', label:'Tanggal PR', html:function (r) { return M.dstr(r.tanggal); }, text:function (r) { return r.tanggal; }}, {key:'requestor', label:'Requestor'},
        {label:'Progress PR', html:badge, text:function (r) { return r.status === 2 ? 'Approved' : 'Waiting'; }},
        {label:'Action', html:function (r) { var x = '';
          if (M.perm.View) x += btn('btn-primary', 'view', r.no, '👁', 'View');
          if (r.status === 1) { if (M.perm.Manage) x += btn('btn-success', 'edit', r.no, '✎', 'Edit'); if (M.perm.Delete) x += btn('btn-success', 'apv', r.no, '✓', 'Approve'); }
          if (M.perm.Add) x += btn('btn-primary', 'copy', r.no, '⎘', 'Copy PR');
          return x; }, text:function () { return ''; }}],
        onAction:function (a, id) { var r = P.pr.filter(function (x) { return x.no === id; })[0];
          if (a === 'view') view(r); else if (a === 'edit') form(r);
          else if (a === 'apv') M.saveFlow({text:'P.R. Akan Diapprove!', okText:'Ya, Approve!', done:'P.R Approved.', save:function () { r.status = 2; t.refresh(); }});
          else M.saveFlow({text:'PR ini akan disalin menjadi PR baru (status Waiting, tanggal hari ini).', okText:'Ya, Salin!', done:'Berhasil menyalin PR.', save:function () {
            var code = nextCode(), d = today(); P.pr.push({no:code, no_surat:nextSurat(d), tanggal:d, requestor:'admin', status:1, det:r.det.map(function (x, i) { var c = JSON.parse(JSON.stringify(x)); c.id = code + '-' + (i + 1); return c; })}); t.refresh(); }}); }});
      root.insertAdjacentHTML('beforeend', '<div class="note">Alur: PR dibuat → <b>Waiting</b> (masih bisa diedit) → <b>Approve</b> → <b>Approved</b> (terkunci; menjadi sumber Purchase Order). <b>Copy PR</b> membuat PR baru berstatus Waiting dengan item yang sama. Di aplikasi tombol Approve juga tampil pada PR Approved tanpa efek; di mockup hanya pada PR Waiting.</div>');
    }
    function detTable(det, withSheetCols) {
      var cols = [{label:'Material', html:function (d) { return esc(d.nm); }}, {label:'Bentuk', html:function (d) { return esc(d.bentuk); }},
        {label:'ID', cls:'num', html:function (d) { return d.bentuk === 'SHEET' ? '' : esc(d.idm_); }}, {label:'OD', cls:'num', html:function (d) { return d.bentuk === 'SHEET' ? '' : num(d.od); }}];
      if (withSheetCols) cols.push({label:'Qty Sheet', cls:'num', html:function (d) { return d.bentuk === 'SHEET' ? fmt(d.qs, 0) : ''; }}, {label:'Weight Sheet', cls:'num', html:function (d) { return d.bentuk === 'SHEET' ? num(d.ws) : ''; }});
      cols.push({label:'Total Weight', cls:'num', html:function (d) { return num(d.tw); }}, {label:'Width', cls:'num', html:function (d) { return num(d.w); }}, {label:'Length', cls:'num', html:function (d) { return num(d.l); }},
        {label:'Supplier', html:function (d) { return esc(supName(d.sup)); }}, {label:'Tanggal Dibutuhkan', html:function (d) { return M.dstr(d.tgl); }}, {label:'Keterangan', html:function (d) { return esc(d.ket || '-'); }});
      return M.stable(cols, det);
    }
    function view(r) {
      M.modal('Purchase Request', '<dl class="dl"><dt>NO.PR</dt><dd>' + esc(r.no_surat) + ' <small>(' + esc(r.no) + ')</small></dd><dt>Tanggal PR</dt><dd>' + M.dstr(r.tanggal) + '</dd><dt>Requestor</dt><dd>' + esc(r.requestor) + '</dd><dt>Status</dt><dd>' + badge(r) + '</dd></dl><div class="sect">Material</div>' +
        detTable(r.det, r.det.some(function (d) { return d.bentuk === 'SHEET'; })), {size:'xl'});
    }

    /* ----- halaman Add / Edit ----- */
    function form(r) {
      var edit = !!r, d0 = edit ? r.tanggal : today();
      var cur = edit ? r : {no:nextCode(), no_surat:nextSurat(d0), tanggal:d0, requestor:''};
      var items = (edit ? r.det : []).map(function (d) { return {idm:d.idm, idd:String(d.idm_ || 500), od:d.od, qs:d.qs, tw:d.tw, w:d.w, l:d.l, sup:d.sup, tgl:d.tgl, ket:d.ket}; });
      root.innerHTML = '<div class="box primary"><div class="box-body"><div style="text-align:center"><h3 style="margin:4px 0 14px">Purchase Request</h3></div><form novalidate><div class="grid2">' +
        M.fg('NO.PR', '<input type="text" readonly value="' + esc(cur.no_surat) + '">') + '<div></div>' +
        M.fg('Tanggal PR', '<input type="text" readonly value="' + esc(cur.tanggal) + '">') + '<div></div>' +
        M.fg('Requestor', '<input type="text" name="requestor" required value="' + esc(cur.requestor) + '">') + '</div>' +
        '<div style="margin:10px 0"><button type="button" class="btn btn-success btn-sm" id="addm">＋ Add</button></div><div id="grid"></div>' +
        '<div class="form-actions"><button class="btn btn-primary" type="submit">💾 Save</button><button class="btn btn-default" type="button" id="back">Back</button></div></form></div></div>';
      var f = root.querySelector('form'); M.$('#back').onclick = list;
      M.$('#addm').onclick = function () { items.push({idm:'', idd:'500', od:0, qs:0, tw:0, w:0, l:0, sup:'', tgl:'', ket:''}); grid(); };
      function grid() {
        var any = items.some(function (it) { var m = matOf(it.idm); return m && m.bentuk === 'SHEET'; });
        var h = '<div class="tbl-wrap"><table class="tbl"><thead><tr class="bg-blue"><th>Material</th><th>Bentuk</th><th>ID</th><th>OD</th>' + (any ? '<th>Qty Sheet</th><th>Weight / Sheet</th>' : '') + '<th>Total Weight</th><th>Width</th><th>Length</th><th>Supplier</th><th>Tanggal Dibutuhkan</th><th>Keterangan</th><th>Aksi</th></tr></thead><tbody>';
        if (!items.length) h += '<tr><td class="empty" colspan="' + (any ? 13 : 11) + '">Belum ada material. Klik Add untuk menambah baris.</td></tr>';
        items.forEach(function (it, i) {
          var m = matOf(it.idm), sh = m && m.bentuk === 'SHEET', ws = sh ? m.tw : 0;
          h += '<tr data-i="' + i + '"><td style="min-width:260px"><select data-k="idm"><option value="">Pilih</option>' + P.material.map(function (x) { return '<option value="' + esc(x.id) + '"' + (x.id === it.idm ? ' selected' : '') + '>' + esc(matLabel(x)) + '</option>'; }).join('') + '</select></td>' +
            '<td>' + inp('readonly', m ? m.bentuk : '') + '</td>' +
            '<td style="min-width:80px">' + (sh ? '' : '<select data-k="idd">' + ['200', '250', '300', '400', '500'].map(function (v) { return '<option' + (v === String(it.idd) ? ' selected' : '') + '>' + v + '</option>'; }).join('') + '</select>') + '</td>' +
            '<td style="min-width:90px">' + (sh ? '' : inp('data-m="od"', it.od ? M.money.fmt(it.od) : '', ' style="text-align:right"')) + '</td>' +
            (any ? '<td style="min-width:90px">' + (sh ? inp('data-m="qs"', it.qs ? M.money.fmt(it.qs) : '', ' style="text-align:right"') : '') + '</td><td style="min-width:90px">' + (sh ? inp('readonly', num(ws), ' style="text-align:right"') : '') + '</td>' : '') +
            '<td style="min-width:110px">' + (sh ? inp('readonly data-tw="1"', num(it.tw), ' style="text-align:right"') : inp('data-m="tw"', it.tw ? M.money.fmt(it.tw) : '', ' style="text-align:right"')) + '</td>' +
            '<td style="min-width:90px">' + inp('data-m="w"', it.w ? M.money.fmt(it.w) : '', ' style="text-align:right"') + '</td><td style="min-width:90px">' + inp('data-m="l"', it.l ? M.money.fmt(it.l) : '', ' style="text-align:right"') + '</td>' +
            '<td style="min-width:200px"><select data-k="sup">' + (m ? '<option value="">Pilih</option>' + m.sup.map(function (s) { return '<option value="' + esc(s) + '"' + (s === it.sup ? ' selected' : '') + '>' + esc(supName(s)) + '</option>'; }).join('') : '<option value="">Empty</option>') + '</select></td>' +
            '<td><input type="date" data-k="tgl" value="' + esc(it.tgl) + '"></td><td style="min-width:140px">' + inp('data-k="ket"', it.ket) + '</td>' +
            '<td><button type="button" class="btn btn-sm btn-danger" data-del="' + i + '" title="Hapus Data">✕</button></td></tr>';
        });
        var g = M.$('#grid'); g.innerHTML = h + '</tbody></table></div>';
        g.querySelectorAll('tr[data-i]').forEach(function (tr) {
          var it = items[+tr.dataset.i], m = matOf(it.idm);
          tr.querySelectorAll('[data-k]').forEach(function (el) {
            el.onchange = el.oninput = function () { var k = el.dataset.k; it[k] = el.value;
              if (k === 'idm') { var nm = matOf(el.value); it.w = nm ? nm.w : 0; it.l = nm ? nm.l : 0; it.sup = nm && nm.sup.length === 1 ? nm.sup[0] : ''; if (nm && nm.bentuk === 'SHEET') { it.qs = 0; it.tw = 0; } grid(); } }; });
          tr.querySelectorAll('[data-m]').forEach(function (el) { var k = el.dataset.m;
            money2(el, function () { it[k] = M.money.val(el); if (k === 'qs' && m) { it.tw = it.qs * m.tw; var t = tr.querySelector('[data-tw]'); if (t) t.value = num(it.tw); } }); });
          tr.querySelector('[data-del]').onclick = function () { items.splice(+tr.dataset.i, 1); grid(); };
        });
      }
      grid();
      f.onsubmit = function (e) {
        e.preventDefault(); if (!M.validate(f)) return;
        if (!items.length) return M.swal({title:'Peringatan !', text:'Tambahkan minimal satu material dengan tombol Add.', type:'warning'});
        for (var i = 0; i < items.length; i++) { var it = items[i], m = matOf(it.idm), no = ' (baris ' + (i + 1) + ')';
          if (!m) return M.swal({title:'Peringatan !', text:'Material belum dipilih' + no + '.', type:'warning'});
          if (m.bentuk === 'SHEET' && !(it.qs > 0)) return M.swal({title:'Peringatan !', text:'Qty Sheet wajib diisi' + no + '.', type:'warning'});
          if (!(it.tw > 0)) return M.swal({title:'Peringatan !', text:'Total Weight wajib diisi' + no + '.', type:'warning'});
          if (!it.sup) return M.swal({title:'Peringatan !', text:'Supplier belum dipilih' + no + '.', type:'warning'});
          if (!it.tgl) return M.swal({title:'Peringatan !', text:'Tanggal Dibutuhkan wajib diisi' + no + '.', type:'warning'}); }
        M.saveFlow({text:'You will not be able to process again this data!', okText:'Yes, Process it!', done:'Data Purchase Request berhasil disimpan.', save:function () {
          var det = items.map(function (it, i) { var m = matOf(it.idm), sh = m.bentuk === 'SHEET';
            return {id:cur.no + '-' + (i + 1), idm:m.id, nm:m.nm, bentuk:m.bentuk, idb:m.id_bentuk, idm_:sh ? 0 : +it.idd, od:sh ? 0 : it.od, tw:it.tw, w:it.w, l:it.l, sup:it.sup, tgl:it.tgl, ket:it.ket || '', qs:sh ? it.qs : 0, ws:sh ? m.tw : 0}; });
          if (edit) { r.requestor = f.requestor.value.trim(); r.det = det; }
          else P.pr.push({no:cur.no, no_surat:cur.no_surat, tanggal:cur.tanggal, requestor:f.requestor.value.trim(), status:1, det:det}); }, after:list});
      };
    }
    list();
  }

  /* =====================================================================
     PURCHASE ORDER  (modul purchase_order)
     ===================================================================== */
  function buildPO(page) {
    var root = document.createElement('div'); page.appendChild(root);
    var ROM = ['I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X', 'XI', 'XII'];
    function nextSurat(d) { var yy = d.slice(2, 4), mx = 0; P.po.forEach(function (o) { if (String(o.no_surat).indexOf('/' + yy + '/') > -1) mx = Math.max(mx, parseInt(String(o.no_surat).slice(-4), 10) || 0); }); return 'PO-MP/' + yy + '/' + ROM[+d.slice(5, 7) - 1] + '/' + pad(mx + 1, 4); }
    var nextCode = function () { return 'P26' + pad(maxNo(P.po) + 1, 5); };
    var badge = function (o) { return o.status === 1 ? M.pill('Waiting', 'info') : o.status === 2 ? M.pill('Approved', 'ok') : M.pill('Closed', 'off'); };
    var stTxt = function (o) { return o.status === 1 ? 'Waiting' : o.status === 2 ? 'Approved' : 'Closed'; };
    var cur = function (o) { return o.matauang === 'usd' ? 'USD' : o.matauang === 'yen' ? 'JPY' : 'Rp'; };

    function list() {
      root.innerHTML = '<div class="box primary"><div class="box-header"><span id="addw"></span></div><div class="box-body" id="tbl"></div></div>';
      function addBtn() { M.$('#addw').innerHTML = M.perm.Add ? '<button class="btn btn-success btn-sm" id="add">＋ Create</button>' : ''; if (M.perm.Add) M.$('#add').onclick = pickBentuk; }
      addBtn(); M.onPerm(addBtn);
      var t = M.table(M.$('#tbl'), {rows:function () { return P.po.slice().sort(function (a, b) { return a.no < b.no ? 1 : -1; }); }, cols:[
        {key:'no_surat', label:'No PO'}, {label:'Tanggal PO', html:function (o) { return M.dstr(o.tanggal); }, text:function (o) { return o.tanggal; }},
        {label:'Supplier', html:function (o) { return esc(up(supName(o.id_suplier))); }, text:function (o) { return supName(o.id_suplier); }},
        {label:'Progress PR', html:badge, text:stTxt},
        {label:'Action', html:function (o) { var x = '';
          if (M.perm.View) x += btn('btn-warning', 'view', o.no, '👁', 'View') + btn('btn-primary', 'print', o.no, '🖶', 'Print');
          if (o.status === 1) { if (M.perm.Manage) x += btn('btn-info', 'edit', o.no, '✎', 'Edit') + btn('btn-success', 'apv', o.no, '✓', 'Approval PO'); if (M.perm.Delete) x += btn('btn-danger', 'del', o.no, '🗑', 'Delete PO'); }
          return x; }, text:function () { return ''; }}],
        onAction:function (a, id) { var o = P.po.filter(function (x) { return x.no === id; })[0];
          if (a === 'view') view(o); else if (a === 'print') M.toast('Cetak PO ' + o.no_surat + ' (PDF, simulasi)'); else if (a === 'edit') form(o, o.sheet);
          else if (a === 'apv') approval(o, t);
          else if (o.det.some(function (d) { return d.inc; })) M.swal({title:'Error', text:'PO tidak dapat dihapus karena sudah memiliki data Incoming (Penerimaan Barang) di gudang!', type:'error'});
          else M.delFlow('Data Purchase Order ini akan dihapus!', 'Purchase Order berhasil dihapus.', function () { P.po.splice(P.po.indexOf(o), 1); t.refresh(); }); }});
      root.insertAdjacentHTML('beforeend', '<div class="note">Alur: PO dibuat dari <b>PR Approved</b> milik satu supplier → <b>Waiting</b> (bisa diedit/dihapus) → <b>Approval PO</b> (menampilkan referensi Last Purchase per item) → <b>Approved</b>. <b>Closed</b> diset oleh proses penerimaan barang (Incoming). PR yang sudah dipakai PO tidak muncul lagi pada pilihan PR. Bentuk <b>Sheet</b> memakai form tanpa kolom Rate LME / Alloy Price / Fab Cost.</div>');
    }
    function pickBentuk() {
      var b = M.modal('Purchase Order', '<div class="fg"><label>Bentuk Material</label><div class="fc"><select id="tp">' + M.opts(S.bentukList, '', 'id', 'nama', '- Pilih Bentuk Material -') + '</select></div></div>', {footer:false});
      b.insertAdjacentHTML('beforeend', '<div class="form-actions"><button class="btn btn-success" id="go">⚙ Process</button><button class="btn btn-default" id="cl">Close</button></div>');
      b.querySelector('#cl').onclick = M.close;
      b.querySelector('#go').onclick = function () { var v = b.querySelector('#tp').value; if (!v) return M.swal({title:'Peringatan !', text:'Tipe bentuk material harus dipilih !', type:'warning'}); M.close(); form(null, v === SHEET); };
    }

    /* ----- perhitungan baris ----- */
    function unit(o, it, sheet) { return (o.loi === 'Import' && !sheet) ? (it.alloy || 0) + (it.fab || 0) : (it.hs || 0); }
    function totals(o, items, sheet) {
      var h = 0, d = 0, t = 0; items.forEach(function (it) { var a = unit(o, it, sheet) * (it.tw || 0); h += a; d += a * (it.disc || 0) / 100; t += a * (it.tax || 0) / 100; });
      return {h:h, d:d, t:t, s:h - d + t};
    }

    /* Last Purchase = harga PO terakhir material yang sama yang sudah incoming (bukan PO yang sedang diperiksa); dibandingkan hanya bila mata uangnya sama */
    function lastPurchase(o, it) {
      var best = null;
      P.po.forEach(function (x) { if (x.no === o.no) return;
        x.det.forEach(function (d) { if (d.idm !== it.idm || !d.inc || !(d.hs > 0)) return; if (!best || x.tanggal > best.tgl || (x.tanggal === best.tgl && x.no > best.no)) best = {no:x.no, surat:x.no_surat, tgl:x.tanggal, hs:d.hs, mu:x.matauang}; }); });
      return best;
    }
    function approval(o, t) {
      var sheet = o.sheet, c = cur(o), d3 = function (v) { return num(v, o.matauang === 'usd' ? 3 : 2); }, n = 0, naik = 0;
      var rows = o.det.map(function (d) { var lp = lastPurchase(o, d), u = d.hs, sel = lp && lp.mu === o.matauang ? (u - lp.hs) / lp.hs * 100 : null; if (lp) n++; if (sel != null && sel > 0) naik++; return {d:d, lp:lp, sel:sel}; });
      var b = M.modal('Approval PO ' + o.no_surat, '<dl class="dl"><dt>Supplier</dt><dd>' + esc(supName(o.id_suplier)) + '</dd><dt>Local / Import</dt><dd>' + esc(o.loi) + '</dd><dt>Tanggal PO</dt><dd>' + M.dstr(o.tanggal) + '</dd><dt>Total Order</dt><dd><b>' + c + ' ' + num(o.subtotal) + '</b></dd></dl>' +
        '<div class="sect">Referensi Last Purchase</div>' +
        M.stable([{label:'Item', html:function (r) { return esc(r.d.nm); }}, {label:'Total Weight', cls:'num', html:function (r) { return num(r.d.tw); }},
          {label:'Unit Price PO', cls:'num', html:function (r) { return '<b>' + d3(r.d.hs) + '</b>'; }},
          {label:'Last Purchase', cls:'num', html:function (r) { return r.lp ? d3(r.lp.hs) + (r.lp.mu !== o.matauang ? ' <small>(' + esc(up(r.lp.mu)) + ')</small>' : '') : '<span class="hint">Belum ada riwayat</span>'; }},
          {label:'Selisih', cls:'num', html:function (r) { return r.sel == null ? '-' : '<span style="color:' + (r.sel > 0 ? '#dd4b39' : '#00a65a') + ';font-weight:700">' + (r.sel > 0 ? '▲ +' : r.sel < 0 ? '▼ ' : '') + num(r.sel) + '%</span>'; }},
          {label:'PO Terakhir', html:function (r) { return r.lp ? esc(r.lp.surat) + ' <small>(' + M.dstr(r.lp.tgl) + ')</small>' : '-'; }}], rows) +
        '<div class="note">Last Purchase diambil dari PO terakhir untuk material yang sama yang <b>sudah incoming</b>' + (sheet ? '' : ' (Roll: per Maker karena Maker melekat pada material)') + '. Selisih dibandingkan hanya bila mata uangnya sama. ' + (n ? naik + ' dari ' + rows.length + ' item lebih mahal dari Last Purchase.' : 'Tidak ada riwayat pembelian sebelumnya.') + ' Hanya referensi; tidak membatasi Approval.</div>' +
        '<div class="form-actions"><button class="btn btn-success" id="apvgo">✓ Approve</button><button class="btn btn-default" id="apvcl">Close</button></div>', {size:'xl', footer:false});
      b.querySelector('#apvcl').onclick = M.close;
      b.querySelector('#apvgo').onclick = function () { M.saveFlow({text:'P.O. Akan Diapprove!', okText:'Ya, Approve!', done:'P.O Approved.', save:function () { o.status = 2; t.refresh(); }}); };
    }
    function view(o) {
      var sheet = o.sheet, imp = o.loi === 'Import', c = cur(o);
      M.modal('Purchase Order', '<dl class="dl"><dt>Supplier</dt><dd>' + esc(supName(o.id_suplier)) + '</dd><dt>Local / Import</dt><dd>' + esc(o.loi) + '</dd><dt>NO.PO</dt><dd>' + esc(o.no_surat) + ' <small>(' + esc(o.no) + ')</small></dd><dt>Kurs</dt><dd>' + num(o.nominal_kurs) + '</dd>' +
        '<dt>Tanggal PO</dt><dd>' + M.dstr(o.tanggal) + '</dd><dt>Mata Uang</dt><dd>' + esc(up(o.matauang)) + '</dd><dt>Expect Date</dt><dd>' + M.dstr(o.expect_tanggal) + '</dd><dt>Payment Term</dt><dd>' + esc(o.term) + '</dd>' +
        '<dt>PR</dt><dd>' + esc((P.pr.filter(function (r) { return r.no === o.no_pr; })[0] || {}).no_surat || o.no_pr) + '</dd><dt>Price Method</dt><dd>' + esc(o.cif) + '</dd><dt>Delivery Date</dt><dd>' + M.dstr(o.delivery_date) + '</dd><dt>PIC</dt><dd>' + esc(o.receiving_person || '-') + '</dd><dt>Status</dt><dd>' + badge(o) + '</dd></dl>' +
        '<div class="sect">Kurs dan Rate LME</div>' + kursTables() + '<div class="sect">Item</div>' +
        M.stable([{label:'Item', html:function (d) { return esc(d.nm); }}, {label:'Description', html:function (d) { return esc(d.desc || '-'); }}].concat(sheet ? [{label:'Width', cls:'num', html:function (d) { return num(d.w); }}, {label:'Length', cls:'num', html:function (d) { return num(d.l); }}] : [],
          [{label:'Total Weight', cls:'num', html:function (d) { return num(d.tw); }}],
          imp && !sheet ? [{label:'Rate LME', html:function (d) { return esc(d.rate || '-'); }}, {label:'Alloy Price', cls:'num', html:function (d) { return num(d.alloy, 3); }}, {label:'Fab Cost', cls:'num', html:function (d) { return num(d.fab, 3); }}] : [],
          [{label:'Unit Price', cls:'num', html:function (d) { return num(d.hs, 3); }}, {label:'Discount %', cls:'num', html:function (d) { return num(d.disc); }}, {label:'Tax', cls:'num', html:function (d) { return num(d.tax); }},
           {label:'Amount', cls:'num', html:function (d) { return num(d.hs * d.tw, 2); }}, {label:'Note', html:function (d) { return esc(d.note || '-'); }}]), o.det) +
        '<dl class="dl" style="margin-top:12px"><dt>Note</dt><dd>' + esc(o.note || '-') + '</dd><dt>Sub Total</dt><dd>' + c + ' ' + num(o.hargatotal) + '</dd><dt>Discount</dt><dd>' + c + ' ' + num(o.diskontotal) + '</dd><dt>TAX</dt><dd>' + c + ' ' + num(o.taxtotal) + '</dd><dt>Total Order</dt><dd><b>' + c + ' ' + num(o.subtotal) + '</b></dd></dl>', {size:'xl'});
    }

    /* ----- halaman Add / Edit (sheet = form tanpa Rate LME, Alloy Price, Fab Cost) ----- */
    function form(o, sheet) {
      var edit = !!o, d0 = today(), k = kursInfo();
      var h = edit ? JSON.parse(JSON.stringify(o)) : {no:nextCode(), no_surat:nextSurat(d0), id_suplier:'', loi:'', nominal_kurs:Math.round(k.d30 * 100) / 100, tanggal:d0, matauang:'idr', expect_tanggal:'', term:'', no_pr:'', cif:'', delivery_date:'', receiving_person:'', note:''};
      var items = edit ? h.det.map(function (d) { return JSON.parse(JSON.stringify(d)); }) : [];
      var others = P.po.filter(function (x) { return !edit || x.no !== o.no; });
      function prUsable(r) { return r.status === 2 && !others.some(function (x) { return x.no_pr === r.no; }) && r.det.some(function (d) { return (d.idb === SHEET) === sheet; }); }
      function suppliers() { var ids = {}; P.pr.filter(prUsable).forEach(function (r) { r.det.forEach(function (d) { if ((d.idb === SHEET) === sheet) ids[d.sup] = 1; }); });
        if (edit && h.id_suplier) ids[h.id_suplier] = 1; return P.supplier.filter(function (s) { return ids[s.id]; }); }
      function prList() { return P.pr.filter(function (r) { return prUsable(r) && r.det.some(function (d) { return d.sup === h.id_suplier; }); }); }
      root.innerHTML = '<div class="box primary"><div class="box-body"><div style="text-align:center"><h3 style="margin:4px 0 14px">Purchase Order' + (sheet ? ' (Sheet)' : '') + '</h3></div><form novalidate><div class="grid2">' +
        M.fg('Supplier', '<select name="sup" required></select>') + M.fg('Local / Import', '<select name="loi" required>' + M.opts(['Import', 'Lokal'], h.loi) + '</select>') +
        M.fg('NO.PO', '<input type="text" readonly value="' + esc(h.no_surat) + '">') + M.fg('Kurs', '<input type="text" name="kurs" required value="' + esc(M.money.fmt(h.nominal_kurs)) + '">') +
        M.fg('Tanggal PO', '<input type="date" name="tgl" required value="' + esc(h.tanggal) + '"' + (edit ? ' readonly' : '') + '>') + M.fg('Mata Uang', '<select name="mu">' + M.opts([{v:'idr', t:'IDR'}, {v:'usd', t:'USD'}, {v:'yen', t:'YEN'}], h.matauang, 'v', 't', '').replace('<option value="">', '<option value="" hidden>') + '</select>') +
        M.fg('Expect Date', '<input type="date" name="exp" required value="' + esc(h.expect_tanggal) + '">') + '<div></div>' +
        M.fg('Payment Term', '<input type="text" name="term" required value="' + esc(h.term) + '">') + M.fg('PR', '<select name="pr" required></select>') +
        M.fg('Price Method', '<select name="cif" required>' + M.opts(['CIF', 'FOB', 'LOCO', 'DDU', 'FRANCO'], h.cif) + '</select>') + '<div></div>' +
        M.fg('Delivery Date', '<input type="date" name="dlv" value="' + esc(h.delivery_date) + '">') + M.fg('PIC', '<input type="text" name="pic" value="' + esc(h.receiving_person) + '">') + '</div>' +
        '<div class="sect">Kurs dan Rate LME</div>' + kursTables() + '<div class="sect">Item</div><div id="grid"></div>' +
        '<div class="grid2" style="margin-top:12px">' + M.fg('Note', '<input type="text" name="note" value="' + esc(h.note) + '">') + '<div></div>' +
        M.fg('Sub Total', '<input type="text" readonly id="t_h">') + '<div></div>' + M.fg('Discount', '<input type="text" readonly id="t_d">') + '<div></div>' + M.fg('TAX', '<input type="text" readonly id="t_t">') + '<div></div>' + M.fg('Total Order', '<input type="text" readonly id="t_s">') + '</div>' +
        '<div class="form-actions"><button class="btn btn-success" type="submit">💾 Simpan</button><button class="btn btn-default" type="button" id="back">Back</button></div></form></div></div>';
      var f = root.querySelector('form'); M.$('#back').onclick = list;
      function fillSup() { f.sup.innerHTML = M.opts(suppliers(), h.id_suplier, 'id', 'nama', '--Pilih--'); }
      function fillPr() { f.pr.innerHTML = '<option value="">' + (prList().length ? 'Pilih PR' : 'List Empty') + '</option>' + prList().map(function (r) { return '<option value="' + esc(r.no) + '"' + (r.no === h.no_pr ? ' selected' : '') + '>' + esc(r.no_surat) + '</option>'; }).join(''); }
      function sums() { var t = totals(h, items, sheet); M.$('#t_h').value = num(t.h); M.$('#t_d').value = num(t.d); M.$('#t_t').value = num(t.t); M.$('#t_s').value = num(t.s); }
      function grid() {
        var imp = h.loi === 'Import', roll = !sheet, c = h.matauang === 'usd' ? 3 : 3;
        var hd = '<th>Item</th><th>Description</th><th>Width</th><th>Length</th><th>Total Weight</th>' + (roll ? '<th>Rate LME</th><th>Alloy Price</th><th>Fab Cost</th>' : '') + '<th>Unit Price</th><th>Disc %</th><th>Tax</th><th>Amount</th><th>Note</th><th>#</th>';
        var b = '';
        if (!items.length) b = '<tr><td class="empty" colspan="' + (roll ? 14 : 11) + '">Tidak ada daftar material</td></tr>';
        items.forEach(function (it, i) {
          var unitRo = roll && imp, abRo = !imp;
          b += '<tr data-i="' + i + '"><td style="min-width:240px">' + esc(it.nm) + '</td><td>' + inp('data-k="desc"', it.desc) + '</td><td>' + inp('data-m="w"', it.w ? M.money.fmt(it.w) : '', ' style="text-align:right"') + '</td><td>' + inp('data-m="l"', it.l ? M.money.fmt(it.l) : '', ' style="text-align:right"') + '</td><td>' + inp('data-m="tw"', it.tw ? M.money.fmt(it.tw) : '', ' style="text-align:right"') + '</td>' +
            (roll ? '<td><select data-k="rate">' + M.opts([{v:'Hari Ini', t:'Hari ini'}, {v:'H-10', t:'H-10'}, {v:'H-30', t:'H-30'}], it.rate, 'v', 't', '-Pilih-') + '</select></td>' +
              '<td>' + inp('data-m="alloy"', it.alloy ? M.money.fmt(it.alloy) : '', (abRo ? ' readonly' : '') + ' style="text-align:right"') + '</td><td>' + inp('data-m="fab"', it.fab ? M.money.fmt(it.fab) : '', (abRo ? ' readonly' : '') + ' style="text-align:right"') + '</td>' : '') +
            '<td>' + inp('data-m="hs"', unit(h, it, sheet) ? M.money.fmt(unit(h, it, sheet)) : '', (unitRo ? ' readonly' : '') + ' style="text-align:right"') + '</td><td>' + inp('data-m="disc"', it.disc ? M.money.fmt(it.disc) : '', ' style="text-align:right"') + '</td><td>' + inp('data-m="tax"', it.tax ? M.money.fmt(it.tax) : '', ' style="text-align:right"') + '</td>' +
            '<td>' + inp('readonly data-amt="1"', num(unit(h, it, sheet) * it.tw, 3), ' style="text-align:right"') + '</td><td>' + inp('data-k="note"', it.note) + '</td><td><button type="button" class="btn btn-sm btn-danger" data-del="1" title="Hapus Data">✕</button></td></tr>';
        });
        var g = M.$('#grid'); g.innerHTML = '<div class="tbl-wrap"><table class="tbl"><thead><tr class="bg-blue">' + hd + '</tr></thead><tbody>' + b + '</tbody></table></div>';
        g.querySelectorAll('tr[data-i]').forEach(function (tr) {
          var it = items[+tr.dataset.i], m = matOf(it.idm);
          function refresh() { if (h.loi === 'Import' && !sheet) { var hs = tr.querySelector('[data-m="hs"]'); it.hs = (it.alloy || 0) + (it.fab || 0); if (hs) hs.value = it.hs ? M.money.fmt(Math.round(it.hs * 1000) / 1000) : ''; }
            tr.querySelector('[data-amt]').value = num(unit(h, it, sheet) * it.tw, 3); sums(); }
          tr.querySelectorAll('[data-k]').forEach(function (el) { el.onchange = el.oninput = function () { it[el.dataset.k] = el.value;
            if (el.dataset.k === 'rate') { it.alloy = alloyPrice(m, el.value); var a = tr.querySelector('[data-m="alloy"]'); if (a) a.value = it.alloy ? M.money.fmt(it.alloy) : ''; refresh(); } }; });
          tr.querySelectorAll('[data-m]').forEach(function (el) { if (el.readOnly) return; var kk = el.dataset.m; M.money.bind(el, function () { it[kk] = M.money.val(el); refresh(); }); });
          tr.querySelector('[data-del]').onclick = function () { items.splice(+tr.dataset.i, 1); grid(); };
        });
        sums();
      }
      function loadItems() { // item diambil dari PR terpilih; jumlah dan pajak awal mengikuti Lokal / Import
        var r = P.pr.filter(function (x) { return x.no === h.no_pr; })[0];
        items = r ? r.det.filter(function (d) { return d.sup === h.id_suplier || !h.id_suplier; }).map(function (d) { var m = matOf(d.idm);
          return {idpr:d.id, idm:d.idm, nm:d.nm, desc:d.ket || '', w:d.w, l:d.l, tw:Math.round(d.tw), rate:'', alloy:0, fab:0, hs:0, disc:0, tax:h.loi === 'Import' ? 0 : 11, note:''}; }) : [];
        grid();
      }
      fillSup(); fillPr(); grid();
      f.sup.onchange = function () { h.id_suplier = f.sup.value; var s = supOf(h.id_suplier);
        if (s) { h.loi = s.loc; f.loi.value = s.loc; h.matauang = s.loc === 'Import' ? 'usd' : 'idr'; f.mu.value = h.matauang; if (!f.term.value && s.pay) f.term.value = s.pay; if (!f.cif.value) f.cif.value = s.loc === 'Import' ? 'CIF' : 'LOCO'; }
        h.no_pr = ''; fillPr(); loadItems(); };
      f.loi.onchange = function () { h.loi = f.loi.value; h.matauang = h.loi === 'Import' ? 'usd' : 'idr'; f.mu.value = h.matauang; items.forEach(function (it) { it.tax = h.loi === 'Import' ? 0 : 11; if (h.loi === 'Import') it.hs = (it.alloy || 0) + (it.fab || 0); }); grid(); };
      f.mu.onchange = function () { h.matauang = f.mu.value; };
      f.pr.onchange = function () { h.no_pr = f.pr.value; loadItems(); };
      M.money.bind(f.kurs, function () { h.nominal_kurs = M.money.val(f.kurs); });
      f.onsubmit = function (e) {
        e.preventDefault(); if (!M.validate(f)) return;
        if (!items.length) return M.swal({title:'Peringatan !', text:'PR belum dipilih atau tidak ada daftar material.', type:'warning'});
        for (var i = 0; i < items.length; i++) { var it = items[i], no = ' (baris ' + (i + 1) + ')';
          if (!(it.tw > 0)) return M.swal({title:'Peringatan !', text:'Total Weight wajib diisi' + no + '.', type:'warning'});
          if (!(unit(h, it, sheet) > 0)) return M.swal({title:'Peringatan !', text:(h.loi === 'Import' && !sheet ? 'Alloy Price / Fab Cost' : 'Unit Price') + ' wajib diisi' + no + '.', type:'warning'}); }
        M.saveFlow({text:'Data Purchase Order akan disimpan.', done:'Data Purchase Order berhasil disimpan.', save:function () {
          var t = totals(h, items, sheet), pr = P.pr.filter(function (x) { return x.no === h.no_pr; })[0];
          var det = items.map(function (it, i) { return {id:h.no + '-' + (i + 1), idpr:it.idpr, idm:it.idm, nm:it.nm, desc:it.desc, w:it.w, l:it.l, tw:it.tw, rate:sheet ? '' : it.rate, alloy:sheet ? 0 : it.alloy, fab:sheet ? 0 : it.fab, hs:unit(h, it, sheet), disc:it.disc, tax:it.tax, jml:unit(h, it, sheet) * it.tw, note:it.note || '', recv:0, inc:0}; });
          var rec = {no:h.no, no_surat:h.no_surat, id_suplier:f.sup.value, loi:f.loi.value, nominal_kurs:M.money.val(f.kurs), tanggal:f.tgl.value, matauang:f.mu.value, expect_tanggal:f.exp.value, term:f.term.value, no_pr:f.pr.value, cif:f.cif.value, delivery_date:f.dlv.value, receiving_person:f.pic.value, note:f.note.value,
            hargatotal:t.h, diskontotal:t.d, taxtotal:t.t, subtotal:t.s, status:1, sheet:sheet, det:det};
          if (edit) { rec.status = o.status; P.po[P.po.indexOf(o)] = rec; } else P.po.push(rec); }, after:list});
      };
    }
    list();
  }
  M.po = {buildPR:buildPR, buildPO:buildPO};
})(Mock);
