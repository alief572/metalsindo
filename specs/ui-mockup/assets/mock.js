/* Mockup UI Metalsindo - helper bersama + data contoh (tanpa backend, data hanya di memori) */
(function (w) {
  'use strict';
  var $ = function (s, r) { return (r || document).querySelector(s); };
  var esc = function (v) { return String(v == null ? '' : v).replace(/[&<>"']/g, function (c) { return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]; }); };

  /* ---------- Data contoh (fiktif) ---------- */
  /* Data contoh: diambil (hanya SELECT) dari database lokal, baris terpilih per modul */
  var seed = {
    "type": [
    {
    "id": "I2000001",
    "nama": "MATERIAL",
    "aktif": "aktif"
    },
    {
    "id": "I2000002",
    "nama": "ATK & HOUSEHOLD",
    "aktif": "aktif"
    },
    {
    "id": "I2000003",
    "nama": "CONSUMABLE",
    "aktif": "aktif"
    },
    {
    "id": "I2000004",
    "nama": "SPAREPART MESIN",
    "aktif": "aktif"
    }
    ],
    "cat1": [
    {
    "id": "I2000001",
    "id_type": "I2000001",
    "nama": "FERROUS",
    "aktif": "aktif",
    "komposisi": [
    "C",
    "Cr",
    "Ni",
    "Fe",
    "Mg"
    ]
    },
    {
    "id": "I2000002",
    "id_type": "I2000001",
    "nama": "NON FERROUS",
    "aktif": "aktif",
    "komposisi": [
    "Cu",
    "Zn",
    "Sn",
    "Ni",
    "Ag",
    "Al"
    ]
    }
    ],
    "cat2": [
    {
    "id": "I2000001",
    "id_cat1": "I2000002",
    "nama": "BSR",
    "coretax": "740000",
    "aktif": "aktif"
    },
    {
    "id": "I2000002",
    "id_cat1": "I2000002",
    "nama": "COPPER",
    "coretax": "740000",
    "aktif": "aktif"
    },
    {
    "id": "I2000003",
    "id_cat1": "I2000002",
    "nama": "PBS",
    "coretax": "740000",
    "aktif": "aktif"
    },
    {
    "id": "I2000004",
    "id_cat1": "I2000001",
    "nama": "SUS",
    "coretax": "722000",
    "aktif": "aktif"
    },
    {
    "id": "I2000005",
    "id_cat1": "I2000002",
    "nama": "AL",
    "coretax": "760000",
    "aktif": "aktif"
    },
    {
    "id": "I2000006",
    "id_cat1": "I2000001",
    "nama": "HOT ROLLED",
    "coretax": "720000",
    "aktif": "aktif"
    },
    {
    "id": "I2000007",
    "id_cat1": "I2000001",
    "nama": "COLD ROLLED",
    "coretax": "720000",
    "aktif": "aktif"
    },
    {
    "id": "I2000011",
    "id_cat1": "I2000002",
    "nama": "NICKEL SILVER",
    "coretax": "740000",
    "aktif": "aktif"
    }
    ],
    "surface": [
    {
    "id": "I2000001",
    "nama": "BA"
    },
    {
    "id": "I2000002",
    "nama": "B"
    },
    {
    "id": "I2000003",
    "nama": "2B"
    },
    {
    "id": "I2000004",
    "nama": "MIROR"
    },
    {
    "id": "I2000005",
    "nama": "HAIRLINE"
    },
    {
    "id": "I2000007",
    "nama": "TIN PLATING"
    },
    {
    "id": "I2000008",
    "nama": "Ag PLATING"
    },
    {
    "id": "I2000009",
    "nama": "Cu PLATING"
    },
    {
    "id": "I2200013",
    "nama": "None"
    },
    {
    "id": "I2300014",
    "nama": "45"
    }
    ],
    "bentuk": [
    {
    "id": "B2000001",
    "nama": "ROLL",
    "dimensi": [
    "THICKNESS"
    ]
    },
    {
    "id": "B2000002",
    "nama": "SHEET",
    "dimensi": [
    "THICKNESS",
    "WIDTH",
    "LENGTH"
    ]
    },
    {
    "id": "B2000003",
    "nama": "ROUND BAR",
    "dimensi": [
    "DIAMETER"
    ]
    },
    {
    "id": "B2000004",
    "nama": "SQUARE BAR",
    "dimensi": [
    "SISI-1",
    "SISI-2"
    ]
    },
    {
    "id": "B2000005",
    "nama": "HEXAGON BAR",
    "dimensi": [
    "SISI"
    ]
    },
    {
    "id": "B2000006",
    "nama": "OKTAGON BAR",
    "dimensi": [
    "SISI"
    ]
    },
    {
    "id": "B2000007",
    "nama": "TUBE",
    "dimensi": [
    "OUT DIAMETER",
    "IN DIAMETER"
    ]
    },
    {
    "id": "B2000009",
    "nama": "PENTAGON BAR",
    "dimensi": [
    "SISI"
    ]
    }
    ],
    "supplier": [
    "SIAM POONGSAN METAL CO.LTD",
    "PT. WALSIN PRECISION TECHNOLOGY SDN BHD",
    "PT. HALIM INTI CEMERLANG",
    "PT.MICS STEEL INDONESIA",
    "JEIL INDUSTRIAL CO.LTD",
    "PT. TISCO DC WORX"
    ],
    "dimensiOpsi": [
    "HARDNESS",
    "THICKNESS",
    "COIL",
    "WIDTH",
    "LENGTH",
    "DIAMETER",
    "SISI",
    "SISI-1",
    "SISI-2",
    "OD",
    "ID",
    "OUT DIAMETER",
    "IN DIAMETER"
    ],
    "material": [
    {
    "id": "I2000002",
    "id_bentuk": "B2000001",
    "id_cat2": "I2000001",
    "nama": "BSR-C2680-1/2H-0.35",
    "spek": "C2680",
    "maker": "SIAM POONGSAN METAL CO.LTD",
    "negara": "SIAM POONGSAN",
    "density": "8.47",
    "hardness": "1/2H",
    "surface": "I2200013",
    "dim": {
    "THICKNESS": "0.35"
    },
    "komp": {
    "Cu": "65",
    "Zn": "35"
    },
    "forecast": "",
    "safety": "",
    "order": "",
    "max": "",
    "sup": [
    {
    "s": "SIAM POONGSAN METAL CO.LTD",
    "lead": "",
    "min": ""
    }
    ],
    "aktif": "aktif"
    },
    {
    "id": "I2000003",
    "id_bentuk": "B2000001",
    "id_cat2": "I2000002",
    "nama": "COPPER-C1100-1/2H-0.50",
    "spek": "C1100",
    "maker": "SIAM POONGSAN METAL CO.LTD",
    "negara": "SIAM POONGSAN",
    "density": "8.94",
    "hardness": "1/2H",
    "surface": "I2200013",
    "dim": {
    "THICKNESS": "0.5"
    },
    "komp": {
    "Cu": "100"
    },
    "forecast": "",
    "safety": "",
    "order": "",
    "max": "",
    "sup": [
    {
    "s": "SIAM POONGSAN METAL CO.LTD",
    "lead": "",
    "min": ""
    }
    ],
    "aktif": "aktif"
    },
    {
    "id": "I2000071",
    "id_bentuk": "B2000001",
    "id_cat2": "I2000003",
    "nama": "PBS-C5191-1/2H-0.80",
    "spek": "C5191",
    "maker": "SIAM POONGSAN METAL CO.LTD",
    "negara": "SIAM POONGSAN",
    "density": "8.80",
    "hardness": "1/2H",
    "surface": "I2200013",
    "dim": {
    "THICKNESS": "0.8"
    },
    "komp": {
    "Cu": "65",
    "Zn": "35"
    },
    "forecast": "",
    "safety": "",
    "order": "",
    "max": "",
    "sup": [
    {
    "s": "SIAM POONGSAN METAL CO.LTD",
    "lead": "",
    "min": ""
    }
    ],
    "aktif": "aktif"
    },
    {
    "id": "I2000031",
    "id_bentuk": "B2000001",
    "id_cat2": "I2000004",
    "nama": "SUS-304-3/4H-0.20",
    "spek": "304",
    "maker": "WALSIN PRECISION TECHNOLOGY SDN BHD",
    "negara": "WALSIN PRECISION TECHNOLOGY SDN BHD",
    "density": "7.93",
    "hardness": "3/4H",
    "surface": "I2200013",
    "dim": {
    "THICKNESS": "0.2"
    },
    "komp": {
    "Cr": "18-20",
    "Fe": "REM",
    "Ni": "8-10"
    },
    "forecast": "",
    "safety": "",
    "order": "",
    "max": "",
    "sup": [
    {
    "s": "PT. WALSIN PRECISION TECHNOLOGY SDN BHD",
    "lead": "",
    "min": ""
    }
    ],
    "aktif": "aktif"
    },
    {
    "id": "I2000072",
    "id_bentuk": "B2000001",
    "id_cat2": "I2000005",
    "nama": "AL-A1100-H14-0.80",
    "spek": "A1100",
    "maker": "STARMAS",
    "negara": "STARMAS",
    "density": "2.70",
    "hardness": "H14",
    "surface": "I2200013",
    "dim": {
    "THICKNESS": "0.8"
    },
    "komp": {},
    "forecast": "",
    "safety": "",
    "order": "",
    "max": "",
    "sup": [
    {
    "s": "PT. HALIM INTI CEMERLANG",
    "lead": "",
    "min": ""
    }
    ],
    "aktif": "aktif"
    },
    {
    "id": "I2000040",
    "id_bentuk": "B2000001",
    "id_cat2": "I2000006",
    "nama": "HOT ROLLED-SPHC-PO-0-2.00",
    "spek": "SPHC-PO",
    "maker": "PT.MICS STEEL INDONESIA",
    "negara": "CHINA STEEL",
    "density": "7.85",
    "hardness": "0",
    "surface": "I2200013",
    "dim": {
    "THICKNESS": "2.0"
    },
    "komp": {
    "Fe": "100",
    "Ni": "0"
    },
    "forecast": "",
    "safety": "",
    "order": "",
    "max": "",
    "sup": [
    {
    "s": "PT.MICS STEEL INDONESIA",
    "lead": "",
    "min": ""
    }
    ],
    "aktif": "aktif"
    },
    {
    "id": "I2000044",
    "id_bentuk": "B2000001",
    "id_cat2": "I2000007",
    "nama": "COLD ROLLED-SPCC-0-0.50",
    "spek": "SPCC",
    "maker": "PT.MICS STEEL INDONESIA",
    "negara": "POSCO",
    "density": "7.85",
    "hardness": "0",
    "surface": "I2200013",
    "dim": {
    "THICKNESS": "0.5"
    },
    "komp": {
    "Fe": "100",
    "Ni": "0"
    },
    "forecast": "",
    "safety": "",
    "order": "",
    "max": "",
    "sup": [
    {
    "s": "PT.MICS STEEL INDONESIA",
    "lead": "",
    "min": ""
    }
    ],
    "aktif": "aktif"
    },
    {
    "id": "I2500765",
    "id_bentuk": "B2000002",
    "id_cat2": "I2000007",
    "nama": "SPCC-SD-0-None-1.40-COLD ROLLED",
    "spek": "SPCC-SD",
    "maker": "PT.MICS STEEL INDONESIA",
    "negara": "POSCO",
    "density": "7.85",
    "hardness": "0",
    "surface": "I2200013",
    "dim": {
    "WIDTH": "166",
    "LENGTH": "1650",
    "THICKNESS": "1.40"
    },
    "komp": {},
    "forecast": "",
    "safety": "",
    "order": "",
    "max": "",
    "sup": [
    {
    "s": "PT.MICS STEEL INDONESIA",
    "lead": "3 DAYS",
    "min": "1000"
    }
    ],
    "aktif": "aktif"
    },
    {
    "id": "I2500776",
    "id_bentuk": "B2000002",
    "id_cat2": "I2000006",
    "nama": "SPH270C-OD-0-None-1.60-360-1219-HOT ROLLED",
    "spek": "SPH270C-OD",
    "maker": "PT.MICS STEEL INDONESIA",
    "negara": "POSCO",
    "density": "7.85",
    "hardness": "0",
    "surface": "I2200013",
    "dim": {
    "WIDTH": "360",
    "LENGTH": "1219",
    "THICKNESS": "1.60"
    },
    "komp": {},
    "forecast": "",
    "safety": "",
    "order": "",
    "max": "",
    "sup": [
    {
    "s": "PT.MICS STEEL INDONESIA",
    "lead": "3 DAYS",
    "min": "1000"
    }
    ],
    "aktif": "aktif"
    },
    {
    "id": "I2500979",
    "id_bentuk": "B2000002",
    "id_cat2": "I2000002",
    "nama": "C1100-1/4H-None-1.00-1000-1000-COPPER",
    "spek": "C1100",
    "maker": "JEIL INDUSTRIAL CO.LTD",
    "negara": "CHINA",
    "density": "8.94",
    "hardness": "1/4H",
    "surface": "I2200013",
    "dim": {
    "WIDTH": "1000",
    "LENGTH": "1000",
    "THICKNESS": "1.00"
    },
    "komp": {},
    "forecast": "",
    "safety": "",
    "order": "",
    "max": "",
    "sup": [
    {
    "s": "JEIL INDUSTRIAL CO.LTD",
    "lead": "2 MONTH",
    "min": "3000"
    }
    ],
    "aktif": "aktif"
    },
    {
    "id": "I2501143",
    "id_bentuk": "B2000002",
    "id_cat2": "I2000004",
    "nama": "304-0-2B-0.5-405-2440-SUS",
    "spek": "304",
    "maker": "PT. TISCO DC WORX",
    "negara": "dowoon",
    "density": "7.93",
    "hardness": "0",
    "surface": "I2000003",
    "dim": {
    "WIDTH": "405",
    "LENGTH": "2440",
    "THICKNESS": "0.5"
    },
    "komp": {},
    "forecast": "",
    "safety": "",
    "order": "",
    "max": "",
    "sup": [
    {
    "s": "PT. TISCO DC WORX",
    "lead": "",
    "min": ""
    }
    ],
    "aktif": "aktif"
    }
    ]
    };

  /* Master Spesifikasi (usulan baru): nilai awal diambil dari spek yang sudah dipakai di material, per Type Alloy (Level 3) */
  (function () {
    var src = {
      I2000001:['C2680','C2600','C2801'], I2000002:['C1100','C1020','C1100R'], I2000003:['C5191','C5210'],
      I2000004:['304','301','430','316L'], I2000005:['A1100','A1050','A5052'],
      I2000006:['SPHC-PO','SPH270C-OD','SPH440','SPH440-OD','SAPH440-PO','SPH590-OD','JSH270C','SS400'],
      I2000007:['SPC270C','SPCC-SD','SCGA270C-45','SPCC','SPC270D','JSC270C','SPC440','SECC','SPCD','JSC270D']
    }, n = 0; seed.spesifikasi = [];
    Object.keys(src).forEach(function (a) { src[a].forEach(function (nm) { n++; seed.spesifikasi.push({id:'I20' + String(n).padStart(5, '0'), id_cat2:a, nama:nm, aktif:'aktif'}); }); });
    /* Material tambahan (data asli) untuk contoh spesifikasi sama / ekuivalen */
    Array.prototype.push.apply(seed.material, [{"id":"I2100112","id_bentuk":"B2000001","id_cat2":"I2000007","nama":"COLD ROLLED-SPC270C-0-1.20","spek":"SPC270C","maker":"PT.MICS STEEL INDONESIA","density":"7.85","hardness":"0","surface":"I2200013","dim":{"THICKNESS":"1.2"},"komp":{},"forecast":"","safety":"","order":"","max":"","sup":[{"s":"PT.MICS STEEL INDONESIA","lead":"","min":""}],"aktif":"aktif"},{"id":"I2200239","id_bentuk":"B2000001","id_cat2":"I2000007","nama":"COLD ROLLED-SPC270C-0-0.7-None","spek":"SPC270C","maker":"PT. POSCO - IJPC","density":"7.85","hardness":"0","surface":"I2200013","dim":{"THICKNESS":"0.7"},"komp":{},"forecast":"","safety":"","order":"","max":"","sup":[{"s":"PT. POSCO - IJPC","lead":"7 Days","min":"1000"}],"aktif":"aktif"},{"id":"I2300383","id_bentuk":"B2000001","id_cat2":"I2000007","nama":"COLD ROLLED-JSC270C-0-2.0-None","spek":"JSC270C","maker":"PT.MICS STEEL INDONESIA","density":"7.85","hardness":"0","surface":"I2200013","dim":{"THICKNESS":"2.0"},"komp":{},"forecast":"","safety":"","order":"","max":"","sup":[{"s":"PT.MICS STEEL INDONESIA","lead":"7 Days","min":"1000"}],"aktif":"aktif"},{"id":"I2300504","id_bentuk":"B2000001","id_cat2":"I2000007","nama":"COLD ROLLED-SPC270C-0-1.0-None","spek":"SPC270C","maker":"PT. TT METALS INDONESIA","density":"7.85","hardness":"0","surface":"I2200013","dim":{"THICKNESS":"1.0"},"komp":{},"forecast":"","safety":"","order":"","max":"","sup":[{"s":"PT. TT METALS INDONESIA","lead":"7 Days","min":"1000"}],"aktif":"aktif"}]);
    [...new Set(seed.material.flatMap(function (m) { return m.sup.map(function (x) { return x.s; }); }))].forEach(function (n) { if (seed.supplier.indexOf(n) < 0) seed.supplier.push(n); });
    seed.material.forEach(function (m) {
      var sp = seed.spesifikasi.filter(function (x) { return x.id_cat2 === m.id_cat2 && x.nama === m.spek; })[0]; m.id_spek = sp ? sp.id : '';
    });
  })();

  /* ---------- Util ---------- */
  var perm = {Add:true, View:true, Manage:true, Delete:true};
  var permCbs = [];
  function nextId(prefix, rows) {
    var max = 0; rows.forEach(function (r) { var n = parseInt(String(r.id).slice(-5), 10); if (n > max) max = n; });
    return prefix + String(max + 1).padStart(5, '0');
  }
  function badge(a) { return a === 'aktif' ? '<span class="lbl ok">Aktif</span>' : '<span class="lbl off">Non Aktif</span>'; }
  function fmt(n, d) { return Number(n).toLocaleString('en-US', {minimumFractionDigits: d || 0, maximumFractionDigits: d == null ? 2 : d}); }

  /* ---------- Shell (topbar + sidebar) ---------- */
  var MENU = [
    {hdr:'Inventory'},
    {key:'inventory_1', href:'inventory_1.html', icon:'①', label:'Level 1 - Type'},
    {key:'inventory_2', href:'inventory_2.html', icon:'②', label:'Level 2 - Ferrous / Non Ferrous'},
    {key:'inventory_3', href:'inventory_3.html', icon:'③', label:'Level 3 - Alloy'},
    {key:'inventory_4', href:'inventory_4.html', icon:'④', label:'Level 4 - Material'},
    {hdr:'Master'},
    {key:'master_bentuk', href:'master_bentuk.html', icon:'▭', label:'Master Bentuk'},
    {key:'master_spesifikasi', href:'master_spesifikasi.html', icon:'≡', label:'Master Spesifikasi'},
    {key:'master_surface', href:'master_surface.html', icon:'▦', label:'Master Surface'},
    {hdr:'Master Data'},
    {key:'master_suplier', href:'master_suplier.html', icon:'⌂', label:'Master Supplier'},
    {key:'master_lme', href:'master_lme.html', icon:'Ⓜ', label:'Master LME'},
    {key:'master_kurs', href:'master_kurs.html', icon:'$', label:'Master Kurs'},
    {key:'bookprice_material', href:'bookprice_material.html', icon:'▤', label:'Bookprice Material'},
    {hdr:'Asset'},
    {key:'asset_category', href:'asset_category.html', icon:'▥', label:'Asset Category'},
    {key:'asset', href:'asset.html', icon:'⚙', label:'Asset'},
    {hdr:'Price Reference'},
    {key:'price_reference_purchase', href:'price_reference_purchase.html', icon:'⇩', label:'Price Reference Purchase'},
    {key:'price_reference_costing', href:'price_reference_costing.html', icon:'⇨', label:'Price Reference Costing'},
    {hdr:'Rate'},
    {key:'master_rate_umum', href:'master_rate_umum.html', icon:'%', label:'Master Rate Umum'},
    {hdr:'Product Costing'},
    {key:'flow_process', href:'flow_process.html', icon:'⇉', label:'Flow Process'},
    {key:'product_costing', href:'product_costing.html', icon:'∑', label:'Product Costing'},
    {key:'report_costing', href:'report_costing.html', icon:'▤', label:'Report Costing Detail'},
    {key:'notifikasi', href:'notifikasi.html', icon:'🔔', label:'Notifikasi Costing'},
    {key:'reset_pc', href:'#reset', icon:'↺', label:'Reset data mockup'},
    {hdr:'Pricelist Dasar'},
    {key:'pricelist_dasar', href:'pricelist_dasar.html', icon:'≣', label:'Pricelist'},
    {key:'pricelist_approval', href:'pricelist_approval.html', icon:'✔', label:'Approval Pricelist'},
    {hdr:'Purchasing'},
    {key:'purchase_request', href:'purchase_request.html', icon:'☷', label:'Purchase Request'},
    {key:'purchase_order', href:'purchase_order.html', icon:'☰', label:'Purchase Order'},
    {hdr:'Pengadaan Non Material'},
    {key:'flow_pengadaan', href:'flow_pengadaan.html', icon:'⇢', label:'Peta Alur Pengadaan'},
    {key:'non_rutin_index', href:'non_rutin_index.html', icon:'☷', label:'PR Departemen'},
    {key:'non_rutin_approval_head', href:'non_rutin_approval_head.html', icon:'✔', label:'  Approval Head Dept'},
    {key:'non_rutin_approval_cost_control', href:'non_rutin_approval_cost_control.html', icon:'✔', label:'  Approval Cost Control'},
    {key:'non_rutin_approval_management', href:'non_rutin_approval_management.html', icon:'✔', label:'  Approval Management'},
    {key:'asset_planning_index_asset', href:'asset_planning_index_asset.html', icon:'▥', label:'Planning Asset'},
    {key:'asset_planning_index_asset_approve', href:'asset_planning_index_asset_approve.html', icon:'✔', label:'  Approval Planning Asset'},
    {key:'pr_asset_pr', href:'pr_asset_pr.html', icon:'☷', label:'PR Asset'},
    {key:'pr_asset_approval_head', href:'pr_asset_approval_head.html', icon:'✔', label:'  Approval Head'},
    {key:'pr_asset_approval_cost_control', href:'pr_asset_approval_cost_control.html', icon:'✔', label:'  Approval Cost Control'},
    {key:'pr_asset_approval_management', href:'pr_asset_approval_management.html', icon:'✔', label:'  Approval Management'},
    {key:'app_pr_stock_approval_head', href:'app_pr_stock_approval_head.html', icon:'✔', label:'PR Stock - Approval Head'},
    {key:'app_pr_stock_approval_cost_control', href:'app_pr_stock_approval_cost_control.html', icon:'✔', label:'PR Stock - Approval Cost Control'},
    {key:'app_pr_stock_approval_management', href:'app_pr_stock_approval_management.html', icon:'✔', label:'PR Stock - Approval Management'},
    {key:'metode_pembelian_pr', href:'metode_pembelian_pr.html', icon:'☰', label:'Metode Pembelian - PR'},
    {key:'purchase_order_non_material', href:'purchase_order_non_material.html', icon:'☰', label:'PO Non Material'},
    {key:'approval_po_non_material', href:'approval_po_non_material.html', icon:'✔', label:'Approval PO Non Material'},
    {hdr:'Penerimaan & Hutang'},
    {key:'incoming_departemen', href:'incoming_departemen.html', icon:'⇩', label:'Incoming Departemen'},
    {key:'incoming_asset_asset', href:'incoming_asset_asset.html', icon:'⇩', label:'Incoming Asset'},
    {key:'purchase_order_payment', href:'purchase_order_payment.html', icon:'▤', label:'Receive Invoice'},
    {key:'request_payment_index', href:'request_payment_index.html', icon:'$', label:'Dashboard Pembayaran (Finance)'},
    {key:'request_payment_list_approve_checker', href:'request_payment_list_approve_checker.html', icon:'✔', label:'Request Payment - Checker'},
    {key:'request_payment_list_approve_management', href:'request_payment_list_approve_management.html', icon:'✔', label:'Request Payment - Management'},
    {key:'pembayaran_material_payment_list', href:'pembayaran_material_payment_list.html', icon:'$', label:'Payment List'},
    {hdr:'Penjualan'},
    {key:'transaksi_inquiry', href:'transaksi_inquiry.html', icon:'?', label:'Inquiry (CRCL)'},
    {key:'pricelist', href:'pricelist.html', icon:'≣', label:'Pricelist (lama, digantikan)'},
    {key:'penawaran', href:'penawaran.html', icon:'✉', label:'Penawaran Material'},
    {key:'index_approval', href:'index_approval.html', icon:'✔', label:'Approval Revisi Penawaran'},
    {key:'approval_diskon', href:'approval_diskon.html', icon:'%', label:'Approval Diskon Penawaran'},
    {key:'penawaran_slitting', href:'penawaran_slitting.html', icon:'✂', label:'Penawaran Slitting'},
    {key:'spk_marketing', href:'spk_marketing.html', icon:'☰', label:'SPK Marketing'},
    {key:'index_slitting', href:'index_slitting.html', icon:'☰', label:'SPK Marketing Slitting'}
  ];
  function shell(opt) {
    var side = '<div class="side-search"><input type="search" id="msq" placeholder="Cari menu..." autocomplete="off" aria-label="Cari menu"></div><a href="index.html" class="mi"><i>⌂</i>Daftar Mockup</a>';
    MENU.forEach(function (m) {
      side += m.hdr ? '<div class="hdr">' + m.hdr + '</div>'
        : '<a class="mi sub' + (m.key === opt.key ? ' active' : '') + '" href="' + m.href + '"><i>' + m.icon + '</i>' + m.label + '</a>';
    });
    document.body.insertAdjacentHTML('afterbegin',
      '<header class="topbar"><div class="brand"><b>Metals</b>indo</div>' +
      '<button class="toggle" id="tg" aria-label="Menu">☰</button>' +
      '<div class="top-right">' + (w.Mock && w.Mock.pc ? '<div class="bell" id="bell"><button type="button" aria-label="Notifikasi">🔔<span class="bc" id="bc"></span></button><div class="bell-pop" id="bp"></div></div>' : '') + '<button type="button" class="guide-btn" id="guide" title="Panduan halaman ini">❓ Panduan</button><div class="perm" id="perm"><button type="button">Simulasi hak akses ▾</button><div class="perm-pop"><h5>has_permission()</h5>' +
      ['Add','View','Manage','Delete'].map(function (p) { return '<label><input type="checkbox" data-p="' + p + '" checked> Level_X.' + p + '</label>'; }).join('') +
      '</div></div></div></header>' +
      '<aside class="sidebar">' + side + '</aside>');
    document.getElementById('guide').onclick = function () { if (w.Mock.guide) return w.Mock.guide.start(); var sc = document.createElement('script'); sc.src = 'assets/guide.js'; sc.onload = function () { w.Mock.guide.start(); }; document.head.appendChild(sc); };
    var msq = document.getElementById('msq');
    msq.oninput = function () {
      var q = msq.value.trim().toLowerCase(), hdr = null, any = false, shown = 0;
      var kids = document.querySelectorAll('.sidebar > .hdr, .sidebar > a.mi, .sidebar > .side-empty');
      kids.forEach(function (el) {
        if (el.classList.contains('side-empty')) { el.style.display = 'none'; return; }
        if (el.classList.contains('hdr')) { if (hdr) hdr.style.display = any || !q ? '' : 'none'; hdr = el; any = false; return; }
        var ok = !q || el.textContent.toLowerCase().indexOf(q) > -1 || (hdr && hdr.textContent.toLowerCase().indexOf(q) > -1);
        el.style.display = ok ? '' : 'none'; if (ok) { any = true; shown++; }
      });
      if (hdr) hdr.style.display = any || !q ? '' : 'none';
      var em = document.querySelector('.side-empty');
      if (!em) { em = document.createElement('div'); em.className = 'side-empty'; em.textContent = 'Menu tidak ditemukan'; document.querySelector('.sidebar').appendChild(em); }
      em.style.display = q && !shown ? 'block' : 'none';
    };
    var rs = document.querySelector('.sidebar a[href="#reset"]'); if (rs) rs.onclick = function (e) { e.preventDefault(); if (w.Mock.pc) { w.Mock.pc.reset(); try { localStorage.removeItem('metalsindo_pen_disc_v1'); localStorage.removeItem('metalsindo_mock_data_v1'); } catch (x) {} location.reload(); } else { try { localStorage.removeItem('metalsindo_pen_disc_v1'); localStorage.removeItem('metalsindo_mock_data_v1'); } catch (x) {} location.reload(); } };
    var c = $('#app');
    c.innerHTML = '<div class="content-wrap"><section class="content-header"><h1>' + esc(opt.title) + ' <small>' + esc(opt.sub || '') + '</small></h1>' +
      '<div class="crumb">' + esc(opt.crumb || 'Inventory') + ' &raquo; ' + esc(opt.title) + '</div></section>' +
      '<section class="content"><div class="mock-banner">' + (w.Mock && w.Mock.pc ? 'Mockup statis - data contoh (workbook dan DB lokal) tersimpan di <b>localStorage browser</b> agar alur rate → costing → pricelist → penawaran terlihat lintas halaman; gunakan menu "Reset data costing" untuk mengembalikan. Halaman: <code>' + esc(opt.module) + '</code>' : 'Mockup statis - data contoh dari database lokal, hasil Add/Edit/Approve/Delete tersimpan di <b>localStorage browser</b> (tetap ada setelah reload; menu "Reset data mockup" mengembalikan data awal). Sumber: <code>application/modules/' + esc(opt.module) + '</code>') + '</div>' +
      '<div id="page"></div></section><footer class="footer">Metalsindo - mockup UI (HTML/CSS/JS)</footer></div>';
    if (w.Mock && w.Mock.pc) { bellInit(); }
    $('#tg').onclick = function () { document.body.classList.toggle(innerWidth <= 800 ? 'open-side' : 'collapsed'); };
    $('#perm>button').onclick = function (e) { e.stopPropagation(); $('#perm').classList.toggle('open'); };
    document.addEventListener('click', function (e) { if (!e.target.closest('#perm')) $('#perm').classList.remove('open'); });
    document.querySelectorAll('#perm input').forEach(function (i) {
      i.onchange = function () { perm[i.dataset.p] = i.checked; permCbs.forEach(function (f) { f(); }); };
    });
    return $('#page');
  }

  function bellInit() {
    var P = w.Mock.pc;
    function draw() { var n = P.st.ntf.filter(function (x) { return !x.dibaca; }); var bc = $('#bc'); bc.textContent = n.length || ''; bc.style.display = n.length ? 'inline-block' : 'none';
      $('#bp').innerHTML = '<h5>Notifikasi</h5>' + (P.st.ntf.length ? P.st.ntf.slice(0, 5).map(function (x) { return '<div class="bn' + (x.dibaca ? '' : ' un') + '">' + esc(x.pesan) + '<small>' + esc(x.tgl) + ' · ' + esc(x.modul) + '</small></div>'; }).join('') : '<div class="bn">Tidak ada notifikasi</div>') + '<a href="notifikasi.html">Lihat semua notifikasi</a>'; }
    draw(); $('#bell>button').onclick = function (e) { e.stopPropagation(); $('#bell').classList.toggle('open'); };
    document.addEventListener('click', function (e) { if (!e.target.closest('#bell')) $('#bell').classList.remove('open'); });
    w.Mock.bellRefresh = draw;
  }

  /* ---------- Modal / confirm / toast ---------- */
  var ovEl;
  function ov() {
    if (!ovEl) {
      ovEl = document.createElement('div'); ovEl.className = 'ov';
      ovEl.addEventListener('mousedown', function (e) { if (e.target === ovEl && ovEl._dismiss !== false) closeModal(); });
      document.body.appendChild(ovEl);
    }
    return ovEl;
  }
  function openModal(title, body, o) {
    o = o || {}; var e = ov();
    e.innerHTML = '<div class="md ' + (o.size || '') + '" role="dialog" aria-modal="true"><div class="md-h"><h4>' + esc(title) + '</h4><button class="x" aria-label="Tutup">&times;</button></div>' +
      '<div class="md-b"></div>' + (o.footer === false ? '' : '<div class="md-f"><button class="btn btn-danger x">✕ Close</button></div>') + '</div>';
    var b = e.querySelector('.md-b'); if (typeof body === 'string') b.innerHTML = body; else b.appendChild(body);
    e.querySelectorAll('.x').forEach(function (x) { x.onclick = closeModal; });
    e.classList.add('show'); e._dismiss = true; return b;
  }
  function closeModal() { if (ovEl) { ovEl.classList.remove('show'); ovEl.innerHTML = ''; } }
  function swal(o, cb) {
    var e = document.createElement('div'); e.className = 'ov show'; e.style.zIndex = 150;
    var ic = {warning:'!', success:'✓', error:'✕'}[o.type || 'warning'];
    e.innerHTML = '<div class="sw"><div class="ic ' + (o.type || '') + '">' + ic + '</div><h2>' + esc(o.title) + '</h2><p>' + esc(o.text || '') + '</p><div class="row"></div></div>';
    var row = e.querySelector('.row'); document.body.appendChild(e);
    function done(v) { e.remove(); if (cb) cb(v); }
    if (o.confirm) {
      row.innerHTML = '<button class="btn btn-default" data-v="0">' + esc(o.cancelText || 'Batal') + '</button><button class="btn ' + (o.danger ? 'btn-danger' : 'btn-primary') + '" data-v="1">' + esc(o.okText || 'Ya') + '</button>';
      row.querySelectorAll('button').forEach(function (b) { b.onclick = function () { done(b.dataset.v === '1'); }; });
    } else { row.innerHTML = '<button class="btn btn-primary">OK</button>'; row.firstChild.onclick = function () { done(true); }; }
  }
  function toast(msg, type) {
    var t = document.createElement('div'); t.className = 'toast ' + (type || 'ok'); t.textContent = msg; document.body.appendChild(t);
    requestAnimationFrame(function () { t.classList.add('show'); });
    setTimeout(function () { t.classList.remove('show'); setTimeout(function () { t.remove(); }, 250); }, 2200);
  }

  /* ---------- Data table (cari, urut, paging, aksi) ---------- */
  function dataTable(host, cfg) {
    var st = {q:'', sort:-1, dir:1, page:1, size:10};
    host.innerHTML = '<div class="dt-top"><label>Show <select class="sz"><option>10</option><option>25</option><option>50</option></select> entries</label>' +
      '<label>Search: <input type="search" class="qq"></label></div><div class="tbl-wrap"><table class="tbl"></table></div><div class="dt-bot"><span class="info"></span><div class="pager"></div></div>';
    var tbl = $('table', host);
    function cols() { var c = cfg.cols.slice(); if (cfg.actions && (perm.View || perm.Manage || perm.Delete)) c.push({label:'Action', act:true, width:'150px'}); return c; }
    function val(r, c) { return c.text ? String(c.text(r)) : String(r[c.key] == null ? '' : r[c.key]); }
    function render() {
      var cs = cols(), rows = cfg.rows().slice();
      if (st.q) { var q = st.q.toLowerCase(); rows = rows.filter(function (r) { return cs.some(function (c) { return !c.act && val(r, c).toLowerCase().indexOf(q) > -1; }); }); }
      if (st.sort >= 0) { var sc = cs[st.sort]; rows.sort(function (a, b) { return val(a, sc).localeCompare(val(b, sc), undefined, {numeric:true}) * st.dir; }); }
      var total = rows.length, pages = Math.max(1, Math.ceil(total / st.size)); if (st.page > pages) st.page = pages;
      var from = (st.page - 1) * st.size, part = rows.slice(from, from + st.size);
      var h = '<thead><tr><th width="5">#</th>' + cs.map(function (c, i) {
        return '<th' + (c.act ? '' : ' class="sortable" data-i="' + i + '"') + (c.width ? ' style="width:' + c.width + '"' : '') + '>' + esc(c.label) +
          (st.sort === i ? (st.dir > 0 ? ' ▲' : ' ▼') : '') + '</th>'; }).join('') + '</tr></thead><tbody>';
      if (!part.length) h += '<tr><td class="empty" colspan="' + (cs.length + 1) + '">Tidak ada data</td></tr>';
      part.forEach(function (r, n) {
        h += '<tr><td>' + (from + n + 1) + '</td>' + cs.map(function (c) {
          if (c.act) {
            var a = cfg.actions, x = '<td class="act">';
            if (a.view && perm.View) x += '<button class="btn btn-sm btn-primary" title="' + (typeof a.view === 'string' ? 'Detail' : 'View') + '" data-a="view" data-id="' + esc(r.id) + '">' + (typeof a.view === 'string' ? esc(a.view) : '👁') + '</button>';
            if (a.edit && perm.Manage) x += '<button class="btn btn-sm btn-success" title="Edit" data-a="edit" data-id="' + esc(r.id) + '">✎</button>';
            if (a.del && perm.Delete) x += '<button class="btn btn-sm btn-danger" title="Delete" data-a="del" data-id="' + esc(r.id) + '">🗑</button>';
            return x + '</td>';
          }
          return '<td>' + (c.html ? c.html(r) : esc(val(r, c))) + '</td>'; }).join('') + '</tr>';
      });
      tbl.innerHTML = h + '</tbody>';
      $('.dt-bot .info', host).textContent = total ? 'Showing ' + (from + 1) + ' to ' + (from + part.length) + ' of ' + total + ' entries' : 'Showing 0 entries';
      var p = '<button data-p="' + (st.page - 1) + '"' + (st.page <= 1 ? ' disabled' : '') + '>Previous</button>';
      for (var i = 1; i <= pages; i++) p += '<button data-p="' + i + '" class="' + (i === st.page ? 'on' : '') + '">' + i + '</button>';
      $('.pager', host).innerHTML = p + '<button data-p="' + (st.page + 1) + '"' + (st.page >= pages ? ' disabled' : '') + '>Next</button>';
    }
    host.addEventListener('click', function (e) {
      var th = e.target.closest('th.sortable'); if (th) { var i = +th.dataset.i; st.dir = st.sort === i ? -st.dir : 1; st.sort = i; render(); return; }
      var pb = e.target.closest('.pager button'); if (pb && !pb.disabled) { st.page = +pb.dataset.p; render(); return; }
      var ab = e.target.closest('button[data-a]'); if (ab && cfg.onAction) cfg.onAction(ab.dataset.a, ab.dataset.id);
    });
    $('.qq', host).oninput = function (e) { st.q = e.target.value; st.page = 1; render(); };
    $('.sz', host).onchange = function (e) { st.size = +e.target.value; st.page = 1; render(); };
    permCbs.push(render); render();
    return {refresh: render};
  }

  /* ---------- Form helpers ---------- */
  function fg(label, control, hint) { return '<div class="fg"><label>' + label + '</label><div class="fc">' + control + (hint ? '<div class="hint">' + hint + '</div>' : '') + '</div></div>'; }
  function opts(list, val, valKey, txtKey, ph) {
    return '<option value="">' + esc(ph || '-- Pilih --') + '</option>' + list.map(function (o) {
      var v = valKey ? o[valKey] : o, t = txtKey ? o[txtKey] : o; return '<option value="' + esc(v) + '"' + (v === val ? ' selected' : '') + '>' + esc(t) + '</option>'; }).join('');
  }
  function validate(form) {
    var ok = true; form.querySelectorAll('.ferr').forEach(function (x) { x.remove(); });
    form.querySelectorAll('[required]').forEach(function (f) {
      f.classList.remove('err');
      if (!String(f.value).trim()) { ok = false; f.classList.add('err'); f.parentNode.insertAdjacentHTML('beforeend', '<div class="ferr">Wajib diisi</div>'); }
    });
    return ok;
  }
  function saveFlow(o) { // konfirmasi -> simpan -> sukses
    swal({title:'Anda Yakin?', text:o.text, type:'warning', confirm:true, okText:o.okText || 'Ya, Simpan!', cancelText:'Batal'}, function (ok) {
      if (!ok) return; o.save(); persistSave(); closeModal();
      swal({title:'Sukses', text:o.done, type:'success'}, o.after);
    });
  }
  function delFlow(text, done, cb) {
    swal({title:'Anda Yakin?', text:text, type:'warning', confirm:true, danger:true, okText:'Ya, Hapus!', cancelText:'Batal'}, function (ok) {
      if (!ok) return; cb(); persistSave(); toast(done, 'ok');
    });
  }

  /* ---------- Util tambahan (modul penjualan & master data) ---------- */
  function tabs(host, list) { // list: [{id,label,render(el)}]
    host.innerHTML = '<ul class="tabs">' + list.map(function (t, i) { return '<li><a href="#" data-i="' + i + '" class="' + (i ? '' : 'on') + '">' + esc(t.label) + '</a></li>'; }).join('') + '</ul><div class="tab-pane"></div>';
    var pane = host.querySelector('.tab-pane');
    function go(i) { host.querySelectorAll('.tabs a').forEach(function (a) { a.classList.toggle('on', +a.dataset.i === i); }); pane.innerHTML = ''; list[i].render(pane); }
    host.querySelector('.tabs').onclick = function (e) { var a = e.target.closest('a'); if (a) { e.preventDefault(); go(+a.dataset.i); } };
    go(0); return {go: go};
  }
  function idr(n) { return 'Rp ' + fmt(n, 0); }
  function pill(t, c) { return '<span class="lbl ' + (c || 'info') + '">' + esc(t) + '</span>'; }
  function dstr(s) { if (!s) return '-'; var d = new Date(String(s).slice(0, 10) + 'T00:00:00'); if (isNaN(d)) return esc(s);
    return ('0' + d.getDate()).slice(-2) + ' ' + ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'][d.getMonth()] + ' ' + d.getFullYear(); }
  function table(cols, rows, foot) { // tabel statis sederhana
    return '<div class="tbl-wrap"><table class="tbl"><thead><tr>' + cols.map(function (c) { return '<th' + (c.cls ? ' class="' + c.cls + '"' : '') + '>' + esc(c.label) + '</th>'; }).join('') + '</tr></thead><tbody>' +
      (rows.length ? rows.map(function (r, i) { return '<tr>' + cols.map(function (c) { var v = c.html ? c.html(r, i) : esc(r[c.key] == null || r[c.key] === '' ? '-' : r[c.key]); return '<td' + (c.cls ? ' class="' + c.cls + '"' : '') + '>' + v + '</td>'; }).join('') + '</tr>'; }).join('') : '<tr><td class="empty" colspan="' + cols.length + '">Tidak ada data</td></tr>') +
      '</tbody>' + (foot || '') + '</table></div>';
  }

  /* ---------- Persistensi hasil Add / Edit / Delete (localStorage) agar hasil input tetap terlihat setelah reload dan lintas halaman ---------- */
  var PERS = 'metalsindo_mock_data_v1', SIG = {};
  function pall() { try { return JSON.parse(localStorage.getItem(PERS)) || {}; } catch (e) { return {}; } }
  function persistRestore(name) {
    var tgt = w.Mock && w.Mock[name]; if (!tgt) return;
    try {
      SIG[name] = String(JSON.stringify(tgt).length); // tanda versi data awal: bila data contoh berubah, snapshot lama dibuang
      var all = pall(), src = all[name]; if (!src || all[name + '_sig'] !== SIG[name]) return;
      Object.keys(src).forEach(function (k) {
        var t = tgt[k], v = src[k];
        if (Array.isArray(t) && Array.isArray(v)) { t.length = 0; Array.prototype.push.apply(t, v); }
        else if (t && typeof t === 'object' && !Array.isArray(t) && v && typeof v === 'object') { Object.keys(t).forEach(function (x) { delete t[x]; }); Object.assign(t, v); }
        else tgt[k] = v;
      });
    } catch (e) {}
  }
  function persistSave() {
    try {
      var all = pall();
      ['seed', 'seedS', 'seedA', 'seedP', 'seedR'].forEach(function (n) {
        var s = w.Mock[n]; if (!s || SIG[n] == null) return; var o = {};
        Object.keys(s).forEach(function (k) { try { o[k] = JSON.parse(JSON.stringify(s[k])); } catch (e) {} });
        all[n] = o; all[n + '_sig'] = SIG[n];
      });
      localStorage.setItem(PERS, JSON.stringify(all));
    } catch (e) {}
  }
  /* Input uang dengan pemisah ribuan saat mengetik; nilai angka lewat money.val(el) */
  var money = {
    fmt: function (v) { v = String(v == null ? '' : v).replace(/[^0-9.]/g, ''); var p = v.split('.'); var i = p[0].replace(/^0+(?=\d)/, '').replace(/\B(?=(\d{3})+(?!\d))/g, ','); return p.length > 1 ? i + '.' + p.slice(1).join('').slice(0, 3) : i; },
    val: function (el) { return parseFloat(String(el.value).replace(/,/g, '')) || 0; },
    bind: function (el, cb) { el.oninput = function () { var pos = el.selectionStart, len = el.value.length; el.value = money.fmt(el.value); var np = Math.max(0, pos + el.value.length - len); try { el.setSelectionRange(np, np); } catch (e) {} if (cb) cb(); }; }
  };
  w.Mock = {tabs:tabs, idr:idr, pill:pill, dstr:dstr, stable:table, seed:seed, perm:perm, $:$, esc:esc, nextId:nextId, badge:badge, fmt:fmt, shell:shell, modal:openModal, close:closeModal,
    persistSave:persistSave, persistRestore:persistRestore, money:money, swal:swal, toast:toast, table:dataTable, fg:fg, opts:opts, validate:validate, saveFlow:saveFlow, delFlow:delFlow,
    onPerm:function (f) { permCbs.push(f); }};
  persistRestore('seed');
})(window);
