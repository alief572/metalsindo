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
}
