/* Builder SPK Marketing (reguler & slitting). Add/Edit berupa halaman penuh: header + grid item dari penawaran terpilih + footer total/diskon; simpan -> kembali ke daftar. */
(function (M) {
  'use strict';
  var S = M.seedS, esc = M.esc;
  function build(page, cfg) {
    var rows = cfg.rows, isSlit = cfg.type === 'slitting';
    rows.forEach(function (s, i) { if (s._d) return; s._d = 1; if (isSlit && i % 3 === 0) s.approve = 0; /* peragaan: sebagian SPK slitting belum di-approve */ s.revOk = s.approve === 1 && i % 4 === 1; });
    var root = document.createElement('div'); page.appendChild(root);
    var sum = function (s) { return s.det.reduce(function (a, d) { return a + d.total; }, 0); };
    var net = function (s) { return sum(s) - (s.discount != null ? s.discount : s.det.reduce(function (a, d) { return a + (d.disc || 0); }, 0)); };

    /* ===== daftar ===== */
    function list() {
      root.innerHTML = '<div class="box primary"><div class="box-header"><span id="addw"></span></div><div class="box-body" id="tbl"></div></div>';
      function addBtn() { M.$('#addw').innerHTML = M.perm.Add ? '<button class="btn btn-success btn-sm" id="add">＋ Add</button>' : ''; if (M.perm.Add) M.$('#add').onclick = function () { formPage(null); }; }
      addBtn(); M.onPerm(addBtn);
      function btn(cls, a, id, ic, ttl) { return ' <button class="btn btn-sm ' + cls + '" data-a="' + a + '" data-id="' + esc(id) + '" title="' + ttl + '">' + ic + '</button>'; }
      var t = M.table(M.$('#tbl'), {rows:function () { return rows; }, cols:[
        {key:'tgl', label:'Tanggal SPK Terbit', html:function (r) { return M.dstr(r.tgl); }, text:function (r) { return r.tgl; }}, {key:'surat', label:'No. SPK'}, {key:'cust', label:'Customer'},
        {label:'Nilai SPK', html:function (r) { return '<span class="num" style="display:block">' + M.fmt(sum(r), 2) + '</span>'; }, text:function (r) { return sum(r); }},
        {label:'Status', html:function (r) { return r.approve ? M.pill('Approved', 'ok') : M.pill('Belum di Approve', 'off'); }, text:function (r) { return r.approve ? 'Approved' : 'Belum di Approve'; }},
        {label:'Action', html:function (r) { var x = '';
          if (M.perm.View) x += btn('btn-primary', 'view', r.id, '👁', 'View') + btn('btn-success', 'print', r.id, '🖶', 'Print');
          if (r.approve) { if (M.perm.Manage && r.revOk) x += btn('btn-primary', 'rev', r.id, '☰', 'Revisi'); }
          else { if (M.perm.Manage) x += btn('btn-info', 'edit', r.id, '✎', 'Edit'); if (M.perm.Delete) x += btn('btn-success', 'apv', r.id, '✓', 'Approve'); }
          return x; }, text:function () { return ''; }}],
        onAction:function (a, id) { var r = rows.filter(function (x) { return x.id === id; })[0];
          if (a === 'view') view(r); else if (a === 'print') M.toast('Cetak SPK ' + r.surat + ' (PDF, simulasi)'); else if (a === 'edit') formPage(r);
          else if (a === 'rev') M.saveFlow({text:'SPK akan dibuka untuk direvisi (revisi penawaran sudah disetujui).', okText:'Ya, Revisi!', done:'SPK dibuka untuk revisi (simulasi).', save:function () { r.revOk = false; t.refresh(); }});
          else M.saveFlow({text:'SPK Marketing akan di-approve dan diteruskan ke Planning.', okText:'Ya, Approve!', done:'SPK berhasil di-approve.', save:function () { r.approve = 1; t.refresh(); }}); }});
      root.insertAdjacentHTML('beforeend', '<div class="note">Alur: SPK dibuat dari penawaran Open → <b>Belum di Approve</b> (masih bisa diedit) → <b>Approved</b> (terkunci; perubahan harus lewat revisi penawaran yang disetujui). Header SPK diturunkan; item nyata dari <code>dt_spkmarketing</code>.</div>');
    }
    function itemCols() { // kolom grid (kolom Weight/Coil di aplikasi disembunyikan)
      return [{key:'material', label:'Nama Material'}, {key:'thickness', label:'Thickness'}, {key:'width', label:'Width'}, {key:'length', label:'Length'}, {label:'Part Number', html:function (d) { return esc(d.part || '-'); }},
        {label:'Harga Penawaran', cls:'num', html:function (d) { return M.fmt(d.harga_penawaran, 0); }}, {label:'Harga Deal / Kg', cls:'num', html:function (d) { return '<b>' + M.fmt(d.harga_deal, 0) + '</b>'; }}, {label:'Disc', cls:'num', html:function (d) { return M.fmt(d.disc || 0, 0); }},
        {label:'Qty (KG)', cls:'num', html:function (d) { return M.fmt(d.qty, 0); }}, {label:'Total Harga', cls:'num', html:function (d) { return M.fmt(d.total, 0); }}, {label:'Delivery Date', html:function (d) { return M.dstr(d.delivery); }}, {label:'Keterangan', html:function (d) { return esc(d.ket || '-'); }}];
    }
    function view(r) {
      M.modal('Detail SPK Marketing', '<dl class="dl"><dt>No. SPK</dt><dd>' + esc(r.surat) + ' <small>(' + esc(r.id) + ')</small></dd><dt>Tanggal Terbit</dt><dd>' + M.dstr(r.tgl) + '</dd><dt>Customer</dt><dd>' + esc(r.cust) + '</dd><dt>No. PO / Tgl PO</dt><dd>' + esc(r.po) + ' / ' + M.dstr(r.tgl_po) + '</dd>' +
        '<dt>Date Plan By Customer</dt><dd>' + M.dstr(r.plan) + '</dd><dt>Sample</dt><dd>' + esc(r.sample) + '</dd><dt>Tipe</dt><dd>' + esc(r.type) + '</dd><dt>Status</dt><dd>' + (r.approve ? M.pill('Approved', 'ok') : M.pill('Belum di Approve', 'off')) + '</dd></dl><div class="sect">Item SPK</div>' +
        M.stable(itemCols(), r.det, '<tfoot><tr><td colspan="9" class="num">Sum Total Harga</td><td class="num">' + M.fmt(sum(r), 0) + '</td><td colspan="2"></td></tr><tr><td colspan="9" class="num">Grand Total (setelah diskon)</td><td class="num">' + M.fmt(net(r), 0) + '</td><td colspan="2"></td></tr></tfoot>'), {size:'xl'});
    }

    /* ===== halaman Add / Edit SPK ===== */
    function formPage(r) {
      var edit = !!r, n = 2900 + rows.length;
      var pens = (isSlit ? S.penawaranSlit : S.penawaran).filter(function (p) { return !p.spk && p.status === 'N'; });
      var cur = edit ? r : {id:'', surat:'MKT-SPK/26/XI/' + String(n).padStart(4, '0'), tgl:new Date().toISOString().slice(0, 10), cust:'', po:'', tgl_po:'', plan:'', sample:'Tidak', type:cfg.type, det:[], approve:0, _d:1, revOk:false, discount:0};
      var items = edit ? r.det : [];
      var ro = function (l, v, nm) { return M.fg(l, '<input type="text" readonly' + (nm ? ' name="' + nm + '"' : '') + ' value="' + esc(v) + '">'); };
      root.innerHTML = '<div class="box primary"><div class="box-body"><div style="text-align:center"><h3 style="margin:4px 0 14px">' + (edit ? 'Edit' : 'Tambah') + ' SPK Marketing' + (isSlit ? ' Slitting' : '') + '</h3></div><form novalidate><div class="grid2">' +
        ro('NO.SPK', cur.surat) + ro('Tanggal', cur.tgl) +
        (edit ? ro('Customer', cur.cust) + ro('No. Penawaran', cur.penNo || '-') : M.fg('Customer', '<select name="cust" required>' + M.opts(S.customer, '', 'nama', 'nama') + '</select>') + M.fg('No. Penawaran', '<select name="pen" required>' + M.opts([], '', null, null, '--Pilih--') + '</select>', 'Hanya penawaran Open milik customer terpilih yang belum punya SPK')) +
        M.fg('No PO', '<input type="text" name="po" required value="' + esc(cur.po) + '">') + M.fg('Sample', '<select name="sample">' + M.opts(['Ya', 'Tidak'], cur.sample) + '</select>') +
        M.fg('Tgl PO', '<input type="text" name="tgl_po" required value="' + esc(cur.tgl_po) + '" placeholder="YYYY-MM-DD">') + M.fg('Date Plan By Customer', '<input type="text" name="plan" required value="' + esc(cur.plan) + '" placeholder="YYYY-MM-DD">') +
        M.fg('Note', '<textarea name="note"></textarea>') + ro('Tipe', cfg.type) + '</div><div class="sect">Item SPK</div><div id="grid"></div>' +
        '<div class="grid2" style="margin-top:12px">' + M.fg('Sum Total Harga', '<input type="text" id="sumt" readonly>') + M.fg('Total Discount', '<input type="number" id="disc" name="discount" value="' + (cur.discount || 0) + '">', 'Dapat diisi per item atau keseluruhan') + M.fg('Grand Total', '<input type="text" id="grand" readonly>') + '</div>' +
        '<div class="form-actions"><button class="btn btn-primary" type="submit">💾 Save</button><button class="btn btn-default" type="button" id="back">Back</button></div></form></div></div>';
      var f = root.querySelector('form'); M.$('#back').onclick = list;
      function recalc() { var s = items.reduce(function (a, d) { return a + d.total; }, 0), ds = +M.$('#disc').value || 0; M.$('#sumt').value = M.fmt(s, 2); M.$('#grand').value = M.fmt(s - ds, 2); }
      function grid() {
        var cols = [{key:'material', label:'Nama Material'}, {key:'thickness', label:'Thickness'}, {key:'width', label:'Width'}, {key:'length', label:'Length'}, {label:'Part Number', html:function (d) { return esc(d.part || '-'); }},
          {label:'Harga Penawaran', cls:'num', html:function (d) { return M.fmt(d.harga_penawaran, 0); }}, {label:'Harga Deal / Kg', html:function (d, i) { return '<input type="number" data-k="harga_deal" data-i="' + i + '" value="' + d.harga_deal + '" style="width:110px"' + '>'; }},
          {label:'Disc', html:function (d, i) { return '<input type="number" data-k="disc" data-i="' + i + '" value="' + (d.disc || 0) + '" style="width:80px">'; }}, {label:'Qty (KG)', html:function (d, i) { return '<input type="number" data-k="qty" data-i="' + i + '" value="' + d.qty + '" style="width:90px">'; }},
          {label:'Total Harga', cls:'num', html:function (d, i) { return '<span data-t="' + i + '">' + M.fmt(d.total, 0) + '</span>'; }}, {label:'Delivery Date', html:function (d, i) { return '<input type="text" data-k="delivery" data-i="' + i + '" value="' + esc(d.delivery || '') + '" placeholder="YYYY-MM-DD" style="width:110px">'; }},
          {label:'CRCL', html:function () { return '-'; }}, {label:'Keterangan', html:function (d, i) { return '<input type="text" data-k="ket" data-i="' + i + '" value="' + esc(d.ket || '') + '" style="width:120px">'; }},
          {label:'Deal', html:function (d, i) { return '<input type="checkbox" data-k="deal" data-i="' + i + '"' + (d.deal === false ? '' : ' checked') + '>'; }}];
        M.$('#grid').innerHTML = items.length ? M.stable(cols, items) : '<div class="note">Pilih Customer dan No. Penawaran untuk memuat item.</div>';
        recalc();
      }
      M.$('#grid').addEventListener('input', function (e) { var i = e.target.dataset.i; if (i == null) return; var d = items[+i], k = e.target.dataset.k, v = e.target.type === 'checkbox' ? e.target.checked : e.target.value;
        if (k === 'harga_deal' || k === 'qty' || k === 'disc') d[k] = +v; else d[k] = v; d.total = d.harga_deal * d.qty; var s = M.$('[data-t="' + i + '"]'); if (s) s.textContent = M.fmt(d.total, 0);
        if (k === 'disc') M.$('#disc').value = items.reduce(function (a, x) { return a + (x.disc || 0); }, 0); recalc(); });
      M.$('#disc').oninput = recalc;
      if (!edit) {
        f.cust.onchange = function () { var list_ = pens.filter(function (p) { return p.cust === f.cust.value; }); f.pen.innerHTML = '<option value="">--Pilih--</option>' + list_.map(function (p) { return '<option value="' + p.no + '">' + esc(p.surat) + '</option>'; }).join(''); items = []; grid();
          if (!list_.length) f.pen.innerHTML = '<option value="">(tidak ada penawaran Open untuk customer ini)</option>'; };
        f.pen.onchange = function () { var p = pens.filter(function (x) { return x.no === f.pen.value; })[0]; items = p ? p.det.map(function (d, i) { var h = d.harga_cust != null ? d.harga_cust : d.harga; return {id:'n' + i, material:d.material, sup:d.maker, thickness:d.thickness, width:d.width, length:d.length, part:d.lotno || '', harga_penawaran:h, harga_deal:h, disc:0, qty:d.forecast, total:h * d.forecast, delivery:'', ket:''}; }) : []; grid(); };
      }
      grid();
      f.onsubmit = function (e) { e.preventDefault(); if (!M.validate(f)) return; if (!items.length) { M.toast('Belum ada item SPK.', 'err'); return; }
        var ds = +M.$('#disc').value || 0; if (ds > items.reduce(function (a, d) { return a + d.total; }, 0)) { M.swal({title:'Peringatan', text:'Total discount tidak boleh melebihi total harga!', type:'error'}); return; }
        M.saveFlow({text:'SPK Marketing akan disimpan.', done:'SPK Marketing berhasil disimpan.', save:function () {
          cur.po = f.po.value; cur.tgl_po = f.tgl_po.value; cur.plan = f.plan.value; cur.sample = f.sample.value; cur.discount = ds; cur.det = items;
          if (!edit) { var p = pens.filter(function (x) { return x.no === f.pen.value; })[0]; cur.cust = p.cust; cur.id_cust = p.id_cust; cur.penNo = p.surat; cur.id = 'P26' + String(n).padStart(5, '0'); rows.unshift(cur); } },
          after:list}); };
    }
    list();
  }
  M.spk = {build:build};
})(window.Mock);
