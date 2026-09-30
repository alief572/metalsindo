<?php
$ENABLE_ADD     = has_permission('Incoming.Add');
$ENABLE_MANAGE  = has_permission('Incoming.Manage');
$ENABLE_VIEW    = has_permission('Incoming.View');
$ENABLE_DELETE  = has_permission('Incoming.Delete');

?>
<link rel="stylesheet" href="<?= base_url('assets/plugins/select2/select2.min.css') ?>">
<style type="text/css">
	thead input {
		width: 100%;
	}

	.incoming-filter {
		background: #f7f7f7;
		border: 1px solid #e5e5e5;
		margin-bottom: 20px;
		padding: 15px 15px 5px;
	}

	.incoming-filter .form-group {
		margin-bottom: 10px;
	}

	.incoming-filter-actions {
		display: flex;
		align-items: center;
		white-space: nowrap;
		padding-top: 25px;
	}

	.incoming-filter-actions .btn + .btn {
		margin-left: 4px;
	}

	.incoming-filter .select2-container {
		width: 100% !important;
	}

	.incoming-filter .select2-container .select2-selection--single {
		height: 34px;
		border-color: #d2d6de;
		border-radius: 0;
	}

	.incoming-filter .select2-container .select2-selection--single .select2-selection__rendered {
		line-height: 32px;
	}

	.incoming-filter .select2-container .select2-selection--single .select2-selection__arrow {
		height: 32px;
	}
</style>
<div id='alert_edit' class="alert alert-success alert-dismissable" style="padding: 15px; display: none;"></div>
<link rel="stylesheet" href="https://cdn.datatables.net/2.2.2/css/dataTables.dataTables.min.css">

<div class="box">
	<div class="box-header">
		<span class="pull-left">
			<?php if ($ENABLE_ADD) : ?>
				<a class="btn btn-success btn-sm" href="<?= base_url('/incoming/add/' . $record->no_penawaran) ?>" title="Add"><i class="fa fa-plus"></i>&nbsp;Create</a>
			<?php endif; ?>
		</span>
	</div>
	<!-- /.box-header -->
	<!-- /.box-header -->
	<div class="box-body">
		<div class="incoming-filter">
			<div class="row">
				<div class="col-md-3">
					<div class="form-group">
						<label for="filter_no_dokumen">No. Dokumen</label>
						<select class="form-control incoming-select2" id="filter_no_dokumen">
							<option value="">Semua No. Dokumen</option>
							<?php foreach ($incoming_documents as $document) : ?>
								<option value="<?= html_escape($document->id_incoming) ?>"><?= html_escape($document->id_incoming) ?></option>
							<?php endforeach; ?>
						</select>
					</div>
				</div>
				<div class="col-md-3">
					<div class="form-group">
						<label for="filter_supplier">Supplier</label>
						<select class="form-control incoming-select2" id="filter_supplier">
							<option value="">Semua Supplier</option>
							<?php foreach ($suppliers as $supplier) : ?>
								<option value="<?= html_escape($supplier->id_suplier) ?>"><?= html_escape($supplier->name_suplier) ?></option>
							<?php endforeach; ?>
						</select>
					</div>
				</div>
				<div class="col-md-2">
					<div class="form-group">
						<label for="filter_tanggal_awal">Tanggal Awal</label>
						<input type="date" class="form-control" id="filter_tanggal_awal">
					</div>
				</div>
				<div class="col-md-2">
					<div class="form-group">
						<label for="filter_tanggal_akhir">Tanggal Akhir</label>
						<input type="date" class="form-control" id="filter_tanggal_akhir">
					</div>
				</div>
				<div class="col-md-2 incoming-filter-actions">
					<button type="button" class="btn btn-primary btn-sm" id="btn_search" title="Search">
						<i class="fa fa-search"></i> Search
					</button>
					<button type="button" class="btn btn-default btn-sm" id="btn_reset" title="Reset">
						<i class="fa fa-refresh"></i> Reset
					</button>
					<button type="button" class="btn btn-success btn-sm" id="btn_export_excel" title="Export Excel">
						<i class="fa fa-file-excel-o"></i> Export Excel
					</button>
				</div>
			</div>
		</div>
		<table id="example5" class="table table-bordered table-striped">
			<thead>
				<tr>
					<th>#</th>
					<th>No.Dokumen</th>
					<th>Supplier</th>
					<th>Tanggal</th>
					<th>PIC</th>
					<th>Keterangan</th>
					<th>Tgl Input</th>
					<th width="13%">Action</th>
				</tr>
			</thead>

			<tbody>
				
			</tbody>
		</table>
	</div>
	<!-- /.box-body -->
</div>

<!-- awal untuk modal dialog -->
<!-- Modal -->
<div class="modal modal-primary" id="dialog-rekap" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
				<h4 class="modal-title" id="myModalLabel"><span class="fa fa-file-pdf-o"></span>&nbsp;Rekap Data Customer</h4>
			</div>
			<div class="modal-body" id="MyModalBody">
				...
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">
					<span class="glyphicon glyphicon-remove"></span> Close</button>
			</div>
		</div>
	</div>
</div>

<div class="modal modal-default fade" id="dialog-popup" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-lg" style='width: 90%;'>
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
				<h4 class="modal-title" id="myModalLabel">DETAIL INCOMING</h4>
			</div>
			<div class="modal-body" id="ModalView">
				...
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-danger" data-dismiss="modal">
					<span class="glyphicon glyphicon-remove"></span> Close</button>
			</div>
		</div>
	</div>
</div>

<!-- DataTables -->
<script src="https://cdn.datatables.net/2.2.2/js/dataTables.min.js"></script>
<script src="<?= base_url('assets/plugins/select2/select2.full.min.js') ?>"></script>

<!-- page script -->
<script type="text/javascript">
	var incomingTable;

	function getIncomingFilters() {
		return {
			no_dokumen: $.trim($('#filter_no_dokumen').val()),
			id_supplier: $('#filter_supplier').val(),
			tanggal_awal: $('#filter_tanggal_awal').val(),
			tanggal_akhir: $('#filter_tanggal_akhir').val()
		};
	}

	function validateIncomingDateRange(filters) {
		if (filters.tanggal_awal && filters.tanggal_akhir && filters.tanggal_awal > filters.tanggal_akhir) {
			swal({
				title: 'Range tanggal tidak valid',
				text: 'Tanggal awal tidak boleh lebih besar dari tanggal akhir.',
				type: 'warning'
			});
			return false;
		}

		return true;
	}

	$(document).on('click', '.edit', function(e) {
		var id = $(this).data('no_penawaran');
		$("#head_title").html("<i class='fa fa-list-alt'></i><b>Edit Inventory</b>");
		$.ajax({
			type: 'POST',
			url: siteurl + 'penawaran/EditHeader/' + id,
			success: function(data) {
				$("#dialog-popup").modal();
				$("#ModalView").html(data);

			}
		})
	});

	$(document).on('click', '.cetak', function(e) {
		var id = $(this).data('no_penawaran');
		$("#head_title").html("<i class='fa fa-list-alt'></i><b>Edit Inventory</b>");
		$.ajax({
			type: 'POST',
			url: siteurl + 'xtes/cetak' + id,
			success: function(data) {

			}
		})
	});

	$(document).on('click', '.view', function() {
		var id = $(this).data('id_data');
		// alert(id);
		$("#head_title").html("<i class='fa fa-list-alt'></i><b>Detail Inventory</b>");
		$.ajax({
			type: 'POST',
			url: siteurl + 'incoming/Lihat/' + id,
			data: {
				'id': id
			},
			success: function(data) {
				$("#dialog-popup").modal();
				$("#ModalView").html(data);

			}
		})
	});
	$(document).on('click', '.add', function() {
		$("#head_title").html("<i class='fa fa-list-alt'></i><b>Tambah Inventory</b>");
		$.ajax({
			type: 'POST',
			url: siteurl + 'penawaran/addHeader',
			success: function(data) {
				$("#dialog-popup").modal();
				$("#ModalView").html(data);

			}
		})
	});


	// DELETE DATA
	$(document).on('click', '.Approve', function(e) {
		e.preventDefault()
		var id = $(this).data('no_po');
		// alert(id);
		swal({
				title: "Anda Yakin?",
				text: "P.R. Akan Dihapus.",
				type: "warning",
				showCancelButton: true,
				confirmButtonClass: "btn-info",
				confirmButtonText: "Ya, Approve!",
				cancelButtonText: "Batal",
				closeOnConfirm: false
			},
			function() {
				$.ajax({
					type: 'POST',
					url: siteurl + 'purchase_order/Approved',
					dataType: "json",
					data: {
						'id': id
					},
					success: function(result) {
						if (result.status == '1') {
							swal({
									title: "Sukses",
									text: "P.R Approved.",
									type: "success"
								},
								function() {
									window.location.reload(true);
								})
						} else {
							swal({
								title: "Error",
								text: "Data error. Gagal Approve data",
								type: "error"
							})

						}
					},
					error: function() {
						swal({
							title: "Error",
							text: "Data error. Gagal request Ajax",
							type: "error"
						})
					}
				})
			});

	})

	$(function() {
		$('.incoming-select2').select2({
			width: '100%'
		});

		DataTables();
		$("#form-area").hide();

		$('#btn_search').on('click', function() {
			var filters = getIncomingFilters();
			if (validateIncomingDateRange(filters)) {
				incomingTable.ajax.reload(null, true);
			}
		});

		$('#btn_reset').on('click', function() {
			$('#filter_no_dokumen').val('').trigger('change');
			$('#filter_supplier').val('').trigger('change');
			$('#filter_tanggal_awal').val('');
			$('#filter_tanggal_akhir').val('');
			incomingTable.ajax.reload(null, true);
		});

		$('#btn_export_excel').on('click', function() {
			var filters = getIncomingFilters();
			if (validateIncomingDateRange(filters)) {
				window.location.href = siteurl + active_controller + 'export_excel?' + $.param(filters);
			}
		});

	});

	function DataTables() {
		incomingTable = $('#example5').DataTable({
			serverSide: true,
			processing: true,
			destroy: true,
			searching: false,
			language: {
				loadingRecords: 'Loading - Please Wait...'
			},
			ajax: {
				url: siteurl + active_controller + 'get_incoming',
				type: 'post',
				dataType: 'json',
				data: function(data) {
					return $.extend(data, getIncomingFilters());
				}
			},
			columns: [
				{
					data: 'no'
				},
				{
					data: 'no_dokumen'
				},
				{
					data: 'suplier'
				},
				{
					data: 'tanggal'
				},
				{
					data: 'pic'
				},
				{
					data: 'keterangan'
				},
				{
					data: 'tgl_input'
				},
				{
					data: 'action'
				}
			]
		});
	}


	//Delete

	function PreviewPdf(id) {
		param = id;
		tujuan = 'customer/print_request/' + param;

		$(".modal-body").html('<iframe src="' + tujuan + '" frameborder="no" width="570" height="400"></iframe>');
	}

	function PreviewRekap() {
		tujuan = 'customer/rekap_pdf';
		$(".modal-body").html('<iframe src="' + tujuan + '" frameborder="no" width="100%" height="400"></iframe>');
	}
</script>
