<?php
$ENABLE_ADD = has_permission('Asset_Depreciations.Add');
$ENABLE_MANAGE = has_permission('Asset_Depreciations.Manage');
$ENABLE_VIEW = has_permission('Asset_Depreciations.View');
$ENABLE_DELETE = has_permission('Asset_Depreciations.Delete');
?>
<style type="text/css">
	thead input {
		width: 100%;
	}
</style>
<link rel="stylesheet" href="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.css'); ?>">
<link href="https://cdnjs.cloudflare.com/ajax/libs/chosen/1.8.7/chosen.min.css" rel="stylesheet" />
<form action="#" method="POST" id="form_proses_bro" enctype="multipart/form-data">
	<div class="box">
		<div class="box-header">
			<div class="box-tool">


				<!-- <button type='button' id='add' class="btn btn-success" title="Tambah Asset"><i class="fa fa-plus">&nbsp;</i>Tambah Asset</button> -->

				<!--<button type=' button' id='jurnal' class="btn btn-primary" title="Buat Jurnal"><i class="fa fa-plus">&nbsp;</i>Buat Jurnal</button>-->

			</div>
			<div class="" style="margin-top: 1rem;">

				<div class="row">
					<div class="col-md-3">
						<select name="" id="" class="form-control form-control-sm chosen-select-sp select_category">
							<option value="0">- Select Category -</option>
							<?php
							foreach ($list_category as $item) {
								echo '<option value="' . strtoupper($item->nm_category) . '">' . strtoupper($item->nm_category) . '</option>';
							}
							?>
						</select>
					</div>
					<div class="col-md-3">
						<select name="" id="" class="form-control form-control-sm chosen-select-sp select_month">
							<option value="">- Select Month -</option>
							<?php
							for ($i = 1; $i <= 12; $i++) {
								$selected = '';
								if (date('F', strtotime('2024-' . sprintf('%02s', $i) . '-01')) == date('F')) {
									$selected = 'selected';
								}
								echo '<option value="' . date('m', strtotime('2024-' . sprintf('%02s', $i) . '-01')) . '" ' . $selected . '>' . date('F', strtotime('2024-' . sprintf('%02s', $i) . '-01')) . '</option>';
							}
							?>
						</select>
					</div>
					<div class="col-md-3">
						<select name="" id="" class="form-control form-control-sm chosen-select-sp select_year">
							<option value="">- Select Year -</option>
							<?php
							foreach ($list_asset_year as $item) {
								$selected = '';
								if ($item->tahun_perolehan == date('Y')) {
									$selected = 'selected';
								}
								echo '<option value="' . $item->tahun_perolehan . '" ' . $selected . '>' . $item->tahun_perolehan . '</option>';
							}
							?>
						</select>
					</div>
					<div class="col-md-3">
						<button type="button" class="btn btn-warning search_dep">
							<i class="fa fa-search"></i> Search
						</button>
						<a href="javascript:void();" class="btn btn-md btn-info download_excel" '>
							<i class="fa fa-file-excel-o"></i> Download ALL
						</a>
					</div>
				</div>



			</div>
		</div>
		<!-- /.box-header -->
		<div class="box-body table-responsive">
			<table id="example1" class="table table-bordered table-striped" width=' 100%'>
							<thead>
								<tr class='bg-blue'>
									<th class="text-center">#</th>
									<th class="text-center" width="200">Kode Asset</th>
									<th class="text-center" width="200">Asset Name</th>
									<th class="text-center" width="100">Tgl Perolehan</th>
									<th class="text-center" width="200">Category</th>
									<th class="text-center" width="200">Kelompok Penyusutan</th>
									<th class="text-center" width="200">Depreciation</th>
									<th class="text-center" width="200">Aquisition</th>
									<th class="text-center" width="200">Depreciation Val</th>
									<th class="text-center" width="200">Akumulasi Depresiasi</th>
									<th class="text-center" width="200">Asset Val</th>
									<th class="text-center">#</th>
								</tr>
							</thead>
							<tbody></tbody>
							</table>
					</div>
					<!-- /.box-body -->
				</div>

				<!-- modal -->
				<div class="modal fade" id="ModalView">
					<div class="modal-dialog" style='width:80%; '>
						<div class="modal-content">
							<div class="modal-header">
								<button type="button" class="close" data-dismiss="modal" aria-label="Close">
									<span aria-hidden="true">&times;</span></button>
								<h4 class="modal-title" id="head_title"></h4>
							</div>
							<div class="modal-body" id="view">
							</div>
							<div class="modal-footer">
								<!--<button type="button" class="btn btn-primary">Save</button>-->
								<button type="button" class="btn btn-default " data-dismiss="modal">Close</button>
							</div>
						</div>
					</div>
				</div>
				<!-- modal -->

				<!-- modal alert -->
				<div class="modal fade" id="myModal" role="dialog">
					<div class="modal-dialog">
						<div class="modal-content" style='margin-top: 150px;'>
							<div class="modal-header">
								<button type="button" class="close" data-dismiss="modal">&times;</button>
								<h4 class="modal-title"><b>Pemberitahuan</b></h4>
							</div>
							<div class="modal-body">
								<p id="error"></p>
							</div>
							<div class="modal-footer">
								<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
							</div>
						</div>
					</div>
				</div>
				<!-- modal alert -->

</form>
<!-- DataTables -->

<style>
	.chosen-container {
		width: 100% !important;
		text-align: left !important;
	}
</style>

<script src="<?= base_url('assets/plugins/datatables/jquery.dataTables.min.js'); ?>"></script>
<script src="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.min.js'); ?>"></script>
<script src="<?= base_url('assets/js/jquery.maskMoney.js') ?>"></script>
<script src="<?= base_url('assets/js/autoNumeric.js') ?>"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/chosen/1.8.7/chosen.jquery.min.js"></script>

<!-- page script -->
<script type="text/javascript">
	$(document).ready(function() {
		var category = $('.select_category').val();
		var bulan = $('.select_month').val();
		var year = $('.select_year').val();
		DataTables(category, bulan, year);
		$('.chosen-select-sp').chosen({
			width: '100%'
		});
	});

	$(document).on('click', '#detail', function(e) {
		e.preventDefault();
		$("#head_title").html("<b>DETAIL ASET</b>");
		$("#view").load(siteurl + 'asset/modal_view/' + $(this).data('id'));
		$("#ModalView").modal();
	});

	$(document).on('click', '.search_dep', function() {
		var category = $('.select_category').val();
		var bulan = $('.select_month').val();
		var year = $('.select_year').val();

		DataTables(category, bulan, year);
	});

	$(document).on('click', '.download_excel', function() {
		var category = $('.select_category').val();
		var bulan = $('.select_month').val();
		var year = $('.select_year').val();

		window.open(siteurl + active_controller + 'download_excel_all_default/' + category + '/' + bulan + '/' + year, '_blank');
	});

	function DataTables(category = null, bulan = null, tahun = null) {
		var dataTables = $('#example1').dataTable({
			ajax: {
				url: siteurl + active_controller + 'get_data_asset',
				type: "POST",
				dataType: "JSON",
				data: function(d) {
					d.category_sp = category;
					d.bulan_sp = bulan;
					d.tahun_sp = tahun
				}
			},
			columns: [{
				data: 'no',
			}, {
				data: 'kode_asset'
			}, {
				data: 'asset_name'
			}, {
				data: 'tgl_perolehan'
			}, {
				data: 'category'
			}, {
				data: 'kelompok_penyusutan'
			}, {
				data: 'depreciation'
			}, {
				data: 'aquisition'
			}, {
				data: 'depreciation_val'
			}, {
				data: 'akumulasi_depresiasi',
			}, {
				data: 'asset_val'
			}, {
				data: 'option'
			}],
			responsive: true,
			processing: true,
			serverSide: true,
			stateSave: true,
			destroy: true,
			paging: true
		});
	}
</script>