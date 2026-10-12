// Eksperimen backend Golang untuk modul material_planing (CodeIgniter -> Go).
//
// Asal port dari PHP:
//   - Inventory_4_model::get_data_material_planning()  -> GET/POST /api/material-planning
//   - Material_planing::EditHeader()                   -> POST /api/material-planning/edit-header
//   - Material_planing::Approve() / Close() / Tutup()  -> POST /api/material-planning/{approve,close,tutup}
//   - Material_planing::SaveEditHeaderNew()            -> POST /api/material-planning/save-fg
//
// Cara jalan (stdlib only, tanpa dependency):
//   cd application/modules/material_planing_experiment/backend-go
//   go run .
//   # atau: go build -o material-planning-go.exe . && ./material-planning-go.exe
//
// Env:
//   PORT=8081 USE_MOCK=true
//   (DB MySQL asli belum di-wire; lihat NewStore() bila mau sambung ke metalsindo_dev)
package main

import (
	"database/sql"
	"encoding/json"
	"fmt"
	"log"
	"net/http"
	"os"
	"strconv"
	"strings"
	"sync"
	"time"
)

// ---------- Types (mirror PHP output) ----------

type PlanningRow struct {
	IDDTSpkmarketing string  `json:"id_dt_spkmarketing"`
	NoSPK            string  `json:"no_spk"`
	Customer         string  `json:"customer"`
	IdCustomer       string  `json:"id_customer"`
	IDMaterial       string  `json:"id_material"`
	NoAlloy          string  `json:"no_alloy"`
	Thickness        float64 `json:"thickness"`
	Width            float64 `json:"width"`
	LengthVal        float64 `json:"length_val"`
	DeliveryDate     string  `json:"delivery_date"` // YYYY-MM-DD
	TotalWeight      float64 `json:"total_weight"`
	TotalSheet       float64 `json:"total_sheet"`
	FgBooking        float64 `json:"fg_booking"`
	StatusLanjutan   string  `json:"status_lanjutan"` // "1"=open, "2"=approved
}

type DataTablesRow struct {
	No           int     `json:"no"`
	NoSPK        string  `json:"no_spk"`
	Customer     string  `json:"customer"`
	KodeMaterial string  `json:"kode_material"`
	NoAlloy      string  `json:"no_aloy"`
	Thickness    float64 `json:"thickness"`
	Width        float64 `json:"width"`
	Length       string  `json:"length"`
	DeliveryDate string  `json:"delivery_date"`
	TotalWeight  string  `json:"total_weight"`
	TotalSheet   string  `json:"total_sheet"`
	TotalSPK     string  `json:"total_spk"`
	FG           string  `json:"fg"`
	Action       string  `json:"action"`
}

type StockItem struct {
	IDStock     string  `json:"id_stock"`
	LotNo       string  `json:"lotno"`
	Width       float64 `json:"width"`
	Qty         float64 `json:"qty"`
	Weight      float64 `json:"weight"`
	TotalWeight float64 `json:"totalweight"`
	Booking     float64 `json:"booking"`
	QtySheet    float64 `json:"qty_sheet,omitempty"`
	Customer    string  `json:"customer,omitempty"`
}

type SpkHeader struct {
	IDDTSpkmarketing string  `json:"id_dt_spkmarketing"`
	IDMaterial       string  `json:"id_material"`
	NoSurat          string  `json:"no_surat"`
	NoAlloy          string  `json:"no_alloy"`
	QtyProduk        float64 `json:"qty_produk"`
	Width            float64 `json:"width"`
	IDCustomer       string  `json:"id_customer"`
}

// ---------- Store (mock; ganti dengan MySQL bila siap) ----------

type Store struct {
	mu   sync.Mutex
	rows []PlanningRow
	// status per id_dt_spkmarketing, untuk simulasi Approve/Close/Tutup
	status map[string]string // id -> status_lanjutan
	closed map[string]bool   // id -> status_close_planning == CLS
	// koneksi MySQL opsional (nil = mock). Lihat db.go.
	db    *sql.DB
	useDB bool
}

func seedRows() []PlanningRow {
	return []PlanningRow{
		{IDDTSpkmarketing: "P2600001-1", NoSPK: "001/MP/SPK/IX/2026", Customer: "PT Contoh Steel", IDMaterial: "SUS304-2B-0.8", NoAlloy: "ALLOY-001", Thickness: 0.8, Width: 1219, LengthVal: 2438, DeliveryDate: "2026-09-20", TotalWeight: 5000, TotalSheet: 200, FgBooking: 1200.5, StatusLanjutan: "1"},
		{IDDTSpkmarketing: "P2600001-2", NoSPK: "001/MP/SPK/IX/2026", Customer: "PT Contoh Steel", IDMaterial: "SUS430-BA-1.0", NoAlloy: "ALLOY-002", Thickness: 1.0, Width: 1000, LengthVal: 2000, DeliveryDate: "2026-09-22", TotalWeight: 3200, TotalSheet: 150, FgBooking: 0, StatusLanjutan: "1"},
		{IDDTSpkmarketing: "P2600002-1", NoSPK: "002/MP/SPK/IX/2026", Customer: "PT Maju Jaya", IDMaterial: "SPCC-SD-1.2", NoAlloy: "ALLOY-003", Thickness: 1.2, Width: 914, LengthVal: 1828, DeliveryDate: "2026-09-25", TotalWeight: 7500, TotalSheet: 300, FgBooking: 7500, StatusLanjutan: "2"},
	}
}

func NewStore() *Store {
	s := &Store{rows: seedRows(), status: map[string]string{}, closed: map[string]bool{}}
	for _, r := range s.rows {
		s.status[r.IDDTSpkmarketing] = r.StatusLanjutan
	}
	// Coba sambung MySQL kecuali USE_MOCK=true. Gagal -> tetap mock (lihat db.go).
	s.enableDB()
	return s
}

// SQL aslinya (untuk referensi waktu wiring MySQL):
//   SELECT * FROM view_material_planning             -- list
//   SELECT a.*, b.id_customer, b.no_surat FROM dt_spkmarketing a
//     LEFT JOIN tr_spk_marketing b ON a.id_spkmarketing=b.id_spkmarketing
//     WHERE a.id_dt_spkmarketing=? AND a.id_material=?      -- EditHeader header
//   SELECT * FROM stock_material WHERE id_category3=? AND width>0 AND id_gudang='1'   -- stok WIP
//   SELECT * FROM stock_material WHERE id_category3=? AND width>=? AND id_gudang='3'   -- stok FG
//   UPDATE dt_spkmarketing SET status_lanjutan='2' WHERE id_dt_spkmarketing=?        -- Approve
//   UPDATE dt_spkmarketing SET status_lanjutan='3' WHERE id_dt_spkmarketing=?        -- Close
//   UPDATE dt_spkmarketing SET status_close_planning='CLS' WHERE id_dt_spkmarketing=? -- Tutup

func (s *Store) filtered(keyword string) []PlanningRow {
	if keyword == "" {
		return s.rows
	}
	kw := strings.ToLower(keyword)
	var out []PlanningRow
	for _, r := range s.rows {
		if strings.Contains(strings.ToLower(r.NoSPK), kw) ||
			strings.Contains(strings.ToLower(r.Customer), kw) ||
			strings.Contains(strings.ToLower(r.IDMaterial), kw) ||
			strings.Contains(strings.ToLower(r.NoAlloy), kw) {
			out = append(out, r)
		}
	}
	return out
}

// ---------- Helpers ----------

func writeJSON(w http.ResponseWriter, status int, v any) {
	w.Header().Set("Content-Type", "application/json")
	w.WriteHeader(status)
	_ = json.NewEncoder(w).Encode(v)
}

func decodeJSON(r *http.Request, v any) error {
	if r.Body == nil {
		return fmt.Errorf("empty body")
	}
	defer r.Body.Close()
	dec := json.NewDecoder(r.Body)
	dec.DisallowUnknownFields()
	return dec.Decode(v)
}

func fmtNum(f float64, dec int) string {
	format := "%." + strconv.Itoa(dec) + "f"
	s := fmt.Sprintf(format, f)
	// tambah koma ribuan sederhana
	parts := strings.Split(s, ".")
	intPart := parts[0]
	neg := ""
	if strings.HasPrefix(intPart, "-") {
		neg = "-"
		intPart = intPart[1:]
	}
	n := len(intPart)
	var out []byte
	for i, c := range intPart {
		if i > 0 && (n-i)%3 == 0 {
			out = append(out, ',')
		}
		out = append(out, byte(c))
	}
	if len(parts) > 1 {
		return neg + string(out) + "." + parts[1]
	}
	return neg + string(out)
}

func actionButtons(id, mat string, width float64, status string) string {
	base := fmt.Sprintf(`<a class="btn btn-warning btn-sm edit" href="javascript:void(0)" data-id_dt_spkmarketing="%s" data-id_material="%s" data-width="%g" data-view="edit"><i class="fa fa-bars"></i></a> `, id, mat, width)
	base += fmt.Sprintf(`<a class="btn btn-primary btn-sm" href="/purchase_request/add_pr/%s"><i class="fa fa-table"></i></a> `, id)
	base += fmt.Sprintf(`<a class="btn btn-success btn-sm delete" href="javascript:void(0)" data-id_dt_spkmarketing="%s"><i class="fa fa-check"></i></a>`, id)
	if status == "2" {
		base += fmt.Sprintf(` <a class="btn btn-warning btn-sm tutup" href="javascript:void(0)" data-id_dt_spkmarketing="%s"><i class="fa fa-times"></i></a>`, id)
	}
	return base
}

func withCORS(next http.Handler) http.Handler {
	return http.HandlerFunc(func(w http.ResponseWriter, r *http.Request) {
		w.Header().Set("Access-Control-Allow-Origin", "*")
		w.Header().Set("Access-Control-Allow-Methods", "GET, POST, OPTIONS")
		w.Header().Set("Access-Control-Allow-Headers", "Content-Type")
		if r.Method == http.MethodOptions {
			w.WriteHeader(http.StatusNoContent)
			return
		}
		next.ServeHTTP(w, r)
	})
}

func withLog(next http.Handler) http.Handler {
	return http.HandlerFunc(func(w http.ResponseWriter, r *http.Request) {
		start := time.Now()
		next.ServeHTTP(w, r)
		log.Printf("%s %s %s", r.Method, r.URL.Path, time.Since(start))
	})
}

// ---------- Handlers ----------

func healthHandler(s *Store) http.HandlerFunc {
	return func(w http.ResponseWriter, _ *http.Request) {
		writeJSON(w, http.StatusOK, map[string]any{
			"ok": true, "service": "material-planning-go",
			"mock":  !s.useDB,
			"useDB": s.useDB,
		})
	}
}

// Mendukung format DataTables (form-encoded dari jQuery) maupun JSON murni.
func listHandler(s *Store) http.HandlerFunc {
	return func(w http.ResponseWriter, r *http.Request) {
		_ = r.ParseForm()
		draw, _ := strconv.Atoi(firstOf(r.FormValue("draw"), "1"))
		start, _ := strconv.Atoi(firstOf(r.FormValue("start"), "0"))
		length, _ := strconv.Atoi(firstOf(r.FormValue("length"), "10"))
		keyword := r.FormValue("search[value]")
		if keyword == "" {
			keyword = r.FormValue("search")
		}
		// fallback JSON body {"draw":1,"start":0,"length":10,"search":{"value":"x"}}
		if strings.Contains(r.Header.Get("Content-Type"), "application/json") {
			var jb struct {
				Draw   int    `json:"draw"`
				Start  int    `json:"start"`
				Length int    `json:"length"`
				Search struct {
					Value string `json:"value"`
				} `json:"search"`
			}
			if err := json.NewDecoder(r.Body).Decode(&jb); err == nil {
				if jb.Draw > 0 {
					draw = jb.Draw
				}
				start = jb.Start
				if jb.Length != 0 {
					length = jb.Length
				}
				if jb.Search.Value != "" {
					keyword = jb.Search.Value
				}
			}
		}

		// paging
		if start < 0 {
			start = 0
		}
		if length <= 0 || length > 100 {
			length = 10
		}
		var page []PlanningRow
		var total, filtered int
		var statusOf = func(id string) string { return s.status[id] }
		if s.useDB {
			var err error
			total, filtered, err = s.dbCounts(keyword)
			if err != nil {
				writeJSON(w, http.StatusInternalServerError, map[string]string{"error": "DB list: " + err.Error()})
				return
			}
			page, err = s.dbListPage(keyword, start, length)
			if err != nil {
				writeJSON(w, http.StatusInternalServerError, map[string]string{"error": "DB list: " + err.Error()})
				return
			}
			statusOf = func(id string) string {
				for _, r := range page {
					if r.IDDTSpkmarketing == id {
						return r.StatusLanjutan
					}
				}
				return ""
			}
		} else {
			all := s.filtered(keyword)
			total = len(s.rows)
			filtered = len(all)
			end := start + length
			if end > len(all) {
				end = len(all)
			}
			if start > len(all) {
				start = len(all)
			}
			page = all[start:end]
		}

		data := make([]DataTablesRow, 0, len(page))
		for i, it := range page {
			no := start + i + 1
			delivery := "-"
			if it.DeliveryDate != "" {
				if t, err := time.Parse("2006-01-02", it.DeliveryDate); err == nil {
					delivery = t.Format("02-Jan-2006")
				} else {
					delivery = it.DeliveryDate
				}
			}
			status := statusOf(it.IDDTSpkmarketing)
			data = append(data, DataTablesRow{
				No: no, NoSPK: it.NoSPK, Customer: it.Customer,
				KodeMaterial: it.IDMaterial, NoAlloy: it.NoAlloy,
				Thickness: it.Thickness, Width: it.Width,
				Length:       fmtNum(it.LengthVal, 2),
				DeliveryDate: delivery,
				TotalWeight:  fmtNum(it.TotalWeight, 0),
				TotalSheet:   fmtNum(it.TotalSheet, 0),
				TotalSPK:     "-",
				FG:           fmtNum(it.FgBooking, 2),
				Action:       actionButtons(it.IDDTSpkmarketing, it.IDMaterial, it.Width, status),
			})
		}
		writeJSON(w, http.StatusOK, map[string]any{
			"draw": draw, "recordsTotal": total, "recordsFiltered": filtered, "data": data,
		})
	}
}

type editHeaderReq struct {
	ID         string  `json:"id"`
	IDMaterial string  `json:"id_material"`
	Width      float64 `json:"width"`
	View       string  `json:"view"`
}

func editHeaderHandler(s *Store) http.HandlerFunc {
	return func(w http.ResponseWriter, r *http.Request) {
		if r.Method != http.MethodPost {
			writeJSON(w, http.StatusMethodNotAllowed, map[string]string{"error": "POST only"})
			return
		}
		var req editHeaderReq
		if strings.Contains(r.Header.Get("Content-Type"), "application/json") {
			var raw struct {
				ID         string  `json:"id"`
				IDMaterial string  `json:"id_material"`
				Width      float64 `json:"width"`
				View       string  `json:"view"`
			}
			if err := json.NewDecoder(r.Body).Decode(&raw); err != nil {
				writeJSON(w, http.StatusBadRequest, map[string]string{"error": "JSON invalid: " + err.Error()})
				return
			}
			req = editHeaderReq{ID: raw.ID, IDMaterial: raw.IDMaterial, Width: raw.Width, View: raw.View}
		} else {
			_ = r.ParseForm()
			req = editHeaderReq{
				ID:         r.FormValue("id"),
				IDMaterial: r.FormValue("id_material"),
				View:       r.FormValue("view"),
			}
			if ws := r.FormValue("width"); ws != "" {
				req.Width, _ = strconv.ParseFloat(ws, 64)
			}
		}
		if req.ID == "" || req.IDMaterial == "" {
			writeJSON(w, http.StatusBadRequest, map[string]string{"error": "id dan id_material wajib"})
			return
		}
		if s.useDB {
			hdr, err := s.dbGetHeader(req.ID)
			if err != nil {
				writeJSON(w, http.StatusNotFound, map[string]string{"error": "SPK tidak ditemukan: " + err.Error()})
				return
			}
			wip, fg, err := s.dbGetStok(req.IDMaterial, hdr.Width)
			if err != nil {
				writeJSON(w, http.StatusInternalServerError, map[string]string{"error": "DB stok: " + err.Error()})
				return
			}
			poPr, _ := s.dbGetPoPr(req.IDMaterial)
			writeJSON(w, http.StatusOK, map[string]any{
				"header": []SpkHeader{{
					IDDTSpkmarketing: hdr.IDDTSpkmarketing, IDMaterial: hdr.IDMaterial,
					NoSurat: hdr.NoSPK, NoAlloy: hdr.NoAlloy,
					QtyProduk: hdr.TotalWeight, Width: hdr.Width, IDCustomer: hdr.IdCustomer,
				}},
				"width": hdr.Width, "stok": wip, "stok_fg": fg, "stok_book": []StockItem{},
				"get_po_pr": poPr, "viewx": req.View, "_mock": false,
			})
			return
		}
		// Cari header di mock (di MySQL: query dt_spkmarketing + tr_spk_marketing)
		var hdr *PlanningRow
		for _, rr := range s.rows {
			if rr.IDDTSpkmarketing == req.ID {
				c := rr
				hdr = &c
				break
			}
		}
		if hdr == nil {
			writeJSON(w, http.StatusNotFound, map[string]string{"error": "SPK tidak ditemukan"})
			return
		}
		// Mock stok WIP (gudang 1) + FG (gudang 3). Di MySQL: query stock_material.
		stok := []StockItem{
			{IDStock: "STK-WIP-001", LotNo: "LOT-WIP-001", Width: hdr.Width, Qty: 2, Weight: 1500, TotalWeight: 3000, Booking: 500},
		}
		stokFG := []StockItem{
			{IDStock: "STK-FG-001", LotNo: "LOT-FG-001", Width: hdr.Width, Qty: 5, Weight: 240, TotalWeight: 1200, Booking: 0, QtySheet: 10, Customer: hdr.Customer},
		}
		writeJSON(w, http.StatusOK, map[string]any{
			"header": []SpkHeader{{
				IDDTSpkmarketing: hdr.IDDTSpkmarketing, IDMaterial: hdr.IDMaterial,
				NoSurat: hdr.NoSPK, NoAlloy: hdr.NoAlloy,
				QtyProduk: hdr.TotalWeight, Width: hdr.Width, IDCustomer: "CUST-001",
			}},
			"width":     hdr.Width,
			"stok":      stok,
			"stok_fg":   stokFG,
			"stok_book": []StockItem{},
			// PHP: SUM(dt_trans_pr.totalweight) - SUM(dt_incoming.width_recive)
			"get_po_pr": "0.00",
			"viewx":     req.View,
			"_mock":     true,
		})
	}
}

func statusHandler(s *Store, to string, pesan string) http.HandlerFunc {
	return func(w http.ResponseWriter, r *http.Request) {
		if r.Method != http.MethodPost {
			writeJSON(w, http.StatusMethodNotAllowed, map[string]string{"error": "POST only"})
			return
		}
		var id string
		if strings.Contains(r.Header.Get("Content-Type"), "application/json") {
			var raw struct {
				ID string `json:"id"`
			}
			if err := json.NewDecoder(r.Body).Decode(&raw); err != nil {
				writeJSON(w, http.StatusBadRequest, map[string]any{"pesan": "JSON invalid: " + err.Error(), "status": 0})
				return
			}
			id = raw.ID
		} else {
			_ = r.ParseForm()
			id = r.FormValue("id")
		}
		if id == "" {
			writeJSON(w, http.StatusBadRequest, map[string]any{"pesan": "id wajib", "status": 0})
			return
		}
		if s.useDB {
			if err := s.dbSetStatus(id, to); err != nil {
				writeJSON(w, http.StatusNotFound, map[string]any{"pesan": "DB: " + err.Error(), "status": 0})
				return
			}
			writeJSON(w, http.StatusOK, map[string]any{"pesan": pesan, "status": 1, "_mock": false})
			return
		}
		s.mu.Lock()
		defer s.mu.Unlock()
		if _, ok := s.status[id]; !ok {
			writeJSON(w, http.StatusNotFound, map[string]any{"pesan": "id tidak ditemukan", "status": 0})
			return
		}
		if to == "CLS" {
			s.closed[id] = true
		} else {
			s.status[id] = to
		}
		writeJSON(w, http.StatusOK, map[string]any{"pesan": pesan, "status": 1})
	}
}

type saveFgDetail struct {
	ID         string  `json:"id"`  // booking biasa (chk_personal)
	ID2        string  `json:"id2"` // kirim (chk_personal2)
	Berat      float64 `json:"berat"`
	Width      float64 `json:"width"`
	Qty        float64 `json:"qty"`
	Weight     float64 `json:"weight"`
	Keterangan string  `json:"keterangan"`
}

type saveFgReq struct {
	IDMaterial       string         `json:"id_material"`
	IDCustomer       string         `json:"id_customer"`
	Width            float64        `json:"width"`
	IDDTSpkmarketing string         `json:"id_dt_spkmarketing"`
	NoSurat          string         `json:"no_surat"`
	Detail           []saveFgDetail `json:"detail"`
}

// Port dari SaveEditHeaderNew(): insert_batch stock_material_customer +
// update_batch stock_material (booking, delivery_book). Versi mock hanya validasi.
func saveFgHandler(_ *Store) http.HandlerFunc {
	return func(w http.ResponseWriter, r *http.Request) {
		if r.Method != http.MethodPost {
			writeJSON(w, http.StatusMethodNotAllowed, map[string]string{"error": "POST only"})
			return
		}
		var req saveFgReq
		ct := r.Header.Get("Content-Type")
		if strings.Contains(ct, "application/json") {
			if err := decodeJSON(r, &req); err != nil {
				writeJSON(w, http.StatusBadRequest, map[string]any{"pesan": "JSON invalid: " + err.Error(), "status": 0})
				return
			}
		} else {
			_ = r.ParseForm()
			req.IDMaterial = r.FormValue("id_material")
			req.IDCustomer = r.FormValue("id_customer")
			req.IDDTSpkmarketing = r.FormValue("id_dt_spkmarketing")
			req.NoSurat = r.FormValue("no_surat")
			req.Width, _ = strconv.ParseFloat(r.FormValue("width"), 64)
			// form multipart detail[0][id] dst. disederhanakan: wajib JSON untuk detail
			var body struct {
				Detail []saveFgDetail `json:"detail"`
			}
			_ = json.NewDecoder(r.Body).Decode(&body)
			req.Detail = body.Detail
		}
		if req.IDDTSpkmarketing == "" || len(req.Detail) == 0 {
			writeJSON(w, http.StatusBadRequest, map[string]any{"pesan": "Checklist minimal satu (detail kosong)", "status": 0})
			return
		}
		nBooking, nKirim := 0, 0
		for _, d := range req.Detail {
			if d.ID2 != "" {
				nKirim++
			} else if d.ID != "" {
				nBooking++
			}
		}
		writeJSON(w, http.StatusOK, map[string]any{
			"pesan":   fmt.Sprintf("Success save process (mock: booking=%d, kirim=%d)", nBooking, nKirim),
			"status":  1,
			"_mock":   true,
			"_todoDB": "insert_batch stock_material_customer + update_batch stock_material",
		})
	}
}

// ---------- small utils ----------

func firstOf(vals ...string) string {
	for _, v := range vals {
		if strings.TrimSpace(v) != "" {
			return v
		}
	}
	return ""
}

// ---------- main ----------

func main() {
	port := os.Getenv("PORT")
	if port == "" {
		port = "8081"
	}
	store := NewStore()

	mux := http.NewServeMux()
	mux.HandleFunc("/health", healthHandler(store))
	mux.HandleFunc("/api/material-planning", listHandler(store))
	mux.HandleFunc("/api/material-planning/list", listHandler(store))
	mux.HandleFunc("/api/material-planning/edit-header", editHeaderHandler(store))
	mux.HandleFunc("/api/material-planning/approve", statusHandler(store, "2", "Success Save Item. Thanks ..."))
	mux.HandleFunc("/api/material-planning/close", statusHandler(store, "3", "Success Save Item. Thanks ..."))
	mux.HandleFunc("/api/material-planning/tutup", statusHandler(store, "CLS", "Success Save Item. Thanks ..."))
	mux.HandleFunc("/api/material-planning/save-fg", saveFgHandler(store))

	handler := withLog(withCORS(mux))
	addr := ":" + port
	log.Printf("material-planning-go listening on %s (useDB=%v)", addr, store.useDB)
	if err := http.ListenAndServe(addr, handler); err != nil {
		log.Fatal(err)
	}
}
