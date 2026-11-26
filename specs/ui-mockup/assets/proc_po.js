/* Builder halaman pembelian: Metode Pembelian - PR, Purchase Order Non Material, Approval PO Non Material, Receive Invoice & Request UM (PO Material). */
(function (M) {
  'use strict';
  var P = M.proc, R = P.R, esc = M.esc, fmt = M.fmt;

  /* qty_po di setiap item PR = jumlah qty yang sudah masuk PO (hitung ulang dari daftar PO) */
  P.syncQtyPo = function () {
    var m = {}; R.po.forEach(function (p) { p.items.forEach(function (i) { m[i.iid] = (m[i.iid] || 0) + i.qty; }); });
    [R.prDept, R.prAsset, R.prStock].forEach(function (arr) { arr.forEach(function (d) { d.items.forEach(function (i) { i.qty_po = m[i.iid] || 0; }); }); });
  };
  P.syncQtyPo();
  P.poOutstanding = function (p, asset) { return p.items.filter(function (i) { return (i.tipe_pr === 'Asset') === asset && (i.qty_in || 0) < i.qty; }); };
  var apprPo = function (p) { return p.status === 2 && !p.close_po; };
  P.cnt.incoming_departemen = function () { return R.po.filter(function (p) { return apprPo(p) && P.poOutstanding(p, false).length; }).length; };
  P.cnt.incoming_asset_asset = function () { return R.po.filter(function (p) { return apprPo(p) && P.poOutstanding(p, true).length; }).length; };

  var CATC = {asset:'#a9179e', stok:'#ff1ab3', departemen:'#1bb885'};
  var catBadge = function (c) { return '<span class="lbl" style="background:' + CATC[c] + '">' + c.toUpperCase() + '</span>'; };
  var poPrNos = function (p) { var o = []; p.items.forEach(function (i) { if (o.indexOf(i.no_pr) < 0) o.push(i.no_pr); }); return o; };
  var money = function (v) { return '<span class="num" style="display:block">' + P.num(v) + '</span>'; };

  /* ===================================================== METODE PEMBELIAN - PR ===================================================== */
  P.build.metode_pembelian_pr = function (host) {
    host.innerHTML = '<div class="box primary"><div class="box-header"><label>Category: <select id="cat" style="padding:5px"><option value="">ALL CATEGORY</option><option value="asset">ASSET</option><option value="stok">STOK</option><option value="departemen">DEPARTEMEN</option></select></label>' +
      ' <label style="margin-left:12px">Status: <select id="st" style="padding:5px"><option value="">Semua</option><option value="out">Outstanding</option><option value="part">Partial</option><option value="clear">Clear</option></select></label></div><div class="box-body" id="tbl"></div></div>' +
      '<div class="note">Daftar seluruh PR yang sudah <b>Approved Management</b> dari tiga sumber: <a href="non_rutin_approval_management.html">PR Departemen</a>, <a href="pr_asset_approval_management.html">PR Asset</a>, dan <a href="app_pr_stock_approval_management.html">PR Stock</a>. Status <b>Outstanding</b> = belum masuk PO, <b>Partial</b> = sebagian qty sudah masuk PO, <b>Clear</b> = seluruh qty sudah masuk PO. Tombol <b>Buat PO</b> membuka <a href="purchase_order_non_material.html">Purchase Order Non Material</a> dengan PR terpilih.</div>';
    var cat = M.$('#cat'), st = M.$('#st');
    var t = P.tbl(M.$('#tbl'), function () { return P.pool().filter(function (e) { return (!cat.value || e.cat === cat.value) && (!st.value || P.prProgress(e).code === st.value); }); }, [
      {label:'No PR', html:function (r) { return esc(r.no_pr); }, text:function (r) { return r.no_pr; }}, {label:'Tgl PR', html:function (r) { return M.dstr(r.tgl); }, text:function (r) { return r.tgl; }},
      {label:'Departemen', html:function (r) { return esc(P.up(r.dept)); }, text:function (r) { return r.dept; }}, {label:'Category', html:function (r) { return catBadge(r.cat); }, text:function (r) { return r.cat; }},
      {label:'Nama Barang', html:function (r) { return P.bullets(r.items.map(function (i) { return P.up(i.nm); })); }, text:function (r) { return r.items.map(function (i) { return i.nm; }).join(' '); }},
      {label:'By', html:function (r) { return esc(P.up(r.by)); }, text:function (r) { return r.by; }}, {label:'Date', html:function (r) { return esc(r.created); }, text:function (r) { return r.created; }},
      {label:'Status', html:function (r) { var s = P.prProgress(r); return P.pill(s.t, s.c); }, text:function (r) { return P.prProgress(r).t; }},
      {label:'#', html:function (r) { var open = P.prProgress(r).code !== 'clear'; return P.btn('btn-primary', 'detail', r.no_pr, '👁', 'Detail') + (open ? ' <a class="btn btn-sm btn-success" href="purchase_order_non_material.html?pr=' + encodeURIComponent(r.no_pr) + '" title="Buat PO dari PR ini">＋ Buat PO</a>' : ''); }, text:function () { return ''; }, width:'150px'}
    ], function (a, id) {
      var e = P.pool().filter(function (x) { return x.no_pr === id; })[0], pos = P.posOfPr(id);
      M.modal('Detail PR ' + id, P.dl([['No PR', e.no_pr], ['Category', e.cat.toUpperCase()], ['Departemen / Untuk', e.dept], ['Dibuat Oleh', e.by], ['Approved Management', e.created], ['Tgl Dibutuhkan', e.req ? M.dstr(e.req) : '-', 1]]) +
        '<div class="sect">Barang</div>' + M.stable([{label:'#', html:function (r, i) { return i + 1; }}, {label:'Nama Barang', html:function (r) { return esc(P.up(r.nm)); }}, {label:'Spec', html:function (r) { return esc(r.spec || '-'); }}, {label:'Qty PR', cls:'num', html:function (r) { return P.q(r.qty) + ' ' + esc(r.satuan); }},
          {label:'Sudah di PO', cls:'num', html:function (r) { return P.q(r.ref.qty_po || 0); }}, {label:'Sisa', cls:'num', html:function (r) { return P.q(Math.max(0, r.qty - (r.ref.qty_po || 0))); }}, {label:'Est Harga', cls:'num', html:function (r) { return P.num(r.harga); }}], e.items) +
        '<div class="sect">PO terkait</div>' + (pos.length ? pos.map(function (p) { return '<a class="tag chain" href="purchase_order_non_material.html">' + esc(p.no_surat) + '</a> ' + P.pill(P.poState(p).t, P.poState(p).c); }).join(' ') : '<small>Belum ada PO untuk PR ini.</small>'), {size:'xl'});
    });
    cat.onchange = st.onchange = function () { t.refresh(); };
  };

  /* ===================================================== PURCHASE ORDER NON MATERIAL ===================================================== */
  /* TOP hanya menyimpan DP (opsional); sisa persen otomatis menjadi tagihan berdasarkan Incoming. Tidak ada Progress. */
  P.normTop = function (top) { var rows = (top || []).filter(function (t) { return t.group === 'DP' && +t.persen > 0; }).slice(0, 1), s = rows.reduce(function (a, t) { return a + (+t.persen || 0); }, 0), rest = Math.round((100 - s) * 100) / 100; return rest > 0 ? rows.concat([{group:'Incoming', persen:rest, ket:'Sisa ditagih berdasarkan barang yang diterima', auto:true}]) : rows; };
  function topTable(p) {
    var c = P.poCalc(p);
    return M.stable([{label:'Group TOP', key:'group'}, {label:'Persen (%)', cls:'num', html:function (r) { return r.persen; }}, {label:'Value (sebelum PPN)', cls:'num', html:function (r) { return P.num(Math.round((c.total - c.ppn) * r.persen / 100)); }}, {label:'Keterangan', html:function (r) { return esc(r.ket || '-'); }}], P.normTop(p.top));
  }
  function poDoc(p) {
    var c = P.poCalc(p), s = P.sup(p.id_suplier), st = P.poState(p);
    return P.dl([['No PO', p.no_surat], ['Tanggal PO', M.dstr(p.tanggal), 1], ['Supplier', s.nama], ['Lokal / Import', p.loi], ['Mata Uang', p.matauang], ['Price Method', p.cif], ['Delivery Date', M.dstr(p.delivery_date), 1], ['PIC Penerima', p.pic], ['No PR', poPrNos(p).join(', ')], ['Status', st.t], ['Revisi', String(p.revisi)], ['Alasan Reject', p.reject_reason], ['Catatan', p.note]]) +
      '<div class="sect">Item</div>' + M.stable([{label:'#', html:function (r, i) { return i + 1; }}, {label:'No PR', key:'no_pr'}, {label:'Item', html:function (r) { return esc(P.up(r.nm)); }}, {label:'Spec', html:function (r) { return esc(r.spec || '-'); }}, {label:'Qty PO', cls:'num', html:function (r) { return P.q(r.qty) + ' ' + esc(r.satuan); }},
        {label:'Diterima', cls:'num', html:function (r) { return P.q(r.qty_in || 0); }}, {label:'Unit Price', cls:'num', html:function (r) { return P.num(r.harga); }}, {label:'Disc %', cls:'num', html:function (r) { return r.disc; }}, {label:'PPN %', cls:'num', html:function (r) { return r.ppn; }},
        {label:'Total Barang + PPN', cls:'num', html:function (r) { var a = r.qty * r.harga * (1 - r.disc / 100); return P.num(a * (1 + r.ppn / 100)); }}], p.items) +
      '<div class="sumbox" style="margin-top:10px"><div><small>Sub Total</small><b>' + P.num(c.sub) + '</b></div><div><small>Discount</small><b>' + P.num(c.disc) + '</b></div><div><small>PPN</small><b>' + P.num(c.ppn) + '</b></div><div><small>Biaya Kirim</small><b>' + P.num(c.kirim) + '</b></div><div><small>Total Order</small><b>' + P.num(c.total) + '</b></div></div>' +
      '<div class="sect">Term of Payment</div>' + topTable(p);
  }
  function viewPo(p) { M.modal('Detail PO ' + p.no_surat, poDoc(p), {size:'xl'}); }
  function printPo(p) { P.printPreview(p.no_surat, '<h3 style="text-align:center">PURCHASE ORDER</h3>' + poDoc(p)); }

  P.build.purchase_order_non_material = function (host) {
    function list() {
      host.innerHTML = '<div class="box primary"><div class="box-header"><span id="addw"></span></div><div class="box-body" id="tbl"></div></div>' +
        '<div class="note">PO dibuat dari PR yang sudah Approved (<a href="metode_pembelian_pr.html">Metode Pembelian - PR</a>). PO baru berstatus <b>Waiting Approval</b> dan disetujui di <a href="approval_po_non_material.html">Approval PO Non Material</a>. PO ditolak kembali ke sini: edit, simpan, dan Revisi bertambah. Setelah Approved, barang diterima di <a href="incoming_departemen.html">Incoming Departemen</a> / <a href="incoming_asset_asset.html">Incoming Asset</a>, dan termin dibayar lewat <a href="purchase_order_payment.html">Receive Invoice</a>. PO Closed tidak tampil di daftar.</div>';
      var t = P.tbl(M.$('#tbl'), function () { return R.po.filter(function (p) { return !p.close_po; }).slice().sort(function (a, b) { return a.no_po < b.no_po ? 1 : -1; }); }, [
        {label:'No PO', html:function (r) { return esc(r.no_surat); }, text:function (r) { return r.no_surat; }},
        {label:'No PR', html:function (r) { return P.bullets(poPrNos(r)); }, text:function (r) { return poPrNos(r).join(' '); }},
        {label:'No Incoming', html:function (r) { var l = R.incoming.filter(function (i) { return i.no_po === r.no_po; }).map(function (i) { return i.kode_trans; }); return l.length ? P.bullets(l) : '-'; }, text:function (r) { return r.no_po; }},
        {label:'Tanggal PO', html:function (r) { return M.dstr(r.tanggal); }, text:function (r) { return r.tanggal; }},
        {label:'Progress PO', html:function (r) { var s = P.poState(r); return P.pill(s.t, s.c) + (r.status === 2 ? '<br><small>Diterima ' + P.poRecv(r) + '%</small>' : ''); }, text:function (r) { return P.poState(r).t; }},
        {label:'PO Vendor', html:function (r) { return esc(P.up(P.sup(r.id_suplier).nama)); }, text:function (r) { return P.sup(r.id_suplier).nama; }},
        {label:'Harga PO', html:function (r) { return money(P.poCalc(r).total); }, text:function (r) { return P.poCalc(r).total; }}, {label:'Revisi', html:function (r) { return r.revisi; }, text:function (r) { return r.revisi; }},
        {label:'Reject Reason', html:function (r) { return r.reject_reason ? '<span style="color:#dd4b39">' + esc(r.reject_reason) + '</span>' : '-'; }, text:function (r) { return r.reject_reason; }},
        {label:'Action', html:function (r) { return P.btn('btn-warning', 'view', r.no_po, '👁', 'View') + (r.status === 1 ? P.btn('btn-primary', 'edit', r.no_po, '✎', 'Edit') : '') + P.btn('btn-success', 'print', r.no_po, '🖨', 'Print') + P.btn('btn-danger', 'close', r.no_po, '✕', 'Close PO'); }, text:function () { return ''; }, width:'160px'}
      ], function (a, id) {
        var p = P.poByNo(id);
        if (a === 'view') viewPo(p); else if (a === 'print') printPo(p); else if (a === 'edit') form(p, null);
        else P.reasonModal('Closing PO ' + p.no_surat, 'Alasan Close PO', 'Close PO', function (r) { M.saveFlow({text:'PO ' + p.no_surat + ' akan di-close dan hilang dari daftar.', okText:'Ya, Close!', done:'PO berhasil di-close.', save:function () { p.close_po = {reason:r, date:P.NOW}; }, after:function () { P.after(); t.refresh(); }}); });
      });
      P.addBtn('addw', '＋ Create PO', function () { pick([]); });
    }
    /* pilih PR (index_pr) */
    function pick(pre) {
      var set = {}; pre.forEach(function (n) { set[n] = 1; });
      host.innerHTML = '<div class="box primary"><div class="box-header"><h3 class="box-title">Pilih PR untuk Purchase Order</h3><span class="pull-right"><label>Status: <select id="fs" style="padding:5px"><option value="">- Filter Status -</option><option value="out">Outstanding</option><option value="part">Partial</option></select></label></span></div><div class="box-body" id="tbl"></div>' +
        '<div class="box-body"><button type="button" class="btn btn-default" id="bk">« Back</button> <button type="button" class="btn btn-warning" id="clr">Clear Checked PR</button> <button type="button" class="btn btn-primary" id="go">Proses PO (<span id="nc">0</span>)</button></div></div>' +
        '<div class="note">Centang satu atau beberapa PR, lalu <b>Proses PO</b>. Hanya sisa qty yang belum masuk PO yang ikut ke form. PR <b>Clear</b> tidak ditampilkan.</div>';
      var fs = M.$('#fs');
      function nc() { M.$('#nc').textContent = Object.keys(set).length; }
      var t = P.tbl(M.$('#tbl'), function () { return P.pool().filter(function (e) { var s = P.prProgress(e).code; return s !== 'clear' && (!fs.value || s === fs.value); }); }, [
        {label:'Pilih', html:function (r) { return '<input type="checkbox" style="width:auto" data-ck="' + esc(r.no_pr) + '"' + (set[r.no_pr] ? ' checked' : '') + '>'; }, text:function () { return ''; }, width:'50px'},
        {label:'No PR', html:function (r) { return esc(r.no_pr); }, text:function (r) { return r.no_pr; }}, {label:'Tgl PR', html:function (r) { return M.dstr(r.tgl); }, text:function (r) { return r.tgl; }},
        {label:'Category', html:function (r) { return catBadge(r.cat); }, text:function (r) { return r.cat; }}, {label:'Barang', html:function (r) { return P.bullets(r.items.map(function (i) { return P.up(i.nm); })); }, text:function (r) { return r.items.map(function (i) { return i.nm; }).join(' '); }},
        {label:'Requestor', html:function (r) { return esc(r.by); }, text:function (r) { return r.by; }}, {label:'Status', html:function (r) { var s = P.prProgress(r); return P.pill(s.t, s.c); }, text:function (r) { return P.prProgress(r).t; }}
      ], function () {});
      host.onchange = function (e) { var c = e.target; if (c.dataset && c.dataset.ck) { if (c.checked) set[c.dataset.ck] = 1; else delete set[c.dataset.ck]; nc(); } };
      fs.onchange = function () { t.refresh(); };
      M.$('#bk').onclick = function () { host.onchange = null; list(); };
      M.$('#clr').onclick = function () { set = {}; nc(); t.refresh(); };
      M.$('#go').onclick = function () { var k = Object.keys(set); if (!k.length) { P.warn('Pilih minimal satu PR.'); return; } host.onchange = null; form(null, k); };
      nc();
    }
    /* form PO (add / edit) */
    function form(p, prs) {
      var edit = !!p, pool = P.pool(), pmap = {}, rows = [];
      pool.forEach(function (e) { e.items.forEach(function (i) { pmap[i.iid] = {e:e, i:i}; }); });
      if (edit) p.items.forEach(function (it) { var m = pmap[it.iid]; rows.push({iid:it.iid, no_pr:it.no_pr, tipe_pr:it.tipe_pr, nm:it.nm, spec:it.spec, satuan:it.satuan, qty_pr:m ? m.i.qty - (m.i.ref.qty_po || 0) + it.qty : it.qty_pr, qty:it.qty, harga:it.harga, disc:it.disc, ppn:it.ppn, qty_in:it.qty_in || 0, ket:it.ket}); });
      else prs.forEach(function (no) { var e = pool.filter(function (x) { return x.no_pr === no; })[0]; if (!e) return; e.items.forEach(function (i) { var sisa = i.qty - (i.ref.qty_po || 0); if (sisa > 0) rows.push({iid:i.iid, no_pr:e.no_pr, tipe_pr:e.tipe, nm:i.nm, spec:i.spec, satuan:i.satuan, qty_pr:sisa, qty:sisa, harga:i.harga, disc:0, ppn:11, qty_in:0, ket:i.ket}); }); });
      if (!rows.length) { P.warn('PR terpilih tidak punya sisa qty untuk PO.'); list(); return; }
      var o = p ? JSON.parse(JSON.stringify(p)) : {id_suplier:'', loi:'', cif:'LOCO', delivery_date:'', pic:'Gudang', note:'', kirim:0, top:[]};
      host.innerHTML = '<div class="box primary"><div class="box-header"><h3 class="box-title">' + (edit ? 'Edit PO ' + esc(p.no_surat) : 'Create Purchase Order Non Material') + '</h3></div><div class="box-body"><form novalidate id="f"><div class="grid2">' +
        M.fg('Supplier *', '<select name="sup" required>' + M.opts(R.supplier.map(function (s) { return {v:s.id, t:s.nama}; }), o.id_suplier, 'v', 't', 'Pilih Supplier') + '</select>') + M.fg('Lokal / Import', '<input type="text" name="loi" readonly value="' + esc(o.loi) + '">') +
        M.fg('Tanggal PO', '<input type="text" name="tgl" readonly value="' + esc(edit ? p.tanggal : P.TODAY) + '">') + M.fg('Mata Uang', '<input type="text" readonly value="IDR (Rupiah)">') +
        M.fg('Delivery Date *', '<input type="text" name="deliv" required placeholder="YYYY-MM-DD" value="' + esc(o.delivery_date) + '">') + M.fg('Price Method', '<select name="cif">' + M.opts(['CIF', 'FOB', 'LOCO', 'DDU', 'FRANCO'], o.cif) + '</select>') +
        M.fg('PIC Penerima', '<input type="text" name="pic" value="' + esc(o.pic) + '">') + M.fg('Catatan', '<textarea name="note" rows="2">' + esc(o.note) + '</textarea>') + '</div>' +
        (edit && p.reject_reason ? P.alert('warnbox', 'PO ditolak: ' + esc(p.reject_reason) + '. Setelah disimpan, Revisi bertambah dan PO kembali ke Approval PO.') : '') +
        '<div class="sect">Item PO (kosongkan Qty PO atau isi 0 bila item tidak ikut)</div><div class="tbl-wrap"><table class="mini" id="it"></table></div>' +
        '<div class="grid2" style="margin-top:10px"><div>' + M.fg('Biaya Kirim', '<input type="number" name="kirim" min="0" value="' + esc(o.kirim) + '">') + '</div><div class="sumbox" id="sum" style="margin:0"></div></div>' +
        '<div class="sect">Term of Payment (TOP) - isi DP bila ada; sisanya otomatis ditagih berdasarkan Incoming</div><div id="top"></div><div id="topsum" class="eqnote"></div>' +
        '<div class="form-actions"><button type="button" class="btn btn-default" id="bk">« Back</button> <button class="btn btn-primary" type="submit">💾 Save</button></div></form></div></div>';
      var f = M.$('#f'); M.$('#bk').onclick = list;
      M.$('#it').innerHTML = '<thead><tr><th>#</th><th>No PR</th><th>Item</th><th>Spec</th><th>Sisa PR</th><th width="90">PO Qty</th><th>Unit</th><th width="120">Unit Price</th><th width="70">Disc %</th><th width="70">PPN %</th><th>Total Barang + PPN</th></tr></thead><tbody>' +
        rows.map(function (r, i) {
          var inp = function (k) { return '<td><input type="number" min="0" step="any" data-r="' + i + '" data-f="' + k + '" value="' + r[k] + '"></td>'; };
          return '<tr><td>' + (i + 1) + '</td><td>' + esc(r.no_pr) + '</td><td>' + esc(P.up(r.nm)) + '</td><td>' + esc(r.spec || '-') + '</td><td class="num">' + P.q(r.qty_pr) + '</td>' + inp('qty') + '<td>' + esc(r.satuan) + '</td>' + inp('harga') + inp('disc') + inp('ppn') + '<td class="num" id="rv' + i + '"></td></tr>'; }).join('') + '</tbody>';
      var top = o.top.filter(function (x) { return x.group === 'DP'; }).slice(0, 1).map(function (x) { return {group:'DP', persen:x.persen, ket:x.ket}; });
      var dp0 = top[0] || {persen:'', ket:''};
      M.$('#top').innerHTML = '<div class="grid2"><div>' + M.fg('DP (%)', '<input type="number" id="dp" min="0" max="100" step="any" value="' + esc(dp0.persen) + '" placeholder="kosongkan bila tanpa DP">') + '</div><div>' + M.fg('Keterangan DP', '<input type="text" id="dpk" value="' + esc(dp0.ket || '') + '">') + '</div></div>';
      M.$('#top').addEventListener('input', function () { var v = parseFloat(M.$('#dp').value) || 0; top.length = 0; if (v > 0) top.push({group:'DP', persen:v, ket:M.$('#dpk').value}); recalc(); });
      function used() { return rows.filter(function (r) { return r.qty > 0; }); }
      function recalc() {
        var c = P.poCalc({items:used(), kirim:+f.kirim.value || 0});
        rows.forEach(function (r, i) { var a = r.qty * r.harga * (1 - r.disc / 100); M.$('#rv' + i).textContent = P.num(a * (1 + r.ppn / 100)); });
        M.$('#sum').innerHTML = '<div><small>Sub Total</small><b>' + P.num(c.sub) + '</b></div><div><small>Discount</small><b>' + P.num(c.disc) + '</b></div><div><small>PPN</small><b>' + P.num(c.ppn) + '</b></div><div><small>Total Order</small><b>' + P.num(c.total) + '</b></div>';
        var s = top.reduce(function (a, x) { return a + (+x.persen || 0); }, 0);
        M.$('#topsum').innerHTML = 'DP: <b>' + s + '%</b> ' + (s > 100 ? '(melebihi 100%)' : '&middot; Sisa <b>' + (Math.round((100 - s) * 100) / 100) + '%</b> ditagih berdasarkan Incoming') + (top.length ? ' &middot; ' + top.map(function (x) { return esc(x.group) + ' ' + P.num(Math.round((c.total - c.ppn) * (+x.persen || 0) / 100)); }).join(' &middot; ') + ' (nilai sebelum PPN)' : '');
      }
      f.addEventListener('input', function (e) { var t = e.target; if (t.dataset.r != null) rows[+t.dataset.r][t.dataset.f] = parseFloat(t.value) || 0; recalc(); });
      f.addEventListener('click', function () { setTimeout(recalc, 0); }); f.addEventListener('change', function () { setTimeout(recalc, 0); });
      f.sup.onchange = function () { var s = P.sup(f.sup.value); f.loi.value = s.loi || ''; if (!edit && s.top && !f.note.value) f.note.value = 'TOP supplier: ' + s.top; };
      recalc();
      f.onsubmit = function (e) {
        e.preventDefault(); if (!M.validate(f)) return;
        var u = used(); if (!u.length) { P.warn('Isi PO Qty minimal satu item.'); return; }
        var bad = u.filter(function (r) { return r.qty > r.qty_pr + 1e-9 || !(r.harga > 0); });
        if (bad.length) { P.warn('PO Qty tidak boleh melebihi sisa PR dan Unit Price harus lebih dari 0 (' + bad[0].nm + ').'); return; }
        var s = top.reduce(function (a, x) { return a + (+x.persen || 0); }, 0);
        if (top.some(function (x) { return !(+x.persen > 0); })) { P.warn('Persen setiap baris TOP harus lebih dari 0.'); return; }
        if (s > 100) { P.warn('DP tidak boleh melebihi 100% (sekarang ' + s + '%).'); return; }
        M.saveFlow({text:'PO akan disimpan dan menunggu approval.', done:'PO berhasil disimpan.', save:function () {
          var x = p || {no_po:P.nextNo('PO' + P.YM, R.po, 'no_po', 3), tanggal:P.TODAY, matauang:'IDR', kurs:1, status:1, reject_reason:'', revisi:0, close_po:null, by:R.me, created:P.NOW};
          if (!p) { x.no_surat = 'PO-NM/26/XI/' + P.pad(parseInt(x.no_po.slice(-3), 10), 4); R.po.push(x); }
          else if (p.reject_reason) x.revisi = (x.revisi || 0) + 1;
          x.id_suplier = f.sup.value; x.loi = f.loi.value; x.cif = f.cif.value; x.delivery_date = f.deliv.value; x.pic = f.pic.value; x.note = f.note.value; x.kirim = +f.kirim.value || 0; x.status = 1; x.reject_reason = '';
          x.items = u.map(function (r) { return {iid:r.iid, no_pr:r.no_pr, tipe_pr:r.tipe_pr, nm:r.nm, spec:r.spec, satuan:r.satuan, qty_pr:r.qty_pr, qty:r.qty, harga:r.harga, disc:r.disc, ppn:r.ppn, qty_in:r.qty_in, ket:r.ket}; });
          x.top = top.map(function (z) { return {group:z.group, persen:+z.persen, ket:z.ket}; });
          P.syncQtyPo();
        }, after:function () { P.after(); list(); }});
      };
    }
    var pre = P.qs('pr'); if (pre) pick([pre]); else list();
  };

  /* ===================================================== APPROVAL PO NON MATERIAL ===================================================== */
  P.build.approval_po_non_material = function (host) {
    function list() {
      host.innerHTML = '<div class="box primary"><div class="box-header"><h3 class="box-title">Approval PO Non Material</h3><span class="pull-right"><label>Tampilkan: <select id="fm" style="padding:5px"><option value="1">Menunggu approval</option><option value="0">Semua</option></select></label></span></div><div class="box-body" id="tbl"></div></div>' +
        '<div class="note">Approve mengubah PO menjadi <b>Approved</b> sehingga dapat diterima di <a href="incoming_departemen.html">Incoming Departemen</a> / <a href="incoming_asset_asset.html">Incoming Asset</a> dan di-invoice-kan di <a href="purchase_order_payment.html">Receive Invoice</a>. Reject mengembalikan PO ke <a href="purchase_order_non_material.html">Purchase Order Non Material</a> dengan alasan.</div>';
      var fm = M.$('#fm');
      var t = P.tbl(M.$('#tbl'), function () { return R.po.filter(function (p) { return !p.close_po && (fm.value === '0' || (p.status === 1 && !p.reject_reason)); }).slice().sort(function (a, b) { return a.no_po < b.no_po ? 1 : -1; }); }, [
        {label:'No. PO', html:function (r) { return esc(r.no_surat); }, text:function (r) { return r.no_surat; }}, {label:'Tanggal PO', html:function (r) { return M.dstr(r.tanggal); }, text:function (r) { return r.tanggal; }},
        {label:'Supplier', html:function (r) { return esc(P.sup(r.id_suplier).nama); }, text:function (r) { return P.sup(r.id_suplier).nama; }}, {label:'Total Order', html:function (r) { return money(P.poCalc(r).total); }, text:function (r) { return P.poCalc(r).total; }},
        {label:'Created By', html:function (r) { return esc(r.by); }, text:function (r) { return r.by; }}, {label:'Created Date', html:function (r) { return esc(r.created); }, text:function (r) { return r.created; }},
        {label:'Status', html:function (r) { var s = P.poState(r); return P.pill(s.t, s.c); }, text:function (r) { return P.poState(r).t; }},
        {label:'Action', html:function (r) { return r.status === 1 && !r.reject_reason ? P.btn('btn-info', 'app', r.no_po, '✓ Approval', 'Approval PO') : P.btn('btn-warning', 'view', r.no_po, '👁', 'View'); }, text:function () { return ''; }, width:'120px'}
      ], function (a, id) { var p = P.poByNo(id); if (a === 'view') viewPo(p); else detail(p); });
      fm.onchange = function () { t.refresh(); };
    }
    function detail(p) {
      host.innerHTML = '<div class="box primary"><div class="box-header"><h3 class="box-title">Approval PO ' + esc(p.no_surat) + '</h3></div><div class="box-body">' + poDoc(p) + '<form novalidate>' + M.fg('Reject Reason', '<textarea name="reason" rows="2" placeholder="Wajib diisi bila Reject"></textarea>') +
        '<div class="form-actions"><button type="button" class="btn btn-default" id="b">« Back</button> <button type="button" class="btn btn-danger" id="r">✕ Reject</button> <button type="button" class="btn btn-success" id="a">✓ Approve</button></div></form></div></div>';
      var f = host.querySelector('form'); M.$('#b').onclick = list;
      M.$('#a').onclick = function () { M.saveFlow({text:'PO ' + p.no_surat + ' akan disetujui.', okText:'Ya, Approve!', done:'PO has been Approved!', save:function () { p.status = 2; p.reject_reason = ''; }, after:function () { P.after(); list(); }}); };
      M.$('#r').onclick = function () { var r = f.reason.value.trim(); f.reason.classList.toggle('err', !r); if (!r) { P.warn('Reject Reason wajib diisi.'); return; }
        M.saveFlow({text:'PO ' + p.no_surat + ' akan ditolak dan dikembalikan ke pembuat.', okText:'Ya, Reject!', done:'PO has been Rejected!', save:function () { p.status = 1; p.reject_reason = r; }, after:function () { P.after(); list(); }}); };
    }
    list();
  };

})(window.Mock);
