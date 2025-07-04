<?php
$ENABLE_ADD     = has_permission('Request_Payment.Add');
$ENABLE_MANAGE  = has_permission('Request_Payment.Manage');
$ENABLE_DELETE  = has_permission('Request_Payment.Delete');
$ENABLE_VIEW    = has_permission('Request_Payment.View');
?>

<style>
	.table-container {
		max-height: 500px;
		/* Example height for the table container (adjust as needed) */
		overflow-y: auto;
		/* Enable vertical scrolling */
	}

	/* Style for the table */
	.table-container table {
		width: 100%;
		border-collapse: collapse !important;
	}

	/* Style for the table header */

	/* Style for table cells */
	.table-container th,
	.table-container td {
		padding: 8px;
		border: 1px solid #ddd;
		text-align: left;
	}

	.sticky-header th {
		position: sticky !important;
		top: 0 !important;
		/* Stick to the top of the container */
		z-index: 1;
		/* Ensure it appears above tbody content */
		background-color: #3c8dbc;
		/* Header background color */
		color: white;
		font-weight: bold;
	}
</style>
<!-- <script src="//cdn.rawgit.com/rainabba/jquery-table2excel/1.1.0/dist/jquery.table2excel.min.js"></script> -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/chosen/1.8.7/chosen.min.css" integrity="sha512-yVvxUQV0QESBt1SyZbNJMAwyKvFTLMyXSyBHDO4BG5t7k/Lw34tyqlSDlKIrIENIzCl+RVUNjmCPG+V/GMesRw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
<link rel="stylesheet" href="https://cdn.datatables.net/2.1.8/css/dataTables.dataTables.min.css">

<div id="alert_edit" class="alert alert-success alert-dismissable" style="padding: 15px; display: none;"></div>

<form action="<?= $this->uri->uri_string() ?>" id="frm_data" name="frm_data" class="form-horizontal" enctype="multipart/form-data">
	<div class="box">
		<div class="box-body">
			<div class="col-md-12">

			</div>
			<input type="hidden" name="" class="actived_tab" value="transport">
			<ul class="nav nav-tabs" role="tablist">
				<li role="presentation" class="transport_tab tab_pin active"><a href="javascript:void();" onclick="change_tab('transport')">Transportasi</a></li>
				<li role="presentation" class="kasbon_tab tab_pin"><a href="javascript:void();" onclick="change_tab('kasbon')">Kasbon</a></li>
				<li role="presentation" class="expense_tab tab_pin"><a href="javascript:void();" onclick="change_tab('expense')">Expense</a></li>
				<li role="presentation" class="periodik_tab tab_pin"><a href="javascript:void();" onclick="change_tab('periodik')">Periodik</a></li>
				<li role="presentation" class="pembayaran_po_tab tab_pin"><a href="javascript:void();" onclick="change_tab('pembayaran_po')">PO Non Material</a></li>
				<li role="presentation" class="po_material_tab tab_pin"><a href="javascript:void();" onclick="change_tab('po_material')">PO Material</a></li>
			</ul>
			<div class="table-container col-md-12" style="margin-top: 10px;">
				<table id="example1" class="table table-bordered">
					<thead class="sticky-header">
						<tr>
							<th>#</th>
							<th>No.</th>
							<th style="min-width: 150px;">No. Invoice / No. Dokumen</th>
							<th style="min-width: 150px;">Supplier</th>
							<th style="min-width: 100px;">Tanggal</th>
							<th style="min-width: 150px;">Keperluan</th>
							<th>Currency</th>
							<th>Nilai Pengajuan</th>
							<th>Status</th>
							<th style="min-width: 360px;"></th>
						</tr>
					</thead>
					<tbody class="list_req_payment">
						<?php
						if (!empty($data)) {
							$hasil = '';
							$numb = 1;
							foreach ($data as $record) {

								$sts = '<div class="badge bg-blue">Open</div>';
								if ($record->sts_reject == '1') {
									$sts = '<div class="badge bg-red">Rejected by Checker</div>';
								}
								if ($record->sts_reject_manage == '1') {
									$sts = '<div class="badge bg-red">Rejected by Management</div>';
								}

								$reject_reason = '';
								if ($record->sts_reject == '1' || $record->sts_reject_manage == '1') {
									$reject_reason = $record->reject_reason;
								}

								$no_invoice = (isset($list_no_invoice[$record->no_doc])) ? $list_no_invoice[$record->no_doc] : '';

								$tipe = $record->tipe;

								$currency = '';
								if ($record->tipe == 'expense') {
									$get_expense = $this->db->get_where('tr_expense', ['no_doc' => $record->no_doc])->row_array();
									if ($get_expense['exp_inv_po'] == '1') {
										$tipe = 'Pembayaran PO';

										$get_inv = $this->db->get_where('tr_invoice_po', ['id' => $record->no_doc])->row_array();
										$currency = $get_inv['curr'];
									}
								}

								$nm_supplier = '';

								// $get_ros = $this->db->select('a.nm_supplier')->get_where('tr_ros a', ['a.id' => $record->no_doc])->row();
								// if (!empty($get_ros)) {
								// 	$nm_supplier = $get_ros->nm_supplier;
								// }

								$get_invoice = $this->db->select('a.no_po')
									->from('tr_invoice_po a')
									->where('a.id', $record->no_doc)
									->get()
									->row();
								if ($nm_supplier == '' && !empty($get_invoice)) {
									$nm_supplier = [];
									$no_po = str_replace(', ', ',', $get_invoice->no_po);

									if (strpos($no_po, 'TR') !== false) {
										$get_supplier = $this->db->query("
											SELECT
												c.name_suplier as nm_supplier
											FROM
												warehouse_adjustment a
												LEFT JOIN tr_purchase_order_non_material b ON b.no_po = a.no_ipp
												LEFT JOIN master_supplier c ON c.id_suplier = b.id_suplier
											WHERE
												a.kode_trans IN ('" . str_replace(",", "','", $no_po) . "')
											GROUP BY c.name_suplier
										")->result();
										foreach ($get_supplier as $item_supplier) {
											$nm_supplier[] = $item_supplier->nm_supplier;
										}
									} else {
										$get_supplier = $this->db->query("
											SELECT
												b.name_suplier as nm_supplier
											FROM
												tr_purchase_order_non_material a
												LEFT JOIN master_supplier b ON b.id_suplier = a.id_suplier
											WHERE
												a.no_surat IN ('" . str_replace(",", "','", $no_po) . "')
											GROUP BY b.name_suplier
										")->result();
										foreach ($get_supplier as $item_supplier) {
											$nm_supplier[] = $item_supplier->nm_supplier;
										}
									}
									$nm_supplier = implode(',', $nm_supplier);
								}

								$valid = 1;

								if ($valid == 1) {
									$hasil .= '<tr>';
									$hasil .= '<td class="exclass">';
									if ($ENABLE_MANAGE) {
										$hasil .= '<input type="hidden" name="no_doc_' . $numb . '" id="no_doc_' . $numb . '" value="' . $record->no_doc . '">';
										$hasil .= '<input type="hidden" name="nama_' . $numb . '" id="nama_' . $numb . '" value="' . $record->nama . '">';
										$hasil .= '<input type="hidden" name="tgl_doc_' . $numb . '" id="tgl_doc_' . $numb . '" value="' . $record->tgl_doc . '">';
										$hasil .= '<input type="hidden" name="keperluan_' . $numb . '" id="keperluan_' . $numb . '" value="' . $record->keperluan . '">';
										$hasil .= '<input type="hidden" name="tipe_' . $numb . '" id="tipe_' . $numb . '" value="' . $record->tipe . '">';
										$hasil .= '<input type="hidden" name="jumlah_' . $numb . '" id="jumlah_' . $numb . '" value="' . $record->jumlah . '">';
										$hasil .= '<input type="hidden" name="bank_id_' . $numb . '" id="bank_id_' . $numb . '" value="' . $record->bank_id . '">';
										$hasil .= '<input type="hidden" name="accnumber_' . $numb . '" id="accnumber_' . $numb . '" value="' . $record->accnumber . '">';
										$hasil .= '<input type="hidden" name="accname_' . $numb . '" id="accname_' . $numb . '" value="' . $record->accname . '">';
										$hasil .= '<input type="hidden" name="ids_' . $numb . '" id="ids_' . $numb . '" value="' . $record->ids . '">';
										$hasil .= '<input type="checkbox" name="status[]" id="status_' . $numb . '" value="' . $numb . '" class="dtlloop" onclick="cektotal()">';
									}
									if ($record->tipe == 'kasbon') {
										$hasil .= '<a href="' . base_url("expense/kasbon_view/" . $record->ids) . '" target="_blank"><i class="fa fa-search pull-right"></i></a>';
									}
									if ($record->tipe == 'transportasi') {
										$hasil .= '<a href="' . base_url('expense/transport_req_view/' . $record->ids) . '" target="_blank"><i class="fa fa-search pull-right"></i></a>';
									}
									if ($record->tipe == 'expense') {
										$get_expense = $this->db->get_where('tr_expense', ['id' => $record->ids])->row_array();
										if ($get_expense['exp_pib'] == '1') {
											$hasil .= '<a href="' . base_url('ros/view/' . $record->no_doc) . '" target="_blank"><i class="fa fa-search pull-right"></i></a>';
										} else if ($get_expense['exp_inv_po'] == '1') {
											$hasil .= '';
										} else {
											$hasil .= '<a href="' . base_url('expense/view/' . $record->ids) . '" target="_blank"><i class="fa fa-search pull-right"></i></a>';
										}
									}
									if ($record->tipe == 'nonpo') {
										$hasil .= '<a href="' . base_url('purchase_order/non_po/view/' . $record->ids) . '" target="_blank"><i class="fa fa-search pull-right"></i></a>';
									}
									if ($record->tipe == 'periodiks') {
										$hasil .= '<a href="' . base_url('pembayaran_rutin/view/' . $record->ids) . '" target="_blank"><i class="fa fa-search pull-right"></i></a>';
									}

									$curr = '';
									$get_curr = $this->db->get_where('tr_invoice_po', ['id' => $record->no_doc])->row();
									if (!empty($get_curr)) {
										$curr = $get_curr->curr;
									}

									$hasil .= '</td>';
									$hasil .= '<td class="">' . $numb . '</td>';
									$hasil .= '<td>' . $record->no_doc . '</td>';
									$hasil .= '<td>' . $nm_supplier . '</td>';
									$hasil .= '<td>' . $record->tgl_doc . '</td>';
									$hasil .= '<td>' . $record->keperluan . '</td>';
									$hasil .= '<td>';
									$hasil .= '<select name="currency_' . $numb . '" id="" class="form-control form-control-sm select2">';
									$hasil .= '<option value="">- Currency -</option>';
									foreach ($list_curr as $item_curr) {
										$selected = '';
										if ($item_curr['kode'] == $curr) {
											$selected = 'selected';
										}
										$hasil .= '<option value="' . $item_curr['kode'] . '" ' . $selected . '>' . $item_curr['kode'] . '</option>';
									}
									$hasil .= '</select>';
									$hasil .= '</td>';
									$hasil .= '<td>' . number_format($record->jumlah) . '</td>';
									$hasil .= '<td>' . $sts . '</td>';
									$hasil .= '<td>';
									$hasil .= '
									<table class="w-100" border="0" style="border: 0px !important;">
										<tr>
											<td>Nilai Pengajuan</td>
											<td class="text-center">:</td>
											<td>
												<input type="text" name="" id="" class="form-control form-control-sm text-right nilai_pengajuan_' . $numb . '" value="' . number_format($record->jumlah) . '" readonly>
											</td>
										</tr>
										<tr>
											<td>
												<select name="tipe_pph_' . $numb . '" id="" class="form-control form-control-sm select_pph_' . $numb . '">
													<option value="">- Select PPh -</option>
													<option value="1">PPh 21</option>
												</select>
											</td>
											<td class="text-center">:</td>
											<td>
												<input type="text" name="nilai_pph_' . $numb . '" id="" class="form-control form-control-sm text-right divide nilai_pph_' . $numb . '">
											</td>
										</tr>
										<tr>
											<td>Admin Charge</td>
											<td class="text-center">:</td>
											<td>
												<input type="text" name="admin_charge_' . $numb . '" id="" class="form-control form-control-sm text-right admin_charge_' . $numb . ' divide" onchange="hitung_net_payment(' . $numb . ')">
											</td>
										</tr>
										<tr>
											<td>Net Payment</td>
											<td class="text-center">:</td>
											<td>
												<input type="text" name="" id="" class="form-control form-control-sm text-right net_payment_' . $numb . '" onchange="hitung_net_payment(' . $numb . ')" readonly>
											</td>
										</tr>
									
										<tr>
											<td>Bank Pengirim</td>
											<td>:</td>
											<td>
												<select name="bank_' . $numb . '" id="" class="form-control form-control-sm select2">
													<option value="">- Bank -</option>
												';

									foreach ($list_coa as $item_coa) {
										$hasil .= '<option value="' . $item_coa['no_perkiraan'] . ' - ' . $item_coa['nama'] . '">' . $item_coa['no_perkiraan'] . ' - ' . $item_coa['nama'] . '</option>';
									}

									$hasil .= '
												</select>
											</td>
										</tr>
										<tr>
											<td>Tanggal Rencana Pembayaran</td>
											<td>:</td>
											<td>
												<input type="text" class="form-control tanggal" id="tanggal_' . $numb . '" name="tanggal_' . $numb . '" value="" placeholder="Tanggal">
											</td>
										</tr>
										<tr>
											<td>Upload Dokumen</td>
											<td>:</td>
											<td>
												<input type="file" name="upload_doc_' . $numb . '" id="" class="form-control form-control-sm">
											</td>
										</tr>
									</table>
									';
									$hasil .= '</td>';
									$hasil .= '</tr>';

									$numb++;
								}

								echo $hasil;
							}
						}  ?>
					</tbody>
					<tbody>
						<tr class="exclass">
							<td colspan="8" align=right>Total</td>
							<td colspan="2"><input type="text" class="form-control divide input-sm text-right" name="total_req" id="total_req" value="0" readonly></td>
						</tr>
					</tbody>
				</table>
				<div class="pull-right">
					<!-- <button type="button" id="btnxls" class="btn btn-default">Export Excel</button>  -->
					<button type="submit" name="save" class="btn btn-success btn-sm" id="submit"><i class="fa fa-save">&nbsp;</i>Update</button>
				</div>
			</div>
		</div>
		<!-- /.box-body -->
	</div>
</form>
<script src="https://cdn.datatables.net/2.1.8/js/dataTables.min.js"></script>
<script src="<?= base_url('assets/js/autoNumeric.js') ?>"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/chosen/1.8.7/chosen.jquery.min.js" integrity="sha512-rMGGF4wg1R73ehtnxXBt5mbUfN9JUJwbk21KMlnLZDJh7BkPmeovBuddZCENJddHYYMkCh9hPFnPmS9sspki8g==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>

<script type="text/javascript">
	load_all_party();

	$(document).ready(function() {
		$('#example1').DataTable();
	});

	function load_all_party() {
		$(".divide").autoNumeric('init');
		$(".select2").select2({
			width: '100%'
		});
		$('.vendor').chosen();
		$('.tipe').chosen();

		$(".tanggal").datepicker({
			todayHighlight: true,
			format: "yyyy-mm-dd",
			showInputs: true,
			autoclose: true
		});
	}

	function cektotal() {
		var total_req = 0;
		$('.dtlloop').each(function() {
			if (this.checked) {
				var ids = $(this).val();
				total_req += Number($("#jumlah_" + ids).val());
			}
		});
		$("#total_req").autoNumeric('set', total_req);
	}
	var url_save = siteurl + 'request_payment/save_request/';


	function change_tab(tab) {
		$('.tab_pin').removeClass('active');

		$('.' + tab + '_tab').addClass('active');
		$('.actived_tab').val(tab);

		$.ajax({
			type: 'POST',
			url: siteurl + active_controller + 'change_tab',
			data: {
				'tab': tab
			},
			cache: false,
			success: function(result) {
				$('.list_req_payment').html(result);
				load_all_party();
			},
			error: function() {
				swal({
					title: 'Error !',
					text: 'Please try again later !',
					type: 'error'
				});
			}
		});
	}

	function hitung_net_payment(no) {
		var nilai_pengajuan = $('.nilai_pengajuan_' + no).val();
		if (nilai_pengajuan !== '') {
			nilai_pengajuan = nilai_pengajuan.split(',').join('');
			nilai_pengajuan = parseFloat(nilai_pengajuan);
		}

		var admin_charge = $('.admin_charge_' + no).val();
		if (admin_charge !== '') {
			admin_charge = admin_charge.split(',').join('');
			admin_charge = parseFloat(admin_charge);
		}

		var nilai_pph = $('.nilai_pph_' + no).val();
		if (nilai_pph !== '') {
			nilai_pph = nilai_pph.split(',').join('');
			nilai_pph = parseFloat(nilai_pph);
		}

		var net_payment = (nilai_pengajuan + admin_charge - nilai_pph);

		$('.net_payment_' + no).val(net_payment.toLocaleString());
	}

	function hitung_total(no_doc, ids, no) {

	}

	$(document).on('click', '.search', function() {
		var from_date = $('.from_date').val();
		var to_date = $('.to_date').val();
		var vendor = $('.vendor').val();
		var actived_tab = $('.actived_tab').val();

		if (from_date == '' || to_date == '') {
			swal({
				title: 'Warning !',
				text: 'Please make sure from and to date is filled !',
				type: 'error'
			});
		} else {
			$.ajax({
				type: 'POST',
				url: siteurl + active_controller + 'search_req_payment',
				data: {
					'from_date': from_date,
					'to_date': to_date,
					'vendor': vendor,
					'actived_tab': actived_tab
				},
				cache: false,
				dataType: 'json',
				success: function(result) {
					$('.list_req_payment').html(result.hasil);
					$(".divide").autoNumeric();
					$(".select2").select2({
						width: '100%'
					});
				},
				error: function(result) {
					swal({
						title: 'Error !',
						text: 'Please try again later !',
						type: 'error'
					});
				}
			});
		}
	});

	//Save
	$('#frm_data').on('submit', function(e) {
		e.preventDefault();
		var errors = "";

		var checked_item = $('.dtlloop:checked').length;
		if (errors == "" && checked_item > 0) {
			swal({
					title: "Anda Yakin?",
					text: "Data Akan Disimpan!",
					type: "info",
					showCancelButton: true,
					confirmButtonText: "Ya, simpan!",
					cancelButtonText: "Tidak!",
					closeOnConfirm: false,
					closeOnCancel: true
				},
				function(isConfirm) {
					if (isConfirm) {
						var formdata = new FormData($('#frm_data')[0]);
						$.ajax({
							url: url_save,
							dataType: "json",
							type: 'POST',
							data: formdata,
							processData: false,
							contentType: false,
							success: function(msg) {
								if (msg['save'] == '1') {
									swal({
										title: "Sukses!",
										text: "Data Berhasil Di Update",
										type: "success",
										timer: 1500,
										showConfirmButton: false
									});
									window.location.href = window.location.href;
								} else {
									swal({
										title: "Gagal!",
										text: "Data Gagal Di Update",
										type: "error",
										timer: 1500,
										showConfirmButton: false
									});
								};
								console.log(msg);
							},
							error: function(msg) {
								swal({
									title: "Gagal!",
									text: "Ajax Data Gagal Di Proses",
									type: "error",
									timer: 1500,
									showConfirmButton: false
								});
								console.log(msg);
							}
						});
					}
				});
		} else {
			if (checked_item < 1) {
				errors = 'Please check at least 1 data before you update it !';
			}
			swal({
				title: 'Error !',
				text: errors,
				type: 'error'
			});
			return false;
		}
	});
</script>