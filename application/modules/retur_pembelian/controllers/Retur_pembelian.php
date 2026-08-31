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
	protected $viewPermission 	= 'Input Retur Pembelian.View';
	protected $addPermission  	= 'Input Retur Pembelian.Add';
	protected $managePermission = 'Input Retur Pembelian.Manage';
	protected $deletePermission = 'Input Retur Pembelian.Delete';

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

	public function getReceiveInvoiceAP()
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
			$result = $this->Retur_pembelian_model->get_receive_invoice_ap_by_supplier($supplier);

			// 2. Kembalikan data dalam bentuk JSON (array kosong jika tidak ada data)
			return $this->output
				->set_content_type('application/json')
				->set_status_header(200)
				->set_output(json_encode($result));
		} catch (Exception $e) {
			// 3. Error handling
			return $this->output
				->set_content_type('application/json')
				->set_status_header(500)
				->set_output(json_encode(['message' => $e->getMessage()]));
		}
	}

	public function getDetailReceiveInvoiceAP()
	{
		$id_rec_inv_ap = $this->input->get('id_rec_inv_ap', true);

		try {
			$list_detail = $this->Retur_pembelian_model->get_detail_by_receive_invoice_ap($id_rec_inv_ap);

			if (empty($list_detail)) {
				http_response_code(404);
				echo json_encode(['msg' => 'Data Receive Invoice AP Tidak Ditemukan !']);
				return;
			}

			$matauang = (!empty($list_detail[0]->matauang)) ? $list_detail[0]->matauang : 'IDR';
			$curr_label = (strtoupper(trim($matauang)) === 'USD') ? 'USD' : ((strtoupper(trim($matauang)) === 'IDR' || strtoupper(trim($matauang)) === 'RP') ? 'Rp' : strtoupper(trim($matauang)));

			$return = '';
			$return .= '<input type="hidden" name="matauang" id="matauang" value="' . $matauang . '">';

			$return .= '<div class="table-responsive">';
			$return .= '<table class="table table-striped table-bordered table-hover table-detail-retur" style="font-size: 13px;">';
			$return .= '<thead style="background-color: #2c3e50; color: #fff;">';
			$return .= '<tr>';
			$return .= '<th class="text-center" style="width: 10%; vertical-align: middle;">Tgl Incoming</th>';
			$return .= '<th class="text-center" style="width: 12%; vertical-align: middle;">Lot Number</th>';
			$return .= '<th class="text-center" style="width: 18%; vertical-align: middle;">Nama Material</th>';
			$return .= '<th class="text-center" style="width: 7%; vertical-align: middle;">Width</th>';
			$return .= '<th class="text-center" style="width: 8%; vertical-align: middle;">Qty Order</th>';
			$return .= '<th class="text-center" style="width: 12%; vertical-align: middle;">Qty Rec</th>';
			$return .= '<th class="text-center" style="width: 12%; vertical-align: middle;">Qty Retur</th>';
			$return .= '<th class="text-center" style="width: 11%; vertical-align: middle;">Harga Satuan</th>';
			$return .= '<th class="text-center" style="width: 10%; vertical-align: middle;">Total Harga</th>';
			$return .= '</tr>';
			$return .= '</thead>';
			$return .= '<tbody>';

			$no_detail = 0;
			foreach ($list_detail as $item) {
				$no_detail++;

				$is_sheet = ($item->id_bentuk == 'B2000002');
				$unit_label = $is_sheet ? 'Sheet' : 'KGS';
				$price_label = $is_sheet ? '/Sheet' : '/Kg';
				$qty_rec_val = $is_sheet ? $item->qty_sheet : $item->width_recive;
				$qty_retur_val = $is_sheet ? $item->qty_sheet : $item->width_recive;
				$harga = (float) $item->hargasatuan;
				$total_harga_item = $qty_retur_val * $harga;

				$return .= '<tr>';
				$return .= '<td class="text-center" style="vertical-align: middle;">';
				$return .= '<input type="hidden" name="dt_' . $item->no_po . '[' . $no_detail . '][id]" value="' . $item->id_dt_po . '">';
				$return .= '<input type="hidden" name="dt_' . $item->no_po . '[' . $no_detail . '][no_po]" value="' . $item->no_po . '">';
				$return .= '<input type="hidden" name="dt_' . $item->no_po . '[' . $no_detail . '][id_pr]" value="' . $item->idpr . '">';
				$return .= '<input type="hidden" name="dt_' . $item->no_po . '[' . $no_detail . '][idmaterial]" value="' . $item->idmaterial . '">';
				$return .= '<input type="hidden" name="dt_' . $item->no_po . '[' . $no_detail . '][namamaterial]" value="' . $item->namamaterial . '">';
				$return .= '<input type="hidden" name="dt_' . $item->no_po . '[' . $no_detail . '][width]" value="' . $item->width . '">';
				$return .= '<input type="hidden" name="dt_' . $item->no_po . '[' . $no_detail . '][qty_order]" value="' . $item->totalwidth . '">';
				$return .= '<input type="hidden" name="dt_' . $item->no_po . '[' . $no_detail . '][lotno]" value="' . $item->lotno . '">';
				$return .= '<input type="hidden" name="dt_' . $item->no_po . '[' . $no_detail . '][is_sheet]" value="' . ($is_sheet ? '1' : '0') . '">';
				$return .= '<input type="hidden" name="dt_' . $item->no_po . '[' . $no_detail . '][matauang]" value="' . $matauang . '">';
				$return .= date('d/m/Y', strtotime($item->tanggal_incoming));
				$return .= '</td>';
				$return .= '<td class="text-center" style="vertical-align: middle;"><span class="badge bg-gray text-bold" style="font-size: 11px;">' . $item->lotno . '</span></td>';
				$return .= '<td style="vertical-align: middle;"><b>' . $item->namamaterial . '</b></td>';
				$return .= '<td class="text-right" style="vertical-align: middle;">' . number_format($item->width, 2) . '</td>';
				$return .= '<td class="text-right" style="vertical-align: middle;">' . number_format($item->totalwidth, 2) . '</td>';
				
				// Qty Receive with input-group
				$return .= '<td style="vertical-align: middle;">';
				$return .= '<div class="input-group input-group-sm">';
				if ($is_sheet) {
					$return .= '<input type="text" class="form-control text-right auto_num" name="dt_' . $item->no_po . '[' . $no_detail . '][qty_sheet]" value="' . $item->qty_sheet . '" readonly style="background-color: #f9f9f9; font-weight: 500;">';
					$return .= '<input type="hidden" name="dt_' . $item->no_po . '[' . $no_detail . '][qty_receive]" value="' . $item->width_recive . '">';
				} else {
					$return .= '<input type="text" class="form-control text-right auto_num" name="dt_' . $item->no_po . '[' . $no_detail . '][qty_receive]" value="' . $item->width_recive . '" readonly style="background-color: #f9f9f9; font-weight: 500;">';
					$return .= '<input type="hidden" name="dt_' . $item->no_po . '[' . $no_detail . '][qty_sheet]" value="' . $item->qty_sheet . '">';
				}
				$return .= '<span class="input-group-addon" style="font-size: 10px; font-weight: bold; background: #eee; min-width: 45px;">' . $unit_label . '</span>';
				$return .= '</div>';
				$return .= '</td>';

				// Qty Retur with input-group
				$return .= '<td style="vertical-align: middle;">';
				$return .= '<div class="input-group input-group-sm">';
				if ($is_sheet) {
					$return .= '<input type="text" class="form-control text-right auto_num hitung_detail_total" name="dt_' . $item->no_po . '[' . $no_detail . '][retur_sheet]" value="' . $item->qty_sheet . '" data-no_po="' . $item->no_po . '" data-no="' . $no_detail . '" data-is_sheet="1" style="border-color: #3c8dbc; font-weight: bold; color: #3c8dbc;">';
					$return .= '<input type="hidden" name="dt_' . $item->no_po . '[' . $no_detail . '][retur]" value="' . $item->width_recive . '">';
				} else {
					$return .= '<input type="text" class="form-control text-right auto_num hitung_detail_total" name="dt_' . $item->no_po . '[' . $no_detail . '][retur]" value="' . $item->width_recive . '" data-no_po="' . $item->no_po . '" data-no="' . $no_detail . '" data-is_sheet="0" style="border-color: #3c8dbc; font-weight: bold; color: #3c8dbc;">';
					$return .= '<input type="hidden" name="dt_' . $item->no_po . '[' . $no_detail . '][retur_sheet]" value="0">';
				}
				$return .= '<span class="input-group-addon" style="font-size: 10px; font-weight: bold; background: #3c8dbc; color: #fff; min-width: 45px;">' . $unit_label . '</span>';
				$return .= '</div>';
				$return .= '</td>';

				// Harga Satuan with input-group
				$return .= '<td style="vertical-align: middle;">';
				$return .= '<div class="input-group input-group-sm">';
				$return .= '<span class="input-group-addon" style="font-size: 10px; background: #eee; min-width: 32px;">' . $curr_label . '</span>';
				$return .= '<input type="text" class="form-control text-right auto_num" name="dt_' . $item->no_po . '[' . $no_detail . '][harga]" value="' . $harga . '" readonly style="background-color: #f9f9f9;">';
				$return .= '<span class="input-group-addon" style="font-size: 10px; background: #eee; min-width: 45px;">' . $price_label . '</span>';
				$return .= '</div>';
				$return .= '</td>';

				// Total Harga with input-group
				$return .= '<td style="vertical-align: middle;">';
				$return .= '<div class="input-group input-group-sm">';
				$return .= '<span class="input-group-addon" style="font-size: 10px; background: #eee; min-width: 32px;">' . $curr_label . '</span>';
				$return .= '<input type="text" class="form-control text-right auto_num row_total_harga" name="dt_' . $item->no_po . '[' . $no_detail . '][total_harga]" value="' . $total_harga_item . '" readonly style="background-color: #f9f9f9; font-weight: bold;">';
				$return .= '</div>';
				$return .= '</td>';

				$return .= '</tr>';
			}

			$return .= '</tbody>';
			$return .= '<tfoot style="background-color: #fcfcfc;">';
			$return .= '<tr>';
			$return .= '<td colspan="5" class="text-right text-bold" style="vertical-align: middle;">Total Qty</td>';
			$return .= '<td style="vertical-align: middle;"><input type="text" class="form-control form-control-sm text-right auto_num" id="footer_total_qty_receive" readonly style="background: transparent; border: none; font-weight: bold;"></td>';
			$return .= '<td style="vertical-align: middle;"><input type="text" class="form-control form-control-sm text-right auto_num" id="footer_total_retur" readonly style="background: transparent; border: none; font-weight: bold; color: #3c8dbc;"></td>';
			$return .= '<td class="text-right text-bold" style="vertical-align: middle;">Subtotal</td>';
			$return .= '<td style="vertical-align: middle;">';
			$return .= '<div class="input-group input-group-sm">';
			$return .= '<span class="input-group-addon" style="font-size: 10px; background: #eee; min-width: 32px;">' . $curr_label . '</span>';
			$return .= '<input type="text" class="form-control text-right auto_num text-bold" id="footer_subtotal" name="subtotal" readonly style="background-color: #f9f9f9;">';
			$return .= '</div>';
			$return .= '</td>';
			$return .= '</tr>';
			$return .= '<tr>';
			$return .= '<td colspan="8" class="text-right text-bold" style="vertical-align: middle;">';
			$return .= '<div style="display: flex; justify-content: flex-end; align-items: center;">';
			$return .= '<span style="margin-right: 10px;">PPN</span>';
			$return .= '<div class="input-group input-group-sm" style="width: 100px;">';
			$return .= '<input type="number" step="any" min="0" max="100" class="form-control text-right" id="footer_ppn_persen" name="ppn_persen" value="11" style="font-weight: bold;">';
			$return .= '<span class="input-group-addon">%</span>';
			$return .= '</div>';
			$return .= '</div>';
			$return .= '</td>';
			$return .= '<td style="vertical-align: middle;">';
			$return .= '<div class="input-group input-group-sm">';
			$return .= '<span class="input-group-addon" style="font-size: 10px; background: #eee; min-width: 32px;">' . $curr_label . '</span>';
			$return .= '<input type="text" class="form-control text-right auto_num" id="footer_nilai_ppn" name="nilai_ppn" readonly style="background-color: #f9f9f9;">';
			$return .= '</div>';
			$return .= '</td>';
			$return .= '</tr>';
			$return .= '<tr style="background-color: #f0f7fd; border-top: 2px solid #3c8dbc;">';
			$return .= '<td colspan="8" class="text-right text-bold" style="font-size: 15px; vertical-align: middle; color: #2c3e50;">Grand Total</td>';
			$return .= '<td style="vertical-align: middle;">';
			$return .= '<div class="input-group input-group-sm">';
			$return .= '<span class="input-group-addon" style="font-size: 11px; background: #3c8dbc; color: #fff; font-weight: bold; min-width: 32px;">' . $curr_label . '</span>';
			$return .= '<input type="text" class="form-control text-right auto_num text-bold" style="font-size: 15px; color: #2c3e50; background: #fff;" id="footer_grand_total" name="grand_total" readonly>';
			$return .= '</div>';
			$return .= '</td>';
			$return .= '</tr>';
			$return .= '</tfoot>';
			$return .= '</table>';
			$return .= '</div>';

			http_response_code(200);
			echo json_encode(['hasil' => $return]);
		} catch (Exception $e) {
			http_response_code(500);
			echo json_encode(['msg' => $e->getMessage()]);
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

				$matauang = (!empty($item_po->matauang)) ? $item_po->matauang : 'IDR';
				$curr_label = (strtoupper(trim($matauang)) === 'USD') ? 'USD' : ((strtoupper(trim($matauang)) === 'IDR' || strtoupper(trim($matauang)) === 'RP') ? 'Rp' : strtoupper(trim($matauang)));

				$po_detail = $this->Retur_pembelian_model->get_po_detail($item_po->no_po);

				$return .= '<h4>No. PO: ' . $item_po->no_surat . ' <span class="badge bg-blue">' . $matauang . '</span></h4>';
				$return .= '<input type="hidden" name="matauang" value="' . $matauang . '">';
				$return .= '<div class="table-responsive">';
				$return .= '<table class="table table-striped table-bordered table-hover table-detail-retur" style="font-size: 13px;">';
				$return .= '<thead style="background-color: #2c3e50; color: #fff;">';
				$return .= '<tr>';
				$return .= '<th class="text-center" style="width: 10%; vertical-align: middle;">Tanggal PO</th>';
				$return .= '<th class="text-center" style="width: 12%; vertical-align: middle;">Lot Number</th>';
				$return .= '<th class="text-center" style="width: 18%; vertical-align: middle;">Nama Material</th>';
				$return .= '<th class="text-center" style="width: 7%; vertical-align: middle;">Width</th>';
				$return .= '<th class="text-center" style="width: 8%; vertical-align: middle;">Qty Order</th>';
				$return .= '<th class="text-center" style="width: 12%; vertical-align: middle;">Qty Rec</th>';
				$return .= '<th class="text-center" style="width: 12%; vertical-align: middle;">Qty Retur</th>';
				$return .= '<th class="text-center" style="width: 11%; vertical-align: middle;">Harga Satuan</th>';
				$return .= '<th class="text-center" style="width: 10%; vertical-align: middle;">Total Harga</th>';
				$return .= '</tr>';
				$return .= '</thead>';
				$return .= '<tbody>';

				$no_detail = 0;
				foreach ($po_detail as $item_po_detail) {
					$no_detail++;

					$material = $this->db->select('id_bentuk')->get_where('ms_inventory_category3', ['id_category3' => $item_po_detail->idmaterial])->row();
					$id_bentuk = !empty($material) ? $material->id_bentuk : '';

					$incoming_detail = $this->db->get_where('dt_incoming', ['id_dt_po' => $item_po_detail->id])->row();
					$lotno = !empty($incoming_detail) ? $incoming_detail->lotno : '';
					$qty_sheet = !empty($incoming_detail) ? $incoming_detail->qty_sheet : 0;
					$berat_terima = !empty($item_po_detail->berat_terima) ? $item_po_detail->berat_terima : 0;

					$is_sheet = ($id_bentuk == 'B2000002');
					$unit_label = $is_sheet ? 'Sheet' : 'KGS';
					$price_label = $is_sheet ? '/Sheet' : '/Kg';
					$qty_rec_val = $is_sheet ? $qty_sheet : $berat_terima;
					$qty_retur_val = $is_sheet ? $qty_sheet : $berat_terima;
					$harga = (float) $item_po_detail->hargasatuan;
					$total_harga_item = $qty_retur_val * $harga;

					$return .= '<tr>';
					$return .= '<td class="text-center" style="vertical-align: middle;">';
					$return .= '<input type="hidden" name="dt_' . $item_po_detail->no_po . '[' . $no_detail . '][id]" value="' . $item_po_detail->id . '">';
					$return .= '<input type="hidden" name="dt_' . $item_po_detail->no_po . '[' . $no_detail . '][no_po]" value="' . $item_po_detail->no_po . '">';
					$return .= '<input type="hidden" name="dt_' . $item_po_detail->no_po . '[' . $no_detail . '][id_pr]" value="' . $item_po_detail->idpr . '">';
					$return .= '<input type="hidden" name="dt_' . $item_po_detail->no_po . '[' . $no_detail . '][idmaterial]" value="' . $item_po_detail->idmaterial . '">';
					$return .= '<input type="hidden" name="dt_' . $item_po_detail->no_po . '[' . $no_detail . '][namamaterial]" value="' . $item_po_detail->namamaterial . '">';
					$return .= '<input type="hidden" name="dt_' . $item_po_detail->no_po . '[' . $no_detail . '][width]" value="' . $item_po_detail->width . '">';
					$return .= '<input type="hidden" name="dt_' . $item_po_detail->no_po . '[' . $no_detail . '][qty_order]" value="' . $item_po_detail->totalwidth . '">';
					$return .= '<input type="hidden" name="dt_' . $item_po_detail->no_po . '[' . $no_detail . '][lotno]" value="' . $lotno . '">';
					$return .= '<input type="hidden" name="dt_' . $item_po_detail->no_po . '[' . $no_detail . '][is_sheet]" value="' . ($is_sheet ? '1' : '0') . '">';
					$return .= '<input type="hidden" name="dt_' . $item_po_detail->no_po . '[' . $no_detail . '][matauang]" value="' . $matauang . '">';
					$return .= date('d/m/Y', strtotime($item_po->tanggal));
					$return .= '</td>';
					$return .= '<td class="text-center" style="vertical-align: middle;"><span class="badge bg-gray text-bold" style="font-size: 11px;">' . $lotno . '</span></td>';
					$return .= '<td style="vertical-align: middle;"><b>' . $item_po_detail->namamaterial . '</b></td>';
					$return .= '<td class="text-right" style="vertical-align: middle;">' . number_format($item_po_detail->width, 2) . '</td>';
					$return .= '<td class="text-right" style="vertical-align: middle;">' . number_format($item_po_detail->totalwidth, 2) . '</td>';
					
					// Qty Receive with input-group
					$return .= '<td style="vertical-align: middle;">';
					$return .= '<div class="input-group input-group-sm">';
					if ($is_sheet) {
						$return .= '<input type="text" class="form-control text-right auto_num" name="dt_' . $item_po_detail->no_po . '[' . $no_detail . '][qty_sheet]" value="' . $qty_sheet . '" readonly style="background-color: #f9f9f9; font-weight: 500;">';
						$return .= '<input type="hidden" name="dt_' . $item_po_detail->no_po . '[' . $no_detail . '][qty_receive]" value="' . $berat_terima . '">';
					} else {
						$return .= '<input type="text" class="form-control text-right auto_num" name="dt_' . $item_po_detail->no_po . '[' . $no_detail . '][qty_receive]" value="' . $berat_terima . '" readonly style="background-color: #f9f9f9; font-weight: 500;">';
						$return .= '<input type="hidden" name="dt_' . $item_po_detail->no_po . '[' . $no_detail . '][qty_sheet]" value="' . $qty_sheet . '">';
					}
					$return .= '<span class="input-group-addon" style="font-size: 10px; font-weight: bold; background: #eee; min-width: 45px;">' . $unit_label . '</span>';
					$return .= '</div>';
					$return .= '</td>';

					// Qty Retur with input-group
					$return .= '<td style="vertical-align: middle;">';
					$return .= '<div class="input-group input-group-sm">';
					if ($is_sheet) {
						$return .= '<input type="text" class="form-control text-right auto_num hitung_detail_total" name="dt_' . $item_po_detail->no_po . '[' . $no_detail . '][retur_sheet]" value="' . $qty_sheet . '" data-no_po="' . $item_po_detail->no_po . '" data-no="' . $no_detail . '" data-is_sheet="1" style="border-color: #3c8dbc; font-weight: bold; color: #3c8dbc;">';
						$return .= '<input type="hidden" name="dt_' . $item_po_detail->no_po . '[' . $no_detail . '][retur]" value="' . $berat_terima . '">';
					} else {
						$return .= '<input type="text" class="form-control text-right auto_num hitung_detail_total" name="dt_' . $item_po_detail->no_po . '[' . $no_detail . '][retur]" value="' . $berat_terima . '" data-no_po="' . $item_po_detail->no_po . '" data-no="' . $no_detail . '" data-is_sheet="0" style="border-color: #3c8dbc; font-weight: bold; color: #3c8dbc;">';
						$return .= '<input type="hidden" name="dt_' . $item_po_detail->no_po . '[' . $no_detail . '][retur_sheet]" value="0">';
					}
					$return .= '<span class="input-group-addon" style="font-size: 10px; font-weight: bold; background: #3c8dbc; color: #fff; min-width: 45px;">' . $unit_label . '</span>';
					$return .= '</div>';
					$return .= '</td>';

					// Harga Satuan with input-group
					$return .= '<td style="vertical-align: middle;">';
					$return .= '<div class="input-group input-group-sm">';
					$return .= '<span class="input-group-addon" style="font-size: 10px; background: #eee; min-width: 32px;">' . $curr_label . '</span>';
					$return .= '<input type="text" class="form-control text-right auto_num" name="dt_' . $item_po_detail->no_po . '[' . $no_detail . '][harga]" value="' . $harga . '" readonly style="background-color: #f9f9f9;">';
					$return .= '<span class="input-group-addon" style="font-size: 10px; background: #eee; min-width: 45px;">' . $price_label . '</span>';
					$return .= '</div>';
					$return .= '</td>';

					// Total Harga with input-group
					$return .= '<td style="vertical-align: middle;">';
					$return .= '<div class="input-group input-group-sm">';
					$return .= '<span class="input-group-addon" style="font-size: 10px; background: #eee; min-width: 32px;">' . $curr_label . '</span>';
					$return .= '<input type="text" class="form-control text-right auto_num row_total_harga" name="dt_' . $item_po_detail->no_po . '[' . $no_detail . '][total_harga]" value="' . $total_harga_item . '" readonly style="background-color: #f9f9f9; font-weight: bold;">';
					$return .= '</div>';
					$return .= '</td>';

					$return .= '</tr>';
				}

				$return .= '</tbody>';
				$return .= '<tfoot style="background-color: #fcfcfc;">';
				$return .= '<tr>';
				$return .= '<td colspan="5" class="text-right text-bold" style="vertical-align: middle;">Total Qty</td>';
				$return .= '<td style="vertical-align: middle;"><input type="text" class="form-control form-control-sm text-right auto_num" id="footer_total_qty_receive" readonly style="background: transparent; border: none; font-weight: bold;"></td>';
				$return .= '<td style="vertical-align: middle;"><input type="text" class="form-control form-control-sm text-right auto_num" id="footer_total_retur" readonly style="background: transparent; border: none; font-weight: bold; color: #3c8dbc;"></td>';
				$return .= '<td class="text-right text-bold" style="vertical-align: middle;">Subtotal</td>';
				$return .= '<td style="vertical-align: middle;">';
				$return .= '<div class="input-group input-group-sm">';
				$return .= '<span class="input-group-addon" style="font-size: 10px; background: #eee; min-width: 32px;">' . $curr_label . '</span>';
				$return .= '<input type="text" class="form-control text-right auto_num text-bold" id="footer_subtotal" name="subtotal" readonly style="background-color: #f9f9f9;">';
				$return .= '</div>';
				$return .= '</td>';
				$return .= '</tr>';
				$return .= '<tr>';
				$return .= '<td colspan="8" class="text-right text-bold" style="vertical-align: middle;">';
				$return .= '<div style="display: flex; justify-content: flex-end; align-items: center;">';
				$return .= '<span style="margin-right: 10px;">PPN</span>';
				$return .= '<div class="input-group input-group-sm" style="width: 100px;">';
				$return .= '<input type="number" step="any" min="0" max="100" class="form-control text-right" id="footer_ppn_persen" name="ppn_persen" value="11" style="font-weight: bold;">';
				$return .= '<span class="input-group-addon">%</span>';
				$return .= '</div>';
				$return .= '</div>';
				$return .= '</td>';
				$return .= '<td style="vertical-align: middle;">';
				$return .= '<div class="input-group input-group-sm">';
				$return .= '<span class="input-group-addon" style="font-size: 10px; background: #eee; min-width: 32px;">' . $curr_label . '</span>';
				$return .= '<input type="text" class="form-control text-right auto_num" id="footer_nilai_ppn" name="nilai_ppn" readonly style="background-color: #f9f9f9;">';
				$return .= '</div>';
				$return .= '</td>';
				$return .= '</tr>';
				$return .= '<tr style="background-color: #f0f7fd; border-top: 2px solid #3c8dbc;">';
				$return .= '<td colspan="8" class="text-right text-bold" style="font-size: 15px; vertical-align: middle; color: #2c3e50;">Grand Total</td>';
				$return .= '<td style="vertical-align: middle;">';
				$return .= '<div class="input-group input-group-sm">';
				$return .= '<span class="input-group-addon" style="font-size: 11px; background: #3c8dbc; color: #fff; font-weight: bold; min-width: 32px;">' . $curr_label . '</span>';
				$return .= '<input type="text" class="form-control text-right auto_num text-bold" style="font-size: 15px; color: #2c3e50; background: #fff;" id="footer_grand_total" name="grand_total" readonly>';
				$return .= '</div>';
				$return .= '</td>';
				$return .= '</tr>';
				$return .= '</tfoot>';
				$return .= '</table>';
				$return .= '</div>';
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
			// Validasi: id_rec_inv_ap wajib dipilih
			if (empty($this->input->post('id_rec_inv_ap', true))) {
				throw new Exception('Receive Invoice AP harus dipilih');
			}

			$target_dir = './assets/file_ba/';
			if (!is_dir($target_dir)) {
				mkdir($target_dir, 0777, true);
				chmod($target_dir, 0777);
			} elseif (!is_writable($target_dir)) {
				@chmod($target_dir, 0777);
			}

			$filenames = '';
			if (!empty($_FILES['file_ba']['name'])) {
				$fileName = $_FILES['file_ba']['name'];
				$this->load->library(array('PHPExcel'));
				$config['upload_path'] = $target_dir;
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
					throw new Exception('Maaf, File BA gagal terupload: ' . $this->upload->display_errors('', ''));
				}
			}

			$get_supplier = $this->Retur_pembelian_model->get_supplier($this->input->post('supplier', true));

			$nm_supplier = (!empty($get_supplier)) ? $get_supplier->name_suplier : '';

			// Kumpulkan no_po unik dari $_POST keys yang dimulai dengan 'dt_'
			$arr_no_po_unique = [];
			foreach (array_keys($_POST) as $post_key) {
				if (strpos($post_key, 'dt_') === 0) {
					$no_po_from_key = substr($post_key, 3); // hapus prefix 'dt_'
					$arr_no_po_unique[$no_po_from_key] = true;
				}
			}

			$arr_insert_detail = [];

			foreach (array_keys($arr_no_po_unique) as $detail_po) {
				if (isset($_POST['dt_' . $detail_po])) {
					foreach ($_POST['dt_' . $detail_po] as $item_detail) {
						$jumlah_retur = (float) str_replace(',', '', (isset($item_detail['retur']) ? $item_detail['retur'] : 0));
						$qty_receive  = (float) str_replace(',', '', (isset($item_detail['qty_receive']) ? $item_detail['qty_receive'] : 0));
						$qty_sheet = (int) str_replace(',', '', (isset($item_detail['qty_sheet']) ? $item_detail['qty_sheet'] : 0));
						$qty_sheet_retur = (int) str_replace(',', '', (isset($item_detail['retur_sheet']) ? $item_detail['retur_sheet'] : 0));
						$nama_material = isset($item_detail['namamaterial']) ? $item_detail['namamaterial'] : '';
						$harga_satuan = isset($item_detail['harga']) ? (float) str_replace(',', '', $item_detail['harga']) : 0;

						$material_check = $this->db->select('id_bentuk')->get_where('ms_inventory_category3', ['id_category3' => $item_detail['idmaterial']])->row();
						$is_sheet = (!empty($material_check) && $material_check->id_bentuk == 'B2000002');

						if ($is_sheet) {
							if ($qty_sheet_retur <= 0) {
								throw new Exception('Jumlah retur (Sheet) ' . $nama_material . ' harus lebih dari 0');
							}
							if ($qty_sheet > 0 && $qty_sheet_retur > $qty_sheet) {
								throw new Exception('Jumlah retur (Sheet) ' . $nama_material . ' melebihi qty receive');
							}
							$grand_total_item = $qty_sheet_retur * $harga_satuan;
						} else {
							if ($jumlah_retur <= 0) {
								throw new Exception('Jumlah retur (Kg) ' . $nama_material . ' harus lebih dari 0');
							}
							if ($qty_receive > 0 && $jumlah_retur > $qty_receive) {
								throw new Exception('Jumlah retur (Kg) ' . $nama_material . ' melebihi qty receive');
							}
							$grand_total_item = $jumlah_retur * $harga_satuan;
						}

						$arr_insert_detail[] = [
							'id_header' => $no_surat,
							'id_detail_po' => $item_detail['id'],
							'no_po' => $item_detail['no_po'],
							'id_pr' => $item_detail['id_pr'],
							'id_material' => $item_detail['idmaterial'],
							'lotno' => isset($item_detail['lotno']) ? $item_detail['lotno'] : NULL,
							'nama_material' => $nama_material,
							'width' => $item_detail['width'],
							'qty_order' => $item_detail['qty_order'],
							'qty_receive' => $qty_receive,
							'qty_sheet' => $qty_sheet,
							'jumlah_retur' => $jumlah_retur,
							'qty_sheet_retur' => $qty_sheet_retur,
							'harga_satuan' => $harga_satuan,
							'grand_total' => $grand_total_item,
							'matauang' => (!empty($item_detail['matauang'])) ? $item_detail['matauang'] : ($this->input->post('matauang', true) ? $this->input->post('matauang', true) : 'IDR'),
							'input_by' => $this->auth->user_id(),
							'input_date' => date('Y-m-d H:i:s')
						];
					}
				}
			}

			if (empty($arr_insert_detail)) {
				throw new Exception('Maaf, data barang yang di akan di retur tidak sesuai !');
			}

			// Kumpulkan no_po unik dari detail items untuk disimpan di header
			$arr_no_po_header = [];
			foreach ($arr_insert_detail as $detail_item) {
				$arr_no_po_header[$detail_item['no_po']] = true;
			}

			$subtotal = isset($_POST['subtotal']) ? (float) str_replace(',', '', $_POST['subtotal']) : 0;
			$ppn_persen = isset($_POST['ppn_persen']) ? (float) str_replace(',', '', $_POST['ppn_persen']) : 0;
			$nilai_ppn = isset($_POST['nilai_ppn']) ? (float) str_replace(',', '', $_POST['nilai_ppn']) : 0;
			$grand_total = isset($_POST['grand_total']) ? (float) str_replace(',', '', $_POST['grand_total']) : 0;

			if ($subtotal <= 0 && !empty($arr_insert_detail)) {
				$subtotal = array_sum(array_column($arr_insert_detail, 'grand_total'));
				$nilai_ppn = ($subtotal * $ppn_persen) / 100;
				$grand_total = $subtotal + $nilai_ppn;
			}

			$id_rec_inv_ap = $this->input->post('id_rec_inv_ap', true);
			$no_ref_invoice = !empty($id_rec_inv_ap) ? $id_rec_inv_ap : $this->input->post('no_ref_invoice', true);
			$matauang = $this->input->post('matauang', true);
			if (empty($matauang) && !empty($arr_insert_detail[0]['matauang'])) {
				$matauang = $arr_insert_detail[0]['matauang'];
			}
			if (empty($matauang)) {
				$matauang = 'IDR';
			}

			$arr_insert_header = [
				'no_surat' => $no_surat,
				'id_supplier' => $this->input->post('supplier', true),
				'nm_supplier' => $nm_supplier,
				'no_po' => implode(',', array_keys($arr_no_po_header)),
				'id_rec_inv_ap' => $id_rec_inv_ap,
				'tgl_retur' => $this->input->post('tanggal_retur', true),
				'no_ng_report' => $this->input->post('no_ng_report', true),
				'alasan_retur' => $this->input->post('alasan_retur', true),
				'file_ba' => 'assets/file_ba/' . $filenames,
				'no_ref_invoice' => $no_ref_invoice,
				'tgl_invoice' => $this->input->post('tanggal_invoice', true),
				'matauang' => $matauang,
				'subtotal' => $subtotal,
				'ppn_persen' => $ppn_persen,
				'nilai_ppn' => $nilai_ppn,
				'grand_total' => $grand_total,
				'input_by' => $this->auth->user_id(),
				'input_date' => date('Y-m-d H:i:s')
			];

			$insert_header = $this->db->insert('tr_retur_pembelian', $arr_insert_header);
			if (!$insert_header) {
				throw new Exception('Maaf, data gagal tidak berhasil disimpan !');
			}

			$insert_detail = $this->db->insert_batch('dt_retur_pembelian', $arr_insert_detail);
			if (!$insert_detail) {
				throw new Exception('Maaf, data gagal tidak berhasil disimpan !');
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

			$target_dir = './assets/file_ba/';
			if (!is_dir($target_dir)) {
				mkdir($target_dir, 0777, true);
				chmod($target_dir, 0777);
			} elseif (!is_writable($target_dir)) {
				@chmod($target_dir, 0777);
			}

			$filenames = '';
			if (!empty($_FILES['file_ba']['name'])) {
				$fileName = $_FILES['file_ba']['name'];
				$this->load->library(array('PHPExcel'));
				$config['upload_path'] = $target_dir;
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
					throw new Exception('Maaf, File BA gagal terupload: ' . $this->upload->display_errors('', ''));
				}
			}

			$id_rec_inv_ap = $this->input->post('id_rec_inv_ap', true);
			$no_ref_invoice = !empty($id_rec_inv_ap) ? $id_rec_inv_ap : $this->input->post('no_ref_invoice', true);

			$arr_insert_detail = [];

			if (!empty($this->input->post('no_po', true))) {
				$no_poooo = $this->input->post('no_po', true);
				foreach (explode(',', $no_poooo) as $detail_po) {
					if (isset($_POST['dt_' . $detail_po])) {
						foreach ($_POST['dt_' . $detail_po] as $item_detail) {
							$jumlah_retur = (float) str_replace(',', '', (isset($item_detail['retur']) ? $item_detail['retur'] : 0));
							$qty_receive  = (float) str_replace(',', '', (isset($item_detail['qty_receive']) ? $item_detail['qty_receive'] : 0));
							$qty_sheet = (int) str_replace(',', '', (isset($item_detail['qty_sheet']) ? $item_detail['qty_sheet'] : 0));
							$qty_sheet_retur = (int) str_replace(',', '', (isset($item_detail['retur_sheet']) ? $item_detail['retur_sheet'] : 0));
							$nama_material = isset($item_detail['namamaterial']) ? $item_detail['namamaterial'] : '';
							$harga_satuan = isset($item_detail['harga']) ? (float) str_replace(',', '', $item_detail['harga']) : 0;

							$material_check = $this->db->select('id_bentuk')->get_where('ms_inventory_category3', ['id_category3' => $item_detail['idmaterial']])->row();
							$is_sheet = (!empty($material_check) && $material_check->id_bentuk == 'B2000002');

							if ($is_sheet) {
								if ($qty_sheet_retur <= 0) {
									throw new Exception('Jumlah retur (Sheet) ' . $nama_material . ' harus lebih dari 0');
								}
								if ($qty_sheet > 0 && $qty_sheet_retur > $qty_sheet) {
									throw new Exception('Jumlah retur (Sheet) ' . $nama_material . ' melebihi qty receive');
								}
								$grand_total_item = $qty_sheet_retur * $harga_satuan;
							} else {
								if ($jumlah_retur <= 0) {
									throw new Exception('Jumlah retur (Kg) ' . $nama_material . ' harus lebih dari 0');
								}
								if ($qty_receive > 0 && $jumlah_retur > $qty_receive) {
									throw new Exception('Jumlah retur (Kg) ' . $nama_material . ' melebihi qty receive');
								}
								$grand_total_item = $jumlah_retur * $harga_satuan;
							}

							$arr_insert_detail[] = [
								'id_header' => $no_surat,
								'id_detail_po' => $item_detail['id'],
								'no_po' => $item_detail['no_po'],
								'id_pr' => $item_detail['id_pr'],
								'id_material' => $item_detail['idmaterial'],
								'lotno' => isset($item_detail['lotno']) ? $item_detail['lotno'] : NULL,
								'nama_material' => $nama_material,
								'width' => $item_detail['width'],
								'qty_order' => $item_detail['qty_order'],
								'qty_receive' => $qty_receive,
								'qty_sheet' => $qty_sheet,
								'jumlah_retur' => $jumlah_retur,
								'qty_sheet_retur' => $qty_sheet_retur,
								'harga_satuan' => $harga_satuan,
								'grand_total' => $grand_total_item,
								'matauang' => (!empty($item_detail['matauang'])) ? $item_detail['matauang'] : ($this->input->post('matauang', true) ? $this->input->post('matauang', true) : 'IDR'),
								'input_by' => $this->auth->user_id(),
								'input_date' => date('Y-m-d H:i:s')
							];
						}
					}
				}
			} else {
				throw new Exception('Maaf, data barang yang di akan di retur tidak sesuai !');
			}

			$subtotal = isset($_POST['subtotal']) ? (float) str_replace(',', '', $_POST['subtotal']) : 0;
			$ppn_persen = isset($_POST['ppn_persen']) ? (float) str_replace(',', '', $_POST['ppn_persen']) : 0;
			$nilai_ppn = isset($_POST['nilai_ppn']) ? (float) str_replace(',', '', $_POST['nilai_ppn']) : 0;
			$grand_total = isset($_POST['grand_total']) ? (float) str_replace(',', '', $_POST['grand_total']) : 0;

			if ($subtotal <= 0 && !empty($arr_insert_detail)) {
				$subtotal = array_sum(array_column($arr_insert_detail, 'grand_total'));
				$nilai_ppn = ($subtotal * $ppn_persen) / 100;
				$grand_total = $subtotal + $nilai_ppn;
			}

			$matauang = $this->input->post('matauang', true);
			if (empty($matauang) && !empty($arr_insert_detail[0]['matauang'])) {
				$matauang = $arr_insert_detail[0]['matauang'];
			}
			if (empty($matauang)) {
				$matauang = 'IDR';
			}

			$arr_insert_header = [
				'tgl_retur' => $this->input->post('tanggal_retur', true),
				'no_ng_report' => $this->input->post('no_ng_report', true),
				'alasan_retur' => $this->input->post('alasan_retur', true),
				'no_ref_invoice' => $no_ref_invoice,
				'tgl_invoice' => $this->input->post('tanggal_invoice', true),
				'matauang' => $matauang,
				'subtotal' => $subtotal,
				'ppn_persen' => $ppn_persen,
				'nilai_ppn' => $nilai_ppn,
				'grand_total' => $grand_total,
				'updated_by' => $this->auth->user_id(),
				'updated_date' => date('Y-m-d H:i:s')
			];
			if (!empty($filenames)) {
				$arr_insert_header['file_ba'] = 'assets/file_ba/' . $filenames;
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

		$this->db->select('a.*, ria.no_invoice');
		$this->db->from('tr_retur_pembelian a');
		$this->db->join('tr_receive_invoice_ap_header ria', 'ria.id_rec_inv_ap = a.id_rec_inv_ap', 'left');
		$this->db->where('a.deleted_by IS NULL');

		$count_all = $this->db->count_all_results('', false);

		if (!empty($search)) {
			$this->db->group_start();
			$this->db->like('a.no_surat', $search['value'], 'both');
			$this->db->or_like('a.nm_supplier', $search['value'], 'both');
			$this->db->or_like('DATE_FORMAT(a.tgl_retur, "%d %M %Y")', $search['value'], 'both');
			$this->db->or_like('a.no_ref_invoice', $search['value'], 'both');
			$this->db->or_like('a.id_rec_inv_ap', $search['value'], 'both');
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

			if (!empty($item->id_rec_inv_ap)) {
				$no_surat_po = $item->no_invoice;
			} else {
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

					if ($get_no_po) {
						$no_surat_po[] = $get_no_po->no_surat;
					}
				}

				$no_surat_po = implode(', ', $no_surat_po);
			}

			$status = $this->_render_dn_status($item);
			$action = $this->_render_action_retur_pembelian($item);

			$arr_data[] = [
				'no' => $no,
				'no_retur' => $item->no_surat,
				'no_po' => $no_surat_po,
				'nama_supplier' => $item->nm_supplier,
				'tanggal_retur' => date('d F Y', strtotime($item->tgl_retur)),
				'no_ref_invoice' => (!empty($item->id_rec_inv_ap) ? $item->id_rec_inv_ap : $item->no_ref_invoice),
				'tanggal_invoice' => date('d F Y', strtotime($item->tgl_invoice)),
				'status' => $status,
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
		$get_dn = $this->db->get_where('tr_dn_retur_pmb', ['id_retur' => $item->id])->result();

		$btn_view = '';
		if (has_permission($this->viewPermission)) {
			$btn_view = '<a href="' . base_url('retur_pembelian/view_retur/' . $item->id) . '" class="btn btn-sm btn-info" data-toggle="tooltip" title="Lihat Detail"><i class="fa fa-eye"></i></a>';
		}

		$btn_edit = '';
		if (has_permission($this->deletePermission) && $item->deleted_by == null && count($get_dn) < 1) {
			$btn_edit = '<a href="' . base_url('retur_pembelian/edit_retur/' . $item->id) . '" class="btn btn-sm btn-warning" data-toggle="tooltip" title="Edit Retur"><i class="fa fa-pencil"></i></a>';
		}

		$btn_delete = '';
		if (has_permission($this->deletePermission) && $item->deleted_by == null && count($get_dn) < 1) {
			$btn_delete = '<button type="button" class="btn btn-sm btn-danger del_retur" data-toggle="tooltip" title="Hapus Retur" data-id="' . $item->id . '"><i class="fa fa-trash"></i></button>';
		}

		$action = $btn_view . ' ' . $btn_edit . ' ' . $btn_delete;

		return $action;
	}

	public function _render_dn_status($item)
	{
		$get_dn = $this->db->get_where('tr_dn_retur_pmb', ['id_retur' => $item->id])->result();

		$status = '<span class="badge bg-yellow badge-status"><i class="fa fa-clock-o"></i> Waiting DN</span>';
		if (count($get_dn) > 0) {
			$status = '<span class="badge bg-green badge-status"><i class="fa fa-check"></i> DN Created</span>';
		}

		return $status;
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

	public function view_retur($id)
	{
		$retur_header = $this->Retur_pembelian_model->get_retur_header($id);
		$retur_detail = $this->Retur_pembelian_model->get_retur_detail($retur_header->no_surat);
		$get_supplier = $this->Retur_pembelian_model->get_supplier();

		$arr_detail = [];
		foreach ($retur_detail as $item_detail) {
			$arr_detail[$item_detail->id_detail_po] = $item_detail;
		}

		$data = [
			'header' => $retur_header,
			'detail' => $retur_detail,
			'list_supplier' => $get_supplier,
			'arr_detail' => $arr_detail,
			'id_rec_inv_ap' => $retur_header->id_rec_inv_ap
		];

		// Requirement 5.1: if id_rec_inv_ap is set, fetch no_invoice from tr_receive_invoice_ap_header
		if (!empty($retur_header->id_rec_inv_ap)) {
			$rec_inv_ap = $this->db->get_where('tr_receive_invoice_ap_header', ['id_rec_inv_ap' => $retur_header->id_rec_inv_ap])->row();
			$data['no_invoice_rec_inv_ap'] = (!empty($rec_inv_ap)) ? $rec_inv_ap->no_invoice : '';
		}

		$this->template->title('View Retur');
		$this->template->set($data);
		$this->template->render('view_retur');
	}

	public function edit_retur($id)
	{
		$retur_header = $this->Retur_pembelian_model->get_retur_header($id);
		$retur_detail = $this->Retur_pembelian_model->get_retur_detail($retur_header->no_surat);
		$get_supplier = $this->Retur_pembelian_model->get_supplier();

		$arr_detail = [];
		foreach ($retur_detail as $item_detail) {
			$arr_detail[$item_detail->id_detail_po] = $item_detail;
		}

		$data = [
			'header' => $retur_header,
			'detail' => $retur_detail,
			'list_supplier' => $get_supplier,
			'arr_detail' => $arr_detail,
			'id_rec_inv_ap' => $retur_header->id_rec_inv_ap
		];

		$this->template->title('Edit Retur');
		$this->template->set($data);
		$this->template->render('edit_retur');
	}
}
