/* Builder bersama untuk penawaran (reguler & slitting) dan approval revisi.
   Alur mengikuti aplikasi: Add header -> simpan -> langsung ke halaman detail/{no_penawaran} -> Add item (form dengan tabel PRICELIST). */
(function (M) {
  'use strict';
  var S = M.seedS, esc = M.esc;
  var FERR = /^(SUS|HOT ROLLED|COLD ROLLED|SECC|SPC|SGCC|SPH|SS)/i;
  var DASAR = ['Book Price', 'LME 10 Hari', 'LME 30 Hari', 'LME SPOT'];
  var KURS = [['spot', 'Kurs Terbaru'], ['10', 'Kurs 10 Hari'], ['30', 'Kurs 30 Hari']];

  /* ---------- kurs, profit, pricelist ---------- */
  function kursSorted() { return S.kurs.slice().sort(function (a, b) { return a.tgl < b.tgl ? 1 : a.tgl > b.tgl ? -1 : (b.id - a.id); }); }
  function kursVal(mode) {
    var s = kursSorted(); if (mode === 'spot' || !mode) return s[0].nominal;
    var d0 = new Date(s[0].tgl + 'T00:00:00'); d0.setDate(d0.getDate() - (+mode)); var lim = d0.toISOString().slice(0, 10);
    var r = s.filter(function (x) { return x.tgl >= lim; }); return r.reduce(function (a, x) { return a + x.nominal; }, 0) / r.length;
  }
  function refProfit(material, forecast) {
    var alloy = FERR.test(material || '') ? 'I2000001' : 'I2000002', f = +forecast || 0, hit = null;
    S.profitRange.forEach(function (p) { if (p.alloy === alloy && f > p.minimum && (p.maksimum == null || f <= p.maksimum)) hit = p; });
    return hit ? hit.profit : 0;
  }
  function plFor(material) {
    var n = S.plNfr.filter(function (x) { return x.nama === material; })[0]; if (n) return {t:'nfr', r:n};
    var f = S.plFr.filter(function (x) { return x.nama === material; })[0]; return f ? {t:'fr', r:f} : null;
  }
  function plValue(pl, dasar, kurs) { // Rp per kg; Non Ferrous: nilai pricelist (USD) x kurs
    if (!pl) return 0;
    if (pl.t === 'fr') return pl.r.bottom;
    if (dasar === 'Book Price') return pl.r.b; // sudah Rp (baris "Dolar" di aplikasi kosong untuk Book Price)
    var usd = {'LME 10 Hari':pl.r.b10, 'LME 30 Hari':pl.r.b30, 'LME SPOT':pl.r.bspot}[dasar] || 0;
    return usd * kursVal(kurs);
  }
  function plTable(pl) {
    if (!pl) return '<div class="note" style="text-align:center">Price List Untuk Material Ini Belum Terinput (Book Price dan LME)</div>';
    if (pl.t === 'fr') return M.stable([{key:'k', label:'Keterangan'}, {label:'Book Price', cls:'num', html:function (r) { return 'Rp. ' + M.fmt(r.v, 0) + ',-'; }}], [{k:'Ferrous (Rupiah)', v:pl.r.bottom}]);
    var rows = KURS.map(function (k) { var kv = kursVal(k[0]); return {k:k[1], kurs:'Rp. ' + M.fmt(kv, 0) + ',-', v:DASAR.map(function (d) { return 'Rp. ' + M.fmt(plValue(pl, d, k[0]), 0) + ',-'; })}; });
    var usd = [pl.r.b, pl.r.b10, pl.r.b30, pl.r.bspot];
    return M.stable([{key:'k', label:'Keterangan'}, {key:'kurs', label:'Kurs'}, {label:'Book Price', cls:'num', html:function (r) { return r.v[0]; }}, {label:'LME 10 Hari', cls:'num', html:function (r) { return r.v[1]; }}, {label:'LME 30 Hari', cls:'num', html:function (r) { return r.v[2]; }}, {label:'LME SPOT', cls:'num', html:function (r) { return r.v[3]; }}],
      rows.concat([{k:'Dolar', kurs:'-', v:['-'].concat(usd.slice(1).map(function (u) { return '$. ' + M.fmt(u, 2) + ',-'; }))}]));
  }

  /* ---------- turunan status (Open / Closed(SPK) / Closed manual; revisi 0-3) ---------- */
  var PLAN = [
    {st:'N', spk:-1, rev:0}, {st:'N', spk:-1, rev:0}, {st:'N', spk:0, rev:1}, {st:'N', spk:1, rev:2}, {st:'N', spk:2, rev:1},
    {st:'Y', spk:-1, rev:0, ket:'Customer batal order'}, {st:'N', spk:3, rev:0}, {st:'N', spk:-1, rev:0}, {st:'N', spk:4, rev:3}
  ];
  var PICS = ['Bpk. Andi', 'Ibu Sari', 'Bpk. Hendra'];
  function derive(rows, spkRows) {
    rows.forEach(function (p, i) {
      if (p._d) return; p._d = 1;
      var pl = PLAN[i % PLAN.length];
      p.status = pl.st; p.rev = pl.rev; p.ket = pl.ket || '';
      p.spk = pl.spk >= 0 && spkRows[pl.spk] ? spkRows[pl.spk].surat : null;
      p.pic = p.pic || PICS[i % 3]; p.email = p.email || 'purchasing' + (i + 1) + '@' + p.cust.toLowerCase().replace(/[^a-z]+/g, '').slice(0, 12) + '.example';
      if (p.spk) p.status = 'N';
    });
  }
  function revBadge(n) { return [M.pill('Tidak Ada Revisi', 'purple'), M.pill('Menunggu Approval Revisi', 'orange'), M.pill('Revisi Disetujui', 'ok'), M.pill('Revisi Ditolak', 'off')][n] || ''; }
  function statBadge(p) { return p.spk ? M.pill('Closed', 'ok') : p.status === 'Y' ? M.pill('Closed', 'off') : M.pill('Open', 'warn'); }
  function ketText(p) { return p.spk ? p.spk : (p.ket || ''); }
  function hargaCust(d) { return d.harga_cust != null ? d.harga_cust : d.harga; }
  /* Pricelist Roundup (K42): Pricelist Rp/Kg dibulatkan KE ATAS ke kelipatan Rp100; hanya dihitung di Penawaran */
  function roundup(v) { return Math.ceil(Math.round(v * 100) / 10000) * 100; }
  function itemNilai(d) { return d.nilai != null ? d.nilai : hargaCust(d) * (d.forecast || 0); }
  /* item baru (dari pricelist) di bawah Bottom Price ditandai dan ikut Approval Direktur (K42, K43) */
  function below(d) { return !!d.id_dt_pricelist && d.bottom != null && hargaCust(d) < d.bottom; }
  function totalQ(r) { return r.det.reduce(function (a, d) { return a + itemNilai(d); }, 0); }
  /* Diskon per penawaran, persen ATAU Rp yang saling terkonversi terhadap Total Quotation (K43) */
  function dstate(r) {
    var tot = totalQ(r), d = r.disc || {pct:0, rp:0, mode:'pct'}, pct, rp;
    if (d.mode === 'rp') { rp = d.rp || 0; pct = tot ? rp / tot * 100 : 0; } else { pct = d.pct || 0; rp = Math.round(tot * pct / 100); }
    pct = Math.round(pct * 100) / 100;
    return {tot:tot, pct:pct, rp:rp, net:tot - rp};
  }
  function belowN(r) { return r.det.filter(below).length; }
  /* Approval Direktur diperlukan bila Diskon > 5% atau ada item di bawah Bottom Price (K43) */
  function needApv(r) { return dstate(r).pct > 5 || belowN(r) > 0; }
  var PK = 'metalsindo_pen_disc_v1';
  function pload() { try { return JSON.parse(localStorage.getItem(PK)) || null; } catch (e) { return null; } }
  function psave(r) { var m = pload() || {}; m[r.no] = {disc:r.disc || null, apv:r.apv || null, surat:r.surat, cust:r.cust, tgl:r.tgl}; try { localStorage.setItem(PK, JSON.stringify(m)); } catch (e) {} }
  function papply(rows, withStub) {
    var m = pload();
    if (!m) { // contoh awal: satu penawaran menunggu approval diskon (Diskon 8%)
      m = {}; var c = rows.filter(function (r) { return r.status === 'N' && !r.spk && r.det.length; })[0];
      if (c) { var t0 = totalQ(c); c.disc = {pct:8, rp:Math.round(t0 * 0.08), mode:'pct'}; c.apv = {status:'MENUNGGU', tgl:'2026-11-12', oleh:'Sales', tot:t0, pct:8, rp:c.disc.rp, below:belowN(c)}; psave(c); }
      m = pload() || {};
    }
    rows.forEach(function (r) { var x = m[r.no]; if (x) { r.disc = x.disc || r.disc; r.apv = x.apv || null; } });
  }
  var APVL = {MENUNGGU:['Menunggu Approval Direktur', 'orange'], DISETUJUI:['Diskon Disetujui', 'ok'], DITOLAK:['Ditolak (revisi penawaran)', 'off']};
  function apvText(r) { var st = r.apv && r.apv.status; return st ? APVL[st][0] : needApv(r) ? 'Perlu diajukan' : 'Tidak perlu'; }
  function apvBadge(r) { var st = r.apv && r.apv.status; return st ? M.pill(APVL[st][0], APVL[st][1]) : needApv(r) ? M.pill('Perlu diajukan', 'warn') : M.pill('Tidak perlu', 'gray'); }

  function build(page, cfg) {
    var rows = cfg.rows, isSlit = cfg.type === 'slitting';
    derive(rows, cfg.spkRows); papply(rows, cfg.apvDiskon);
    var root = document.createElement('div'); page.appendChild(root);

    /* ===== halaman daftar ===== */
    function list() {
      root.innerHTML = '<div class="box primary"><div class="box-header"><span id="addw"></span></div><div class="box-body" id="tbl"></div></div>';
      function addBtn() { var on = M.perm.Add && !cfg.approval && !cfg.apvDiskon; M.$('#addw').innerHTML = on ? '<button class="btn btn-success btn-sm" id="add">＋ Add</button>' : ''; if (on) M.$('#add').onclick = function () { header(null); }; }
      addBtn(); M.onPerm(addBtn);
      var t = M.table(M.$('#tbl'), {rows:function () { return cfg.apvDiskon ? rows.filter(function (p) { return p.apv && p.apv.status === 'MENUNGGU'; }) : cfg.approval ? rows.filter(function (p) { return p.rev === 1; }) : rows; }, cols:cfg.apvDiskon ? apvCols() : [
        {key:'surat', label:'No.Penawaran'}, {key:'cust', label:'Nama Customer'}, {key:'tgl', label:'Tanggal Inquiry', html:function (r) { return M.dstr(r.tgl); }, text:function (r) { return r.tgl; }},
        {label:'Status', html:statBadge, text:function (r) { return r.spk || r.status === 'Y' ? 'Closed' : 'Open'; }}, {label:'Revisi', html:function (r) { return revBadge(r.rev); }, text:function (r) { return ['Tidak Ada Revisi', 'Menunggu Approval Revisi', 'Revisi Disetujui', 'Revisi Ditolak'][r.rev]; }},
        {label:'Ket Closing', html:function (r) { return esc(ketText(r)) || '-'; }, text:ketText}, {label:'Approval Diskon', html:apvBadge, text:apvText},
        {label:'Action', html:function (r) { return actions(r); }, text:function () { return ''; }}], onAction:function (a, id) { var r = rows.filter(function (x) { return x.no === id; })[0]; act(a, r, t); }});
      if (cfg.apvDiskon) page.insertAdjacentHTML('beforeend', '<div class="note">Antrean <b>Approval Diskon Penawaran oleh Direktur</b>: penawaran dengan <b>Diskon lebih dari 5%</b> atau item yang <b>Price Quotation-nya di bawah Bottom Price</b>. Jika disetujui, diskon berlaku; jika ditolak, penawaran <b>dikembalikan ke halaman Penawaran untuk direvisi</b> Sales.</div>');
      if (cfg.approval) page.insertAdjacentHTML('beforeend', '<div class="note">Hanya penawaran dengan status revisi "Menunggu Approval Revisi". Setelah disetujui, penawaran dapat direvisi dari halaman Penawaran; jika ditolak, revisi tidak diizinkan.</div>');
    }
    function btn(cls, a, id, ic, ttl) { return ' <button class="btn btn-sm ' + cls + '" data-a="' + a + '" data-id="' + esc(id) + '" title="' + ttl + '">' + ic + '</button>'; }
    function actions(r) {
      var x = '', open = r.status === 'N' && !r.spk, has = !!r.spk;
      if (cfg.apvDiskon) return M.perm.Manage ? btn('btn-primary', 'apvd', r.no, '✓ Approval Diskon', 'Approval Diskon') : '';
      if (cfg.approval) return M.perm.Manage ? btn('btn-primary', 'apv', r.no, '⟲ Approval Revisi', 'Approval Revisi') : '';
      if (M.perm.View) x += btn('btn-warning', 'view', r.no, '👁', 'View');
      if (M.perm.Manage && r.status === 'N' && !has) x += btn('btn-success', 'edit', r.no, '✎', 'Edit');
      if (M.perm.View && open) x += btn('btn-primary', 'detail', r.no, '▦', 'Detail');
      if (M.perm.View) x += btn('btn-info', 'print', r.no, '🖶', 'Print');
      if (M.perm.Manage && open) x += btn('btn-purple', 'close', r.no, '✓', 'Close Penawaran');
      if (M.perm.Manage) x += btn('btn-success', 'copy', r.no, '⎘', 'Copy');
      if (M.perm.Manage && has && r.rev === 0) x += btn('btn-primary', 'revisi', r.no, '⟲', 'Ajukan Revisi');
      if (M.perm.Manage && has && r.rev === 2) x += btn('btn-primary', 'detail', r.no, '☰', 'Revisi');
      return x;
    }
    function act(a, r, t) {
      if (a === 'view') viewHeader(r); else if (a === 'edit') header(r); else if (a === 'detail') detail(r);
      else if (a === 'print') M.toast('Cetak penawaran ' + r.surat + ' (PDF, simulasi)');
      else if (a === 'close') closing(r, t);
      else if (a === 'copy') M.saveFlow({text:'Penawaran akan disalin sebagai penawaran baru.', okText:'Ya, Salin!', done:'Penawaran berhasil disalin.', save:function () {
        var c = JSON.parse(JSON.stringify(r)); c.no = 'P26' + String(3100 + rows.length).padStart(5, '0'); c.surat = r.surat.replace(/\d+$/, String(3100 + rows.length)); c.tgl = new Date().toISOString().slice(0, 10); c.status = 'N'; c.rev = 0; c.spk = null; c.ket = ''; rows.unshift(c); t.refresh(); }});
      else if (a === 'revisi') M.saveFlow({text:'Pengajuan revisi akan dikirim untuk di-approve.', okText:'Ya, Ajukan!', done:'Pengajuan revisi terkirim.', save:function () { r.rev = 1; t.refresh(); }});
      else if (a === 'apv') apv(r, t);
      else if (a === 'apvd') apvd(r, t);
    }
    function apvCols() {
      var q = function (r) { var a = r.apv || {}; return a; };
      return [{key:'surat', label:'No.Penawaran'}, {key:'cust', label:'Nama Customer'}, {label:'Tanggal', html:function (r) { return M.dstr(r.tgl); }, text:function (r) { return r.tgl; }},
        {label:'Total Quotation (Rp)', html:function (r) { return '<span class="num" style="display:block">' + M.fmt(q(r).tot || 0, 0) + '</span>'; }, text:function (r) { return q(r).tot || 0; }},
        {label:'Diskon (%)', html:function (r) { return '<span class="num" style="display:block">' + M.fmt(q(r).pct || 0, 2) + '</span>'; }, text:function (r) { return q(r).pct || 0; }},
        {label:'Diskon (Rp)', html:function (r) { return '<span class="num" style="display:block">' + M.fmt(q(r).rp || 0, 0) + '</span>'; }, text:function (r) { return q(r).rp || 0; }},
        {label:'Item di bawah Bottom Price', html:function (r) { return '<span class="num" style="display:block">' + (q(r).below || 0) + '</span>'; }, text:function (r) { return q(r).below || 0; }},
        {label:'Diajukan', html:function (r) { return M.dstr(q(r).tgl) + ' oleh ' + esc(q(r).oleh || 'Sales'); }, text:function (r) { return q(r).tgl || ''; }},
        {label:'Action', html:function (r) { return actions(r); }, text:function () { return ''; }}];
    }
    /* Direktur memutuskan approval diskon (K43): ditolak -> kembali ke Penawaran untuk direvisi */
    function apvd(r, t) {
      var a = r.apv || {}, bl = r.det.filter(below);
      var b = M.modal('Approval Diskon Penawaran', '<dl class="dl"><dt>No.Penawaran</dt><dd>' + esc(r.surat) + '</dd><dt>Customer</dt><dd>' + esc(r.cust) + '</dd><dt>Total Quotation</dt><dd>' + M.idr(a.tot || 0) + '</dd><dt>Diskon</dt><dd><b>' + M.fmt(a.pct || 0, 2) + '%</b> (' + M.idr(a.rp || 0) + ')</dd><dt>Total setelah Diskon</dt><dd><b>' + M.idr((a.tot || 0) - (a.rp || 0)) + '</b></dd></dl>' +
        (bl.length ? '<div class="sect">Item di bawah Bottom Price</div>' + M.stable([{key:'material', label:'Material'}, {label:'Bottom Price (Rp/Kg)', cls:'num', html:function (d) { return M.fmt(d.bottom, 0); }}, {label:'Price Quotation (Rp/Kg)', cls:'num', html:function (d) { return M.fmt(hargaCust(d), 0); }}], bl) : (a.below ? '<div class="note">' + a.below + ' item di bawah Bottom Price (rincian pada halaman Penawaran).</div>' : '')) +
        '<form novalidate>' + M.fg('Catatan', '<textarea name="cat" placeholder="Wajib diisi bila ditolak"></textarea>') + '<div class="form-actions"><button type="button" class="btn btn-success" data-d="DISETUJUI">✓ Setujui</button><button type="button" class="btn btn-danger" data-d="DITOLAK">✕ Tolak</button></div></form>', {size:'sm'});
      var f = b.querySelector('form');
      b.querySelectorAll('[data-d]').forEach(function (x) { x.onclick = function () { var d = x.dataset.d;
        if (d === 'DITOLAK' && !f.cat.value.trim()) { M.validate(f); f.cat.classList.add('err'); f.cat.parentNode.insertAdjacentHTML('beforeend', '<div class="ferr">Catatan wajib diisi bila ditolak</div>'); return; }
        M.saveFlow({text:d === 'DISETUJUI' ? 'Diskon penawaran akan disetujui.' : 'Diskon ditolak dan penawaran dikembalikan ke Sales untuk direvisi.', okText:d === 'DISETUJUI' ? 'Ya, Setujui!' : 'Ya, Tolak!', done:d === 'DISETUJUI' ? 'Diskon disetujui.' : 'Diskon ditolak; penawaran dikembalikan untuk direvisi.', save:function () {
          r.apv = Object.assign({}, r.apv, {status:d, catatan:f.cat.value, putusOleh:'Direktur', tglPutus:new Date().toISOString().slice(0, 10)}); psave(r); t.refresh(); }}); }; });
    }
    function apv(r, t) {
      var b = M.modal('Approval Revisi Penawaran', '<dl class="dl"><dt>No.Penawaran</dt><dd>' + esc(r.surat) + '</dd><dt>Customer</dt><dd>' + esc(r.cust) + '</dd><dt>SPK Terkait</dt><dd>' + esc(r.spk || '-') + '</dd><dt>Total Nilai</dt><dd>' + M.idr(total(r)) + '</dd></dl>' +
        '<form novalidate>' + M.fg('Alasan (jika ditolak)', '<textarea name="alasan"></textarea>') + '<div class="form-actions"><button type="button" class="btn btn-success" data-d="2">✓ Approve</button><button type="button" class="btn btn-danger" data-d="3">✕ Reject</button></div></form>');
      b.querySelectorAll('[data-d]').forEach(function (x) { x.onclick = function () { var d = +x.dataset.d;
        M.saveFlow({text:d === 2 ? 'Revisi penawaran akan disetujui.' : 'Revisi penawaran akan ditolak.', okText:d === 2 ? 'Ya, Setujui!' : 'Ya, Tolak!', done:d === 2 ? 'Revisi disetujui.' : 'Revisi ditolak.', save:function () { r.rev = d; t.refresh(); }}); }; });
    }
    function closing(r, t) {
      var b = M.modal('Closing Penawaran', '<form novalidate>' + M.fg('Alasan Closing', '<textarea name="ket" required placeholder="Mis. customer batal / harga tidak cocok"></textarea>') + '<div class="form-actions"><button class="btn btn-primary" type="submit">Save</button></div></form>', {size:'sm'});
      var f = b.querySelector('form'); f.onsubmit = function (e) { e.preventDefault(); if (!M.validate(f)) return;
        M.saveFlow({text:'Penawaran akan di-close.', done:'Penawaran berhasil di-close.', save:function () { r.status = 'Y'; r.ket = f.ket.value; t.refresh(); }}); };
    }
    function total(r) { return r.det.reduce(function (a, d) { return a + hargaCust(d) * (d.forecast || 0); }, 0); }
    function viewHeader(r) {
      M.modal('Detail Penawaran', '<dl class="dl"><dt>No.Penawaran</dt><dd>' + esc(r.surat) + ' <small>(' + esc(r.no) + ')</small></dd><dt>Tanggal</dt><dd>' + M.dstr(r.tgl) + '</dd><dt>Customer</dt><dd>' + esc(r.cust) + '</dd><dt>Sales</dt><dd>' + esc(r.sales) + '</dd><dt>Email / PIC</dt><dd>' + esc(r.email) + ' / ' + esc(r.pic) + '</dd>' +
        '<dt>Kurs</dt><dd>' + esc(r.mu) + '</dd><dt>Valid Until</dt><dd>' + M.dstr(r.valid) + '</dd><dt>Term Of Payment</dt><dd>' + esc(r.terms) + '</dd><dt>Exclude VAT</dt><dd>' + r.ppn + '%</dd><dt>Pengiriman</dt><dd>' + esc(r.kirim) + '</dd>' +
        '<dt>Status</dt><dd>' + statBadge(r) + ' ' + revBadge(r.rev) + '</dd></dl><div class="sect">Item</div>' + M.stable([{key:'material', label:'Nama Material'}, {label:'Harga Penawaran', cls:'num', html:function (d) { return M.fmt(hargaCust(d), 0); }}], r.det), {size:'sm'});
    }

    /* ===== header: tambah / edit. Simpan header baru -> langsung ke halaman detail ===== */
    function header(r) {
      var edit = !!r, now = new Date().toISOString().slice(0, 10), n = 3100 + rows.length;
      r = r || {cust:'', sales:'', mu:'IDR', valid:'', terms:'', ppn:11, kirim:'Franco', pic:'', email:'', det:[]};
      var b = M.modal(edit ? 'Edit Header Penawaran' : 'Tambah Header Penawaran', '<form novalidate><div class="grid2">' +
        M.fg('NO.Penawaran', '<input type="text" readonly value="' + (edit ? esc(r.surat) : 'Q-MP/26/XI/' + String(n).padStart(4, '0') + ' (otomatis)') + '">') + M.fg('Tanggal', '<input type="text" readonly value="' + (edit ? esc(r.tgl) : now) + '">') +
        M.fg('Customer', '<select name="cust" required>' + M.opts(S.customer, r.cust, 'nama', 'nama') + '</select>') + M.fg('Sales / Marketing', '<select name="sales" required>' + M.opts(['Rama', 'Dewi', 'Budi'], r.sales) + '</select>') +
        M.fg('Email', '<input type="text" name="email" required value="' + esc(r.email) + '">') + M.fg('PIC Customer', '<input type="text" name="pic" required value="' + esc(r.pic) + '">') +
        M.fg('Kurs', '<select name="mu">' + M.opts(['IDR', 'USD'], r.mu) + '</select>') + M.fg('Pengiriman', '<select name="kirim">' + M.opts(['Franco', 'Ex-Works'], r.kirim) + '</select>') +
        M.fg('Valid Until', '<input type="text" name="valid" required value="' + esc(r.valid || '') + '" placeholder="YYYY-MM-DD">') + M.fg('Term Of Payment', '<input type="text" name="terms" required value="' + esc(r.terms || '') + '">') +
        M.fg('Exclude VAT (%)', '<input type="number" name="ppn" required value="' + r.ppn + '">') + M.fg('Note', '<textarea name="note"></textarea>') + '</div>' +
        '<div class="form-actions"><button class="btn btn-primary" type="submit">💾 Save</button></div></form>', {size:'xl'});
      var f = b.querySelector('form');
      f.cust.onchange = function () { if (!f.email.value && f.cust.value) { f.email.value = 'purchasing@' + f.cust.value.toLowerCase().replace(/[^a-z]+/g, '').slice(0, 12) + '.example'; f.pic.value = f.pic.value || PICS[0]; } };
      f.onsubmit = function (e) { e.preventDefault(); if (!M.validate(f)) return;
        M.saveFlow({text:'Header penawaran akan disimpan.', done:'Header penawaran berhasil disimpan.', save:function () {
          var cu = S.customer.filter(function (c) { return c.nama === f.cust.value; })[0];
          if (edit) { r.cust = f.cust.value; r.id_cust = cu.id; r.sales = f.sales.value; r.email = f.email.value; r.pic = f.pic.value; r.mu = f.mu.value; r.valid = f.valid.value; r.terms = f.terms.value; r.ppn = +f.ppn.value; r.kirim = f.kirim.value; edited = r; }
          else { edited = {no:'P26' + String(n).padStart(5, '0'), surat:'Q-MP/26/XI/' + String(n).padStart(4, '0'), tgl:now, cust:f.cust.value, id_cust:cu.id, type:cfg.type, det:[], valid:f.valid.value, terms:f.terms.value, ppn:+f.ppn.value, kirim:f.kirim.value, mu:f.mu.value, sales:f.sales.value, email:f.email.value, pic:f.pic.value, status:'N', rev:0, spk:null, ket:'', _d:1}; rows.unshift(edited); isNew = true; } },
          after:function () { if (isNew) { isNew = false; detail(edited); } else list(); }}); };
    }
    var edited = null, isNew = false;

    /* ===== halaman detail penawaran (penawaran/detail/{no_penawaran}) ===== */
    function detail(r) {
      var open = r.status === 'N' && !r.spk;
      var ro = function (l, v) { return M.fg(l, '<input type="text" readonly value="' + esc(v) + '">'); };
      root.innerHTML = '<div class="box primary"><div class="box-body"><div style="text-align:center"><h3 style="margin:4px 0 14px">' + (isSlit ? 'Penawaran Slitting' : 'Penawaran') + '</h3></div><div class="grid2">' +
        ro('NO.Penawaran', r.surat) + ro('Tanggal', r.tgl) + ro('CUSTOMER', r.cust) + ro('SALES/MARKETING', r.sales) + ro('EMAIL', r.email) + ro('PIC CUSTOMER', r.pic) + '</div></div>' +
        '<div class="box-header"><span id="addw"></span><button class="btn btn-primary btn-sm" id="back">Back</button><span class="pull-right">' + statBadge(r) + ' ' + revBadge(r.rev) + '</span></div><div class="box-body" id="items"></div><div class="box-body" id="disc"></div></div>';
      M.$('#back').onclick = list;
      function addBtn() { M.$('#addw').innerHTML = M.perm.Add && open ? '<button class="btn btn-success btn-sm" id="add">＋ Add</button>' : ''; if (M.perm.Add && open) M.$('#add').onclick = function () { item(r, null, t); }; }
      addBtn(); M.onPerm(addBtn);
      var t = M.table(M.$('#items'), {rows:function () { return r.det; }, cols:[
        {key:'material', label:'Nama Material'}, {label:'Pricelist Roundup (Rp/Kg)', html:function (d) { return '<span class="num" style="display:block">' + (d.roundup != null ? M.fmt(d.roundup, 0) : '-') + '</span>'; }, text:function (d) { return d.roundup || 0; }}, {label:'Price Quotation (Rp/Kg)', html:function (d) { return '<span class="num" style="display:block">' + M.fmt(hargaCust(d), 0) + '</span>' + (below(d) ? '<span class="lbl warn" title="Di bawah Bottom Price ' + M.fmt(d.bottom, 0) + '; ikut Approval Direktur">di bawah Bottom</span>' : ''); }, text:function (d) { return hargaCust(d); }}, {label:'Nilai Item', html:function (d) { return '<span class="num" style="display:block">' + (d.nilai != null ? M.fmt(d.nilai, 0) : '-') + '</span>'; }, text:function (d) { return d.nilai || 0; }}],
        actions:open ? {view:1, edit:1, del:1} : {view:1}, onAction:function (a, id) { var d = r.det.filter(function (x) { return String(x.id) === id; })[0];
          if (a === 'view') viewItem(d); else if (a === 'edit') item(r, d, t); else M.delFlow('Item penawaran akan dihapus.', 'Item berhasil dihapus.', function () { r.det.splice(r.det.indexOf(d), 1); t.refresh(); }); }});
      M.$('#items').insertAdjacentHTML('beforeend', '<div class="note">' + (open ? 'Klik Add untuk menambah material. Pricelist (Bottom Price) diambil dari pricelist yang sudah dirilis Management (Rp/Kg, berlaku semua customer), dibulatkan ke atas ke kelipatan Rp100 menjadi <b>Pricelist Roundup</b>. <b>Price Quotation</b> default-nya Pricelist Roundup dan dapat diedit (boleh di bawah Bottom Price, ditandai dan ikut Approval Direktur). Nilai Roll = Price Quotation × Kg order; Sheet = Price Quotation × Berat/Sheet × Qty sheet. Pilihan Dasar Harga, Komisi, dan Profit % lama tidak lagi dipakai.' : 'Penawaran tidak berstatus Open sehingga item tidak dapat diubah.') + '</div>');
      var rf = t.refresh; t.refresh = function () { rf(); renderDisc(r, open); };
      renderDisc(r, open);
    }
    /* Diskon per penawaran + status Approval Direktur (K43) */
    function renderDisc(r, open) {
      var el = M.$('#disc'); if (!el) return;
      var s = dstate(r), bn = belowN(r), a = r.apv || null, st = a && a.status;
      if (st === 'DISETUJUI' && (a.tot !== s.tot || a.pct !== s.pct || a.below !== bn)) { r.apv = a = null; st = null; psave(r); M.toast('Item atau diskon berubah setelah disetujui; approval Direktur perlu diajukan ulang.'); }
      var lock = !open || st === 'MENUNGGU', need = needApv(r);
      var info = need ? '<div class="warnbox">Memerlukan <b>Approval Direktur</b>: ' + [s.pct > 5 ? 'diskon ' + M.fmt(s.pct, 2) + '% (lebih dari 5%)' : '', bn ? bn + ' item di bawah Bottom Price' : ''].filter(Boolean).join(' dan ') + '.</div>' : '<div class="okbox">Tidak memerlukan approval (diskon sampai dengan 5% dan tidak ada item di bawah Bottom Price).</div>';
      el.innerHTML = '<div class="sect" style="text-align:center">DISKON PENAWARAN DAN APPROVAL (per penawaran)</div><div class="grid2">' +
        M.fg('Total Quotation (Rp)', '<input type="text" readonly value="' + M.fmt(s.tot, 0) + '">', 'Jumlah nilai seluruh item dari Price Quotation') +
        M.fg('Item di bawah Bottom Price', '<input type="text" readonly value="' + bn + '">') +
        M.fg('Diskon (%)', '<input type="number" step="any" min="0" max="100" name="dpct"' + (lock ? ' readonly' : '') + ' value="' + (s.pct || '') + '">', 'Input persen atau Rp; yang lain terhitung otomatis') +
        M.fg('Diskon (Rp)', '<input type="text" inputmode="decimal" autocomplete="off" name="drp"' + (lock ? ' readonly' : '') + ' value="' + (s.rp ? M.money.fmt(s.rp) : '') + '" placeholder="0">', 'Pemisah ribuan otomatis') +
        M.fg('Total setelah Diskon (Rp)', '<input type="text" readonly name="dnet" value="' + M.fmt(s.net, 0) + '">') +
        M.fg('Approval Diskon', '<div style="padding-top:6px">' + apvBadge(r) + (st === 'DITOLAK' ? ' <small>Catatan Direktur: ' + esc(a.catatan || '-') + '. Silakan revisi penawaran lalu ajukan ulang.</small>' : st === 'DISETUJUI' ? ' <small>oleh ' + esc(a.putusOleh || 'Direktur') + ', ' + M.dstr(a.tglPutus) + '</small>' : '') + '</div>') + '</div>' + info +
        '<div class="form-actions">' + (!lock && M.perm.Manage ? '<button class="btn btn-primary" type="button" id="dsave">💾 Simpan Diskon</button> ' : '') + (!lock && M.perm.Manage && need && st !== 'DISETUJUI' ? '<button class="btn btn-success" type="button" id="dapv">➤ Ajukan Approval Direktur</button>' : '') + '</div>';
      var pctEl = el.querySelector('[name=dpct]'), rpEl = el.querySelector('[name=drp]'), net = el.querySelector('[name=dnet]'), mode = (r.disc && r.disc.mode) || 'pct';
      function upd() { var pct = +pctEl.value || 0, rp = M.money.val(rpEl); net.value = M.fmt(s.tot - rp, 0); return {pct:Math.round(pct * 100) / 100, rp:rp}; }
      pctEl.oninput = function () { mode = 'pct'; rpEl.value = s.tot ? M.money.fmt(Math.round(s.tot * (+pctEl.value || 0) / 100)) : ''; upd(); };
      M.money.bind(rpEl, function () { mode = 'rp'; pctEl.value = s.tot ? Math.round(M.money.val(rpEl) / s.tot * 10000) / 100 : 0; upd(); });
      function save() { var v = upd(); r.disc = {pct:v.pct, rp:v.rp, mode:mode}; psave(r); }
      var sv = el.querySelector('#dsave'), ap = el.querySelector('#dapv');
      if (sv) sv.onclick = function () { save(); M.toast('Diskon tersimpan.'); renderDisc(r, open); };
      if (ap) ap.onclick = function () {
        save(); var s2 = dstate(r), bn2 = belowN(r);
        if (!(s2.pct > 5 || bn2)) { M.toast('Tidak memerlukan approval.'); renderDisc(r, open); return; }
        M.saveFlow({text:'Penawaran akan diajukan ke Direktur untuk approval diskon.', okText:'Ya, Ajukan!', done:'Pengajuan approval diskon terkirim ke Direktur.', save:function () {
          r.apv = {status:'MENUNGGU', tgl:new Date().toISOString().slice(0, 10), oleh:r.sales || 'Sales', tot:s2.tot, pct:s2.pct, rp:s2.rp, below:bn2}; psave(r); renderDisc(r, open); }});
      };
    }
    function viewItem(d) {
      M.modal('Detail Penawaran', '<dl class="dl"><dt>Material</dt><dd>' + esc(d.material) + '</dd><dt>Maker</dt><dd>' + esc(d.maker || '-') + '</dd><dt>Bentuk</dt><dd>' + esc(d.bentuk) + '</dd><dt>Thickness / Density</dt><dd>' + d.thickness + ' / ' + esc(d.density) + '</dd><dt>Width x Length</dt><dd>' + esc(d.width) + ' x ' + esc(d.length) + '</dd>' +
        '<dt>Forecast / Bulan</dt><dd>' + M.fmt(d.forecast, 0) + ' Kg</dd>' + (d.bentuk === 'SHEET' && d.qty_sheet ? '<dt>Qty (Sheet)</dt><dd>' + M.fmt(d.qty_sheet, 0) + '</dd><dt>Berat / Sheet</dt><dd>' + M.fmt(d.berat_sheet || 0, 3) + ' kg</dd>' : (d.qty_kg ? '<dt>Jumlah Order</dt><dd>' + M.fmt(d.qty_kg, 0) + ' Kg</dd>' : '')) +
        '<dt>Sumber Harga</dt><dd>' + esc(d.dasar || '-') + (d.plNo ? ' (' + esc(d.plNo) + ')' : '') + '</dd><dt>Kurs Digunakan</dt><dd>' + esc(d.kurs) + '</dd><dt>Pricelist / Bottom Price (Rp/Kg)</dt><dd>' + M.idr(d.bottom) + '</dd>' + (d.roundup != null ? '<dt>Pricelist Roundup (Rp/Kg)</dt><dd>' + M.idr(d.roundup) + '</dd>' : '') + '<dt>Price Quotation (Rp/Kg)</dt><dd><b>' + M.idr(hargaCust(d)) + '</b> (USD ' + M.fmt(hargaCust(d) / kursVal(d.kurs), 2) + ')</dd>' +
        (d.nilai != null ? '<dt>Nilai Item</dt><dd><b>' + M.idr(d.nilai) + '</b></dd>' : '') + '</dl>', {size:'sm'});
    }

    /* ===== form add/edit item. Poin 2 "Temuan Tambahan": 1) pilih Bentuk, 2) pilih Material berdasarkan bentuk.
       Harga: Bottom Price diambil dari PRICELIST DIRILIS (K9, K17); satuan Rp/Kg. Roll: Rp/Kg x Kg order. Sheet: Rp/Kg x Berat/Sheet x Qty sheet (K24). ===== */
    function item(r, d, t) {
      var edit = !!d, P = M.pc;
      if (!P) { M.toast('Data pricelist tidak tersedia (costing.js belum dimuat).', 'err'); return; }
      d = d || {material:'', bentuk:'', thickness:'', width:'', length:'', density:'', forecast:'', kurs:'spot', ket:'', lotno:'', qty_sheet:'', qty_kg:''};
      var reg = {}; S.bentukList.forEach(function (b) { reg[b.nama] = {}; });
      function put(m) { if (!reg[m.bentuk]) reg[m.bentuk] = {}; if (!reg[m.bentuk][m.nama]) reg[m.bentuk][m.nama] = m; }
      P.st.mats.forEach(function (m) { put({nama:m.nama, bentuk:m.bentuk, thickness:m.thickness, density:m.density, width:m.width || '', length:m.length || '', dim:{}, maker:m.maker, mid:m.id}); });
      function sheetNm(m) { if (m.bentuk !== 'SHEET' || !m.dim || !m.dim.THICKNESS) return m.nama; var al = m.nama.split('-').pop() || ''; return [m.spek, m.dim.THICKNESS, m.dim.WIDTH, m.dim.LENGTH, al].filter(Boolean).join('-'); }
      S.matList.forEach(function (m) { put({nama:sheetNm(m), bentuk:m.bentuk, thickness:m.thickness, density:m.density, width:m.dim.WIDTH || '', length:m.dim.LENGTH || '', dim:m.dim, spek:m.spek, maker:m.maker}); });
      rows.concat(S.penawaran, S.penawaranSlit).forEach(function (p) { p.det.forEach(function (x) { put({nama:x.material, bentuk:x.bentuk, thickness:x.thickness, density:x.density, width:x.width, length:x.length, dim:{}, spek:x.spek, maker:x.maker}); }); });
      function mat(b, n) { return (reg[b] || {})[n]; }
      function relOf(m) { var pm = P.st.mats.filter(function (x) { return x.nama === m.nama; })[0]; return pm ? P.releasedLine(pm.id) : null; }
      var b = M.modal(edit ? 'Edit Penawaran' : 'Tambah Penawaran', '<form novalidate><div class="sect" style="text-align:center">PENAWARAN' + (isSlit ? ' SLITTING' : '') + '</div><div class="grid2">' +
        M.fg('Material', '<select name="material" required></select>', '<b>2.</b> Pilih material sesuai bentuk') + M.fg('Bentuk', '<select name="bentuk" required>' + M.opts(S.bentukList, d.bentuk, 'nama', 'nama', '-- Pilih Bentuk --') + '</select>', '<b>1.</b> Pilih bentuk material dahulu') + '</div>' +
        '<div class="grid2">' + M.fg('Thickness', '<input type="text" name="thickness" readonly value="' + esc(d.thickness) + '">') + M.fg('Density', '<input type="text" name="density" readonly value="' + esc(d.density) + '">') +
        M.fg('Forecast/Month(Kg)', '<input type="number" name="forecast" required value="' + esc(d.forecast) + '">') + M.fg('Width', '<input type="text" name="width" value="' + esc(d.width) + '">', '<span class="whint"></span>') +
        M.fg('Length', '<input type="text" name="length" readonly value="' + esc(d.length) + '">', 'Muncul untuk bentuk Sheet (dari master material)') + M.fg('Part Number', '<input type="text" name="lotno" value="' + esc(d.lotno || '') + '">') +
        M.fg('Dimensi', '<input type="text" name="dimtxt" readonly>') + M.fg('Berat / Sheet (Kg)', '<input type="text" name="wsheet" readonly>', 'Thickness (mm) × Width (mm) × Length (mm) × Berat jenis (g/cm³) ÷ 1.000.000') +
        M.fg('Jumlah Order (Kg)', '<input type="number" name="qty_kg" value="' + esc(d.qty_kg == null ? '' : d.qty_kg) + '">', 'Diinput untuk bentuk Roll') + M.fg('Qty (Sheet)', '<input type="number" name="qty_sheet" value="' + esc(d.qty_sheet == null ? '' : d.qty_sheet) + '">', 'Diinput untuk bentuk Sheet') +
        M.fg('Kurs Digunakan', '<select name="kurs">' + KURS.map(function (k) { return '<option value="' + k[0] + '">' + k[1] + '</option>'; }).join('') + '</select>', 'Hanya untuk tampilan harga USD') + '</div>' +
                '<div class="sect" style="text-align:center">HARGA PENAWARAN (dari Pricelist dirilis)</div><div class="plinfo"></div><div class="grid2">' +
        M.fg('Pricelist / Bottom Price (Rp/Kg)', '<input type="text" name="bottom" readonly>', 'Dari pricelist dirilis') + M.fg('Pricelist Roundup (Rp/Kg)', '<input type="text" name="roundup" readonly>', 'Dibulatkan ke atas, kelipatan Rp100 (hanya dihitung di Penawaran)') +
        M.fg('Price Quotation (Rp/Kg)', '<input type="text" inputmode="decimal" autocomplete="off" name="harga" required>', 'Default dari Pricelist Roundup, dapat diedit. Harga USD: <b class="usd">-</b><span class="qwarn"></span>') +
        M.fg('Harga / Sheet (Rp)', '<input type="text" name="price_sheet" readonly>', 'Price Quotation × Berat/Sheet (hanya Sheet)') + M.fg('Nilai Item (Rp)', '<input type="text" name="nilai" readonly>', '<span class="nhint"></span>') + '</div>' +
        M.fg('Keterangan', '<input type="text" name="ket" value="' + esc(d.ket || '') + '">') + '<div class="form-actions"><button class="btn btn-primary" type="submit">💾 Simpan</button></div></form>', {size:'xl'});
      var f = b.querySelector('form'); f.kurs.value = d.kurs || 'spot';
      var fgOf = function (n) { return f[n].closest('.fg'); };
      function show(n, on) { fgOf(n).style.display = on ? '' : 'none'; }
      function fillMaterials(keep) {
        var bt = f.bentuk.value, names = Object.keys(reg[bt] || {}).sort();
        f.material.disabled = !bt;
        f.material.innerHTML = !bt ? '<option value="">-- Pilih bentuk dahulu --</option>' : (names.length ? '<option value="">-- Pilih Material (' + bt + ') --</option>' + names.map(function (n) { var has = relOf(reg[bt][n]); return '<option value="' + esc(n) + '"' + (n === keep ? ' selected' : '') + '>' + esc(n) + (has ? '  ✓ pricelist' : '') + '</option>'; }).join('') : '<option value="">(belum ada material untuk bentuk ' + esc(bt) + ')</option>');
        var isRoll = bt === 'ROLL', isSheet = bt === 'SHEET', other = bt && !isRoll && !isSheet;
        show('width', isRoll || isSheet); f.width.readOnly = isSheet; f.width.required = isRoll; b.querySelector('.whint').textContent = isRoll ? 'Diinput untuk bentuk Roll' : isSheet ? 'Otomatis dari master material (Sheet)' : '';
        show('length', isSheet); show('qty_sheet', isSheet); f.qty_sheet.required = isSheet; show('qty_kg', isRoll); f.qty_kg.required = isRoll; show('wsheet', isSheet); show('price_sheet', isSheet); show('dimtxt', other);
      }
      var cur = null, qDirty = edit && d.id_dt_pricelist != null;
      function calc(mode) {
        var bt = f.bentuk.value, m = mat(bt, f.material.value);
        if (mode === 'bentuk') { ['thickness', 'density', 'width', 'length', 'dimtxt'].forEach(function (n) { f[n].value = ''; }); }
        if (mode === 'mat') {
          if (m) { f.thickness.value = m.thickness; f.density.value = m.density; f.dimtxt.value = Object.keys(m.dim || {}).map(function (k) { return k + ' ' + m.dim[k]; }).join(' · '); f.width.value = bt === 'ROLL' ? (edit ? f.width.value : '') : m.width; f.length.value = bt === 'SHEET' ? m.length : ''; }
          else ['thickness', 'density', 'width', 'length', 'dimtxt'].forEach(function (n) { f[n].value = ''; });
        }
        cur = m ? relOf(m) : null;
        b.querySelector('.plinfo').innerHTML = !f.material.value ? '' : cur ? '<div class="okbox">Pricelist dirilis tersedia: <b>' + esc(cur.pl.no) + '</b> (dirilis ' + M.dstr(cur.tglRilis) + '), berlaku untuk semua customer.</div>' : '<div class="warnbox">Belum ada pricelist dirilis untuk material ini. Item belum dapat disimpan; minta Sales menyusun dan mengajukan pricelist (lihat menu Pricelist).</div>';
        var bottom = cur ? cur.bottom : 0, ru = cur ? roundup(bottom) : 0, k = kursVal(f.kurs.value);
        f.bottom.value = cur ? M.fmt(bottom, 2) : '-'; f.roundup.value = cur ? M.fmt(ru, 0) : '-';
        if (cur && (mode === 'mat' || mode === 'bentuk' || !qDirty)) { f.harga.value = M.money.fmt(ru); qDirty = false; } else if (!cur) f.harga.value = '';
        var q = M.money.val(f.harga);
        b.querySelector('.usd').textContent = cur ? M.fmt(q / k, 2) + ' (kurs ' + M.fmt(k, 0) + ')' : '-';
        b.querySelector('.qwarn').innerHTML = cur && q && q < bottom ? '<div class="warnbox" style="margin-top:6px">Price Quotation di bawah Bottom Price (Rp ' + M.fmt(bottom, 0) + '): item ditandai dan ikut Approval Direktur.</div>' : '';
        var ws = 0; if (bt === 'SHEET' && +f.thickness.value && +f.width.value && +f.length.value && +f.density.value) ws = f.thickness.value * f.width.value * f.length.value * f.density.value / 1e6;
        f.wsheet.value = ws ? M.fmt(ws, 3) : '-'; f.price_sheet.value = cur && ws ? M.fmt(q * ws, 2) : '-';
        var nilai = null, hint = '';
        if (cur && bt === 'ROLL' && +f.qty_kg.value) { nilai = q * f.qty_kg.value; hint = 'Price Quotation Rp/Kg × Jumlah order Kg'; }
        if (cur && bt === 'SHEET' && ws && +f.qty_sheet.value) { nilai = q * ws * f.qty_sheet.value; hint = 'Price Quotation Rp/Kg × Berat/Sheet × Jumlah order sheet'; }
        f.nilai.value = nilai == null ? '-' : M.fmt(nilai, 2); b.querySelector('.nhint').textContent = hint; f._nilai = nilai; f._ws = ws;
      }
      f.bentuk.onchange = function () { fillMaterials(''); calc('bentuk'); };
      f.material.onchange = function () { calc('mat'); }; f.kurs.onchange = function () { calc(''); };
      ['qty_kg', 'qty_sheet', 'width'].forEach(function (n) { f[n].oninput = function () { calc(''); }; });
      M.money.bind(f.harga, function () { qDirty = true; calc(''); });
      fillMaterials(d.material);
      if (edit) { var m0 = mat(d.bentuk, d.material); if (m0 && !f.dimtxt.value) f.dimtxt.value = Object.keys(m0.dim || {}).map(function (k) { return k + ' ' + m0.dim[k]; }).join(' · '); }
      if (edit && d.id_dt_pricelist != null) f.harga.value = M.money.fmt(hargaCust(d));
      calc('');
      f.onsubmit = function (e) { e.preventDefault(); if (!M.validate(f)) return;
        if (!cur) { M.swal({title:'Pricelist belum tersedia', text:'Belum ada pricelist dirilis untuk material ini, item tidak dapat disimpan.', type:'error'}); return; }
        M.saveFlow({text:'Data penawaran akan disimpan.', done:'Data penawaran berhasil disimpan.', save:function () {
          var m = mat(f.bentuk.value, f.material.value) || {}; var bt = f.bentuk.value;
          var o = {material:f.material.value, spek:m.spek || '', maker:m.maker || '', bentuk:bt, thickness:+f.thickness.value, density:f.density.value, width:f.width.value, length:f.length.value, lotno:f.lotno.value, forecast:+f.forecast.value,
            bottom:Math.round(cur.bottom * 100) / 100, roundup:roundup(cur.bottom), komisi:0, profit:0, harga:Math.round(M.money.val(f.harga)), harga_cust:Math.round(M.money.val(f.harga)), kurs:f.kurs.value, dasar:'Pricelist dirilis', plNo:cur.pl.no, id_dt_pricelist:cur.line.id, ket:f.ket.value,
            qty_kg:bt === 'ROLL' ? +f.qty_kg.value : null, qty_sheet:bt === 'SHEET' ? +f.qty_sheet.value : null, berat_sheet:f._ws || null, price_sheet:f._ws ? M.money.val(f.harga) * f._ws : 0, nilai:f._nilai};
          if (edit) Object.assign(d, o); else { o.id = 'n' + Date.now() % 100000; r.det.push(o); } t.refresh(); }}); };
    }
    list();
  }
  M.pen = {roundup:roundup, plTable:plTable, plFor:plFor, build:build, derive:derive, kursVal:kursVal, refProfit:refProfit};
})(window.Mock);
