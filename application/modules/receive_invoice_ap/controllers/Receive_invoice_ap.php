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

  public function add()
  {

    $this->db->select('a.id_suplier, b.name_suplier');
    $this->db->from('tr_incoming a');
    $this->db->join('master_supplier b', 'b.id_suplier = a.id_suplier');
    $this->db->where('a.no_invoice_rec_ap', null);
    $this->db->or_where('a.no_invoice_rec_ap', '');
    $this->db->group_by('a.id_suplier');
    $this->db->order_by('b.name_suplier', 'asc');
    $get_supplier = $this->db->get()->result();

    $data = [
      'list_supplier' => $get_supplier
    ];

    $this->template->set($data);
    $this->template->render('add');
  }

  public function view($id_rec_inv_ap)
  {
    $this->db->select('a.*');
    $this->db->from('tr_receive_invoice_ap_header a');
    $this->db->where('a.id_rec_inv_ap', $id_rec_inv_ap);
    $get_data_header = $this->db->get()->row();

    $this->db->select('a.*');
    $this->db->from('tr_receive_invoice_ap_detail a');
    $this->db->where('a.id_rec_inv_ap', $id_rec_inv_ap);
    $get_data_detail = $this->db->get()->result();

    $this->db->select('a.*');
    $this->db->from('master_supplier a');
    $this->db->where('a.deleted', 0);
    $get_supplier = $this->db->get()->result();

    $data = [
      'header' => $get_data_header,
      'detail' => $get_data_detail,
      'list_supplier' => $get_supplier
    ];

    $this->template->set($data);
    $this->template->render('view');
  }

  public function edit($id_rec_inv_ap)
  {
    $this->db->select('a.*');
    $this->db->from('tr_receive_invoice_ap_header a');
    $this->db->where('a.id_rec_inv_ap', $id_rec_inv_ap);
    $get_data_header = $this->db->get()->row();

    $this->db->select('a.*');
    $this->db->from('tr_receive_invoice_ap_detail a');
    $this->db->where('a.id_rec_inv_ap', $id_rec_inv_ap);
    $get_data_detail = $this->db->get()->result();

    $this->db->select('a.*');
    $this->db->from('master_supplier a');
    $this->db->where('a.deleted', 0);
    $get_supplier = $this->db->get()->result();

    $data = [
      'header' => $get_data_header,
      'detail' => $get_data_detail,
      'list_supplier' => $get_supplier
    ];

    $this->template->set($data);
    $this->template->render('edit');
  }

  public function get_data_incoming()
  {
    $draw = $this->input->post('draw');
    $start = $this->input->post('start');
    $length = $this->input->post('length');
    $search = $this->input->post('search');

    $this->db->select('a.*');
    $this->db->from('tr_receive_invoice_ap_header a');
    if (!empty($search)) {
      $this->db->group_start();
      $this->db->or_like('a.no_invoice', $search['value'], 'both');
      $this->db->or_like('a.nm_suplier', $search['value'], 'both');
      $this->db->group_end();
    }
    $this->db->order_by('a.created_date', 'desc');
    $this->db->limit($length, $start);

    $get_data = $this->db->get();

    $this->db->select('a.*');
    $this->db->from('tr_receive_invoice_ap_header a');
    if (!empty($search)) {
      $this->db->group_start();
      $this->db->or_like('a.no_invoice', $search['value'], 'both');
      $this->db->or_like('a.nm_suplier', $search['value'], 'both');
      $this->db->group_end();
    }
    $this->db->order_by('a.created_date', 'desc');

    $get_data_all = $this->db->get();

    $hasil = [];

    $no = ($start + 1);
    foreach ($get_data->result() as $item) {

      $this->db->select('SUM(a.total_nilai) as nominal_invoice');
      $this->db->from('tr_receive_invoice_ap_detail a');
      $this->db->where('a.id_rec_inv_ap', $item->id_rec_inv_ap);
      $get_nominal_invoice = $this->db->get()->row();

      $nominal_invoice = (!empty($get_nominal_invoice->nominal_invoice)) ? $get_nominal_invoice->nominal_invoice : 0;

      $btn_view = '<a href="' . base_url('receive_invoice_ap/view/' . $item->id_rec_inv_ap) . '" class="btn btn-sm btn-info" title="View Receiving Invoice"><i class="fa fa-eye"></i></a>';

      $btn_edit = '<a href="' . base_url('receive_invoice_ap/edit/' . $item->id_rec_inv_ap) . '" class="btn btn-sm btn-warning" title="Edit Receiving Invoice" style="margin-left: 0.5rem;"><i class="fa fa-pencil"></i></a>';
      if (!has_permission('Receive_Invoice_AP.Manage')) {
        $btn_edit = '';
      }

      $btn_delete = '<button type="button" class="btn btn-sm btn-danger del_rec_inv" data-id="' . $item->id_rec_inv_ap . '" title="Delete Receiving Invoice" style="margin-left: 0.5rem;"><i class="fa fa-trash"></i></button>';
      if (!has_permission('Receive_Invoice_AP.Delete')) {
        $btn_delete = '';
      }

      $option = $btn_view . $btn_edit . $btn_delete;

      $hasil[] = [
        'no' => $no,
        'no_invoice' => $item->no_invoice,
        'nm_suplier' => $item->nm_suplier,
        'nominal_invoice' => number_format($nominal_invoice, 2),
        'option' => $option
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

  public function save_receive_invoice()
  {
    $post = $this->input->post();

    $this->db->trans_begin();

    $data_update = [
      'no_invoice_rec_ap' => $post['no_invoice'],
      'receive_date' => $post['receive_date'],
      'nilai_invoice' => str_replace(',', '', $post['total_invoice']),
      'nilai_ppn' => str_replace(',', '', $post['nilai_ppn']),
      'no_faktur_pajak' => $post['no_faktur_pajak'],
      'rec_ap' => 1
    ];

    $this->db->update('tr_incoming', $data_update, array('id_incoming' => $post['id_incoming']));

    if ($this->db->trans_status() === false) {
      $this->db->trans_rollback();

      $valid = 0;
      $pesan = 'Please try again later !';
    } else {
      $this->db->trans_commit();

      $valid = 1;
      $pesan = 'Receive Invoice has been success !';
    }

    echo json_encode([
      'status' => $valid,
      'pesan' => $pesan
    ]);
  }



  public function view_inv()
  {
    $id_incoming = $this->input->post('id_incoming');

    $get_incoming = $this->db->get_where('tr_incoming', ['id_incoming' => $id_incoming])->row();

    echo json_encode([
      'data_incoming' => $get_incoming
    ]);
  }

  public function TambahRequest()
  {
    // $customer = $this->uri->segment(3);
    // $invoice = $this->db->query("SELECT * FROM tr_request WHERE id_suplier ='$customer' AND sisa_invoice_idr >'0' and kategori='hutang'")->result();
    // $data = [
    // 	'detail' => $customer
    // ];
    // $this->template->set('results', $data);
    // $this->template->title('List Request');
    // $this->template->render('request');

    $id_suplier = $this->input->post('id_suplier');

    $this->db->select('a.*, b.name_suplier');
    $this->db->from('tr_incoming a');
    $this->db->join('master_supplier b', 'b.id_suplier = a.id_suplier', 'left');
    $this->db->where('a.id_suplier', $id_suplier);
    $this->db->group_start();
    $this->db->where('a.no_invoice_rec_ap', '');
    $this->db->or_where('a.no_invoice_rec_ap', null);
    $this->db->group_end();
    $get_data_supplier = $this->db->get()->result();

    $list_no_po = [];

    foreach ($get_data_supplier as $item) {
      $this->db->select('c.no_surat');
      $this->db->from('dt_incoming a');
      $this->db->join('dt_trans_po b', 'b.id_dt_po = a.id_dt_po', 'left');
      $this->db->join('tr_purchase_order c', 'c.no_po = b.no_po', 'left');
      $this->db->where('a.id_incoming', $item->id_incoming);
      $this->db->group_by('c.no_surat');
      $get_no_po = $this->db->get()->result();

      foreach ($get_no_po as $item_no_po) {
        $list_no_po[$item->id_incoming] = [$item_no_po->no_surat];
      }

      if (!empty($list_no_po[$item->id_incoming])) {
        $list_no_po[$item->id_incoming] = implode(',', $list_no_po[$item->id_incoming]);
      } else {
        $list_no_po[$item->id_incoming] = '';
      }
    }

    $this->template->set('results', $get_data_supplier);
    $this->template->set('list_no_po', $list_no_po);

    $this->template->title('List Request');
    $this->template->render('request');
  }

  public function save_receive_invoice_ap()
  {
    $post = $this->input->post();

    $this->db->trans_begin();

    $id_rec_inv_ap = $this->Receive_invoice_ap_model->generate_id_invoice_ap();

    $get_supplier = $this->db->get_where('master_supplier', ['id_suplier' => $post['supplier']])->row();

    $data_header = [
      'id_rec_inv_ap' => $id_rec_inv_ap,
      'tgl_bayar' => $post['tgl_bayar'],
      'no_invoice' => $post['no_invoice'],
      'id_suplier' => $post['supplier'],
      'nm_suplier' => $get_supplier->name_suplier,
      'created_by' => $this->auth->user_id(),
      'created_date' => date('Y-m-d H:i:s')
    ];

    $data_detail = [];
    if (isset($post['kp'])) {
      foreach ($post['kp'] as $item) {
        $data_detail[] = [
          'id_rec_inv_ap' => $id_rec_inv_ap,
          'id_incoming' => $item['id_incoming'],
          'no_po' => $item['no_po'],
          'tanggal_incoming' => $item['tanggal_incoming'],
          'id_suplier' => $item['id_suplier'],
          'nm_suplier' => $item['nm_suplier'],
          'nilai' => str_replace(',', '', $item['nilai']),
          'no_faktur_pajak' => $item['no_faktur_pajak'],
          'ppn' => str_replace(',', '', $item['ppn']),
          'total_nilai' => str_replace(',', '', $item['total']),
          'created_by' => $this->auth->user_id(),
          'created_date' => date('Y-m-d')
        ];

        $data_update_incoming = [
          'id_incoming' => $item['id_incoming'],
          'no_invoice_rec_ap' => $post['no_invoice'],
          'nilai_invoice' => str_replace(',', '', $item['total']),
          'nilai_ppn' => (str_replace(',', '', $item['total']) - str_replace(',', '', $item['nilai'])),
          'no_faktur_pajak' => $item['no_faktur_pajak'],
          'rec_ap' => 1
        ];

        $update_incoming = $this->db->update('tr_incoming', $data_update_incoming, ['id_incoming' => $item['id_incoming'], 'id_suplier' => $item['id_suplier']]);
        if (!$update_incoming) {
          $this->db->trans_rollback();

          print_r($this->db->error($update_incoming));
          exit;
        }
      }
    }

    $insert_header = $this->db->insert('tr_receive_invoice_ap_header', $data_header);
    if (!$insert_header) {
      $this->db->trans_rollback();

      print_r($this->db->error($insert_header));
      exit;
    }

    $insert_detail = $this->db->insert_batch('tr_receive_invoice_ap_detail', $data_detail);
    if (!$insert_detail) {
      $this->db->trans_rollback();

      print_r($this->db->error($insert_detail));
      exit;
    }

    if ($this->db->trans_status() === false) {
      $this->db->trans_rollback();

      $valid = 0;
      $pesan = 'Please try again later !';
    } else {
      $this->db->trans_commit();

      $valid = 1;
      $pesan = 'Data has been saved !';
    }

    echo json_encode([
      'status' => $valid,
      'pesan' => $pesan
    ]);
  }

  public function update_receive_invoice_ap()
  {
    $post = $this->input->post();

    $this->db->trans_begin();

    $get_supplier = $this->db->get_where('master_supplier', ['id_suplier' => $post['supplier']])->row();

    $this->db->select('a.*');
    $this->db->from('tr_receive_invoice_ap_detail a');
    $this->db->where('a.id_rec_inv_ap', $post['id_rec_inv_ap']);
    $get_detail = $this->db->get()->result();

    foreach ($get_detail as $item) {
      $this->db->update('tr_incoming', [
        'no_invoice_rec_ap' => null,
        'nilai_invoice' => null,
        'nilai_ppn' => null,
        'no_faktur_pajak' => null,
        'rec_ap' => 0
      ], [
        'id_incoming' => $item->id_incoming,
        'id_suplier' => $item->id_suplier
      ]);
    }

    $this->db->delete('tr_receive_invoice_ap_detail', ['id_rec_inv_ap' => $post['id_rec_inv_ap']]);

    $data_header = [
      'tgl_bayar' => $post['tgl_bayar'],
      'no_invoice' => $post['no_invoice'],
      'id_suplier' => $post['supplier'],
      'nm_suplier' => $get_supplier->name_suplier,
      'created_by' => $this->auth->user_id(),
      'created_date' => date('Y-m-d H:i:s')
    ];

    $data_detail = [];
    if (isset($post['kp'])) {
      foreach ($post['kp'] as $item) {
        $data_detail[] = [
          'id_rec_inv_ap' => $post['id_rec_inv_ap'],
          'id_incoming' => $item['id_incoming'],
          'no_po' => $item['no_po'],
          'tanggal_incoming' => $item['tanggal_incoming'],
          'id_suplier' => $item['id_suplier'],
          'nm_suplier' => $item['nm_suplier'],
          'nilai' => str_replace(',', '', $item['nilai']),
          'no_faktur_pajak' => $item['no_faktur_pajak'],
          'ppn' => str_replace(',', '', $item['ppn']),
          'total_nilai' => str_replace(',', '', $item['total']),
          'created_by' => $this->auth->user_id(),
          'created_date' => date('Y-m-d')
        ];

        $data_update_incoming = [
          'id_incoming' => $item['id_incoming'],
          'no_invoice_rec_ap' => $post['no_invoice'],
          'nilai_invoice' => str_replace(',', '', $item['total']),
          'nilai_ppn' => (str_replace(',', '', $item['total']) - str_replace(',', '', $item['nilai'])),
          'no_faktur_pajak' => $item['no_faktur_pajak'],
          'rec_ap' => 1
        ];

        $update_incoming = $this->db->update('tr_incoming', $data_update_incoming, ['id_incoming' => $item['id_incoming'], 'id_suplier' => $item['id_suplier']]);
        if (!$update_incoming) {
          $this->db->trans_rollback();

          print_r($this->db->error($update_incoming));
          exit;
        }
      }
    }

    $insert_header = $this->db->update('tr_receive_invoice_ap_header', $data_header, ['id_rec_inv_ap' => $post['id_rec_inv_ap']]);
    if (!$insert_header) {
      $this->db->trans_rollback();

      print_r($this->db->error($insert_header));
      exit;
    }

    $insert_detail = $this->db->insert_batch('tr_receive_invoice_ap_detail', $data_detail);
    if (!$insert_detail) {
      $this->db->trans_rollback();

      print_r($this->db->error($insert_detail));
      exit;
    }

    if ($this->db->trans_status() === false) {
      $this->db->trans_rollback();

      $valid = 0;
      $pesan = 'Please try again later !';
    } else {
      $this->db->trans_commit();

      $valid = 1;
      $pesan = 'Data has been updated !';
    }

    echo json_encode([
      'status' => $valid,
      'pesan' => $pesan
    ]);
  }

  public function del_rec_inv_ap() {
    $id = $this->input->post('id');

    $this->db->trans_begin();

    $get_detail = $this->db->get_where('tr_receive_invoice_ap_detail', ['id_rec_inv_ap' => $id])->result();
    foreach($get_detail as $item) {
      $this->db->update('tr_incoming', [
        'no_invoice_rec_ap' => null,
        'nilai_invoice' => 0,
        'nilai_ppn' => 0,
        'no_faktur_pajak' => null,
        'rec_ap' => 0
      ], ['id_incoming' => $item->id_incoming, 'id_suplier' => $item->id_suplier]);
    }

    $this->db->delete('tr_receive_invoice_ap_detail', ['id_rec_inv_ap' => $id]);
    $this->db->delete('tr_receive_invoice_ap_header', ['id_rec_inv_ap' => $id]);

    if ($this->db->trans_status() === false) {
      $this->db->trans_rollback();

      $valid = 0;
      $pesan = 'Please try again later !';
    } else {
      $this->db->trans_commit();

      $valid = 1;
      $pesan = 'Data has been deleted !';
    }

    echo json_encode([
      'status' => $valid,
      'pesan' => $pesan
    ]);    
  }
}
