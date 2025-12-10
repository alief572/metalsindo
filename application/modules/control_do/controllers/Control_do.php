<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

/*
 * @author Ichsan
 * @copyright Copyright (c) 2019, Ichsan
 *
 * This is controller for Master Supplier
 */

class Control_do extends Admin_Controller
{
    //Permission
    protected $viewPermission     = 'Control_PO.View';
    protected $addPermission      = 'Control_PO.Add';
    protected $managePermission = 'Control_PO.Manage';
    protected $deletePermission = 'Control_PO.Delete';

    public function __construct()
    {
        parent::__construct();
        $this->load->library(array('Mpdf', 'upload', 'Image_lib'));
        $this->load->model(array(
            'control_do/control_do_model',
        ));
        $this->template->title('Control DO');
        $this->template->page_icon('fa fa-building-o');

        date_default_timezone_set('Asia/Bangkok');
    }

    public function index()
    {
        $this->auth->restrict($this->viewPermission);
        $session = $this->session->userdata('app_session');
        $this->template->page_icon('fa fa-users');
        $this->template->title('Control DO');
        $this->template->render('index');
    }

    public function modal_detail()
    {
        $id = $this->uri->segment(3);

        $detail = $this->db->get_where('dt_incoming', array('id_dt_po' => $id))->result_array();
        $data = [
            'detail' => $detail
        ];
        $this->template->set('results', $data);
        $this->template->title('Detail');
        $this->template->render('detail');
    }

    public function close_do()
    {
        $id_po = $this->input->post('id_po');

        $data = [
            'close_do' => 'Y',
            'status_do' => 'CLS',
            'close_on' => date('Y-m-d H:i:s'),
            'close_by' => $this->auth->user_id()
        ];

        $this->db->trans_begin();
        $this->db->where('id_dt_spkmarketing', $id_po)->update("dt_spkmarketing", $data);
        $this->db->trans_complete();

        if ($this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
            $status    = array(
                'pesan'        => 'Failed Process Data. Try Again ...',
                'status'    => 0
            );
        } else {
            $this->db->trans_commit();
            $status    = array(
                'pesan'        => 'Success Process Data. Thanks ...',
                'status'    => 1
            );
        }
        echo json_encode($status);
    }

    public function confirm_do()
    {
        $id = $this->input->post('id');

        $get_do_header = $this->db->get_where('tr_delivery_order', ['id_delivery_order' => $id])->row();

        $this->db->select('a.*');
        $this->db->from('dt_delivery_order_child a');
        $this->db->where('a.id_delivery_order', $id);
        $get_do_detail = $this->db->get()->result();

        $data = [
            'do_header' => $get_do_header,
            'do_detail' => $get_do_detail
        ];

        $this->load->view('confirm_do', $data);
    }

    public function save_confirm_data()
    {
        $post = $this->input->post();

        $do_detail = $this->control_do_model->do_detail($post['id_do']);

        $valid = 1;
        $msg = '';

        $this->db->trans_begin();

        $no = 0;
        foreach ($do_detail as $item_do_detail) {
            $no++;
            if (isset($post['detail'][$no])) {

                $qty_in = str_replace(',', '', $post['detail'][$no]['qty_in']);
                $qty_ng = str_replace(',', '', $post['detail'][$no]['qty_ng']);

                $get_stock = $this->db->get_where('stock_material', ['id_stock' => $item_do_detail->id_stock])->row();

                $arr_do_confirm = [
                    'id_detail_do' => $item_do_detail->id,
                    'qty_do' => $item_do_detail->weight_mat,
                    'qty_in' => $qty_in,
                    'qty_ng' => $qty_ng,
                    'created_by' => $this->auth->user_id(),
                    'created_at' => date('Y-m-d H:i:s')
                ];

                $insert_confirm_do = $this->db->insert('dt_do_confirm', $arr_do_confirm);
                if (!$insert_confirm_do) {
                    $this->db->trans_rollback();

                    print_r($this->db->last_query());
                    exit;
                }

                $arr_do_in_ng = [
                    'id' => $item_do_detail->id,
                    'qty_in' => ($item_do_detail->qty_in + $qty_in),
                    'qty_ng' => ($item_do_detail->qty_ng + $qty_ng)
                ];

                $update_do_in_ng = $this->db->update('dt_delivery_order_child', $arr_do_in_ng, array('id' => $item_do_detail->id));
                if (!$update_do_in_ng) {
                    $this->db->trans_rollback();

                    print_r($this->db->last_query());
                    exit;
                }

                $get_stock = $this->db->get_where('stock_material', array('id_stock' => $item_do_detail->id_stock))->row_array();

                $arr_stock_ng = [
                    'id_category3' => $get_stock['id_category3'],
                    'nama_material' => $get_stock['nama_material'],
                    'width' => $get_stock['width'],
                    'length' => $get_stock['length'],
                    'id_bentuk' => $get_stock['id_bentuk'],
                    'lotno' => $get_stock['lotno'],
                    'qty' => $qty_ng,
                    'weight' => $get_stock['weight'],
                    'totalweight' => $get_stock['totalweight'],
                    'booking' => $get_stock['booking'],
                    'thickness' => $get_stock['thickness'],
                    'aktif' => 'Y',
                    'id_gudang' => '6',
                    'created_by' => $this->auth->user_id(),
                    'created_on' => date('Y-m-d H:i:s'),
                    'no_po' => $get_stock['no_po'],
                    'id_incoming' => $get_stock['id_incoming'],
                    'lot_slitting' => $get_stock['lot_slitting'],
                    'keterangan' => $get_stock['keterangan'],
                    'status_do' => 'OPN',
                    'tipe_material' => $get_stock['tipe_material'],
                    'qty_sheet' => $get_stock['qty_sheet'],
                    'sisa_spk' => $qty_ng
                ];

                if ($qty_ng > 0) {
                    $insert_stock_ng = $this->db->insert('stock_material', $arr_stock_ng);
                    if (!$insert_stock_ng) {
                        $this->db->trans_rollback();

                        print_r($this->db->last_query());
                        exit;
                    }
                }

                $arr_stock = [
                    'id_stock' => $item_do_detail->id_stock,
                    'status_do' => 'CLS',
                    'sisa_spk' => ($get_stock->sisa_spk - $qty_in),
                    'total_kirim' => $qty_in,
                    'deleted' => '1',
                    'aktif' => 'N',
                    'no_kirim' => $item_do_detail->id_delivery_order
                ];
                $update_stock = $this->db->update('stock_material', $arr_stock, array('id_stock' => $item_do_detail->id_stock));
                if (!$update_stock) {
                    $this->db->trans_rollback();

                    print_r($this->db->last_query());
                    exit;
                }
            }
        }

        $arr_update_do_header = [
            'status_approve' => '1',
            'status_date' => date('Y-m-d H:i:s'),
            'status_by' => $this->auth->user_id()
        ];

        $update_do_header = $this->db->update('tr_delivery_order', $arr_update_do_header, array('id_delivery_order' => $post['id_do']));
        // if (!$update_do_header) {
        // }
        if (!$update_do_header) {
            $this->db->trans_rollback();

            print_r($this->db->last_query());
            exit;
        }



        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();

            $valid = 0;
            $msg = (empty($msg)) ? 'Please try again later !' : $msg;
        } else {
            $this->db->trans_commit();

            $valid = 1;
            $msg = 'DO has been Confirmed !';
        }

        $response = [
            'status' => $valid,
            'msg' => $msg
        ];

        echo json_encode($response);
    }

    public function get_data_control_do()
    {
        $post = $this->input->post();

        $draw = intval($post['draw']);
        $length = $post['length'];
        $start = $post['start'];
        $search = $post['search']['value'];

        $this->db->select('a.id_delivery_order');
        $this->db->from('tr_delivery_order a');
        $this->db->join('master_customers b', 'b.id_customer = a.id_customer');
        $this->db->join('tr_invoice d', 'd.no_do = a.no_surat', 'left');
        $this->db->where('a.deleted', null);
        $this->db->where('a.close_do', null);
        $this->db->where('d.no_invoice', null);
        $this->db->group_by('a.id_delivery_order');
        $count_all = $this->db->get()->num_rows();

        $this->db->select('a.id_delivery_order');
        $this->db->from('tr_delivery_order a');
        $this->db->join('master_customers b', 'b.id_customer = a.id_customer');
        $this->db->join('tr_invoice d', 'd.no_do = a.no_surat', 'left');
        $this->db->where('a.deleted', null);
        $this->db->where('a.close_do', null);
        $this->db->where('d.no_invoice', null);

        if (!empty($search)) {
            $this->db->group_start();
            $this->db->like('a.tgl_delivery_order', $search, 'both');
            $this->db->or_like('a.no_surat', $search, 'both');
            $this->db->or_like('a.no_spk_marketing', $search, 'both');
            $this->db->or_like('b.name_customer', $search, 'both');
            $this->db->group_end();
        }
        $this->db->group_by('a.id_delivery_order');

        $count_filter = $this->db->get()->num_rows();

        $this->db->select('a.*, b.name_customer');
        $this->db->from('tr_delivery_order a');
        $this->db->join('master_customers b', 'b.id_customer = a.id_customer');
        $this->db->join('tr_invoice d', 'd.no_do = a.no_surat', 'left');
        $this->db->where('a.deleted', null);
        $this->db->where('a.close_do', null);
        $this->db->where('d.no_invoice', null);

        if (!empty($search)) {
            $this->db->group_start();
            $this->db->like('a.tgl_delivery_order', $search, 'both');
            $this->db->or_like('a.no_surat', $search, 'both');
            $this->db->or_like('a.no_spk_marketing', $search, 'both');
            $this->db->or_like('b.name_customer', $search, 'both');
            $this->db->group_end();
        }

        $this->db->group_by('a.id_delivery_order');
        $this->db->order_by('a.id_delivery_order', 'desc');
        $this->db->limit($length, $start);

        $get_data = $this->db->get()->result();

        $no = (0 + $start);
        $hasil = [];

        foreach ($get_data as $item) {
            $no++;

            $this->db->select('a.id');
            $this->db->from('dt_delivery_order_child a');
            $this->db->where('a.id_delivery_order', $item->id_delivery_order);
            $this->db->where('a.qty_in <=', 0);
            $this->db->where('a.qty_ng <=', 0);
            $get_do_detail = $this->db->get()->num_rows();

            $this->db->select('SUM(a.weight_mat) as total_do, SUM(a.qty_in) as total_delivered');
            $this->db->from('dt_delivery_order_child a');
            $this->db->where('a.id_delivery_order', $item->id_delivery_order);
            $get_total = $this->db->get()->row();

            $total_do = (!empty($get_total->total_do)) ? $get_total->total_do : 0;
            $total_delivered = (!empty($get_total->total_delivered)) ? $get_total->total_delivered : 0;

            $btn_confirm = '';
            if (has_permission($this->managePermission) && $get_do_detail > 0) {
                $btn_confirm = '<button type="button" class="btn btn-sm btn-success confirm_do" data-id="' . $item->id_delivery_order . '" title="Confirm DO" ><i class="fa fa-check"></i></button>';
            }

            $hasil[] = [
                'no' => $no,
                'tanggal_do' => date('d F Y', strtotime($item->tgl_delivery_order)),
                'no_do' => $item->no_surat,
                'spk_marketing' => $item->no_spk_marketing,
                'customer' => $item->name_customer,
                'qty_order' => number_format($total_do, 2),
                'qty_delivery' => number_format($total_delivered, 2),
                'balance' => number_format($total_do - $total_delivered, 2),
                'option' => $btn_confirm
            ];
        }

        $response = [
            'draw' => $draw,
            'recordsTotal' => $count_all,
            'recordsFiltered' => $count_filter,
            'data' => $hasil
        ];

        echo json_encode($response);
    }
}
