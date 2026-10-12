// Koneksi MySQL opsional untuk eksperimen.
// Kalau USE_MOCK=true (default) atau DB tidak terjangkau -> pakai mock di main.go.
// Kalau DB terjangkau -> query beneran ke view_material_planning / dt_spkmarketing / stock_material.
package main

import (
	"database/sql"
	"fmt"
	"log"
	"os"
	"strconv"
	"strings"

	_ "github.com/go-sql-driver/mysql"
)

type DBConfig struct {
	Host, Port, User, Pass, Name string
}

func dbConfigFromEnv() DBConfig {
	// Default = DB yang sama dengan modul PHP asli (lihat
	// application/config/development/database.php, ENVIRONMENT=development).
	return DBConfig{
		Host: envOr("DB_HOST", "206.84.97.106"),
		Port: envOr("DB_PORT", "3306"),
		User: envOr("DB_USER", "alief"),
		Pass: envOr("DB_PASS", "sdk*^HkfJKjkefJFJLEJLE"),
		Name: envOr("DB_NAME", "metalsindo_dev"),
	}
}

func envOr(k, d string) string {
	if v := os.Getenv(k); v != "" {
		return v
	}
	return d
}

func openDB(cfg DBConfig) (*sql.DB, error) {
	dsn := fmt.Sprintf("%s:%s@tcp(%s:%s)/%s?parseTime=true&charset=utf8&loc=Local",
		cfg.User, cfg.Pass, cfg.Host, cfg.Port, cfg.Name)
	db, err := sql.Open("mysql", dsn)
	if err != nil {
		return nil, err
	}
	db.SetMaxOpenConns(5)
	if err := db.Ping(); err != nil {
		db.Close()
		return nil, err
	}
	// pastikan tabel/view eksperimen ada (di DB asli namanya view_material_planning)
	var n int
	if err := db.QueryRow("SELECT COUNT(*) FROM view_material_planning").Scan(&n); err != nil {
		db.Close()
		return nil, fmt.Errorf("view_material_planning tidak ada / tidak bisa dibaca: %w", err)
	}
	return db, nil
}

// dipanggil sekali saat startup; mengisi s.db dan s.useDB
func (s *Store) enableDB() {
	if os.Getenv("USE_MOCK") == "true" {
		log.Printf("USE_MOCK=true -> pakai data mock (DB di-skip)")
		return
	}
	cfg := dbConfigFromEnv()
	db, err := openDB(cfg)
	if err != nil {
		log.Printf("MySQL %s:%s/%s tidak terjangkau (%v) -> fallback mock", cfg.Host, cfg.Port, cfg.Name, err)
		return
	}
	s.mu.Lock()
	s.db = db
	s.useDB = true
	s.mu.Unlock()
	log.Printf("MySQL OK %s:%s/%s -> pakai data asli", cfg.Host, cfg.Port, cfg.Name)
}

const planningCols = `id_dt_spkmarketing, id_material, no_spk, customer,
	COALESCE(id_customer,''), no_alloy,
	COALESCE(thickness,0), COALESCE(width,0), COALESCE(length_val,''),
	COALESCE(DATE_FORMAT(delivery_date,'%Y-%m-%d'),''), COALESCE(total_weight,0), COALESCE(total_sheet,0),
	COALESCE(fg_booking,0), COALESCE(status_lanjutan,'1')`

func scanPlanningRow(rows *sql.Rows) (PlanningRow, error) {
	var r PlanningRow
	var lengthStr, delivery string
	err := rows.Scan(&r.IDDTSpkmarketing, &r.IDMaterial, &r.NoSPK, &r.Customer,
		&r.IdCustomer, &r.NoAlloy,
		&r.Thickness, &r.Width, &lengthStr, &delivery,
		&r.TotalWeight, &r.TotalSheet, &r.FgBooking, &r.StatusLanjutan)
	if err != nil {
		return r, err
	}
	// length_val di DB bertipe varchar ("1275.00") -> parse defensif
	r.LengthVal, _ = strconv.ParseFloat(strings.TrimSpace(lengthStr), 64)
	r.DeliveryDate = delivery
	return r, nil
}

func (s *Store) dbCounts(keyword string) (total, filtered int, err error) {
	if err = s.db.QueryRow("SELECT COUNT(*) FROM view_material_planning").Scan(&total); err != nil {
		return
	}
	if keyword == "" {
		filtered = total
		return
	}
	kw := "%" + keyword + "%"
	q := `SELECT COUNT(*) FROM view_material_planning
		WHERE no_spk LIKE ? OR customer LIKE ? OR id_material LIKE ? OR no_alloy LIKE ?`
	err = s.db.QueryRow(q, kw, kw, kw, kw).Scan(&filtered)
	return
}

func (s *Store) dbListPage(keyword string, start, length int) ([]PlanningRow, error) {
	var rows *sql.Rows
	var err error
	if keyword == "" {
		rows, err = s.db.Query(
			"SELECT "+planningCols+" FROM view_material_planning ORDER BY id_dt_spkmarketing ASC LIMIT ? OFFSET ?", length, start)
	} else {
		kw := "%" + keyword + "%"
		rows, err = s.db.Query(
			"SELECT "+planningCols+" FROM view_material_planning"+
				" WHERE no_spk LIKE ? OR customer LIKE ? OR id_material LIKE ? OR no_alloy LIKE ?"+
				" ORDER BY id_dt_spkmarketing ASC LIMIT ? OFFSET ?",
			kw, kw, kw, kw, length, start)
	}
	if err != nil {
		return nil, err
	}
	defer rows.Close()
	var out []PlanningRow
	for rows.Next() {
		r, err := scanPlanningRow(rows)
		if err != nil {
			return nil, err
		}
		out = append(out, r)
	}
	return out, rows.Err()
}

func (s *Store) dbSetStatus(id, to string) error {
	var q string
	if to == "CLS" {
		q = "UPDATE dt_spkmarketing SET status_close_planning='CLS' WHERE id_dt_spkmarketing=?"
	} else {
		q = "UPDATE dt_spkmarketing SET status_lanjutan=? WHERE id_dt_spkmarketing=?"
		return execOne(s.db, q, to, id)
	}
	return execOne(s.db, q, id)
}

func execOne(db *sql.DB, q string, args ...any) error {
	res, err := db.Exec(q, args...)
	if err != nil {
		return err
	}
	n, _ := res.RowsAffected()
	if n == 0 {
		return fmt.Errorf("id tidak ditemukan")
	}
	return nil
}

func (s *Store) dbGetHeader(id string) (*PlanningRow, error) {
	row := s.db.QueryRow("SELECT "+planningCols+" FROM view_material_planning WHERE id_dt_spkmarketing=? LIMIT 1", id)
	var r PlanningRow
	var lengthStr, delivery string
	err := row.Scan(&r.IDDTSpkmarketing, &r.IDMaterial, &r.NoSPK, &r.Customer,
		&r.IdCustomer, &r.NoAlloy,
		&r.Thickness, &r.Width, &lengthStr, &delivery,
		&r.TotalWeight, &r.TotalSheet, &r.FgBooking, &r.StatusLanjutan)
	if err != nil {
		return nil, err
	}
	r.LengthVal, _ = strconv.ParseFloat(strings.TrimSpace(lengthStr), 64)
	r.DeliveryDate = delivery
	return &r, nil
}

func (s *Store) dbGetStok(idMaterial string, width float64) (wip, fg []StockItem, err error) {
	wipRows, err := s.db.Query(
		`SELECT id_stock, lotno, width, qty, weight, totalweight, booking
		 FROM stock_material WHERE id_category3=? AND width > 0 AND id_gudang='1'`, idMaterial)
	if err != nil {
		return nil, nil, err
	}
	defer wipRows.Close()
	for wipRows.Next() {
		var it StockItem
		if err := wipRows.Scan(&it.IDStock, &it.LotNo, &it.Width, &it.Qty, &it.Weight, &it.TotalWeight, &it.Booking); err != nil {
			return nil, nil, err
		}
		wip = append(wip, it)
	}
	if err := wipRows.Err(); err != nil {
		return nil, nil, err
	}
	fgRows, err := s.db.Query(
		`SELECT id_stock, lotno, width, qty, weight, totalweight, booking, COALESCE(qty_sheet,0), COALESCE(customer,'')
		 FROM stock_material
		 WHERE id_category3=? AND width >= ? AND id_gudang='3' AND sisa_spk > 0
		   AND (sisa_spk - IF(delivery_book IS NULL, 0, delivery_book)) > 0
		 ORDER BY width ASC`, idMaterial, width)
	if err != nil {
		return nil, nil, err
	}
	defer fgRows.Close()
	for fgRows.Next() {
		var it StockItem
		if err := fgRows.Scan(&it.IDStock, &it.LotNo, &it.Width, &it.Qty, &it.Weight, &it.TotalWeight, &it.Booking, &it.QtySheet, &it.Customer); err != nil {
			return nil, nil, err
		}
		fg = append(fg, it)
	}
	return wip, fg, fgRows.Err()
}

func (s *Store) dbGetPoPr(idMaterial string) (string, error) {
	var pr, in sql.NullFloat64
	_ = s.db.QueryRow("SELECT SUM(totalweight) FROM dt_trans_pr WHERE idmaterial=?", idMaterial).Scan(&pr)
	_ = s.db.QueryRow("SELECT SUM(width_recive) FROM dt_incoming WHERE id_material=?", idMaterial).Scan(&in)
	pv, iv := 0.0, 0.0
	if pr.Valid {
		pv = pr.Float64
	}
	if in.Valid {
		iv = in.Float64
	}
	return fmt.Sprintf("%.2f", pv-iv), nil
}
