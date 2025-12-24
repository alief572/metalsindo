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
}
