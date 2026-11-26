/* Builder halaman penerimaan dan keuangan: Incoming Departemen / Asset, Receive Invoice (DP / Incoming), Request Payment (Checker, Management), Payment List, peta alur. */
(function (M) {
  'use strict';
  var P = M.proc, R = P.R, esc = M.esc, fmt = M.fmt;
  var apprPo = function (p) { return p.status === 2 && !p.close_po; };
  var money = function (v) { return '<span class="num" style="display:block">' + P.num(v) + '</span>'; };
  var sumIn = function (inc) { return inc.items.reduce(function (a, i) { return a + i.qty_in; }, 0); };
  var today = function () { return P.TODAY; };
  var tipeLabel = function (t) { return P.TIPE_REQ[t] || t; };

  /* ===================================================== INCOMING (departemen / asset) ===================================================== */
  function incDoc(inc) {
    var po = P.poByNo(inc.no_po), asset = inc.kind === 'asset';
    var pairs = [['No Trans', inc.kode_trans], ['Tanggal Incoming', M.dstr(inc.tanggal), 1], ['No PO', po ? po.no_surat : inc.no_po], ['Supplier', P.incSupplier(inc)], ['No SJ Supplier', inc.sj], ['PIC', inc.pic], ['Receiver', inc.receiver], ['Catatan', inc.note]];
    if (asset) pairs = pairs.concat([['Department', P.dept(inc.dept)], ['Cost Center', P.cc(inc.cc)], ['Asset baru (tabel asset_new)', (inc.assets || []).join(', ') || '-']]);
    pairs.push(['Dokumen', inc.doc || 'Tidak ada'], ['Receive Invoice', inc.invoiced || 'Belum di-invoice']);
    var cols = [{label:'#', html:function (r, i) { return i + 1; }}, {label:'Nama Barang / Jasa', html:function (r) { return esc(P.up(r.nm)); }}, {label:'Qty Order', cls:'num', html:function (r) { return P.q(r.qty_order); }}, {label:'Qty Diterima', cls:'num', html:function (r) { return P.q(r.qty_in); }}];
    if (asset) cols.push({label:'Status', html:function (r) { return esc(r.status || '-'); }});
    cols.push({label:'Catatan', html:function (r) { return esc(r.ket || '-'); }});
    return P.dl(pairs) + '<div class="sect">Barang</div>' + M.stable(cols, inc.items);
  }
  function incomingPage(host, asset) {
    var kind = asset ? 'asset' : 'dept', title = asset ? 'Incoming Asset' : 'Incoming Departemen';
    function list() {
      host.innerHTML = '<div class="box primary"><div class="box-header"><span id="addw"></span></div><div class="box-body" id="tbl"></div></div>' +
        '<div class="note">Penerimaan barang ' + (asset ? 'asset' : 'dan jasa departemen') + ' berdasarkan <a href="purchase_order_non_material.html">PO Non Material</a> yang sudah <a href="approval_po_non_material.html">Approved</a>. Qty diterima menambah progress penerimaan PO. ' +
        (asset ? 'Setiap unit yang diterima <b>OK</b> membentuk asset baru (tabel <code>asset_new</code>, kode AST-…) yang selanjutnya dikelola di <a href="asset.html">Asset</a>. ' : '') +
        'Incoming yang belum di-invoice muncul di <a href="purchase_order_payment.html">Receive Invoice</a> (tab Incoming).</div>';
      var t = P.tbl(M.$('#tbl'), function () { return R.incoming.filter(function (i) { return i.kind === kind; }).slice().sort(function (a, b) { return a.kode_trans < b.kode_trans ? 1 : -1; }); }, [
        {label:'No Trans', key:'kode_trans'}, {label:'Incoming Date', html:function (r) { return M.dstr(r.tanggal); }, text:function (r) { return r.tanggal; }},
        {label:'No PO / Non-PO', html:function (r) { var p = P.poByNo(r.no_po); return esc(p ? p.no_surat : r.no_po); }, text:function (r) { return r.no_po; }},
        {label:'Sum Barang', html:function (r) { return money(sumIn(r)); }, text:function (r) { return sumIn(r); }}, {label:'PIC', key:'pic'}
      ].concat(asset ? [{label:'Department', html:function (r) { return esc(P.dept(r.dept)); }, text:function (r) { return P.dept(r.dept); }}] : [{label:'Receiver', key:'receiver'}, {label:'Supplier', html:function (r) { return esc(P.incSupplier(r)); }, text:function (r) { return P.incSupplier(r); }}]).concat([
        {label:'Note', html:function (r) { return esc(r.note || '-'); }, text:function (r) { return r.note; }}, {label:'Invoice', html:function (r) { return r.invoiced ? P.pill(r.invoiced, 'ok') : P.pill('Belum', 'warn'); }, text:function (r) { return r.invoiced; }},
        {label:'Detail', html:function (r) { return P.btn('btn-warning', 'view', r.kode_trans, '👁', 'Detail') + P.btn('btn-success', 'print', r.kode_trans, '🖨', 'Print'); }, text:function () { return ''; }, width:'100px'}]),
      function (a, id) { var inc = R.incoming.filter(function (x) { return x.kode_trans === id; })[0]; if (a === 'view') M.modal('Detail ' + title + ' ' + id, incDoc(inc), {size:'xl'}); else P.printPreview(id, '<h3 style="text-align:center">BUKTI PENERIMAAN BARANG (' + (asset ? 'ASSET' : 'DEPARTEMEN') + ')</h3>' + incDoc(inc)); });
      P.addBtn('addw', '＋ Incoming Baru', function () { form(); });
    }
    function form() {
      var sups = {}; R.po.forEach(function (p) { if (apprPo(p) && P.poOutstanding(p, asset).length) sups[p.id_suplier] = 1; });
      var supList = R.supplier.filter(function (s) { return sups[s.id]; });
      if (!supList.length) { P.warn('Tidak ada PO Approved yang masih punya barang ' + (asset ? 'asset' : 'departemen') + ' untuk diterima. Setujui PO dulu di Approval PO Non Material.'); return; }
      var rows = [], po = null;
      host.innerHTML = '<div class="box primary"><div class="box-header"><h3 class="box-title">' + title + ' Baru</h3></div><div class="box-body"><form novalidate id="f"><div class="grid2">' +
        M.fg('Supplier *', '<select name="sup" required>' + M.opts(supList.map(function (s) { return {v:s.id, t:s.nama}; }), '', 'v', 't', 'Pilih Supplier') + '</select>') + M.fg('Nomor PO *', '<select name="po" required><option value="">Pilih supplier dahulu</option></select>') +
        M.fg('No SJ Supplier', '<input type="text" name="sj">') + M.fg('PIC *', '<input type="text" name="pic" required>') + M.fg('Date Transaksi *', '<input type="text" name="tgl" required value="' + today() + '">') +
        (asset ? M.fg('Department *', '<select name="dept" required>' + M.opts(R.dept.map(function (d) { return {v:String(d.id), t:d.nama.toUpperCase()}; }), '', 'v', 't', 'Select An Department') + '</select>') + M.fg('Cost Center', '<select name="cc"></select>') : '') +
        M.fg('Note', '<textarea name="note" rows="2"></textarea>') + M.fg('Upload Document', '<input type="file" name="doc">') + '</div><div class="sect">Barang yang diterima</div><div id="its" class="tbl-wrap"><small>Pilih nomor PO.</small></div>' +
        '<div class="form-actions"><button type="button" class="btn btn-default" id="bk">« Back</button> <button class="btn btn-primary" type="submit">💾 Save</button></div></form></div></div>';
      var f = M.$('#f'); M.$('#bk').onclick = list;
      f.sup.onchange = function () { var l = R.po.filter(function (p) { return apprPo(p) && p.id_suplier === f.sup.value && P.poOutstanding(p, asset).length; }); f.po.innerHTML = '<option value="">-- Pilih --</option>' + l.map(function (p) { return '<option value="' + p.no_po + '">' + esc(p.no_surat) + '</option>'; }).join(''); rows = []; po = null; draw(); };
      f.po.onchange = function () { po = P.poByNo(f.po.value); rows = po ? P.poOutstanding(po, asset).map(function (i) { var sisa = i.qty - (i.qty_in || 0); return {iid:i.iid, nm:i.nm, spec:i.spec, qty_po:i.qty, sisa:sisa, qty_in:sisa, ket:'', status:'OK'}; }) : []; draw(); };
      if (asset) f.dept.onchange = function () { var l = R.cc.filter(function (c) { return String(c.dept) === f.dept.value; }); f.cc.innerHTML = '<option value="">' + (l.length ? '-- Pilih --' : 'List Empty') + '</option>' + l.map(function (c) { return '<option value="' + c.id + '">' + esc(c.nama) + '</option>'; }).join(''); };
      function draw() {
        M.$('#its').innerHTML = rows.length ? '<table class="mini"><thead><tr><th>#</th><th>Nama Barang/Jasa</th><th>Spec</th><th>Qty PO</th><th>Qty Belum Dikirim</th><th width="110">Qty Diterima</th>' + (asset ? '<th width="100">Status</th>' : '') + '<th>Catatan</th></tr></thead><tbody>' +
          rows.map(function (r, i) { return '<tr><td>' + (i + 1) + '</td><td>' + esc(P.up(r.nm)) + '</td><td>' + esc(r.spec || '-') + '</td><td class="num">' + P.q(r.qty_po) + '</td><td class="num">' + P.q(r.sisa) + '</td><td><input type="number" min="0" step="any" data-i="' + i + '" data-k="qty_in" value="' + r.qty_in + '"></td>' +
            (asset ? '<td><select data-i="' + i + '" data-k="status"><option>OK</option><option>NG</option></select></td>' : '') + '<td><input type="text" data-i="' + i + '" data-k="ket"></td></tr>'; }).join('') + '</tbody></table>' : '<small>Pilih nomor PO.</small>';
      }
      M.$('#its').oninput = M.$('#its').onchange = function (e) { var t = e.target; if (t.dataset.k) rows[+t.dataset.i][t.dataset.k] = t.dataset.k === 'qty_in' ? (parseFloat(t.value) || 0) : t.value; };
      f.onsubmit = function (e) {
        e.preventDefault(); if (!M.validate(f)) return;
        var got = rows.filter(function (r) { return r.qty_in > 0; });
        if (!got.length) { P.warn('Isi Qty Diterima minimal satu barang.'); return; }
        var bad = got.filter(function (r) { return r.qty_in > r.sisa + 1e-9; }); if (bad.length) { P.warn('Qty Diterima ' + bad[0].nm + ' melebihi qty belum dikirim.'); return; }
        M.saveFlow({text:'Penerimaan barang akan disimpan.', done:'Process data success. Thanks ...', save:function () {
          var inc = {kode_trans:P.nextNo((asset ? 'TRA' : 'TRS') + P.YM, R.incoming, 'kode_trans', 3), kind:kind, tanggal:f.tgl.value, no_po:po.no_po, sj:f.sj.value, pic:f.pic.value, receiver:(asset ? P.dept(f.dept.value) : 'Gudang') + ' - ' + f.pic.value, note:f.note.value, dept:asset ? +f.dept.value : '', cc:asset ? f.cc.value : '', doc:P.fileName(f.doc), invoiced:'', items:[]};
          got.forEach(function (r) { inc.items.push({iid:r.iid, nm:r.nm, qty_order:r.sisa, qty_in:r.qty_in, ket:r.ket, status:asset ? r.status : undefined}); var pi = po.items.filter(function (x) { return x.iid === r.iid; })[0]; pi.qty_in = (pi.qty_in || 0) + r.qty_in; });
          if (asset) { var n = 0; R.incoming.forEach(function (x) { n += (x.assets || []).length; }); inc.assets = []; got.forEach(function (r) { if (r.status === 'OK') for (var k = 0; k < Math.round(r.qty_in); k++) inc.assets.push('AST-' + P.YM + '-' + P.pad(++n, 4)); }); }
          R.incoming.push(inc);
        }, after:function () { P.after(); list(); }});
      };
    }
    list();
  }
  P.build.incoming_departemen = function (h) { incomingPage(h, false); };
  P.build.incoming_asset_asset = function (h) { incomingPage(h, true); };

  /* ===================================================== RECEIVE INVOICE (satu modul: hanya DP dan Incoming; PO Non Material + PO Material) =====================================================
     Sumber PO: R.po (Non Material, status Approved) dan Mock.seedP.po (Material, status Approved). Satu tabel R.invoice dengan lines[] per PO.
     Aturan: DP = persen DP di TOP x total PO. Incoming = nilai aktual yang diterima (qty diterima x harga PO, biaya kirim ikut proporsional)
     dikurangi potongan DP proporsional (nilai incoming x persen DP). Tidak ada tipe Progress.
     Total invoice per PO tidak boleh melebihi total PO. Retensi tidak ada di TOP. */
  var MAT_TOP = { /* TOP PO Material (belum ada tabel TOP pada data PO Material); selain ini dianggap Incoming 100% */
    P2600997:[{group:'DP', persen:30, ket:'Uang muka'}, {group:'Incoming', persen:70, ket:'Pelunasan setelah barang diterima'}],
    P2601004:[{group:'DP', persen:20, ket:'Uang muka'}, {group:'Incoming', persen:80, ket:'Pelunasan setelah barang diterima'}]
  };
  var DEF_TOP = [{group:'Incoming', persen:100, ket:'Dibayar setelah barang diterima'}];
  var round = function (v, cur) { return cur === 'IDR' ? Math.round(v) : Math.round(v * 100) / 100; };
  var fm = function (v, cur) { var d = !cur || cur === 'IDR' ? 0 : 2; return (d ? cur + ' ' : '') + Number(v || 0).toLocaleString('en-US', {minimumFractionDigits:d, maximumFractionDigits:d}); };
  var uniq = function (v, i, a) { return a.indexOf(v) === i; };
  var SRC = {nm:{t:'NON MATERIAL', c:'#1bb885'}, mat:{t:'MATERIAL', c:'#3c8dbc'}};
  var srcBadge = function (s) { return '<span class="lbl" style="background:' + SRC[s].c + '">' + SRC[s].t + '</span>'; };
  var pill = function (s) { return P.pill(s.t, s.c); };
  var numCell = function (txt) { return '<span class="num" style="display:block">' + txt + '</span>'; };

  function viewNm(p) { var s = P.sup(p.id_suplier); return {key:'nm:' + p.no_po, src:'nm', id:p.no_po, no:p.no_surat, sup:s.nama, bank:s.bank || '', norek:s.norek || '', cur:'IDR', kurs:1, total:P.poCalc(p).total - P.poCalc(p).ppn, totalGross:P.poCalc(p).total, top:P.normTop(p.top), raw:p, tgl:p.tanggal}; }
  function viewMat(o) {
    var s = ((M.seedP && M.seedP.supplier) || []).filter(function (x) { return x.id === o.id_suplier; })[0] || {nama:o.id_suplier};
    return {key:'mat:' + o.no, src:'mat', id:o.no, no:o.no_surat, sup:s.nama, bank:'', norek:'', cur:String(o.matauang || 'idr').toUpperCase(), kurs:o.nominal_kurs || 1, total:o.subtotal - (o.taxtotal || 0), totalGross:o.subtotal, top:P.normTop(MAT_TOP[o.no] || DEF_TOP), raw:o, tgl:o.tanggal};
  }
  P.invPos = function () { /* semua PO Approved dari dua modul PO */
    var out = R.po.filter(apprPo).map(viewNm);
    ((M.seedP && M.seedP.po) || []).forEach(function (o) { if (o.status === 2) out.push(viewMat(o)); });
    return out.sort(function (a, b) { return a.tgl < b.tgl ? 1 : a.tgl > b.tgl ? -1 : (a.no < b.no ? 1 : -1); });
  };
  var pct = function (po, g) { return po.top.filter(function (t) { return t.group === g; }).reduce(function (a, t) { return a + (+t.persen || 0); }, 0); };
  var termVal = function (po, t) { return round(po.total * (+t.persen || 0) / 100, po.cur); };
  var invOfDoc = function (kode) { return R.invoice.filter(function (v) { return v.lines.some(function (l) { return l.kode === kode; }); })[0] || null; };
  var termInv = function (po, idx) { return R.invoice.filter(function (v) { var l = v.lines[0]; return v.tipe !== 'Incoming' && l.src === po.src && l.no_po === po.id && l.top_idx === idx; })[0] || null; };
  var reqOf = function (v) { return v.requested ? R.reqPay.filter(function (r) { return r.id === v.requested; })[0] || null : null; };
  P.invState = function (v) {
    var r = reqOf(v); if (!r) return {t:'Belum diajukan', c:'warn', code:'draft'};
    if (r.rej) return {t:'Request Ditolak', c:'off', code:'rej'};
    var s = P.reqState(r); return {t:s.t, c:s.c, code:r.pay_id ? 'paid' : 'req'};
  };
  var canEdit = function (v) { var r = reqOf(v); return !r || !!r.rej || (!r.chk && !r.mgm && !r.pay_id); }; /* boleh diubah / dihapus selama Waiting Checker atau setelah ditolak */

  /* dokumen incoming dari satu PO: Non Material = R.incoming; Material = penerimaan material (qty diterima dibatasi qty PO) */
  function incDocs(po) {
    if (po.src === 'nm') {
      var netSub = po.raw.items.reduce(function (a, i) { return a + i.qty * i.harga * (1 - (i.disc || 0) / 100); }, 0), kirim = +po.raw.kirim || 0;
      return R.incoming.filter(function (i) { return i.no_po === po.id; }).map(function (i) { var v = P.incValue(i), k = netSub ? kirim * v.sub / netSub : 0; return {kode:i.kode_trans, tgl:i.tanggal, sj:i.sj, sub:v.sub, ppn:v.ppn, kirim:k, gross:round(v.sub + k, 'IDR'), po:po}; });
    }
    var sub = 0, ppn = 0; po.raw.det.forEach(function (d) { var a = Math.min(d.recv || 0, d.tw) * d.hs * (1 - (d.disc || 0) / 100); sub += a; ppn += a * (d.tax || 0) / 100; });
    return sub > 0 ? [{kode:'RCV-' + po.no, tgl:po.raw.tanggal, sj:'Penerimaan material', sub:sub, ppn:ppn, kirim:0, gross:round(sub, po.cur), po:po}] : [];
  }
  function incLine(d) { /* baris invoice Incoming dengan potongan DP proporsional */
    var po = d.po, pd = round(d.gross * pct(po, 'DP') / 100, po.cur), pp = 0;
    return withTax({src:po.src, no_po:po.id, no_surat:po.no, top_idx:-1, kode:d.kode, gross:d.gross, pot_dp:pd, pot_prog:pp, net:round(d.gross - pd - pp, po.cur)}, po.cur);
  }
  P.poLedger = function (po) {
    var o = {DP:0, Incoming:0};
    R.invoice.forEach(function (v) { v.lines.forEach(function (l) { if (l.src === po.src && l.no_po === po.id) o[v.tipe] += l.net; }); });
    o.sum = o.DP + o.Incoming; o.sisa = po.total - o.sum; o.pct = po.total ? Math.round(o.sum / po.total * 1000) / 10 : 0; return o;
  };
  var overCap = function (po, add) { return P.poLedger(po).sum + add > po.total + (po.cur === 'IDR' ? 1 : 0.01); };
  var topText = function (po) { return po.top.map(function (t) { return t.group + ' ' + t.persen + '%'; }).join(' + '); };
  var bankOf = function (name) { var s = R.supplier.filter(function (x) { return x.nama === name; })[0]; return s ? {bank:s.bank, norek:s.norek} : {bank:'', norek:''}; };
  var pendTerm = function (g) { var n = 0; P.invPos().forEach(function (po) { po.top.forEach(function (t, i) { if (t.group === g && !termInv(po, i)) n++; }); }); return n; };
  var pendDocs = function () { var out = []; P.invPos().forEach(function (po) { if (pct(po, 'Incoming') > 0) incDocs(po).forEach(function (d) { if (!invOfDoc(d.kode)) out.push(d); }); }); return out; };
  P.cnt.purchase_order_payment = function () { return pendTerm('DP') + pendDocs().length + R.invoice.filter(function (v) { return P.invState(v).code === 'rej'; }).length; };

  P.retax = function () { /* data lama (sebelum aturan DPP / PPn) dihitung ulang sekali */
    R.invoice.forEach(function (v) { if (v.taxed) return; var pos = P.invPos(), ok = true;
      v.lines.forEach(function (l) { var po = pos.filter(function (x) { return x.src === l.src && x.id === l.no_po; })[0]; if (!po) { ok = false; return; }
        if (l.top_idx >= 0) { var g = termVal(po, po.top[l.top_idx]); l.gross = g; l.net = g; l.pot_dp = 0; l.pot_prog = 0; } else { var d = incDocs(po).filter(function (x) { return x.kode === l.kode; })[0]; if (!d) { ok = false; return; } var n = incLine(d); l.gross = n.gross; l.pot_dp = n.pot_dp; l.pot_prog = n.pot_prog; l.net = n.net; }
        withTax(l, po.cur); });
      if (!ok) return; v.taxed = 1;
      v.nilai = round(sumL(v.lines, 'net'), v.cur); v.dpp = round(sumL(v.lines, 'dpp'), v.cur); v.ppn = round(sumL(v.lines, 'ppn'), v.cur); v.value = round(sumL(v.lines, 'inv'), v.cur); v.idr = Math.round(v.value * v.kurs);
      var r = reqOf(v); if (r && !r.pay_id) r.jumlah = v.idr; });
  };
  function newReq(o) { // buat Request Payment baru (jumlah dalam Rupiah)
    var r = {id:P.nextNo('RP' + P.YM, R.reqPay, 'id', 3), tipe:o.tipe, ref:o.ref, no_invoice:o.no_invoice || '', supplier:o.supplier || '', requester:R.me, tgl:today(), keperluan:o.keperluan, jumlah:Math.round(o.jumlah), tgl_bayar:o.tgl_bayar, ket_po:o.ket_po || '', chk:null, mgm:null, rej:null, pay_id:''};
    r.no_doc = r.id; R.reqPay.push(r); return r;
  }
  function reqModal(title, defKep, cb) { // modal kecil: tanggal pembayaran + keperluan
    var b = M.modal(title, '<form novalidate>' + M.fg('Tanggal Pembayaran *', '<input type="text" name="tb" required placeholder="YYYY-MM-DD" value="2026-11-24">') + M.fg('Keperluan *', '<textarea name="kep" rows="2" required>' + esc(defKep) + '</textarea>') + '<div class="form-actions"><button class="btn btn-primary" type="submit">💰 Ajukan Request Payment</button></div></form>', {size:'sm'});
    var f = b.querySelector('form'); f.onsubmit = function (e) { e.preventDefault(); if (!M.validate(f)) return; cb(f.tb.value, f.kep.value); };
  }
  function doRequest(v, tb, kep) { /* membuat request baru; bila sebelumnya ditolak, request lama tetap tersimpan sebagai riwayat */
    var r = newReq({tipe:'po', ref:v.id, no_invoice:v.no_invoice, supplier:v.sup, keperluan:kep, jumlah:v.idr, tgl_bayar:tb,
      ket_po:v.lines.map(function (l) { return l.no_surat; }).filter(uniq).join(', ') + ' (' + v.tipe + (v.cur !== 'IDR' ? ', ' + fm(v.value, v.cur) + ' @' + P.num(v.kurs) : '') + ')'});
    var old = reqOf(v); if (old && old.rej) old.superseded = r.id;
    v.requested = r.id;
  }
  function mkInvoice(tipe, lines, f, po, rq) {
    var value = sumL(lines, 'inv'), v = {id:P.nextNo('IV' + P.YM, R.invoice, 'id', 3), tipe:tipe, tgl:f.tgl.value, tgl_inv:f.tinv.value, nm_bank:f.nmb.value, no_invoice:f.inv.value, no_faktur:f.fp.value, tgl_faktur:f.tfp.value, sup:po.sup, cur:po.cur, kurs:po.kurs,
      bank:f.bank.value, no_bank:f.nb.value, note:f.note.value, doc:P.fileName(f.doc), requested:'', lines:lines, nilai:round(sumL(lines, 'net'), po.cur), dpp:round(sumL(lines, 'dpp'), po.cur), ppn:round(sumL(lines, 'ppn'), po.cur), taxed:1, value:round(value, po.cur), idr:Math.round(value * po.kurs)};
    R.invoice.push(v); lines.forEach(function (l) { if (l.src === 'nm' && l.kode) { var i = R.incoming.filter(function (x) { return x.kode_trans === l.kode; })[0]; if (i) i.invoiced = v.id; } });
    doRequest(v, f.tb.value, 'Pembayaran ' + tipe + ' ' + lines.map(function (l) { return l.no_surat; }).filter(uniq).join(', '));
    return v;
  }
  /* Pajak: DPP = Nilai x 11/12, PPn = DPP x 12%, Nilai Invoice = DPP + PPn. Nilai = nilai DP / Incoming sebelum PPN (setelah potongan DP). */
  var taxOf = function (n, cur) { var dpp = round(n * 11 / 12, cur), ppn = round(dpp * 0.12, cur); return {dpp:dpp, ppn:ppn, inv:round(dpp + ppn, cur)}; };
  var withTax = function (l, cur) { var t = taxOf(l.net, cur); l.dpp = t.dpp; l.ppn = t.ppn; l.inv = t.inv; return l; };
  var sumL = function (lines, k) { return lines.reduce(function (a, l) { return a + (l[k] || 0); }, 0); };
  var topDays = function (po) { var t = po.src === 'nm' ? P.sup(po.raw.id_suplier).top : '', m = /(\d+)\s*hari/i.exec(t || ''); return m ? +m[1] : 0; };
  var addDays = function (d, n) { var x = new Date(d + 'T00:00:00Z'); x.setUTCDate(x.getUTCDate() + n); return x.toISOString().slice(0, 10); };
  var COMMON = function (sup, po) { return M.fg('Currency', '<input type="text" readonly value="' + esc(po.cur) + '">') + M.fg('Kurs', '<input type="text" readonly value="' + P.num(po.kurs) + '">') + M.fg('Receive Invoice Date *', '<input type="text" name="tgl" required value="' + today() + '">') + M.fg('Nomor Invoice *', '<input type="text" name="inv" required>') +
    M.fg('Invoice Date *', '<input type="text" name="tinv" required placeholder="YYYY-MM-DD" value="' + today() + '">', 'Tanggal pada invoice supplier') + M.fg('Nomor Faktur Pajak', '<input type="text" name="fp">') + M.fg('Tanggal Faktur Pajak', '<input type="text" name="tfp" placeholder="YYYY-MM-DD">') +
    M.fg('Upload Invoice', '<input type="file" name="doc">') + M.fg('Tanggal Pembayaran *', '<input type="text" name="tb" required placeholder="YYYY-MM-DD" value="' + addDays(today(), topDays(po)) + '">', 'Default: hari ini + TOP supplier') + M.fg('Notes', '<textarea name="note" rows="2"></textarea>') +
    '<div class="sect" style="grid-column:1/-1">Informasi Bank</div>' + M.fg('Bank', '<input type="text" name="bank" value="' + esc(sup.bank) + '">') + M.fg('No. Bank', '<input type="text" name="nb" value="' + esc(sup.norek) + '">') + M.fg('Nama (Acc Bank)', '<input type="text" name="nmb" value="' + esc(po.sup) + '">'); };
  var RQ = P.alert('note', 'Saat disimpan, <b>Request Payment otomatis dibuat</b> dan masuk ke Checker.');

  /* ---------- tampilan dokumen ---------- */
  function invLines(v) {
    return M.stable([{label:'No. PO', html:function (l) { return esc(l.no_surat); }}, {label:'Dasar', html:function (l) { return l.kode ? 'Incoming ' + esc(l.kode) : l.top_idx >= 0 ? 'Termin ' + esc(v.tipe) : '-'; }},
      {label:'Nilai Dasar (sebelum PPN)', cls:'num', html:function (l) { return fm(l.gross, v.cur); }}, {label:'Potongan DP (proporsional)', cls:'num', html:function (l) { return l.pot_dp ? fm(l.pot_dp, v.cur) : '-'; }},
      {label:'Nilai', cls:'num', html:function (l) { return fm(l.net, v.cur); }},
      {label:'DPP (Nilai x 11/12)', cls:'num', html:function (l) { return fm(l.dpp, v.cur); }}, {label:'PPn (12% x DPP)', cls:'num', html:function (l) { return fm(l.ppn, v.cur); }}, {label:'Nilai Invoice', cls:'num', html:function (l) { return '<b>' + fm(l.inv, v.cur) + '</b>'; }}], v.lines);
  }
  function invDoc(v) {
    var st = P.invState(v), r = reqOf(v);
    return P.dl([['ID Invoice', v.id], ['Tipe', v.tipe], ['Tgl Invoice', M.dstr(v.tgl), 1], ['No. Invoice', v.no_invoice], ['No. Faktur Pajak', v.no_faktur], ['Tgl Faktur Pajak', v.tgl_faktur ? M.dstr(v.tgl_faktur) : '-', 1], ['Supplier', v.sup], ['Invoice Date', M.dstr(v.tgl_inv || v.tgl), 1], ['Bank', ((v.bank || '') + ' ' + (v.no_bank || '') + (v.nm_bank ? ' a.n. ' + v.nm_bank : '')).trim()], ['Dokumen', v.doc || 'Tidak ada'], ['Catatan', v.note],
      ['Status', st.t], ['Request Payment', v.requested || 'Belum diajukan']]) + (st.code === 'rej' ? P.alert('warnbox', 'Request ' + esc(r.id) + ' ditolak ' + esc(r.rej.by) + ': ' + esc(r.rej.reason) + '. Perbaiki invoice: menyimpan perubahan akan <b>mengirim ulang</b> Request Payment ke Checker.') : '') +
      '<div class="sect">Rincian</div>' + invLines(v) + '<div class="sumbox" style="margin-top:10px"><div><small>Nilai (' + esc(v.cur) + ')</small><b>' + fm(v.nilai, v.cur) + '</b></div><div><small>DPP</small><b>' + fm(v.dpp, v.cur) + '</b></div><div><small>PPn 12% x DPP</small><b>' + fm(v.ppn, v.cur) + '</b></div><div><small>Nilai Invoice (' + esc(v.cur) + ')</small><b>' + fm(v.value, v.cur) + '</b></div>' + (v.cur !== 'IDR' ? '<div><small>Kurs</small><b>' + P.num(v.kurs) + '</b></div>' : '') + '<div><small>Nilai Rupiah</small><b>' + P.num(v.idr) + '</b></div></div>';
  }
  function poDetail(po) {
    var lg = P.poLedger(po), docs = incDocs(po), invs = R.invoice.filter(function (v) { return v.lines.some(function (l) { return l.src === po.src && l.no_po === po.id; }); });
    return P.dl([['No PO', po.no], ['Sumber', SRC[po.src].t], ['Supplier', po.sup], ['Tgl PO', M.dstr(po.tgl), 1], ['Mata Uang', po.cur + (po.cur !== 'IDR' ? ' (kurs ' + P.num(po.kurs) + ')' : '')], ['Total PO (sebelum PPN)', fm(po.total, po.cur)], ['Total PO (termasuk PPN 11%)', fm(po.totalGross, po.cur)]]) +
      '<div class="sect">Term of Payment</div>' + M.stable([{label:'Group', key:'group'}, {label:'Persen', cls:'num', html:function (t) { return t.persen + '%'; }}, {label:'Nilai', cls:'num', html:function (t) { return fm(termVal(po, t), po.cur); }}, {label:'Keterangan', html:function (t) { return esc(t.ket || '-'); }},
        {label:'Invoice', html:function (t, i) { if (t.group === 'Incoming') return '<small>lihat penerimaan di bawah</small>'; var v = termInv(po, i); return v ? esc(v.id) + ' ' + pill(P.invState(v)) : P.pill('Belum', 'warn'); }}], po.top) +
      '<div class="sect">Penerimaan (Incoming)</div>' + (docs.length ? M.stable([{label:'No. Incoming', key:'kode'}, {label:'Tgl', html:function (d) { return M.dstr(d.tgl); }}, {label:'No. SJ', html:function (d) { return esc(d.sj || '-'); }}, {label:'Nilai Incoming', cls:'num', html:function (d) { return fm(d.gross, po.cur); }},
        {label:'Invoice', html:function (d) { var v = invOfDoc(d.kode); return v ? esc(v.id) : P.pill('Belum', 'warn'); }}], docs) : '<small>Belum ada penerimaan.</small>') +
      '<div class="sect">Posisi Tagihan (nilai sebelum PPN)</div><div class="sumbox"><div><small>DP</small><b>' + fm(lg.DP, po.cur) + '</b></div><div><small>Incoming</small><b>' + fm(lg.Incoming, po.cur) + '</b></div><div><small>Sudah di-invoice (' + lg.pct + '%)</small><b>' + fm(lg.sum, po.cur) + '</b></div><div><small>Sisa</small><b>' + fm(lg.sisa, po.cur) + '</b></div></div>' +
      '<div class="sect">Invoice</div>' + (invs.length ? M.stable([{label:'ID', key:'id'}, {label:'Tipe', key:'tipe'}, {label:'No. Invoice', key:'no_invoice'}, {label:'Nilai Invoice', cls:'num', html:function (v) { return fm(v.lines.filter(function (l) { return l.src === po.src && l.no_po === po.id; }).reduce(function (a, l) { return a + (l.inv || 0); }, 0), v.cur); }}, {label:'Status', html:function (v) { return pill(P.invState(v)); }}], invs) : '<small>Belum ada invoice.</small>');
  }

  /* ---------- aksi bersama untuk satu invoice ---------- */
  function invAct(a, v, done) {
    if (a === 'view') M.modal('Invoice ' + v.id, invDoc(v), {size:'xl'});
    else if (a === 'edit') editForm(v, done);
    else if (a === 'del') M.delFlow('Invoice ' + v.id + ' akan dihapus dan Request Payment-nya ditarik. Incoming / termin terkait dapat di-invoice ulang.', 'Invoice berhasil dihapus.', function () {
      var rq = reqOf(v); if (rq && !rq.rej) R.reqPay.splice(R.reqPay.indexOf(rq), 1);
      v.lines.forEach(function (l) { if (l.src === 'nm' && l.kode) { var i = R.incoming.filter(function (x) { return x.kode_trans === l.kode; })[0]; if (i) i.invoiced = ''; } }); R.invoice.splice(R.invoice.indexOf(v), 1); done(); });
  }
  var invBtns = function (v) {
    var rej = P.invState(v).code === 'rej';
    return P.btn('btn-warning', 'view', v.id, '👁', 'View') + (canEdit(v) ? P.btn('btn-primary', 'edit', v.id, rej ? '✎ Perbaiki & Kirim Ulang' : '✎', rej ? 'Perbaiki lalu kirim ulang Request Payment' : 'Edit (selama Waiting Checker)') + (rej ? '' : P.btn('btn-danger', 'del', v.id, '🗑', 'Delete')) : '');
  };
  var invById = function (id) { return R.invoice.filter(function (x) { return x.id === id; })[0]; };
  function editForm(v, done) {
    var rq = reqOf(v), rej = !!(rq && rq.rej);
    var b = M.modal((rej ? 'Perbaiki & Kirim Ulang ' : 'Edit ') + 'Invoice ' + v.id, '<form novalidate>' + (rej ? P.alert('warnbox', 'Request ' + esc(rq.id) + ' ditolak: ' + esc(rq.rej.reason) + '. Setelah disimpan, Request Payment baru otomatis dikirim ke Checker.') : P.alert('note', 'Invoice masih Waiting Checker: perubahan ikut memperbarui Request Payment ' + esc(v.requested || '') + '.')) + '<div class="grid2">' +
      M.fg('Receive Invoice Date *', '<input type="text" name="tgl" required value="' + esc(v.tgl) + '">') + M.fg('Nomor Invoice *', '<input type="text" name="inv" required value="' + esc(v.no_invoice) + '">') + M.fg('Invoice Date *', '<input type="text" name="tinv" required value="' + esc(v.tgl_inv || v.tgl) + '">') +
      M.fg('Nomor Faktur Pajak', '<input type="text" name="fp" value="' + esc(v.no_faktur) + '">') + M.fg('Tanggal Faktur Pajak', '<input type="text" name="tfp" value="' + esc(v.tgl_faktur) + '">') +
      M.fg('Bank', '<input type="text" name="bank" value="' + esc(v.bank) + '">') + M.fg('No. Bank', '<input type="text" name="nb" value="' + esc(v.no_bank) + '">') + M.fg('Nama (Acc Bank)', '<input type="text" name="nmb" value="' + esc(v.nm_bank || v.sup) + '">') +
      M.fg('Tanggal Pembayaran *', '<input type="text" name="tb" required value="' + esc(rq ? rq.tgl_bayar : '') + '">') + M.fg('Upload Invoice', '<input type="file" name="doc">', v.doc ? 'File saat ini: ' + esc(v.doc) : '') + M.fg('Notes', '<textarea name="note" rows="2">' + esc(v.note) + '</textarea>') + '</div>' +
      '<small>Nilai dan rincian tidak dapat diubah. Untuk mengganti incoming / termin, hapus invoice lalu buat ulang (hanya saat Waiting Checker).</small><div class="form-actions"><button class="btn btn-primary" type="submit">💾 ' + (rej ? 'Simpan & Kirim Ulang' : 'Save') + '</button></div></form>', {size:'xl'});
    var f = b.querySelector('form'); f.onsubmit = function (e) { e.preventDefault(); if (!M.validate(f)) return;
      M.saveFlow({text:rej ? 'Invoice diperbaiki dan Request Payment dikirim ulang ke Checker.' : 'Perubahan invoice akan disimpan.', done:rej ? 'Request Payment berhasil dikirim ulang.' : 'Invoice berhasil diubah.', save:function () {
        v.tgl = f.tgl.value; v.tgl_inv = f.tinv.value; v.nm_bank = f.nmb.value; v.no_invoice = f.inv.value; v.no_faktur = f.fp.value; v.tgl_faktur = f.tfp.value; v.bank = f.bank.value; v.no_bank = f.nb.value; v.note = f.note.value; v.doc = P.fileName(f.doc) || v.doc;
        if (rej || !rq) doRequest(v, f.tb.value, 'Pembayaran ' + v.tipe + ' ' + v.lines.map(function (l) { return l.no_surat; }).filter(uniq).join(', ')); else { rq.no_invoice = v.no_invoice; rq.tgl_bayar = f.tb.value; }
      }, after:done}); };
  }

  /* ---------- tampilan mengikuti aplikasi asli (purchase_order_payment/index_incoming): pilih tipe lewat radio, filter Supplier + Cari, tabel per tipe ---------- */
  var supBar = function (names, id) { return '<div class="filter-bar" style="margin:10px 0"><label>Supplier: <select id="sp" style="padding:5px;min-width:240px"><option value="">- Pilih Supplier -</option>' + names.map(function (s) { return '<option>' + esc(s) + '</option>'; }).join('') + '</select></label> <button type="button" class="btn btn-sm btn-primary" id="cari">🔍 Cari</button>' + (id || '') + '</div>'; };
  var payNo = function (v) { var r = v && reqOf(v); return r && r.pay_id ? r.pay_id : '-'; };

  /* ---------- tipe DP (berdasarkan TOP) ---------- */
  function termForm(r, group, done) {
    var po = r.po, val = termVal(po, r.t), tx = taxOf(val, po.cur), sup = po.src === 'nm' ? {bank:po.bank, norek:po.norek} : bankOf(po.sup);
    var b = M.modal('Receive Invoice Down Payment (DP) - ' + po.no, '<form novalidate><div class="grid2">' + M.fg('Nama Supplier', '<input type="text" readonly value="' + esc(po.sup) + '">') + M.fg('Nomor PO', '<input type="text" readonly value="' + esc(po.no) + '">') +
      COMMON(sup, po) + M.fg('Persen ' + group, '<input type="text" readonly value="' + r.t.persen + '%' + (r.t.ket ? ' - ' + esc(r.t.ket) : '') + '">') + M.fg('Total PO sebelum PPN (' + po.cur + ')', '<input type="text" readonly value="' + fm(po.total, po.cur) + '">') + M.fg('Nilai ' + group + ' (' + po.cur + ')', '<input type="text" readonly value="' + fm(val, po.cur) + '">') + M.fg('DPP (Nilai x 11/12)', '<input type="text" readonly value="' + fm(tx.dpp, po.cur) + '">') + M.fg('PPn (12% x DPP)', '<input type="text" readonly value="' + fm(tx.ppn, po.cur) + '">') + M.fg('Nilai Invoice (' + po.cur + ')', '<input type="text" readonly value="' + fm(tx.inv, po.cur) + '">') + '</div>' + RQ +
      '<div class="form-actions"><button class="btn btn-primary" type="submit">💾 Save</button></div></form>', {size:'xl'});
    var f = b.querySelector('form'); f.onsubmit = function (e) { e.preventDefault(); if (!M.validate(f)) return;
      if (overCap(po, val)) { P.warn('Total invoice PO ini akan melebihi nilai PO.'); return; }
      M.saveFlow({text:'Invoice ' + group + ' akan disimpan.', done:'Receive Invoice berhasil disimpan.', save:function () { mkInvoice(group, [withTax({src:po.src, no_po:po.id, no_surat:po.no, top_idx:r.i, kode:'', gross:val, pot_dp:0, pot_prog:0, net:val}, po.cur)], f, po, true); }, after:done}); };
  }
  function termPanel(group, pane, done) {
    var ap = '', all = function () { var out = []; P.invPos().forEach(function (po) { po.top.forEach(function (t, i) { if (t.group === group) out.push({po:po, t:t, i:i, id:po.key + ':' + i, v:termInv(po, i)}); }); }); return out; };
    pane.innerHTML = '<b>Receive Invoice DP</b>' + supBar(all().map(function (r) { return r.po.sup; }).filter(uniq)) + '<div id="tb"></div>';
    var rows = function () { return all().filter(function (r) { return !ap || r.po.sup === ap; }); };
    var t = P.tbl(M.$('#tb'), rows, [
      {label:'No', html:function (r) { return rows().map(function (x) { return x.id; }).indexOf(r.id) + 1; }, text:function () { return ''; }, width:'45px'},
      {label:'No. PO', html:function (r) { return esc(r.po.no) + '<br>' + srcBadge(r.po.src); }, text:function (r) { return r.po.no; }},
      {label:'No. Purchase Invoice', html:function (r) { return r.v ? esc(r.v.id) : '-'; }, text:function (r) { return r.v ? r.v.id : ''; }},
      {label:'No. Invoice', html:function (r) { return r.v ? esc(r.v.no_invoice) : '-'; }, text:function (r) { return r.v ? r.v.no_invoice : ''; }},
      {label:'No. Payment', html:function (r) { return esc(payNo(r.v)); }, text:function (r) { return payNo(r.v); }},
      {label:'Nama Supplier', html:function (r) { return esc(r.po.sup); }, text:function (r) { return r.po.sup; }},
      {label:'Tanggal PO', html:function (r) { return M.dstr(r.po.tgl); }, text:function (r) { return r.po.tgl; }},
      {label:'Keterangan', html:function (r) { return esc(r.t.group) + ' ' + r.t.persen + '%' + (r.t.ket ? ' - ' + esc(r.t.ket) : '') + '<br><small>' + fm(termVal(r.po, r.t), r.po.cur) + '</small>'; }, text:function (r) { return r.t.ket || r.t.group; }},
      {label:'Status', html:function (r) { return r.v ? pill(P.invState(r.v)) : P.pill('Belum di-invoice', 'warn'); }, text:function (r) { return r.v ? P.invState(r.v).t : 'Belum'; }},
      {label:'Action', html:function (r) { return P.btn('btn-info', 'po', r.id, '📄', 'Detail PO dan posisi tagihan') + (r.v ? invBtns(r.v) : P.btn('btn-success', 'rec', r.id, 'Receive Invoice', 'Receive Invoice ' + group)); }, text:function () { return ''; }, width:'300px'}
    ], function (a, id) { var r = rows().filter(function (x) { return x.id === id; })[0]; if (a === 'po') M.modal('Tagihan PO ' + r.po.no, poDetail(r.po), {size:'xl'}); else if (a === 'rec') termForm(r, group, done); else invAct(a, r.v, done); });
    M.$('#cari').onclick = function () { ap = M.$('#sp').value; t.refresh(); };
  }

  /* ---------- tipe Incoming (berdasarkan penerimaan aktual) ---------- */
  function incForm(docs, done) {
    var lines = docs.map(incLine), po0 = docs[0].po, cur = po0.cur, sup = po0.src === 'nm' ? {bank:po0.bank, norek:po0.norek} : bankOf(po0.sup), tot = sumL(lines, 'inv'), pos = {}; docs.forEach(function (d) { pos[d.po.src + ':' + d.po.id] = d.po; });
    var gross = lines.reduce(function (a, l) { return a + l.gross; }, 0), pot = lines.reduce(function (a, l) { return a + l.pot_dp + l.pot_prog; }, 0);
    var noDp = Object.keys(pos).filter(function (k) { var po = pos[k]; return pct(po, 'DP') > 0 && !po.top.some(function (t, i) { return t.group === 'DP' && termInv(po, i); }); });
    var b = M.modal('Receive Invoice Incoming', '<form novalidate><div class="grid2">' + M.fg('Nama Supplier', '<input type="text" readonly value="' + esc(po0.sup) + '">') + M.fg('Nomor Incoming', '<input type="text" readonly value="' + esc(docs.map(function (d) { return d.kode; }).join(', ')) + '">') + COMMON(sup, po0) +
      M.fg('Total Incoming sebelum PPN (' + cur + ')', '<input type="text" readonly value="' + fm(gross, cur) + '">') + M.fg('Potongan DP (proporsional)', '<input type="text" readonly value="' + fm(pot, cur) + '">') + M.fg('Nilai (' + cur + ')', '<input type="text" readonly value="' + fm(sumL(lines, 'net'), cur) + '">') + M.fg('DPP (Nilai x 11/12)', '<input type="text" readonly value="' + fm(sumL(lines, 'dpp'), cur) + '">') + M.fg('PPn (12% x DPP)', '<input type="text" readonly value="' + fm(sumL(lines, 'ppn'), cur) + '">') + M.fg('Nilai Invoice (' + cur + ')', '<input type="text" readonly value="' + fm(tot, cur) + '">') + (cur !== 'IDR' ? M.fg('Nilai Rupiah', '<input type="text" readonly value="' + P.num(tot * po0.kurs) + '">') : '') + '</div>' +
      '<div class="sect">Detail Incoming</div>' + invLines({tipe:'Incoming', cur:cur, lines:lines}) +
      (noDp.length ? P.alert('warnbox', 'DP untuk ' + esc(noDp.map(function (k) { return pos[k].no; }).join(', ')) + ' belum di-invoice-kan. Potongan tetap dihitung proporsional; pastikan invoice DP dibuat di Receive Invoice DP.') : '') + RQ +
      '<div class="form-actions"><button class="btn btn-primary" type="submit">💾 Save</button></div></form>', {size:'xl'});
    var f = b.querySelector('form'); f.onsubmit = function (e) { e.preventDefault(); if (!M.validate(f)) return;
      var over = lines.filter(function (l) { return overCap(pos[l.src + ':' + l.no_po], l.net); }); if (over.length) { P.warn('Total invoice ' + over[0].no_surat + ' akan melebihi nilai PO.'); return; }
      M.saveFlow({text:'Invoice incoming akan disimpan.', done:'Receive Invoice berhasil disimpan.', save:function () { mkInvoice('Incoming', lines, f, po0, true); }, after:done}); };
  }
  function incPanel(pane, done) {
    var sups = []; P.invPos().forEach(function (po) { if (pct(po, 'Incoming') > 0 && sups.indexOf(po.sup) < 0) sups.push(po.sup); });
    pane.innerHTML = '<b>Receive Invoice by Incoming</b>' + supBar(sups, ' <span class="pull-right"><button type="button" class="btn btn-sm btn-danger" id="clr">Clear Checked Invoice</button> <button type="button" class="btn btn-sm btn-success" id="go">Receive Invoice (<span id="nc">0</span>)</button></span>') + '<div id="tb"></div>';
    var set = {}, ap = '', nc = function () { M.$('#nc').textContent = Object.keys(set).length; };
    var rows = function () { return pendDocs().filter(function (d) { return !ap || d.po.sup === ap; }); };
    var t = P.tbl(M.$('#tb'), rows, [
      {label:'', html:function (d) { return '<input type="checkbox" style="width:auto" data-ck="' + esc(d.kode) + '"' + (set[d.kode] ? ' checked' : '') + '>'; }, text:function () { return ''; }, width:'40px'},
      {label:'No', html:function (d) { return rows().map(function (x) { return x.kode; }).indexOf(d.kode) + 1; }, text:function () { return ''; }, width:'45px'},
      {label:'No. Incoming', html:function (d) { return esc(d.kode); }, text:function (d) { return d.kode; }},
      {label:'No. PO', html:function (d) { return esc(d.po.no) + '<br>' + srcBadge(d.po.src); }, text:function (d) { return d.po.no; }},
      {label:'Nominal Incoming (sebelum PPN)', html:function (d) { return numCell(fm(d.gross, d.po.cur)); }, text:function (d) { return d.gross; }},
      {label:'Potongan DP', html:function (d) { var l = incLine(d); return numCell(fm(l.pot_dp + l.pot_prog, d.po.cur)); }, text:function () { return ''; }},
      {label:'Nilai Invoice (DPP + PPn)', html:function (d) { return numCell('<b>' + fm(incLine(d).inv, d.po.cur) + '</b>'); }, text:function (d) { return incLine(d).inv; }},
      {label:'Tanggal Incoming', html:function (d) { return M.dstr(d.tgl); }, text:function (d) { return d.tgl; }},
      {label:'Nama Supplier', html:function (d) { return esc(d.po.sup); }, text:function (d) { return d.po.sup; }}], function () {});
    pane.onchange = function (e) { var c = e.target; if (c.dataset && c.dataset.ck) { if (c.checked) set[c.dataset.ck] = 1; else delete set[c.dataset.ck]; nc(); } };
    M.$('#cari').onclick = function () { ap = M.$('#sp').value; set = {}; nc(); t.refresh(); }; M.$('#clr').onclick = function () { set = {}; nc(); t.refresh(); };
    M.$('#go').onclick = function () {
      var ks = Object.keys(set); if (!ks.length) { P.warn('Pilih minimal satu incoming.'); return; }
      var docs = pendDocs().filter(function (d) { return set[d.kode]; }), sups2 = docs.map(function (d) { return d.po.sup; }).filter(uniq), curs = docs.map(function (d) { return d.po.cur; }).filter(uniq);
      if (sups2.length > 1) { P.warn('Incoming yang dipilih harus dari supplier yang sama.'); return; } if (curs.length > 1) { P.warn('Incoming yang dipilih harus memakai mata uang yang sama.'); return; }
      if (docs.some(function (d) { return d.po.kurs !== docs[0].po.kurs; })) { P.warn('Kurs PO yang dipilih harus sama.'); return; }
      incForm(docs, done);
    };
    nc();
  }

  /* ---------- Daftar Invoice (edit / hapus / perbaiki yang ditolak) ---------- */
  function listPanel(pane, done) {
    pane.innerHTML = '<b>Daftar Invoice</b><div class="filter-bar" style="margin:10px 0"><label>Tipe: <select id="tf" style="padding:5px"><option value="">Semua</option><option>DP</option><option>Incoming</option></select></label> <label style="margin-left:10px">Status: <select id="stf" style="padding:5px"><option value="">Semua</option><option value="rej">Request Ditolak</option><option value="req">Dalam proses approval</option><option value="paid">Sudah dibayar</option></select></label></div><div id="tb"></div>';
    var tf = M.$('#tf'), stf = M.$('#stf');
    var t = P.tbl(M.$('#tb'), function () { return R.invoice.filter(function (v) { return (!tf.value || v.tipe === tf.value) && (!stf.value || P.invState(v).code === stf.value); }).slice().sort(function (a, b) { return a.id < b.id ? 1 : -1; }); }, [
      {label:'No. PO', html:function (v) { return P.bullets(v.lines.map(function (l) { return l.no_surat; }).filter(uniq)); }, text:function (v) { return v.lines.map(function (l) { return l.no_surat; }).join(' '); }},
      {label:'No. Purchase Invoice', key:'id'}, {label:'No. Invoice', key:'no_invoice'}, {label:'No. Payment', html:function (v) { return esc(payNo(v)); }, text:function (v) { return payNo(v); }}, {label:'Nama Supplier', key:'sup'},
      {label:'Tipe', key:'tipe'}, {label:'Tgl Invoice', html:function (v) { return M.dstr(v.tgl); }, text:function (v) { return v.tgl; }},
      {label:'Nominal Invoice', html:function (v) { return numCell(fm(v.value, v.cur)); }, text:function (v) { return v.value; }},
      {label:'Status', html:function (v) { return pill(P.invState(v)); }, text:function (v) { return P.invState(v).t; }}, {label:'Action', html:invBtns, text:function () { return ''; }, width:'290px'}
    ], function (a, id) { invAct(a, invById(id), done); });
    tf.onchange = stf.onchange = function () { t.refresh(); };
  }

  P.build.purchase_order_payment = function (host) { P.retax();
    var mode = 'dp';
    host.innerHTML = '<div class="box primary"><div class="box-header"><h3 class="box-title">Receive Invoice</h3></div><div class="box-body"><p>Select Request Payment</p><div id="rd"></div><div id="dic"></div></div></div><div class="note">Satu modul Receive Invoice untuk <b>semua PO Approved</b> (<a href="approval_po_non_material.html">PO Non Material</a> dan <a href="purchase_order.html">PO Material</a>). Hanya ada dua tipe: <b>DP</b> (persen DP di TOP PO) dan <b>Incoming</b> (nilai aktual yang diterima dari <a href="incoming_departemen.html">Incoming Departemen</a> / <a href="incoming_asset_asset.html">Incoming Asset</a>, dikurangi potongan DP proporsional; mencakup seluruh sisa persen di luar DP). Total invoice per PO tidak boleh melebihi nilai PO. <b>Request Payment otomatis terbentuk</b> saat invoice disimpan dan masuk ke <a href="request_payment_list_approve_checker.html">Checker</a>; tanggal pembayaran wajib diisi (default hari ini + TOP supplier). Invoice dapat diedit atau dihapus selama Waiting Checker; bila ditolak, perbaiki invoice dan menyimpannya akan mengirim ulang request (menu <i>Daftar Invoice</i>).</div>';
    var MODES = [['dp', 'Receive Invoice DP', function () { return pendTerm('DP'); }], ['inc', 'Receive Invoice Incoming', function () { return pendDocs().length; }],
      ['all', 'Daftar Invoice', function () { return R.invoice.filter(function (v) { return P.invState(v).code === 'rej'; }).length; }]];
    function paint() {
      P.after();
      M.$('#rd').innerHTML = MODES.map(function (m) { var n = m[2](); return '<label style="display:block;font-weight:normal;cursor:pointer"><input type="radio" name="checkbx" class="checkbx" value="' + m[0] + '"' + (mode === m[0] ? ' checked' : '') + ' style="width:auto"> ' + m[1] + (n ? ' <span class="lbl" style="background:' + (m[0] === 'all' ? '#dd4b39' : '#f39c12') + '">' + n + (m[0] === 'all' ? ' ditolak' : '') + '</span>' : '') + '</label>'; }).join('');
      var pane = M.$('#dic'); pane.innerHTML = '';
      if (mode === 'dp') termPanel('DP', pane, paint); else if (mode === 'inc') incPanel(pane, paint); else listPanel(pane, paint);
    }
    M.$('#rd').onchange = function (e) { if (e.target.name === 'checkbx') { mode = e.target.value; paint(); } };
    paint();
  };

  /* ===================================================== REQUEST PAYMENT (Checker / Management) ===================================================== */
  var RGRP = [['transportasi', 'Transportasi', ['transportasi']], ['kasbon', 'Kasbon', ['kasbon']], ['expense', 'Expense', ['expense']], ['periodik', 'Periodik', ['periodik']], ['cash', 'Cash', ['cash']], ['po', 'Pembayaran PO', ['po']], ['direct', 'Direct Payment', ['direct_payment']]];
  var waitReq = function (r, stage) { return !r.rej && (stage === 1 ? !r.chk : r.chk && !r.mgm); };
  function reqDoc(r) {
    var rs = P.reqState(r), src = r.tipe === 'po' ? '<a href="purchase_order_payment.html">Receive Invoice ' + esc(r.ref) + '</a>' : '';
    return P.dl([['No Dokumen', r.no_doc || r.id], ['Tipe', tipeLabel(r.tipe)], ['Request By', r.requester], ['Tanggal', M.dstr(r.tgl), 1], ['Keperluan', r.keperluan], ['Supplier / Penerima', r.supplier], ['No Invoice', r.no_invoice], ['Keterangan PO', r.ket_po], ['Nilai Pengajuan', P.idr(r.jumlah)], ['Tanggal Pembayaran', M.dstr(r.tgl_bayar), 1], ['Sumber dokumen', src || '-', 1], ['Status', rs.t]]) +
      '<div class="sect">Riwayat Approval</div>' + M.stable([{label:'Level', key:'l'}, {label:'Status', html:function (x) { return x.a ? (x.a.st === 'Y' ? P.pill('Approved', 'ok') : P.pill('Rejected', 'off')) : P.pill('Waiting', 'blue'); }}, {label:'Oleh', html:function (x) { return esc(x.a ? x.a.by : '-'); }}, {label:'Tanggal', html:function (x) { return esc(x.a ? x.a.date : '-'); }}, {label:'Alasan', html:function (x) { return esc(x.a ? x.a.reason || '-' : '-'); }}],
        [{l:'Checker', a:r.chk}, {l:'Management', a:r.mgm}]) + (r.pay_id ? '<div class="okbox">Sudah dibayar: ' + esc(r.pay_id) + ' (<a href="pembayaran_material_payment_list.html">Payment List</a>)</div>' : '');
  }
  function reqPage(host, stage) { P.retax();
    var nm = stage === 1 ? 'Checker' : 'Management', cur = 0, show = '1';
    function build() { var want = cur;
      host.innerHTML = '<div class="box primary"><div class="box-header"><h3 class="box-title">Request Payment Approval ' + nm + '</h3><span class="pull-right"><label>Tampilkan: <select id="fm" style="padding:5px"><option value="1"' + (show === '1' ? ' selected' : '') + '>Menunggu ' + nm + '</option><option value="0"' + (show === '0' ? ' selected' : '') + '>Semua</option></select></label></span></div><div class="box-body" id="tabs"></div></div>' +
        '<div class="note">' + (stage === 1 ? 'Checker memeriksa kelengkapan dokumen. Yang disetujui menunggu <a href="request_payment_list_approve_management.html">Approval Management</a>. ' : 'Management menyetujui nilai pengajuan. Yang disetujui muncul di <a href="pembayaran_material_payment_list.html">Payment List</a> untuk dibayar. ') + 'Reject mengembalikan pengajuan ke pembuat dengan alasan. Pembayaran PO terbentuk otomatis dari <a href="purchase_order_payment.html">Receive Invoice</a> (invoice diperbaiki di sana dan request terkirim ulang otomatis). Request non-PO (Transportasi, Kasbon, Expense, Periodik, Cash, Direct Payment) terbentuk otomatis saat dokumennya Approved di modul asal; setelah reject, pembuat memperbaiki di modul asal. Ringkasan semua pengajuan ada di <a href="request_payment_index.html">Dashboard Pembayaran</a>.</div>';
      M.$('#fm').onchange = function () { show = M.$('#fm').value; build(); };
      var tabs = M.tabs(M.$('#tabs'), RGRP.map(function (g, gi) {
        var n = R.reqPay.filter(function (r) { return g[2].indexOf(r.tipe) > -1 && waitReq(r, stage); }).length;
        return {label:g[1] + ' (' + n + ')', render:function (pane) { cur = gi; pane.innerHTML = '<div id="tb"></div>';
          P.tbl(M.$('#tb'), function () { return R.reqPay.filter(function (r) { return g[2].indexOf(r.tipe) > -1 && (show === '0' || waitReq(r, stage)); }).slice().sort(function (a, b) { return a.id < b.id ? 1 : -1; }); }, [
            {label:'No Dokumen', html:function (r) { return esc(r.no_doc || r.id); }, text:function (r) { return r.id; }}, {label:'Request By', key:'requester'}, {label:'Tanggal', html:function (r) { return M.dstr(r.tgl); }, text:function (r) { return r.tgl; }},
            {label:'Keperluan', html:function (r) { return esc(r.keperluan) + (r.no_invoice ? '<br><small>Invoice: ' + esc(r.no_invoice) + '</small>' : ''); }, text:function (r) { return r.keperluan + ' ' + r.no_invoice; }}, {label:'Tipe', html:function (r) { return esc(tipeLabel(r.tipe)); }, text:function (r) { return r.tipe; }},
            {label:'Nilai Pengajuan', html:function (r) { return money(r.jumlah); }, text:function (r) { return r.jumlah; }}, {label:'Tanggal Pembayaran', html:function (r) { return M.dstr(r.tgl_bayar); }, text:function (r) { return r.tgl_bayar; }},
            {label:'Status', html:function (r) { var s = P.reqState(r); return P.pill(s.t, s.c); }, text:function (r) { return P.reqState(r).t; }},
            {label:'Action', html:function (r) { return P.btn('btn-warning', 'view', r.id, '👁', 'View') + (waitReq(r, stage) ? P.btn('btn-primary', 'ok', r.id, '✓ Approve', 'Approve') + P.btn('btn-danger', 'no', r.id, '✕', 'Reject') : '') + (r.rej && r.tipe !== 'po' && !r.superseded ? P.btn('btn-default', 'fix', r.id, '↻ Simulasi perbaikan', 'Simulasi: pembuat memperbaiki dokumen di modul asal dan di-approve ulang') : ''); }, text:function () { return ''; }, width:'240px'}
          ], function (a, id) {
            var r = R.reqPay.filter(function (x) { return x.id === id; })[0], lvl = stage === 1 ? 1 : 3;
            if (a === 'view') M.modal('Request Payment ' + id, reqDoc(r), {size:'sm'});
            else if (a === 'fix') M.saveFlow({text:'Simulasi: dokumen asal ' + (r.no_doc || id) + ' diperbaiki dan di-approve ulang, lalu Request Payment baru terbentuk otomatis.', okText:'Ya', done:'Request Payment baru terbentuk dan masuk ke Checker.', save:function () { var n = newReq({tipe:r.tipe, ref:r.ref, no_invoice:r.no_invoice, supplier:r.supplier, keperluan:r.keperluan, jumlah:r.jumlah, tgl_bayar:r.tgl_bayar, ket_po:r.ket_po}); n.no_doc = r.no_doc; n.requester = r.requester; r.superseded = n.id; }, after:function () { P.after(); build(); }});
            else if (a === 'ok') M.saveFlow({text:'Request payment ' + id + ' akan disetujui ' + nm + '.', okText:'Ya, Approve!', done:'Request payment berhasil di-approve.', save:function () { var rec = {st:'Y', by:R.approver[lvl], date:P.NOW, reason:''}; if (stage === 1) r.chk = rec; else r.mgm = rec; }, after:function () { P.after(); build(); }});
            else P.reasonModal('Reject Request Payment ' + id, 'Alasan Reject', 'Reject', function (why) { M.saveFlow({text:'Request payment akan ditolak.', okText:'Ya, Reject!', done:'Request payment berhasil di-reject.', save:function () { var rec = {st:'D', by:R.approver[lvl], date:P.NOW, reason:why}; if (stage === 1) r.chk = rec; else r.mgm = rec; r.rej = {lvl:stage === 1 ? 1 : 2, by:rec.by, date:rec.date, reason:why}; }, after:function () { P.after(); build(); }}); });
          }); } }; }));
      tabs.go(want);
    }
    build();
  }
  P.build.request_payment_list_approve_checker = function (h) { reqPage(h, 1); };
  P.build.request_payment_list_approve_management = function (h) { reqPage(h, 2); };

  /* ===================================================== DASHBOARD PEMBAYARAN (request_payment/index) =====================================================
     Index tidak lagi memproses apa pun: halaman baca-saja untuk Finance yang merangkum semua Request Payment (jalur Invoice PO dan jalur non-PO)
     berdasarkan status, kategori, dan jatuh tempo. Tindakan dilakukan di Checker, Management, dan Payment List. */
  var TIPE_KAT = {po:'Purchase Invoice', kasbon:'Kasbon', expense:'Expense', transportasi:'Transport', periodik:'Periodik', cash:'Cash', direct_payment:'Direct Payment'};
  var KAT_CLS = {'Cash':'info', 'Kasbon':'warn', 'Transport':'blue', 'Periodik':'gray', 'Expense':'ok', 'Purchase Invoice':'off', 'Direct Payment':'gray'};
  var KATS = ['Purchase Invoice', 'Kasbon', 'Expense', 'Transport', 'Periodik', 'Cash', 'Direct Payment'];
  var DST = [{k:'wc', t:'Waiting Checker', c:'warn', href:'request_payment_list_approve_checker.html'}, {k:'wm', t:'Waiting Management', c:'blue', href:'request_payment_list_approve_management.html'},
    {k:'mp', t:'Menunggu Pembayaran', c:'info', href:'pembayaran_material_payment_list.html'}, {k:'sd', t:'Sudah Dibayar', c:'ok', href:'pembayaran_material_payment_list.html'}, {k:'rj', t:'Ditolak', c:'off', href:'request_payment_list_approve_checker.html'}];
  var dstOf = function (k) { return DST.filter(function (x) { return x.k === k; })[0]; };
  var stOf = function (r) { return r.rej ? 'rj' : r.pay_id ? 'sd' : r.mgm ? 'mp' : r.chk ? 'wm' : 'wc'; };
  var payDate = function (r) { var p = R.payment.filter(function (x) { return x.id === r.pay_id; })[0]; return p ? p.tgl : ''; };
  var plusDays = function (d, n) { var x = new Date(d + 'T00:00:00Z'); x.setUTCDate(x.getUTCDate() + n); return x.toISOString().slice(0, 10); };
  P.build.request_payment_index = function (host) { P.retax();
    var f = {from:'2026-01-01', to:P.TODAY, kat:'', st:'', due:''};
    host.innerHTML = '<div class="box primary"><div class="box-header"><h3 class="box-title">Dashboard Pembayaran</h3><span class="pull-right"><button type="button" class="btn btn-sm btn-success" id="xl">📊 Export Excel</button> <button type="button" class="btn btn-sm btn-default" id="rs">⟳ Reset Filter</button></span></div>' +
      '<div class="box-body"><div class="filter-bar"><label>Periode pengajuan: <input type="text" id="ff" style="width:120px" placeholder="YYYY-MM-DD"></label> <label>s/d <input type="text" id="ft" style="width:120px" placeholder="YYYY-MM-DD"></label> ' +
      '<label style="margin-left:10px">Kategori: <select id="fk" style="padding:5px"><option value="">Semua</option>' + KATS.map(function (k) { return '<option>' + k + '</option>'; }).join('') + '</select></label> <button type="button" class="btn btn-sm btn-primary" id="fb">Filter</button> <small id="fa" style="margin-left:8px"></small></div>' +
      '<div class="sumbox" id="cards" style="margin:12px 0"></div><div class="sect">Rincian per kategori dan status</div><div id="mx"></div><div class="sect" id="ttl">Daftar Pengajuan</div><div id="tb"></div></div></div>' +
      '<div class="note">Dashboard Finance untuk memantau jumlah dan nominal pengajuan pembayaran. <b>Hanya membaca</b>: Request Payment terbentuk otomatis dari <a href="purchase_order_payment.html">Receive Invoice</a> (jalur PO) dan dari dokumen non-PO (Kasbon, Expense, Transport, Periodik, PR Cash, Direct Payment) saat Approved di modul asalnya. Persetujuan dilakukan di <a href="request_payment_list_approve_checker.html">Checker</a> dan <a href="request_payment_list_approve_management.html">Management</a>, pembayaran di <a href="pembayaran_material_payment_list.html">Payment List</a>. Klik kartu untuk menyaring daftar; jatuh tempo dihitung dari Tgl Pembayaran untuk pengajuan yang belum dibayar.</div>';
    var F = M.$('#ff'), T = M.$('#ft'), K = M.$('#fk');
    function setFilters() { F.value = f.from; T.value = f.to; K.value = f.kat; M.$('#fa').textContent = 'Periode: ' + f.from + ' s/d ' + f.to + (f.kat ? ' | Kategori: ' + f.kat : ''); }
    var base = function () { return R.reqPay.filter(function (r) { return !r.superseded; }).map(function (r) { return {r:r, st:stOf(r), kat:TIPE_KAT[r.tipe] || r.tipe, tgl:r.tgl, nilai:r.jumlah, tb:r.pay_id ? payDate(r) : r.tgl_bayar}; })
      .filter(function (x) { return (!f.from || x.tgl >= f.from) && (!f.to || x.tgl <= f.to) && (!f.kat || x.kat === f.kat); }); };
    var open = function (x) { return x.st === 'wc' || x.st === 'wm' || x.st === 'mp'; };
    var overdue = function (x) { return open(x) && x.tb && x.tb < P.TODAY; };
    var soon = function (x) { return open(x) && x.tb && x.tb >= P.TODAY && x.tb <= plusDays(P.TODAY, 7); };
    var agg = function (l) { return {n:l.length, v:l.reduce(function (a, x) { return a + x.nilai; }, 0)}; };
    function cards() {
      var all = base(), h = DST.map(function (s) { var a = agg(all.filter(function (x) { return x.st === s.k; })); return card(s.t, a, 'st', s.k, f.st === s.k); }).join('');
      h += card('Lewat Tempo', agg(all.filter(overdue)), 'due', 'over', f.due === 'over') + card('Jatuh Tempo ≤ 7 Hari', agg(all.filter(soon)), 'due', 'soon', f.due === 'soon');
      M.$('#cards').innerHTML = h;
    }
    var card = function (t, a, key, val, on) { return '<div data-f="' + key + '" data-v="' + val + '" style="cursor:pointer;' + (on ? 'outline:2px solid #3c8dbc;' : '') + '"><small>' + esc(t.toUpperCase()) + '</small><b>' + a.n + ' item</b><small>' + P.idr(a.v) + '</small></div>'; };
    function matrix() {
      var all = base(), cell = function (l) { var a = agg(l); return a.n ? a.n + ' item<br><small>' + P.idr(a.v) + '</small>' : '-'; };
      var rows = KATS.concat(['TOTAL']).map(function (k) { return {k:k, l:k === 'TOTAL' ? all : all.filter(function (x) { return x.kat === k; })}; });
      M.$('#mx').innerHTML = M.stable([{label:'Kategori', html:function (r) { return r.k === 'TOTAL' ? '<b>TOTAL</b>' : P.pill(r.k, KAT_CLS[r.k] || 'gray'); }}].concat(DST.map(function (s) { return {label:s.t, cls:'num', html:function (r) { return cell(r.l.filter(function (x) { return x.st === s.k; })); }}; })).concat([{label:'Total', cls:'num', html:function (r) { return cell(r.l); }}]), rows);
    }
    var t;
    var rows = function () { return base().filter(function (x) { return (!f.st || x.st === f.st) && (!f.due || (f.due === 'over' ? overdue(x) : soon(x))); }).sort(function (a, b) { return a.tgl < b.tgl ? 1 : -1; }); };
    function table() {
      M.$('#ttl').textContent = 'Daftar Pengajuan' + (f.st ? ' - ' + dstOf(f.st).t : '') + (f.due ? ' - ' + (f.due === 'over' ? 'Lewat Tempo' : 'Jatuh Tempo ≤ 7 Hari') : '');
      t = P.tbl(M.$('#tb'), rows, [
        {label:'No. Dokumen', html:function (x) { return esc(x.r.no_doc || x.r.id) + '<br><small>' + esc(x.r.id) + '</small>'; }, text:function (x) { return x.r.no_doc || x.r.id; }},
        {label:'Kategori', html:function (x) { return P.pill(x.kat, KAT_CLS[x.kat] || 'gray'); }, text:function (x) { return x.kat; }}, {label:'Request By', html:function (x) { return esc(x.r.requester); }, text:function (x) { return x.r.requester; }},
        {label:'Tgl Pengajuan', html:function (x) { return M.dstr(x.tgl); }, text:function (x) { return x.tgl; }}, {label:'Keperluan', html:function (x) { return esc(x.r.keperluan); }, text:function (x) { return x.r.keperluan; }},
        {label:'Nilai (Rp)', html:function (x) { return '<span class="num" style="display:block">' + P.num(x.nilai) + '</span>'; }, text:function (x) { return x.nilai; }},
        {label:'Tgl Pembayaran', html:function (x) { return (x.tb ? M.dstr(x.tb) : '-') + (overdue(x) ? '<br><small style="color:#dd4b39">lewat tempo</small>' : ''); }, text:function (x) { return x.tb; }},
        {label:'Status', html:function (x) { var s = dstOf(x.st); return P.pill(s.t, s.c); }, text:function (x) { return dstOf(x.st).t; }},
        {label:'Posisi', html:function (x) { var s = dstOf(x.st); return '<a href="' + s.href + '">' + (x.st === 'rj' ? 'Lihat di Checker / Management' : 'Buka halaman') + ' »</a>'; }, text:function () { return ''; }}
      ], function () {});
    }
    function refresh() { P.after(); cards(); matrix(); if (t) t.refresh(); else table(); M.$('#ttl').textContent = 'Daftar Pengajuan' + (f.st ? ' - ' + dstOf(f.st).t : '') + (f.due ? ' - ' + (f.due === 'over' ? 'Lewat Tempo' : 'Jatuh Tempo ≤ 7 Hari') : ''); }
    M.$('#cards').onclick = function (e) { var c = e.target.closest('[data-f]'); if (!c) return; var k = c.dataset.f, v = c.dataset.v; if (k === 'st') { f.st = f.st === v ? '' : v; f.due = ''; } else { f.due = f.due === v ? '' : v; f.st = ''; } refresh(); };
    M.$('#fb').onclick = function () { var a = F.value.trim(), b = T.value.trim(); if (a && b && a > b) { P.warn('Tanggal awal tidak boleh lebih besar dari tanggal akhir.'); return; } f.from = a; f.to = b; f.kat = K.value; setFilters(); refresh(); };
    M.$('#rs').onclick = function () { f = {from:'2026-01-01', to:P.TODAY, kat:'', st:'', due:''}; setFilters(); refresh(); };
    M.$('#xl').onclick = function () { M.toast('Export Excel: ' + rows().length + ' baris (mockup, file tidak diunduh).', 'ok'); };
    setFilters(); cards(); matrix(); table();
  };

  /* ===================================================== PAYMENT LIST ===================================================== */
  var PRT = ['po'];
  function payDoc(p) {
    var rs = p.reqs.map(function (id) { return R.reqPay.filter(function (r) { return r.id === id; })[0]; }).filter(Boolean), bk = R.bank.filter(function (b) { return b.id === p.bank; })[0];
    return P.dl([['No Payment', p.id], ['Jenis', p.jenis === 'pr' ? 'Pembayaran PR (PO)' : 'Pembayaran Non PR'], ['Tanggal Bayar', M.dstr(p.tgl), 1], ['Dibayar dari', bk ? bk.nama : p.bank], ['Penerima / Supplier', p.penerima], ['Mata Uang', p.matauang + ' (kurs ' + p.kurs + ')'], ['Biaya Admin', P.idr(p.admin)], ['Nilai Bayar', P.idr(p.nilai)], ['Keterangan', p.ket]]) +
      '<div class="sect">Request Payment yang dibayar</div>' + M.stable([{label:'No Dokumen', key:'id'}, {label:'Tipe', html:function (r) { return esc(tipeLabel(r.tipe)); }}, {label:'Keperluan', html:function (r) { return esc(r.keperluan); }}, {label:'Nilai', cls:'num', html:function (r) { return P.num(r.jumlah); }}], rs);
  }
  P.build.pembayaran_material_payment_list = function (host) { P.retax();
    var cur = 0, tabs;
    function build() { var want = cur;
      host.innerHTML = '<div class="box primary"><div class="box-header"><span id="addw"></span></div><div class="box-body" id="tabs"></div></div><div class="note">Pembayaran dilakukan atas <b>Request Payment yang sudah Approved Management</b> (<a href="request_payment_list_approve_management.html">Approval Management</a>). Setelah dibayar, status request menjadi <b>Sudah Dibayar</b>. <b>Payment PR</b> = pembayaran PO / invoice / uang muka; <b>Non PR</b> = expense, kasbon, transportasi, periodik, direct payment.</div>';
      tabs = M.tabs(M.$('#tabs'), [['pr', 'Payment PR'], ['nonpr', 'Payment Non PR']].map(function (g, gi) {
        return {label:g[1], render:function (pane) { cur = gi; pane.innerHTML = '<div id="tb"></div>';
          P.tbl(M.$('#tb'), function () { return R.payment.filter(function (p) { return p.jenis === g[0]; }).slice().sort(function (a, b) { return a.id < b.id ? 1 : -1; }); }, [
            {label:'No Payment', key:'id'}, {label:'No Dokumen', html:function (p) { return P.bullets(p.reqs); }, text:function (p) { return p.reqs.join(' '); }}, {label:'Tgl Bayar', html:function (p) { return M.dstr(p.tgl); }, text:function (p) { return p.tgl; }},
            {label:'Requesto / Supplier', key:'penerima'}, {label:'Nilai Bayar', html:function (p) { return money(p.nilai); }, text:function (p) { return p.nilai; }}, {label:'Keterangan', html:function (p) { return esc(p.ket || '-'); }, text:function (p) { return p.ket; }},
            {label:'Option', html:function (p) { return P.btn('btn-warning', 'view', p.id, '👁', 'View') + P.btn('btn-success', 'print', p.id, '🖨', 'Print'); }, text:function () { return ''; }, width:'100px'}
          ], function (a, id) { var p = R.payment.filter(function (x) { return x.id === id; })[0]; if (a === 'view') M.modal('Payment ' + id, payDoc(p), {size:'xl'}); else P.printPreview(id, '<h3 style="text-align:center">BUKTI PEMBAYARAN</h3>' + payDoc(p)); }); } }; }));
      tabs.go(want);
      P.addBtn('addw', '＋ New Payment', jenisModal);
    }
    function jenisModal() {
      var b = M.modal('Pilih Jenis Payment', '<form novalidate>' + M.fg('Jenis Payment *', '<select name="j" required><option value="">- Jenis Payment -</option><option value="pr">Pembayaran PR</option><option value="nonpr">Pembayaran Non PR</option></select>') + '<div class="form-actions"><button class="btn btn-primary" type="submit">Proses</button></div></form>', {size:'sm'});
      var f = b.querySelector('form'); f.onsubmit = function (e) { e.preventDefault(); if (!M.validate(f)) return; M.close(); pick(f.j.value); };
    }
    function pick(j) {
      var av = R.reqPay.filter(function (r) { return r.mgm && !r.pay_id && ((PRT.indexOf(r.tipe) > -1) === (j === 'pr')); }), set = {};
      host.innerHTML = '<div class="box primary"><div class="box-header"><h3 class="box-title">Request Payment Approved - ' + (j === 'pr' ? 'Pembayaran PR' : 'Pembayaran Non PR') + '</h3></div><div class="box-body" id="tb"></div><div class="box-body"><button type="button" class="btn btn-default" id="bk">« Back</button> <button type="button" class="btn btn-warning" id="clr">Clear Checked Payment</button> <button type="button" class="btn btn-primary" id="go">Proses Payment (<span id="nc">0</span>)</button></div></div>';
      function nc() { M.$('#nc').textContent = Object.keys(set).length; }
      var t = P.tbl(M.$('#tb'), function () { return av; }, [
        {label:'Pilih', html:function (r) { return '<input type="checkbox" style="width:auto" data-ck="' + r.id + '"' + (set[r.id] ? ' checked' : '') + '>'; }, text:function () { return ''; }, width:'50px'}, {label:'No. Dokumen', html:function (r) { return esc(r.no_doc || r.id); }, text:function (r) { return r.id; }},
        {label:'Tgl', html:function (r) { return M.dstr(r.tgl); }, text:function (r) { return r.tgl; }}, {label:'Keperluan', html:function (r) { return esc(r.keperluan); }, text:function (r) { return r.keperluan; }}, {label:'Currency', html:function () { return 'IDR'; }, text:function () { return 'IDR'; }},
        {label:'Supplier / Request By', html:function (r) { return esc(r.supplier || r.requester); }, text:function (r) { return r.supplier || r.requester; }}, {label:'Total', html:function (r) { return money(r.jumlah); }, text:function (r) { return r.jumlah; }}], function () {});
      host.onchange = function (e) { var c = e.target; if (c.dataset && c.dataset.ck) { if (c.checked) set[c.dataset.ck] = 1; else delete set[c.dataset.ck]; nc(); } };
      M.$('#bk').onclick = function () { host.onchange = null; build(); }; M.$('#clr').onclick = function () { set = {}; nc(); t.refresh(); };
      M.$('#go').onclick = function () { var ks = Object.keys(set); if (!ks.length) { P.warn('Pilih minimal satu request payment.'); return; } host.onchange = null; form(j, ks); }; nc();
    }
    function form(j, ks) {
      var rs = ks.map(function (id) { return R.reqPay.filter(function (r) { return r.id === id; })[0]; }), total = rs.reduce(function (a, r) { return a + r.jumlah; }, 0), names = []; rs.forEach(function (r) { var n = r.supplier || r.requester; if (names.indexOf(n) < 0) names.push(n); });
      host.innerHTML = '<div class="box primary"><div class="box-header"><h3 class="box-title">Form Payment ' + (j === 'pr' ? 'PR' : 'Non PR') + '</h3></div><div class="box-body"><form novalidate id="f"><div class="grid2">' +
        M.fg('Tanggal Bayar *', '<input type="text" name="tgl" required value="' + today() + '">') + M.fg('Bank / Kas *', '<select name="bank" required>' + M.opts(R.bank.map(function (b) { return {v:b.id, t:b.nama}; }), '', 'v', 't', 'Pilih Bank') + '</select>') +
        M.fg('Dibayar kepada', '<input type="text" name="peng" readonly value="' + esc(names.join(', ')) + '">') + M.fg('Mata Uang / Kurs', '<input type="text" readonly value="IDR / 1">') +
        M.fg('Total Request', '<input type="text" readonly value="' + P.num(total) + '">') + M.fg('Biaya Admin Bank', '<input type="number" name="admin" min="0" value="0">') +
        M.fg('Keterangan', '<textarea name="ket" rows="2">' + esc(rs.map(function (r) { return r.keperluan; }).join('; ')) + '</textarea>') + '</div>' +
        M.stable([{label:'No Dokumen', key:'id'}, {label:'Tipe', html:function (r) { return esc(tipeLabel(r.tipe)); }}, {label:'Keperluan', html:function (r) { return esc(r.keperluan); }}, {label:'Nilai', cls:'num', html:function (r) { return P.num(r.jumlah); }}], rs) +
        '<div class="form-actions"><button type="button" class="btn btn-default" id="bk">« Back</button> <button class="btn btn-primary" type="submit">💾 Save Payment</button></div></form></div></div>';
      var f = M.$('#f'); M.$('#bk').onclick = function () { pick(j); };
      f.onsubmit = function (e) { e.preventDefault(); if (!M.validate(f)) return;
        M.saveFlow({text:'Payment senilai ' + P.idr(total) + ' akan disimpan.', done:'Payment berhasil disimpan.', save:function () {
          var p = {id:P.nextNo('PAYM' + P.YM, R.payment, 'id', 3), tgl:f.tgl.value, bank:f.bank.value, penerima:names.join(', '), reqs:ks.slice(), nilai:total, admin:+f.admin.value || 0, ket:f.ket.value, jenis:j, matauang:'IDR', kurs:1};
          R.payment.push(p); rs.forEach(function (r) { r.pay_id = p.id; });
        }, after:function () { P.after(); cur = j === 'pr' ? 0 : 1; build(); }}); };
    }
    build();
  };

  /* ===================================================== PETA ALUR ===================================================== */
  P.hub = function () {
    var root = M.shell({key:'flow_pengadaan', title:'Peta Alur Pengadaan', sub:'PR sampai Pembayaran', module:'(non_rutin, pr_asset, app_pr_stock, metode_pembelian, purchase_order_non_material, ... pembayaran_material)', crumb:'Pengadaan Non Material'});
    root.innerHTML = '<div class="note">Klik kartu untuk membuka halaman. Angka kuning = jumlah dokumen yang menunggu tindakan pada halaman itu. Data lintas halaman tersimpan di browser; menu <b>Reset data mockup</b> mengembalikan data awal.</div><div id="stg"></div>' +
      '<div class="box" style="margin-top:14px"><div class="box-header"><h3>Alur ringkas</h3></div><div class="box-body"><ol>' +
      '<li><b>PR</b>: tiga sumber. <a href="non_rutin_index.html">PR Departemen</a>; <a href="asset_planning_index_asset.html">Planning Asset</a> → <a href="pr_asset_pr.html">PR Asset</a>; <a href="app_pr_stock_approval_head.html">PR Stock</a>.</li>' +
      '<li><b>Approval PR</b>: Head → Cost Control → Management. Nomor PR sudah ada sejak PR dibuat dan tidak berubah saat approval.</li>' +
      '<li><b>Metode Pembelian - PR</b> → <b>Purchase Order Non Material</b> → <b>Approval PO</b>.</li>' +
      '<li><b>Incoming</b> Departemen / Asset → <b>Receive Invoice</b> (AP atau PO termin) → <b>Request Payment</b> Checker → Management → <b>Payment List</b>.</li></ol></div></div>';
    var st = M.$('#stg'), h = '';
    P.stages.forEach(function (s, i) {
      var pg = P.pages.filter(function (g) { return g[4] === i; });
      h += '<div class="box"><div class="box-header"><h3>' + (i + 1) + '. ' + esc(s) + '</h3></div><div class="box-body"><div class="cards">' + pg.map(function (g) {
        var k = g[0], n = P.cnt[k] ? P.cnt[k]() : 0; return '<a class="card" href="' + k + '.html"><h3>' + esc(g[1]) + (n ? ' <span class="lbl warn">' + n + '</span>' : '') + '</h3><small>modul <code>' + esc(g[2]) + '</code></small><p>' + esc(g[5]) + '</p></a>'; }).join('') + '</div></div></div>';
    });
    st.innerHTML = h;
  };
})(window.Mock);