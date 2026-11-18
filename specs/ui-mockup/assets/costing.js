/* Product Costing (To Be): data contoh + logika bersama untuk semua halaman costing.
   Data disimpan di localStorage ('metalsindo_pc_v1') agar alur lintas halaman (rate -> costing -> pricelist -> penawaran) tetap terlihat.
   Nilai contoh berasal dari workbook "New Structure Material&Product" (sheet Simulasi Costing) dan nama material dari DB lokal; harga acuan lain ilustratif. */
(function (M) {
  'use strict';
  var KEY = 'metalsindo_pc_v1', TODAY = '2026-11-10', S = M.seedS;
  var TIPE = [['FERROUS', 'Ferrous'], ['NONFERROUS', 'Non Ferrous']];
  var OPSI = [['1B', '1 Bulan', 1], ['3B', '3 Bulan', 3], ['SM', 'Semester', 6], ['TH', 'Tahunan', 12]];
  var ELEM = [['material', 'Material'], ['manpower', 'Man Power'], ['machine', 'Machine'], ['consumable', 'Consumable'], ['foh', 'FOH'], ['waste', 'Waste']];

  function addMonths(d, n) { var x = new Date(d + 'T00:00:00'); x.setMonth(x.getMonth() + n); return x.toISOString().slice(0, 10); }
  function opsiMonths(k) { var o = OPSI.filter(function (x) { return x[0] === k; })[0]; return o ? o[2] : 1; }
  function opsiLabel(k) { var o = OPSI.filter(function (x) { return x[0] === k; })[0]; return o ? o[1] : k; }
  function kursNow() { var s = S.kurs.slice().sort(function (a, b) { return a.tgl < b.tgl ? 1 : a.tgl > b.tgl ? -1 : (b.id - a.id); }); return s[0].nominal; }

  /* ---------- seed ---------- */
  function seed() {
    var mats = [
      {id:'M01', tipe:'NONFERROUS', nama:'BSR-C2600-1/2H-0.64', bentuk:'ROLL', maker:'LEEKU', thickness:0.64, density:8.55},
      {id:'M02', tipe:'NONFERROUS', nama:'C1100-1-1000-1000-COPPER', bentuk:'SHEET', maker:'', thickness:1, width:1000, length:1000, density:8.94, contoh:true},
      {id:'M03', tipe:'NONFERROUS', nama:'BSR-C2680-1/2H-0.35', bentuk:'ROLL', maker:'SIAM POONGSAN METAL CO.LTD', thickness:0.35, density:8.47},
      {id:'M04', tipe:'NONFERROUS', nama:'COPPER-C1100-1/2H-0.50', bentuk:'ROLL', maker:'SIAM POONGSAN METAL CO.LTD', thickness:0.5, density:8.94},
      {id:'M05', tipe:'FERROUS', nama:'SUS-304-3/4H-0.20', bentuk:'ROLL', maker:'WALSIN PRECISION TECHNOLOGY SDN BHD', thickness:0.2, density:7.93},
      {id:'M06', tipe:'NONFERROUS', nama:'AL-A1100-H14-0.80', bentuk:'ROLL', maker:'STARMAS', thickness:0.8, density:2.7},
      {id:'M07', tipe:'FERROUS', nama:'SPC270D-1.00-214-180-COLD ROLLED', bentuk:'SHEET', maker:'', thickness:1, width:214, length:180, density:7.85},
      {id:'M08', tipe:'FERROUS', nama:'JSC270OD-0.80-515-1300-COLD ROLLED', bentuk:'SHEET', maker:'', thickness:0.8, width:515, length:1300, density:7.85}
    ];
    mats.forEach(function (m) { if (m.bentuk === 'SHEET') m.berat = m.thickness * m.width * m.length * m.density / 1e6; });
    var K = kursNow();
    // Masa berlaku GLOBAL per tipe material (K39): satu untuk Ferrous, satu untuk Non Ferrous
    var mb = {FERROUS:'1B', NONFERROUS:'3B'};
    var tipeOf = function (mid) { return mats.filter(function (m) { return m.id === mid; })[0].tipe; };
    // History Pembelian (PO): hanya PO yang sudah incoming yang dipakai sebagai Last Purchase (K40). Data ilustratif.
    var po = [
      {no:'PO-26/09/014', tgl:'2026-09-20', tglIn:'2026-09-28', mid:'M01', maker:'LEEKU', usd:10.5, incoming:true},
      {no:'PO-26/10/031', tgl:'2026-10-25', tglIn:null, mid:'M01', maker:'LEEKU', usd:11.2, incoming:false},
      {no:'PO-26/09/009', tgl:'2026-09-12', tglIn:'2026-09-30', mid:'M02', maker:'', usd:15.5, incoming:true},
      {no:'PO-26/10/002', tgl:'2026-10-02', tglIn:'2026-10-14', mid:'M03', maker:'SIAM POONGSAN METAL CO.LTD', usd:9.8, incoming:true},
      {no:'PO-26/08/021', tgl:'2026-08-18', tglIn:'2026-09-02', mid:'M05', maker:'WALSIN PRECISION TECHNOLOGY SDN BHD', usd:5.2, incoming:true},
      {no:'PO-26/10/005', tgl:'2026-10-05', tglIn:'2026-10-18', mid:'M06', maker:'STARMAS', usd:3.9, incoming:true},
      {no:'PO-26/10/008', tgl:'2026-10-08', tglIn:'2026-10-19', mid:'M07', maker:'', usd:1.1, incoming:true}
    ];
    var prp = [], prpd = [], prcd = [], did = 0;
    function lastPO0(mid) { return po.filter(function (x) { return x.mid === mid && x.incoming; }).sort(function (a, b) { return a.tglIn < b.tglIn ? 1 : -1; })[0] || null; }
    function pr(mid, tgl, cp, kurs, note) {
      var h = prp.filter(function (x) { return x.mid === mid; })[0]; if (!h) { h = {id:'PRP' + (prp.length + 1), mid:mid}; prp.push(h); }
      var lp = lastPO0(mid), sd = addMonths(tgl, opsiMonths(mb[tipeOf(mid)]));
      var d = {id:'PRD' + (++did), hid:h.id, mid:mid, tgl:tgl, lp:lp ? lp.usd : null, lp_rp:lp ? Math.round(lp.usd * kurs) : null, po:lp ? lp.no : null, cp:cp, kurs:kurs, cp_rp:Math.round(cp * kurs), opsi:mb[tipeOf(mid)], sd:sd, evidence:'', note:note || ''};
      prpd.push(d);
      prcd.push({id:'PRC' + did, mid:mid, src:d.id, cp_usd:cp, cp_rp:d.cp_rp, opsi:d.opsi, sd:sd, tgl:tgl});
    }
    pr('M01', '2026-10-01', 10.9, 18000, 'Contoh dari workbook');
    pr('M02', '2026-10-01', 16, 18000, 'Contoh dari workbook');
    pr('M03', '2026-10-15', 10.2, K);
    pr('M04', '2026-10-15', 9.4, K, 'Belum ada riwayat PO incoming: Last Purchase kosong');
    pr('M05', '2026-09-30', 5.5, K, 'Masa berlaku Ferrous 1 bulan (sudah lewat)');
    pr('M06', '2026-10-20', 4.1, K);
    pr('M07', '2026-10-20', 1.2, K);
    var rate = [{ver:1, tgl:'2026-10-01', by:'admin', aktif:true, v:{manpower:2442.81, machine:200, consumable:100, foh:150, waste:1.2, admin:2000, profit:10, na:5, komisi:250}}];
    var flow = {
      M01: {upd:'2026-10-05', by:'admin', rows:[[1, 'Slitting', 'Setting mesin dan unpack coil', 0.04, 2, 9], [2, 'Slitting', 'Proses Slitting', 0.003, 1, 9]]},
      M02: {upd:'2026-10-05', by:'admin', rows:[[1, 'Slitting', 'Setting mesin dan unpack coil', 0.04, 2, 9], [2, 'Slitting', 'Proses Slitting', 0.003, 1, 9]]},
      M03: {upd:'2026-10-06', by:'admin', rows:[[1, 'Slitting', 'Proses Slitting', 0.05, 2, 9]]},
      M06: {upd:'2026-10-07', by:'admin', rows:[[1, 'Slitting', 'Proses Slitting', 0.045, 2, 9]]}
    };
    Object.keys(flow).forEach(function (k) { flow[k].rows = flow[k].rows.map(function (r) { return {no:r[0], cc:r[1], proses:r[2], ct:r[3], mp:r[4], asset:r[5]}; }); });
    return {v:5, mats:mats, mb:mb, po:po, prp:prp, prpd:prpd, prcd:prcd, rate:rate, flow:flow, cost:{}, pl:[], ntf:[], seq:100};
  }

  var st = null;
  function load() { try { var s = localStorage.getItem(KEY); return s ? JSON.parse(s) : null; } catch (e) { return null; } }
  function save() { try { localStorage.setItem(KEY, JSON.stringify(st)); } catch (e) {} }
  function reset() { st = seed(); runUpdate(true); seedPricelist(); save(); }
  function nid(p) { return p + (++st.seq); }
  function mat(id) { return st.mats.filter(function (m) { return m.id === id; })[0]; }
  function active(list) { return list.filter(function (x) { return x.aktif; })[0] || list[list.length - 1]; }

  /* ---------- costing ---------- */
  function flowCT(mid) { var f = st.flow[mid]; return f && f.rows.length ? f.rows.reduce(function (a, r) { return a + (+r.ct || 0); }, 0) : null; }
  function priceRef(mid) { var l = st.prcd.filter(function (c) { return c.mid === mid; }); return l.sort(function (a, b) { return a.tgl < b.tgl ? 1 : -1; })[0] || null; }
  function expired(c) { return c.sd < TODAY; }
  function calc(m) {
    var pr = priceRef(m.id);
    if (!pr) return {mid:m.id, state:'TANPA_PR'};
    if (expired(pr)) return {mid:m.id, state:'BLOKIR', sd:pr.sd};
    var rate = active(st.rate).v;
    var el = [];
    var material = pr.cp_rp;
    el.push({k:'material', rate:pr.cp_rp, sat:'Rp/Kg', kg:material});
    el.push({k:'manpower', rate:rate.manpower, sat:'Rp/Kg', kg:rate.manpower});
    el.push({k:'machine', rate:rate.machine, sat:'Rp/Kg', kg:rate.machine});
    el.push({k:'consumable', rate:rate.consumable, sat:'Rp/Kg', kg:rate.consumable});
    el.push({k:'foh', rate:rate.foh, sat:'Rp/Kg', kg:rate.foh});
    el.push({k:'waste', rate:rate.waste, sat:'% × Material', kg:rate.waste / 100 * material});
    var hpp = el.reduce(function (a, e) { return a + (e.kg || 0); }, 0);
    var cos = hpp + rate.admin, profit = cos * rate.profit / 100, sales = cos + profit, na = sales * rate.na / 100, bottom = sales + na + rate.komisi;
    var lengkap = el.every(function (e) { return e.kg != null; });
    return {mid:m.id, state:'OK', status:lengkap ? 'LENGKAP' : 'SEBAGIAN', tgl:TODAY, el:el, hpp:hpp, admin:rate.admin, cos:cos, profit:profit, profitPct:rate.profit, sales:sales, na:na, naPct:rate.na, komisi:rate.komisi, bottom:bottom,
      berat:m.berat || null, bottom_sheet:m.berat ? bottom * m.berat : null,
      refs:{prc:pr.id, rate:'v' + active(st.rate).ver, flow:st.flow[m.id] ? st.flow[m.id].upd : null}};
  }
  function runUpdate(silent) { // tombol Update Costing: semua material
    var sum = {lengkap:0, sebagian:0, blokir:0, tanpa:0, ntf:0};
    st.mats.forEach(function (m) {
      var r = calc(m);
      if (r.state === 'OK') { st.cost[m.id] = r; if (r.status === 'LENGKAP') sum.lengkap++; else sum.sebagian++; }
      else if (r.state === 'BLOKIR') {
        sum.blokir++; delete st.cost[m.id];
        if (!st.ntf.some(function (n) { return n.mid === m.id && !n.dibaca && n.jenis === 'PRICE_REF_KEDALUWARSA'; })) {
          st.ntf.unshift({id:nid('N'), jenis:'PRICE_REF_KEDALUWARSA', mid:m.id, modul:'Price Reference', dibaca:false, tgl:TODAY, pesan:'Price Reference Costing ' + m.nama + ' kedaluwarsa (' + r.sd + '). Update Costing diblokir untuk material ini.'}); sum.ntf++;
        }
      } else { sum.tanpa++; delete st.cost[m.id]; }
    });
    if (!silent) save();
    return sum;
  }

  /* ---------- price reference ---------- */
  function tipeOfMat(mid) { var m = mat(mid); return m ? m.tipe : 'NONFERROUS'; }
  function tipeLabel(t) { var o = TIPE.filter(function (x) { return x[0] === t; })[0]; return o ? o[1] : t; }
  function mbOf(mid) { return st.mb[tipeOfMat(mid)]; }
  /* Last Purchase: PO terbaru material (Roll: maker sama) yang SUDAH incoming; kosong bila belum ada (K40) */
  function lastPO(mid) {
    var m = mat(mid);
    return st.po.filter(function (x) { return x.mid === mid && x.incoming && (m.bentuk !== 'ROLL' || x.maker === m.maker); }).sort(function (a, b) { return a.tglIn < b.tglIn ? 1 : a.tglIn > b.tglIn ? -1 : 0; })[0] || null;
  }
  function latestPrp(mid) { return st.prpd.filter(function (x) { return x.mid === mid; }).sort(function (a, b) { return a.tgl < b.tgl ? 1 : a.tgl > b.tgl ? -1 : (a.id < b.id ? 1 : -1); })[0] || null; }
  function addPurchase(mid, cp, kurs, note, evidence) {
    var h = st.prp.filter(function (x) { return x.mid === mid; })[0]; if (!h) { h = {id:nid('PRP'), mid:mid}; st.prp.push(h); }
    var lp = lastPO(mid), opsi = mbOf(mid), sd = addMonths(TODAY, opsiMonths(opsi));
    // Last Purchase disimpan sebagai snapshot beserta rujukan PO-nya
    var d = {id:nid('PRD'), hid:h.id, mid:mid, tgl:TODAY, lp:lp ? lp.usd : null, lp_rp:lp ? Math.round(lp.usd * kurs) : null, po:lp ? lp.no : null, cp:cp, kurs:kurs, cp_rp:Math.round(cp * kurs), opsi:opsi, sd:sd, evidence:evidence || '', note:note || ''};
    st.prpd.push(d);
    // diturunkan otomatis ke Price Reference Costing dari Costing Price; masa berlaku mengikuti Purchase (K31, K40)
    st.prcd.push({id:nid('PRC'), mid:mid, src:d.id, cp_usd:cp, cp_rp:d.cp_rp, opsi:opsi, sd:sd, tgl:TODAY});
    save(); return d;
  }

  /* masa berlaku GLOBAL per tipe (Ferrous / Non Ferrous), tanpa harga baru: Berlaku s/d = tanggal update terbaru + masa berlaku tipenya (K39);
     Price Reference Costing turunan ikut. Baris riwayat lama tidak diubah. */
  function setMasaBerlakuTipe(tipe, opsi) {
    st.mb[tipe] = opsi; var n = 0;
    st.mats.forEach(function (m) {
      if (m.tipe !== tipe) return;
      var d = latestPrp(m.id); if (!d) return;
      d.opsi = opsi; d.sd = addMonths(d.tgl, opsiMonths(opsi));
      st.prcd.forEach(function (c) { if (c.src === d.id) { c.opsi = opsi; c.sd = d.sd; } });
      n++;
    });
    save(); return n;
  }

  /* ---------- pricelist ---------- */
  function releasedLine(mid) {
    var best = null;
    st.pl.forEach(function (p) { p.lines.forEach(function (l) { if (l.mid === mid && l.status === 'DIRILIS' && (!best || l.tglRilis >= best.tglRilis)) best = {line:l, pl:p, bottom:l.bottom, tglRilis:l.tglRilis}; }); });
    return best;
  }
  function plStatus(p) { return p.lines.some(function (l) { return l.status === 'DIRILIS'; }) ? 'DIRILIS' : p.lines.some(function (l) { return l.status === 'DIAJUKAN'; }) ? 'DIAJUKAN' : 'DRAFT'; }
  function newPricelist(mids) {
    var p = {id:nid('PL'), no:'PL-26/XI/' + String(st.pl.length + 1).padStart(3, '0'), tgl:TODAY, by:'Sales', status:'DRAFT', appr:[], lines:mids.map(function (mid) { return {id:nid('PLD'), mid:mid, bottom:st.cost[mid].bottom, status:'DRAFT', aid:null}; })};
    st.pl.unshift(p); save(); return p;
  }
  function submitPricelist(p) { p.lines.forEach(function (l) { if (l.status === 'DRAFT') l.status = 'DIAJUKAN'; }); p.status = plStatus(p); save(); }
  function decide(p, dec, catatan) { // dec: {mid: true|false}; hanya baris DIAJUKAN
    var a = {id:nid('APR'), tgl:TODAY, user:'Management', catatan:catatan || '', setuju:[], tolak:[]};
    p.lines.forEach(function (l) { if (l.status !== 'DIAJUKAN') return; if (dec[l.mid]) { l.status = 'DIRILIS'; l.aid = a.id; l.tglRilis = TODAY; a.setuju.push(l.mid); } else { l.status = 'DRAFT'; l.aid = a.id; a.tolak.push(l.mid); } });
    a.keputusan = a.tolak.length === 0 ? 'SETUJU' : a.setuju.length === 0 ? 'TOLAK' : 'SETUJU SEBAGIAN';
    p.appr.unshift(a); p.status = plStatus(p); save(); return a;
  }
  function seedPricelist() {
    var mids = ['M01', 'M02'].filter(function (m) { return st.cost[m] && st.cost[m].status === 'LENGKAP'; });
    if (!mids.length) return;
    var p = newPricelist(mids); p.tgl = '2026-10-10'; p.no = 'PL-26/X/001'; submitPricelist(p);
    var dec = {}; mids.forEach(function (m) { dec[m] = true; }); var a = decide(p, dec, 'Pricelist awal'); a.tgl = '2026-10-12';
    p.lines.forEach(function (l) { l.tglRilis = '2026-10-12'; });
  }

  st = load();
  if (!st || st.v !== 5) { reset(); }

  M.pc = {TODAY:TODAY, OPSI:OPSI, ELEM:ELEM, get st() { return st; }, save:save, reset:reset, nid:nid, mat:mat, active:active, calc:calc, runUpdate:runUpdate, priceRef:priceRef, expired:expired,
    flowCT:flowCT, addPurchase:addPurchase, setMasaBerlakuTipe:setMasaBerlakuTipe, lastPO:lastPO, latestPrp:latestPrp, mbOf:mbOf, tipeLabel:tipeLabel, TIPE:TIPE, releasedLine:releasedLine, plStatus:plStatus, newPricelist:newPricelist, submitPricelist:submitPricelist, decide:decide, opsiLabel:opsiLabel, opsiMonths:opsiMonths, addMonths:addMonths, kursNow:kursNow};
})(window.Mock);
