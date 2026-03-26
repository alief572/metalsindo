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

class Retur_pembelian extends Admin_Controller
{
	//Permission
	protected $viewPermission 	= 'Retur_Pembelian.View';
	protected $addPermission  	= 'Retur_Pembelian.Add';
	protected $managePermission = 'Retur_Pembelian.Manage';
	protected $deletePermission = 'Retur_Pembelian.Delete';

	public function __construct()
	{
		parent::__construct();
		$this->load->library(array('Mpdf', 'upload', 'Image_lib'));
		$this->load->model(array(
			'Retur_pembelian/Retur_pembelian_model'
		));
		$this->template->title('Retur Penjualan');
		$this->template->page_icon('fa fa-building-o');

		date_default_timezone_set('Asia/Bangkok');
	}
	public function index()
	{
		$this->auth->restrict($this->viewPermission);
		$session = $this->session->userdata('app_session');
		$this->template->page_icon('fa fa-users');

		$this->template->title('Retur Pembelian');
		$this->template->render('index');
	}

	public function add()
	{
		$get_supplier = $this->Retur_pembelian_model->get_supplier();

		$data = [
			'list_supplier' => $get_supplier
		];

		$this->template->title('Add Retur Pembelian');
		$this->template->set($data);
		$this->template->render('add_retur');
	}

	public function getPO()
	{
		// 1. Ambil dan validasi input
		$supplier = $this->input->get('supplier', TRUE); // TRUE untuk XSS filtering

		if (empty($supplier)) {
			return $this->output
				->set_content_type('application/json')
				->set_status_header(400)
				->set_output(json_encode(['message' => 'Supplier ID is required']));
		}

		try {
			$get_no_po = $this->Retur_pembelian_model->get_no_po($supplier);

			// 2. Cek apakah data ditemukan
			// if (!$get_no_po) {
			// 	throw new Exception('No Purchase Order found for this supplier.');
			// }

			// 3. Kembalikan data dalam bentuk JSON
			return $this->output
				->set_content_type('application/json')
				->set_status_header(200)
				->set_output(json_encode($get_no_po));
		} catch (Exception $e) {
			// 4. Error handling yang lebih informatif
			return $this->output
				->set_content_type('application/json')
				->set_status_header(500)
				->set_output(json_encode(['message' => $e->getMessage()]));
		}
	}

	public function getDetailPO()
	{
		$no_po = $this->input->get('no_po', true);

		try {
			$list_po = $this->Retur_pembelian_model->get_no_po(null, ['no_po', $no_po]);
			if (!$list_po) {
				throw new Exception('Data PO Tidak Ditemukan !');
			}

			$return = '';

			foreach ($list_po as $item_po) {

				$po_detail = $this->Retur_pembelian_model->get_po_detail($item_po->no_po);

				$type_sheet = $this->Retur_pembelian_model->get_po_check_sheet($item_po->no_po);

				$satuan = ($type_sheet > 0) ? '(Sheet)' : '(Kg)';

				$return .= '<h4>No. PO: ' . $item_po->no_surat . '</h4>';
				$return .= '<table class="table table-striped table-bordered">';
				$return .= '<thead>';
				$return .= '<tr>';
				$return .= '<th class="text-center">Tanggal PO</th>';
				$return .= '<th class="text-center">Nama Material</th>';
				$return .= '<th class="text-center">Width</th>';
				$return .= '<th class="text-center">Qty Order ' . $satuan . '</th>';
				$return .= '<th class="text-center">Qty Receive ' . $satuan . '</th>';
				$return .= '<th class="text-center">Retur ' . $satuan . '</th>';
				$return .= '<th class="text-center">Harga</th>';
				$return .= '<th class="text-center">Total</th>';
				$return .= '</tr>';
				$return .= '</thead>';
				$return .= '<tbody>';

				$no_detail = 0;
				foreach ($po_detail as $item_po_detail) {
					$no_detail++;



					$return .= '<tr>';
					$return .= '<td class="text-center">';
					$return .= '<input type="hidden" name="dt_' . $item_po_detail->no_po . '[' . $no_detail . '][id]" value="' . $item_po_detail->id . '">';
					$return .= '<input type="hidden" name="dt_' . $item_po_detail->no_po . '[' . $no_detail . '][no_po]" value="' . $item_po_detail->no_po . '">';
					$return .= '<input type="hidden" name="dt_' . $item_po_detail->no_po . '[' . $no_detail . '][id_pr]" value="' . $item_po_detail->idpr . '">';
					$return .= '<input type="hidden" name="dt_' . $item_po_detail->no_po . '[' . $no_detail . '][idmaterial]" value="' . $item_po_detail->idmaterial . '">';
					$return .= '<input type="hidden" name="dt_' . $item_po_detail->no_po . '[' . $no_detail . '][namamaterial]" value="' . $item_po_detail->namamaterial . '">';
					$return .= '<input type="hidden" name="dt_' . $item_po_detail->no_po . '[' . $no_detail . '][width]" value="' . $item_po_detail->width . '">';
					$return .= '<input type="hidden" name="dt_' . $item_po_detail->no_po . '[' . $no_detail . '][qty_order]" value="' . $item_po_detail->totalwidth . '">';
					$return .= date('d F Y', strtotime($item_po->tanggal));
					$return .= '</td>';
					$return .= '<td>' . $item_po_detail->namamaterial . '</td>';
					$return .= '<td class="text-center">' . $item_po_detail->width . '</td>';
					$return .= '<td class="text-center">' . $item_po_detail->totalwidth . '</td>';
					$return .= '<td>';
					$return .= '<input type="text" class="form-control form-control-sm text-right auto_num" name="dt_' . $item_po_detail->no_po . '[' . $no_detail . '][qty_receive]" value="' . $item_po_detail->berat_terima . '">';
					$return .= '</td>';
					$return .= '<td>';
					$return .= '<input type="text" class="form-control form-control-sm text-right auto_num hitung_detail_total" name="dt_' . $item_po_detail->no_po . '[' . $no_detail . '][retur]" data-no_po="' . $item_po_detail->no_po . '" data-no="' . $no_detail . '">';
					$return .= '</td>';
					$return .= '<td>';
					$return .= '<input type="text" class="form-control form-control-sm text-right auto_num hitung_detail_total" name="dt_' . $item_po_detail->no_po . '[' . $no_detail . '][harga]" value="' . $item_po_detail->hargasatuan . '" data-no_po="' . $item_po_detail->no_po . '" data-no="' . $no_detail . '">';
					$return .= '</td>';
					$return .= '<td>';
					$return .= '<input type="text" class="form-control form-control-sm text-right auto_num" name="dt_' . $item_po_detail->no_po . '[' . $no_detail . '][total_harga]" value="" readonly>';
					$return .= '</td>';
					$return .= '</tr>';
				}

				$return .= '</tbody>';
				$return .= '</table>';
			}

			http_response_code(200);
			echo json_encode([
				'hasil' => $return
			]);
		} catch (Exception $e) {
			$response = [
				'msg' => $e->getMessage()
			];

			echo json_encode($response);
		}
	}

	public function save_retur_pembelian()
	{
		$no_surat = $this->Retur_pembelian_model->BuatNomor();

		$this->db->trans_begin();

		try {
			$fileName = $_FILES['file_ba']['name'];
			$this->load->library(array('PHPExcel'));
			$config['upload_path'] = './assets/file_ba/';
			$config['file_name'] = $fileName;
			$config['allowed_types'] = '*';
			$config['max_size'] = 10000;
			$config['remove_spaces'] = TRUE;
			$config['encrypt_name'] = TRUE;

			$this->load->library('upload', $config);
			$this->upload->initialize($config);
			if ($this->upload->do_upload('file_ba')) {
				$uploadData = $this->upload->data();
				$filenames = $uploadData['file_name'];
			} else {
				throw new Exception('Maaf, File BA gagal terupload !');
			}

			$get_supplier = $this->Retur_pembelian_model->get_supplier($this->input->post('supplier', true));

			$nm_supplier = (!empty($get_supplier)) ? $get_supplier->name_suplier : '';

			$arr_insert_header = [
				'no_surat' => $no_surat,
				'id_supplier' => $this->input->post('supplier', true),
				'nm_supplier' => $nm_supplier,
				'no_po' => implode(',', $this->input->post('no_po', true)),
				'tgl_retur' => $this->input->post('tanggal_retur', true),
				'no_ng_report' => $this->input->post('no_ng_report', true),
				'alasan_retur' => $this->input->post('alasan_retur', true),
				'file_ba' => 'assets/file_ba/' . $filenames,
				'no_ref_invoice' => $this->input->post('no_ref_invoice', true),
				'tgl_invoice' => $this->input->post('tanggal_invoice', true),
				'input_by' => $this->auth->user_id(),
				'input_date' => date('Y-m-d H:i:s')
			];

			$arr_insert_detail = [];

			$arr_update_po = [];

			if (!empty($this->input->post('no_po', true))) {
				foreach ($this->input->post('no_po', true) as $detail_po) {
					if (isset($_POST['dt_' . $detail_po])) {
						foreach ($_POST['dt_' . $detail_po] as $item_detail) {
							$arr_insert_detail[] = [
								'id_header' => $no_surat,
								'id_detail_po' => $item_detail['id'],
								'no_po' => $item_detail['no_po'],
								'id_pr' => $item_detail['id_pr'],
								'id_material' => $item_detail['idmaterial'],
								'nama_material' => $item_detail['namamaterial'],
								'width' => $item_detail['width'],
								'qty_order' => $item_detail['qty_order'],
								'qty_receive' => str_replace(',', '', $item_detail['qty_receive']),
								'jumlah_retur' => str_replace(',', '', $item_detail['retur']),
								'harga_satuan' => str_replace(',', '', $item_detail['harga']),
								'grand_total' => str_replace(',', '', $item_detail['total_harga']),
								'input_by' => $this->auth->user_id(),
								'input_date' => date('Y-m-d H:i:s')
							];

							$arr_update_po[] = [
								'no_po' => $item_detail['no_po'],
								'sts_retur' => 'Y'
							];
						}
					}
				}
			} else {
				throw new Exception('Maaf, data barang yang di akan di retur tidak sesuai !');
			}

			$insert_header = $this->db->insert('tr_retur_pembelian', $arr_insert_header);
			if (!$insert_header) {
				throw new Exception('Maaf, data gagal tidak berhasil disimpan !');
			}

			if (!empty($arr_insert_detail)) {
				$insert_detail = $this->db->insert_batch('dt_retur_pembelian', $arr_insert_detail);
				if (!$insert_detail) {
					throw new Exception('Maaf, data gagal tidak berhasil disimpan !');
				}
			}

			if (!empty($arr_update_po)) {
				$update_po = $this->db->update_batch('tr_purchase_order', $arr_update_po, 'no_po');
				if (!$update_po) {
					throw new Exception('Maaf, data gagal tidak berhasil disimpan !');
				}
			}

			$this->db->trans_commit();
			http_response_code(200);
			header('Content-Type: application/json');

			$response = [
				'status' => true,
				'code'   => 200,
				'pesan'  => 'Data berhasil disimpan !'
			];

			echo json_encode($response);
			exit; // Pastikan tidak ada output lain setelah ini
		} catch (Exception $e) {
			// 1. Rollback transaksi
			$this->db->trans_rollback();

			// 2. Log error untuk kebutuhan debugging di server
			log_message('error', 'Error Retur Pembelian: ' . $e->getMessage());

			// 3. Set HTTP Status Code & Header
			http_response_code(500);
			header('Content-Type: application/json');

			// 4. Return response yang aman
			$response = [
				'status' => false,
				'code'   => 500,
				'pesan'  => (ENVIRONMENT === 'development') ? $e->getMessage() : 'Gagal menyimpan data retur.'
			];

			echo json_encode($response);
			exit; // Pastikan tidak ada output lain setelah ini
		}
	}

	public function update_retur_pembelian()
	{
		$id = $this->input->post('id', true);
		$no_surat = $this->input->post('no_surat', true);

		$this->db->trans_begin();

		try {
			$reset_detail = $this->db->delete('dt_retur_pembelian', ['id_header' => $no_surat]);

			$fileName = $_FILES['file_ba']['name'];
			$this->load->library(array('PHPExcel'));
			$config['upload_path'] = './assets/file_ba/';
			$config['file_name'] = $fileName;
			$config['allowed_types'] = '*';
			$config['max_size'] = 10000;
			$config['remove_spaces'] = TRUE;
			$config['encrypt_name'] = TRUE;

			$this->load->library('upload', $config);
			$this->upload->initialize($config);
			if ($this->upload->do_upload('file_ba')) {
				$uploadData = $this->upload->data();
				$filenames = $uploadData['file_name'];
			}

			$arr_insert_header = [
				'tgl_retur' => $this->input->post('tanggal_retur', true),
				'no_ng_report' => $this->input->post('no_ng_report', true),
				'alasan_retur' => $this->input->post('alasan_retur', true),
				'no_ref_invoice' => $this->input->post('no_ref_invoice', true),
				'tgl_invoice' => $this->input->post('tanggal_invoice', true),
				'updated_by' => $this->auth->user_id(),
				'updated_date' => date('Y-m-d H:i:s')
			];
			if(!empty($filenames)) {
				$arr_insert_header['file_ba'] = 'assets/file_ba/' . $filenames;
			}

			$arr_insert_detail = [];

			if (!empty($this->input->post('no_po', true))) {
				$no_poooo = $this->input->post('no_po', true);
				foreach (explode(',', $no_poooo) as $detail_po) {
					if (isset($_POST['dt_' . $detail_po])) {
						foreach ($_POST['dt_' . $detail_po] as $item_detail) {
							$arr_insert_detail[] = [
								'id_header' => $no_surat,
								'id_detail_po' => $item_detail['id'],
								'no_po' => $item_detail['no_po'],
								'id_pr' => $item_detail['id_pr'],
								'id_material' => $item_detail['idmaterial'],
								'nama_material' => $item_detail['namamaterial'],
								'width' => $item_detail['width'],
								'qty_order' => $item_detail['qty_order'],
								'qty_receive' => str_replace(',', '', $item_detail['qty_receive']),
								'jumlah_retur' => str_replace(',', '', $item_detail['retur']),
								'harga_satuan' => str_replace(',', '', $item_detail['harga']),
								'grand_total' => str_replace(',', '', $item_detail['total_harga']),
								'input_by' => $this->auth->user_id(),
								'input_date' => date('Y-m-d H:i:s')
							];
						}
					}
				}
			} else {
				throw new Exception('Maaf, data barang yang di akan di retur tidak sesuai !');
			}

			$insert_header = $this->db->update('tr_retur_pembelian', $arr_insert_header, ['id' => $id]);
			if (!$insert_header) {
				throw new Exception('Maaf, data gagal tidak berhasil disimpan !');
			}

			if (!empty($arr_insert_detail)) {
				$insert_detail = $this->db->insert_batch('dt_retur_pembelian', $arr_insert_detail);
				if (!$insert_detail) {
					throw new Exception('Maaf, data gagal tidak berhasil disimpan !');
				}
			}

			$this->db->trans_commit();
			http_response_code(200);
			header('Content-Type: application/json');

			$response = [
				'status' => true,
				'code'   => 200,
				'pesan'  => 'Data berhasil disimpan !'
			];

			echo json_encode($response);
			exit; // Pastikan tidak ada output lain setelah ini
		} catch (Exception $e) {
			// 1. Rollback transaksi
			$this->db->trans_rollback();

			// 2. Log error untuk kebutuhan debugging di server
			log_message('error', 'Error Retur Pembelian: ' . $e->getMessage());

			// 3. Set HTTP Status Code & Header
			http_response_code(500);
			header('Content-Type: application/json');

			// 4. Return response yang aman
			$response = [
				'status' => false,
				'code'   => 500,
				'pesan'  => (ENVIRONMENT === 'development') ? $e->getMessage() : 'Gagal menyimpan data retur.'
			];

			echo json_encode($response);
			exit; // Pastikan tidak ada output lain setelah ini
		}
	}

	public function get_datatable_retur()
	{
		$draw = $this->input->get('draw', true);
		$length = $this->input->get('length', true);
		$start = $this->input->get('start', true);
		$search = $this->input->get('search', true);

		$this->db->select('a.*');
		$this->db->from('tr_retur_pembelian a');
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
		$btn_view = '';
		if (has_permission($this->viewPermission)) {
			$btn_view = '<a href="' . base_url('retur_pembelian/view_retur/' . $item->id) . '" class="btn btn-sm btn-info" title="View Retur"><i class="fa fa-eye"></i></a>';
		}

		$btn_delete = '';
		if (has_permission($this->deletePermission) && $item->deleted_by == null) {
			$btn_delete = '<button type="button" class="btn btn-sm btn-danger del_retur" title="Delete Retur" data-id="' . $item->id . '"><i class="fa fa-trash"></i></button>';
		}

		$btn_edit = '';
		if (has_permission($this->deletePermission) && $item->deleted_by == null) {
			$btn_edit = '<a href="' . base_url('retur_pembelian/edit_retur/' . $item->id) . '" class="btn btn-sm btn-warning" title="Edit Retur"><i class="fa fa-pencil"></i></a>';
		}

		$action = $btn_view . ' ' . $btn_delete . ' ' . $btn_edit;

		return $action;
	}

	public function del_retur()
	{
		$id = $this->input->post('id', true);

		// 1. Validasi ID di awal
		if (empty($id)) {
			http_response_code(400);
			echo json_encode(['code' => 400, 'msg' => 'ID tidak ditemukan!']);
			return;
		}

		$this->db->trans_begin();

		try {
			$arr_update = [
				// 'id' => $id, // Baris ini tidak perlu masuk ke set data update
				'deleted_by'   => $this->auth->user_id(),
				'deleted_date' => date('Y-m-d H:i:s')
			];

			// 2. Perbaikan parameter update (menggunakan array untuk WHERE)
			$this->db->update('tr_retur_pembelian', $arr_update, ['id' => $id]);

			// 3. Cek apakah ada baris yang terupdate
			if ($this->db->affected_rows() < 1) {
				throw new Exception('Data tidak ditemukan atau sudah dihapus!');
			}

			if ($this->db->trans_status() === FALSE) {
				throw new Exception('Gagal memproses transaksi database.');
			}

			$this->db->trans_commit();

			header('Content-Type: application/json');
			http_response_code(200);
			echo json_encode([
				'code' => 200,
				'msg'  => 'Retur berhasil dihapus!'
			]);
		} catch (Exception $e) {
			$this->db->trans_rollback();

			log_message('error', 'Del Retur Error: ' . $e->getMessage());

			header('Content-Type: application/json');
			http_response_code(500);
			echo json_encode([
				'code' => 500,
				'msg'  => (ENVIRONMENT === 'development') ? $e->getMessage() : 'Gagal menghapus data retur.'
			]);
		}
	}

	public function view_retur($id) {
		$retur_header = $this->Retur_pembelian_model->get_retur_header($id);
		$retur_detail = $this->Retur_pembelian_model->get_retur_detail($retur_header->no_surat);
		$get_supplier = $this->Retur_pembelian_model->get_supplier();

		$arr_detail = [];
		foreach($retur_detail as $item_detail) {
			$arr_detail[$item_detail->id_detail_po] = $item_detail;
		}

		$data = [
			'header' => $retur_header,
			'detail' => $retur_detail,
			'list_supplier' => $get_supplier,
			'arr_detail' => $arr_detail
		];

		$this->template->title('View Retur');
		$this->template->set($data);
		$this->template->render('view_retur');
	}

	public function edit_retur($id) {
		$retur_header = $this->Retur_pembelian_model->get_retur_header($id);
		$retur_detail = $this->Retur_pembelian_model->get_retur_detail($retur_header->no_surat);
		$get_supplier = $this->Retur_pembelian_model->get_supplier();

		$arr_detail = [];
		foreach($retur_detail as $item_detail) {
			$arr_detail[$item_detail->id_detail_po] = $item_detail;
		}

		$data = [
			'header' => $retur_header,
			'detail' => $retur_detail,
			'list_supplier' => $get_supplier,
			'arr_detail' => $arr_detail
		];

		$this->template->title('Edit Retur');
		$this->template->set($data);
		$this->template->render('edit_retur');
	}
}
