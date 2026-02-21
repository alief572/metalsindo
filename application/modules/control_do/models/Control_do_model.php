<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/*
 * @author Yunas Handra
 * @copyright Copyright (c) 2018, Yunas Handra
 *
 * This is model class for table "Customer"
 */

class Control_do_model extends BF_Model
{

    public function do_detail($id_delivery_order)
    {
        $this->db->select('a.*');
        $this->db->from('dt_delivery_order_child a');
        $this->db->where('a.id_delivery_order', $id_delivery_order);
        $get_data = $this->db->get()->result();

        return $get_data;
    }

    public function do_detail_scrap($id_delivery_order)
    {
        $this->db->select('a.*');
        $this->db->from('dt_delivery_order_child_scrap a');
        $this->db->where('a.id_delivery_order', $id_delivery_order);
        $get_data = $this->db->get()->result();

        return $get_data;
    }

    public function get_list_customer()
    {
        $this->db->select('a.*');
        $this->db->from('master_customers a');
        $this->db->where('a.deleted', 0);
        $this->db->order_by('a.name_customer', 'ASC');
        $get_data = $this->db->get()->result();

        return $get_data;
    }

    public function get_list_spk_marketing()
    {
        $this->db->select('a.id_spkmarketing, a.no_surat');
        $this->db->from('tr_spk_marketing a');
        $this->db->where('a.deleted', null);
        $this->db->order_by('a.no_surat', 'ASC');
        $get_data = $this->db->get()->result();

        return $get_data;
    }

    public function get_list_do()
    {
        $this->db->select('a.id_delivery_order, a.no_surat');
        $this->db->from('tr_delivery_order a');
        $this->db->where('a.deleted', null);
        $this->db->order_by('a.no_surat', 'ASC');
        $get_data = $this->db->get()->result();

        return $get_data;
    }

    public function get_total_do($id_delivery_order)
    {
        $this->db->select('SUM(a.weight_mat) as total_do, SUM(a.qty_in) as total_delivered, SUM(a.qty_fg) as total_fg, SUM(a.qty_ng) as total_ng');
        $this->db->from('dt_delivery_order_child a');
        $this->db->where('a.id_delivery_order', $id_delivery_order);
        $get_total = $this->db->get()->row();

        return $get_total;
    }

    public function get_total_do_scrap($id_delivery_order)
    {
        $this->db->select('SUM(a.weight_mat) as total_do, SUM(a.qty_in) as total_delivered, SUM(a.qty_fg) as total_fg, SUM(a.qty_ng) as total_ng');
        $this->db->from('dt_delivery_order_child_scrap a');
        $this->db->where('a.id_delivery_order', $id_delivery_order);
        $get_total = $this->db->get()->row();

        return $get_total;
    }

    public function kartu_stock($id_delivery_order)
    {
        $this->db->select('a.id_delivery_order, a.id_material, COALESCE(SUM(a.qty_in), 0) as ttl_in, COALESCE(SUM(a.qty_fg), 0) as ttl_fg, COALESCE(SUM(a.qty_ng), 0) as ttl_ng, b.id_gudang, c.wh_name as nm_gudang, d.no_surat');
        $this->db->from('dt_delivery_order_child a');
        $this->db->join('stock_material b', 'b.id_stock = a.id_stock');
        $this->db->join('ms_warehouse c', 'c.id = b.id_gudang');
        $this->db->join('tr_delivery_order d', 'd.id_delivery_order = a.id_delivery_order');
        $this->db->where('a.id_delivery_order', $id_delivery_order);
        $this->db->group_by('a.id_material, b.id_gudang');
        $get_data = $this->db->get()->result();

        $this->db->trans_begin();

        try {
            $arr_kartu_stock_in = [];
            $arr_kartu_stock_fg = [];
            $arr_kartu_stock_ng = [];

            // $arr_update_stock = [];

            foreach ($get_data as $item) {
                $this->db->select('a.id_material, a.nm_material, a.id_gudang, a.kd_gudang as nm_gudang, a.qty_stock, a.qty_booking, (a.qty_stock - a.qty_booking) as qty_free');
                $this->db->from('warehouse_stock a');
                $this->db->where('a.id_material', $item->id_material);
                $this->db->where('a.id_gudang', $item->id_gudang);
                $get_stock = $this->db->get()->row();

                $arr_update_stock = [
                    'qty_stock' => ($get_stock->qty_stock - $item->ttl_in),
                    'qty_booking' => $get_stock->qty_booking
                ];
                $this->db->update('warehouse_stock', $arr_update_stock, ['id_material' => $item->id_material, 'id_gudang' => $item->id_gudang]);

                $arr_kartu_stock_in[] = [
                    'id_material' => $item->id_material,
                    'idmaterial' => $item->id_material,
                    'nm_material' => $get_stock->nm_material,
                    'id_gudang' => $get_stock->id_gudang,
                    'kd_gudang' => $get_stock->nm_gudang,
                    'id_gudang_ke' => $get_stock->id_gudang,
                    'kd_gudang_ke' => $get_stock->nm_gudang,
                    'qty_stock_awal' => $get_stock->qty_stock,
                    'qty_booking_awal' => $get_stock->qty_booking,
                    'qty_stock_akhir' => ($get_stock->qty_stock - $item->ttl_in),
                    'qty_booking_akhir' => $get_stock->qty_booking,
                    'no_ipp' => $item->no_surat,
                    'jumlah_mat' => $item->ttl_in,
                    'ket' => 'Confirm DO',
                    'update_by' => $this->auth->user_id(),
                    'update_date' => date('Y-m-d H:i:s'),
                    'jenis_transaksi' => 'Confirm DO'
                ];

                if ($item->ttl_fg > 0) {
                    $this->db->select('a.id_material, a.nm_material, a.id_gudang, a.kd_gudang as nm_gudang, a.qty_stock, a.qty_booking, (a.qty_stock - a.qty_booking) as qty_free');
                    $this->db->from('warehouse_stock a');
                    $this->db->where('a.id_material', $item->id_material);
                    $this->db->where('a.id_gudang', '3');
                    $get_stock_fg = $this->db->get()->row();

                    $arr_update_stock = [
                        'qty_stock' => ($get_stock_fg->qty_stock + $item->ttl_fg),
                        'qty_booking' => $get_stock_fg->qty_booking
                    ];
                    $this->db->update('warehouse_stock', $arr_update_stock, ['id_material' => $item->id_material, 'id_gudang' => '3']);

                    $arr_kartu_stock_fg[] = [
                        'id_material' => $item->id_material,
                        'idmaterial' => $item->id_material,
                        'nm_material' => $get_stock_fg->nm_material,
                        'id_gudang' => '3',
                        'kd_gudang' => 'Gudang Finish Good',
                        'id_gudang_ke' => '3',
                        'kd_gudang_ke' => 'Gudang Finish Good',
                        'qty_stock_awal' => $get_stock_fg->qty_stock,
                        'qty_booking_awal' => $get_stock_fg->qty_booking,
                        'qty_stock_akhir' => ($get_stock_fg->qty_stock + $item->ttl_fg),
                        'qty_booking_akhir' => $get_stock_fg->qty_booking,
                        'no_ipp' => $item->no_surat,
                        'jumlah_mat' => $item->ttl_fg,
                        'ket' => 'Confirm DO',
                        'update_by' => $this->auth->user_id(),
                        'update_date' => date('Y-m-d H:i:s'),
                        'jenis_transaksi' => 'Confirm DO FG'
                    ];
                }

                if ($item->ttl_ng > 0) {
                    $this->db->select('a.id_material, a.nm_material, a.id_gudang, a.kd_gudang as nm_gudang, a.qty_stock, a.qty_booking, (a.qty_stock - a.qty_booking) as qty_free');
                    $this->db->from('warehouse_stock a');
                    $this->db->where('a.id_material', $item->id_material);
                    $this->db->where('a.id_gudang', '6');
                    $get_stock_ng = $this->db->get()->row();

                    $arr_update_stock = [
                        'qty_stock' => ($get_stock_ng->qty_stock + $item->ttl_ng),
                        'qty_booking' => $get_stock_ng->qty_booking
                    ];
                    $this->db->update('warehouse_stock', $arr_update_stock, ['id_material' => $item->id_material, 'id_gudang' => '6']);

                    $arr_kartu_stock_ng[] = [
                        'id_material' => $item->id_material,
                        'idmaterial' => $item->id_material,
                        'nm_material' => $get_stock_ng->nm_material,
                        'id_gudang' => '6',
                        'kd_gudang' => 'Gudang NCR',
                        'id_gudang_ke' => '6',
                        'kd_gudang_ke' => 'Gudang NCR',
                        'qty_stock_awal' => $get_stock_ng->qty_stock,
                        'qty_booking_awal' => $get_stock_ng->qty_booking,
                        'qty_stock_akhir' => ($get_stock_ng->qty_stock + $item->ttl_ng),
                        'qty_booking_akhir' => $get_stock_ng->qty_booking,
                        'no_ipp' => $item->no_surat,
                        'jumlah_mat' => $item->ttl_ng,
                        'ket' => 'Confirm DO',
                        'update_by' => $this->auth->user_id(),
                        'update_date' => date('Y-m-d H:i:s'),
                        'jenis_transaksi' => 'Confirm DO NG'
                    ];
                }
            }

            $this->db->insert_batch('warehouse_history', $arr_kartu_stock_in);
            if (!empty($arr_kartu_stock_fg)) {
                $this->db->insert_batch('warehouse_history', $arr_kartu_stock_fg);
            }
            if (!empty($arr_kartu_stock_ng)) {
                $this->db->insert_batch('warehouse_history', $arr_kartu_stock_ng);
            }

            $this->db->trans_commit();
        } catch (Exception $e) {
            $this->db->trans_rollback();
        }
    }
}
