/* Helper UI bersama halaman Product Costing */
(function (M) {
  'use strict';
  var P = M.pc, esc = M.esc, fmt = M.fmt;
  var U = {};
  U.mname = function (id) { var m = P.mat(id); return m ? m.nama : id; };
  U.badgeCost = function (r) {
    if (!r) return M.pill('Belum dihitung', 'gray');
    if (r.state === 'BLOKIR') return M.pill('Diblokir (Price Ref kedaluwarsa)', 'off');
    if (r.state === 'TANPA_PR') return M.pill('Belum ada Price Reference', 'gray');
    return r.status === 'LENGKAP' ? M.pill('LENGKAP', 'ok') : M.pill('SEBAGIAN', 'warn');
  };
  U.badgePR = function (c) { return P.expired(c) ? M.pill('Kedaluwarsa', 'off') : M.pill('Berlaku', 'ok'); };
  U.rp = function (v, d) { return v == null ? '-' : fmt(v, d == null ? 2 : d); };
  /* rincian elemen costing: Rp/Kg, dan Rp/Sheet bila material Sheet */
  U.elTable = function (r, m) {
    var sheet = m.bentuk === 'SHEET' && m.berat, rows = [];
    r.el.forEach(function (e) {
      var lab = (P.ELEM.filter(function (x) { return x[0] === e.k; })[0] || [0, e.k])[1];
      rows.push({lab:lab, rate:e.rate, sat:e.sat, kg:e.kg, note:e.k === 'manpower' ? (e.kg == null ? 'Rate Man Power belum diisi di Master Rate Umum (dihitung sebagian)' : 'Rate Man Power langsung Rp/Kg (Master Rate Umum)') : ''});
    });
    var tot = function (l, v, b) { rows.push({lab:l, kg:v, bold:true, note:b || ''}); };
    tot('HPP', r.hpp); rows.push({lab:'Biaya Admin', rate:r.admin, sat:'Rp/Kg', kg:r.admin}); tot('Cost of Sales', r.cos);
    rows.push({lab:'Profit', rate:r.profitPct, sat:'% × Cost of Sales', kg:r.profit}); tot('Sales Price', r.sales);
    rows.push({lab:'Negotiation Allowance', rate:r.naPct, sat:'% × Sales Price', kg:r.na}); rows.push({lab:'Commission Fee', rate:r.komisi, sat:'Rp/Kg', kg:r.komisi}); tot('Bottom Price', r.bottom, 'Nilai yang muncul pada quotation');
    var cols = [{label:'Elemen', html:function (x) { return x.bold ? '<b>' + esc(x.lab) + '</b>' : esc(x.lab); }}, {label:'Rate', cls:'num', html:function (x) { return x.rate == null ? '-' : fmt(x.rate, 2); }}, {label:'Satuan rate', html:function (x) { return esc(x.sat || ''); }},
      {label:'Rp/Kg', cls:'num', html:function (x) { return x.kg == null ? '-' : (x.bold ? '<b>' + fmt(x.kg, 2) + '</b>' : fmt(x.kg, 2)); }}];
    if (sheet) cols.push({label:'Rp/Sheet', cls:'num', html:function (x) { return x.kg == null ? '-' : (x.bold ? '<b>' + fmt(x.kg * m.berat, 2) + '</b>' : fmt(x.kg * m.berat, 2)); }});
    cols.push({label:'Keterangan', html:function (x) { return '<small>' + esc(x.note || '') + '</small>'; }});
    return M.stable(cols, rows);
  };
  U.head = function (key, title, sub, crumb) { return M.shell({key:key, title:title, sub:sub, module:key, crumb:crumb || 'Product Costing'}); };
  U.refresh = function () { if (M.bellRefresh) M.bellRefresh(); };
  M.pcui = U;
})(window.Mock);
