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

class Retur_pmb_cn extends Admin_Controller
{
	//Permission
	protected $viewPermission 	= 'Retur_Credit_Note.View';
	protected $addPermission  	= 'Retur_Credit_Note.Add';
	protected $managePermission = 'Retur_Credit_Note.Manage';
	protected $deletePermission = 'Retur_Credit_Note.Delete';

	public function __construct()
	{
		parent::__construct();
		$this->load->library(array('Mpdf', 'upload', 'Image_lib'));
		$this->load->model(array(
			'Retur_pmb_cn/Retur_pmb_cn_model'
		));
		$this->template->title('Retur Penjualan');
		$this->template->page_icon('fa fa-building-o');

		date_default_timezone_set('Asia/Bangkok');
	}

	public function index()
	{
		$this->auth->restrict($this->viewPermission);
		$session = $this->session->userdata('app_session');
		$this->template->page_icon('fa fa-list');

		$this->template->title('Credit Note Retur Pembelian');
		$this->template->render('index');
	}

	public function add_cn($id_retur)
	{
		$get_header = $this->Retur_pmb_cn_model->get_retur_header($id_retur);
		$get_detail = $this->Retur_pmb_cn_model->get_retur_detail($get_header->no_surat);

		$arr_po = [];

		$this->db->select('a.no_surat');
		$this->db->from('tr_purchase_order a');
		$this->db->where_in('a.no_po', explode(',', $get_header->no_po));
		$get_no_surat_po = $this->db->get()->result();

		foreach ($get_no_surat_po as $item_po) {
			$arr_po[] = $item_po->no_surat;
		}

		$data = [
			'header' => $get_header,
			'detail' => $get_detail,
			'no_surat_po' => implode(', ', $arr_po)
		];

		$this->template->set($data);
		$this->template->title('Add DN Retur Pembelian');
		$this->template->render('add_cn');
	}

	public function list_retur()
	{
		$this->template->title('List Retur');
		$this->template->render('list_retur');
	}

	public function get_datatable_retur()
	{
		$draw = $this->input->get('draw', true);
		$length = $this->input->get('length', true);
		$start = $this->input->get('start', true);
		$search = $this->input->get('search', true);

		$this->db->select('a.*');
		$this->db->from('tr_retur_pembelian a');
		$this->db->join('tr_dn_retur_pmb b', 'b.id_retur = a.id', 'left');
		$this->db->where('b.id IS NULL');
		$this->db->where('a.deleted_by IS NULL');

		$count_all = $this->db->count_all_results('', false);

		if (!empty($search)) {
			$this->db->group_start();
			$this->db->like('a.no_surat', $search['value'], 'both');
			$this->db->or_like('a.nm_supplier', $search['value'], 'both');
			$this->db->or_like('DATE_FORMAT(a.tgl_retur, "%d %M %Y")', $search['value'], 'both');
			$this->db->or_like('a.no_ref_invoice', $search['value'], 'both');
			$this->db->or_like('DATE_FORMAT(a.tgl_invoice, "%d %M %Y")', $search['value'], 'both');
			$this->db->group_end();
		}

		$count_filter = $this->db->count_all_results('', false);

		$this->db->order_by('a.input_date', 'desc');
		$this->db->limit($length, $start);

		$get_data = $this->db->get()->result();

		$no = (0 + $start);
		$arr_data = [];

		foreach ($get_data as $item) {
			$no++;

			$no_surat_po = [];
			if (strpos($item->no_po, ',') !== false) {
				$this->db->select('a.no_surat');
				$this->db->from('tr_purchase_order a');
				$this->db->where_in('a.no_po', explode(',', $item->no_po));
				$get_no_po = $this->db->get()->result();

				foreach ($get_no_po as $item_no_po) {
					$no_surat_po[] = $item_no_po->no_surat;
				}
			} else {
				$this->db->select('a.no_surat');
				$this->db->from('tr_purchase_order a');
				$this->db->where('a.no_po', $item->no_po);
				$get_no_po = $this->db->get()->row();

				$no_surat_po[] = $get_no_po->no_surat;
			}

			$no_surat_po = implode(', ', $no_surat_po);

			$action = $this->_render_action_retur_pembelian($item);

			$arr_data[] = [
				'no' => $no,
				'no_retur' => $item->no_surat,
				'no_po' => $no_surat_po,
				'nama_supplier' => $item->nm_supplier,
				'tanggal_retur' => date('d F Y', strtotime($item->tgl_retur)),
				'no_ref_invoice' => $item->no_ref_invoice,
				'tanggal_invoice' => date('d F Y', strtotime($item->tgl_invoice)),
				'action' => $action
			];
		}

		$response = [
			'draw' => intval($draw),
			'recordsTotal' => $count_all,
			'recordsFiltered' => $count_filter,
			'data' => $arr_data
		];

		echo json_encode($response);
	}

	public function _render_action_retur_pembelian($item)
	{
		$action = '<a href="' . base_url('retur_pmb_cn/add_cn/' . $item->id) . '" class="btn btn-sm btn-primary"><i class="fa fa-arrow-up"></i></a>';

		return $action;
	}

	public function save_dn_retur()
	{
		$this->db->trans_begin();

		$id_retur = $this->input->post('id_retur', true);
		$header = $this->Retur_pmb_cn_model->get_retur_header($id_retur);
		$detail = $this->Retur_pmb_cn_model->get_retur_detail($header->no_surat);

		try {
			$no_dn = $this->Retur_pmb_cn_model->generate_no_dn();
			$arr_insert_header = [
				'no_surat' => $no_dn,
				'id_retur' => $id_retur,
				'id_supplier' => $header->id_supplier,
				'nm_supplier' => $header->nm_supplier,
				'no_po' => $header->no_po,
				'tgl_retur' => $header->tgl_retur,
				'alasan_retur' => $header->alasan_retur,
				'subtotal' => $this->input->post('total', true),
				'nilai_ppn' => str_replace(',', '', $this->input->post('ppn', true)),
				'grand_total' => str_replace(',', '', $this->input->post('grand_total', true)),
				'input_by' => $this->auth->user_id(),
				'input_date' => date('Y-m-d H:i:s')
			];

			$insert_header = $this->db->insert('tr_dn_retur_pmb', $arr_insert_header);
			if (!$insert_header) {
				throw new Exception('Maaf, DN gagal terbuat !');
			}

			$arr_insert_detail = [];

			foreach ($detail as $item_detail) {
				$arr_insert_detail[] = [
					'id_cn' => $no_dn,
					'id_retur' => $id_retur,
					'id_detail_po' => $item_detail->id_detail_po,
					'no_po' => $item_detail->no_po,
					'id_pr' => $item_detail->id_pr,
					'id_material' => $item_detail->id_material,
					'nama_material' => $item_detail->nama_material,
					'width' => $item_detail->width,
					'qty' => $item_detail->jumlah_retur,
					'unit_price' => $item_detail->harga_satuan,
					'grand_total' => $item_detail->grand_total,
					'input_by' => $this->auth->user_id(),
					'input_date' => date('Y-m-d H:i:s')
				];
			}

			if (!empty($arr_insert_detail)) {
				$insert_dn_detail = $this->db->insert_batch('dt_dn_retur_pmb', $arr_insert_detail);
				if (!$insert_dn_detail) {
					throw new Exception('Maaf, DN gagal terbuat !');
				}
			} else {
				throw new Exception('Maaf, DN gagal terbuat !');
			}

			$this->db->trans_commit();
			http_response_code(200);

			$response = [
				'code' => 200,
				'msg' => 'Selamat! DN telah berhasil dibuat !'
			];

			echo json_encode($response);
		} catch (Exception $e) {
			$this->db->trans_rollback();
			http_response_code(500);

			$response = [
				'code' => 500,
				'msg' => $e->getMessage()
			];

			echo json_encode($response);
		}
	}

	public function get_datatable_dn()
	{
		$draw = $this->input->get('draw', true);
		$length = $this->input->get('length', true);
		$start = $this->input->get('start', true);
		$search = $this->input->get('search', true);

		$this->db->select('a.*');
		$this->db->from('tr_dn_retur_pmb a');
		$this->db->where('a.deleted_by IS NULL');

		$count_all = $this->db->count_all_results('', false);

		if (!empty($search)) {
			$this->db->group_start();
			$this->db->like('a.no_surat', $search['value'], 'both');
			$this->db->or_like('a.nm_supplier', $search['value'], 'both');
			$this->db->or_like('a.alasan_retur', $search['value'], 'both');
			$this->db->group_end();
		}

		$count_filter = $this->db->count_all_results('', false);

		$this->db->order_by('a.input_date', 'desc');
		$this->db->limit($length, $start);

		$get_data = $this->db->get()->result();

		$no = (0 + $start);
		$arr_data = [];

		foreach ($get_data as $item) {
			$no++;

			$no_surat_po = [];
			if (strpos($item->no_po, ',') !== false) {
				$this->db->select('a.no_surat');
				$this->db->from('tr_purchase_order a');
				$this->db->where_in('a.no_po', explode(',', $item->no_po));
				$get_no_po = $this->db->get()->result();

				foreach ($get_no_po as $item_no_po) {
					$no_surat_po[] = $item_no_po->no_surat;
				}
			} else {
				$this->db->select('a.no_surat');
				$this->db->from('tr_purchase_order a');
				$this->db->where('a.no_po', $item->no_po);
				$get_no_po = $this->db->get()->row();

				$no_surat_po[] = $get_no_po->no_surat;
			}

			$no_surat_po = implode(', ', $no_surat_po);

			$action = $this->_render_action_retur_dn($item);

			$arr_data[] = [
				'no' => $no,
				'no_cn' => $item->no_surat,
				'nama_supplier' => $item->nm_supplier,
				'nomor_po' => $no_surat_po,
				'alasan_retur' => $item->alasan_retur,
				'action' => $action
			];
		}

		$response = [
			'draw' => intval($draw),
			'recordsTotal' => $count_all,
			'recordsFiltered' => $count_filter,
			'data' => $arr_data
		];

		echo json_encode($response);
	}

	public function _render_action_retur_dn($item)
	{
		$btn_view = '';
		$btn_print = '';
		if (has_permission($this->viewPermission)) {
			$btn_view = '<a href="' . base_url('retur_pmb_cn/view/' . $item->id) . '" class="btn btn-sm btn-info" title="View DN"><i class="fa fa-eye"></i></a>';

			$btn_print = '<a href="' . base_url('retur_pmb_cn/print/' . $item->id) . '" class="btn btn-sm btn-primary" title="Print DN"><i class="fa fa-print"></i></a>';
		}

		$action = $btn_view . ' ' . $btn_print;

		return $action;
	}

	public function view($id_dn)
	{
		$get_dn_header = $this->Retur_pmb_cn_model->get_dn_header($id_dn);
		$get_dn_detail = $this->Retur_pmb_cn_model->get_dn_detail($get_dn_header->id_cn);

		$arr_po = [];

		$this->db->select('a.no_surat');
		$this->db->from('tr_purchase_order a');
		$this->db->where_in('a.no_po', explode(',', $get_dn_header->no_po));
		$get_no_surat_po = $this->db->get()->result();

		foreach ($get_no_surat_po as $item_po) {
			$arr_po[] = $item_po->no_surat;
		}

		$data = [
			'header' => $get_dn_header,
			'detail' => $get_dn_detail,
			'no_surat_po' => implode(', ', $arr_po)
		];

		$this->template->set($data);
		$this->template->title('View DN Retur Pembelian');
		$this->template->render('view');
	}
}
