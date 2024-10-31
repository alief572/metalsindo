<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Receive_invoice_ap extends Admin_Controller
{
  //Permission
  protected $viewPermission   = 'Receive_Invoice_AP.View';
  protected $addPermission    = 'Receive_Invoice_AP.Add';
  protected $managePermission = 'Receive_Invoice_AP.Manage';
  protected $deletePermission = 'Receive_Invoice_AP.Delete';

  public function __construct()
  {
    parent::__construct();
    $this->load->library(array('Mpdf', 'upload', 'Image_lib'));

    $this->load->model(array('Receive_invoice_ap/Receive_invoice_ap_model'));
    date_default_timezone_set('Asia/Bangkok');

    $this->id_user  = $this->auth->user_id();
    $this->datetime = date('Y-m-d H:i:s');
  }

  public function index()
  {
    $this->auth->restrict($this->viewPermission);
    $session  = $this->session->userdata('app_session');
    $this->template->title('Receive Invoice AP');
    $this->template->render('index');
  }

  public function get_data_incoming()
  {
    $draw = $this->input->post('draw');
    $start = $this->input->post('start');
    $length = $this->input->post('length');
    $search = $this->input->post('search');

    $this->db->select('a.*, b.name_suplier');
    $this->db->from('tr_incoming a');
    $this->db->join('master_supplier b', 'b.id_suplier = a.id_suplier', 'left');
    $this->db->group_start();
    $this->db->where('a.no_invoice', '');
    $this->db->or_where('a.no_invoice', null);
    $this->db->group_end();
    if (!empty($search)) {
      $this->db->group_start();
      $this->db->like('a.id_incoming', $search['value'], 'both');
      $this->db->or_like('a.no_invoice', $search['value'], 'both');
      $this->db->or_like('b.name_suplier', $search['value'], 'both');
      $this->db->or_like('a.nilai_invoice', $search['value'], 'both');
      $this->db->group_end();
    }
    $this->db->order_by('a.created_date', 'desc');
    $this->db->limit($length, $start);

    $get_data = $this->db->get();

    $this->db->select('a.*, b.name_suplier');
    $this->db->from('tr_incoming a');
    $this->db->join('master_supplier b', 'b.id_suplier = a.id_suplier', 'left');
    $this->db->group_start();
    $this->db->where('a.no_invoice', '');
    $this->db->or_where('a.no_invoice', null);
    $this->db->group_end();

    if (!empty($search)) {
      $this->db->group_start();
      $this->db->like('a.id_incoming', $search['value'], 'both');
      $this->db->or_like('a.no_invoice', $search['value'], 'both');
      $this->db->or_like('b.name_suplier', $search['value'], 'both');
      $this->db->or_like('a.nilai_invoice', $search['value'], 'both');
      $this->db->group_end();
    }

    $this->db->order_by('a.created_date', 'desc');

    $get_data_all = $this->db->get();

    $hasil = [];

    $no = 1;
    foreach ($get_data->result() as $item) {

      $this->db->select('c.no_surat');
      $this->db->from('dt_incoming a');
      $this->db->join('dt_trans_po b', 'b.id_dt_po = a.id_dt_po', 'left');
      $this->db->join('tr_purchase_order c', 'c.no_po = b.no_po', 'left');
      $this->db->where('a.id_incoming', $item->id_incoming);
      $this->db->group_by('c.no_surat');

      $get_no_po = $this->db->get()->result();

      $no_po = [];
      foreach($get_no_po as $item_po) {
        $no_po[] = $item_po->no_surat;
      }

      $hasil[] = [
        'no' => $no,
        'no_incoming' => $item->id_incoming,
        'no_po' => implode(', ', $no_po),
        'no_invoice' => $item->no_invoice,
        'supplier' => $item->name_suplier,
        'nominal_invoice' => $item->nilai_invoice,
        'status' => 'test',
        'option' => 'test'
      ];

      $no++;
    }

    echo json_encode([
      'draw' => intval($draw),
      'recordsTotal' => $get_data_all->num_rows(),
      'recordsFiltered' => $get_data_all->num_rows(),
      'data' => $hasil
    ]);
  }
}
