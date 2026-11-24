<?php
$ENABLE_ADD     = has_permission('Master_Spesifikasi.Add');
$ENABLE_MANAGE  = has_permission('Master_Spesifikasi.Manage');
$ENABLE_VIEW    = has_permission('Master_Spesifikasi.View');
$ENABLE_DELETE  = has_permission('Master_Spesifikasi.Delete');
?>
<link rel="stylesheet" href="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.css') ?>">
<link rel="stylesheet" href="<?= base_url('assets/plugins/sweetalert/dist/sweetalert.css') ?>">

<div class="box">
	<div class="box-header">
		<?php if ($ENABLE_ADD) : ?>
			<a class="btn btn-success btn-sm add" href="javascript:void(0)" title="Add"><i class="fa fa-plus">&nbsp;</i>Add</a>
		<?php endif; ?>
		<span class="pull-right">
			<label style="font-weight:normal">Filter Type Alloy:
				<select id="filter_alloy" class="form-control input-sm" style="display:inline-block;width:auto">
					<option value="">Semua</option>
					<?php foreach ($alloys as $a) : ?>
						<option value="<?= html_escape($a->nama) ?>"><?= html_escape($a->nama) ?></option>
					<?php endforeach; ?>
				</select>
			</label>
		</span>
	</div>
	<div class="box-body">
		<table id="example1" class="table table-bordered table-striped">
			<thead>
				<tr>
					<th width="5">#</th>
					<th width="14%">Id Spesifikasi</th>
					<th>Type Alloy</th>
					<th>Nama Spesifikasi</th>
					<th>Dipakai (material)</th>
					<th>Status</th>
					<th width="15%">Action</th>
				</tr>
			</thead>
			<tbody>
				<?php $numb = 0;
				foreach ((array) $results as $r) : $numb++; ?>
					<tr>
						<td><?= $numb ?></td>
						<td><?= html_escape($r->id_spesifikasi) ?></td>
						<td><?= html_escape($r->nama_alloy) ?></td>
						<td><?= html_escape($r->nama) ?></td>
						<td><?= (int) $r->dipakai ?></td>
						<td><?= $r->aktif === 'aktif' ? '<label class="label label-success">Aktif</label>' : '<label class="label label-danger">Non Aktif</label>' ?></td>
						<td style="padding-left:20px">
							<?php if ($ENABLE_VIEW) : ?>
								<a class="btn btn-primary btn-sm view" href="javascript:void(0)" title="View" data-id="<?= html_escape($r->id_spesifikasi) ?>"><i class="fa fa-eye"></i></a>
							<?php endif; ?>
							<?php if ($ENABLE_MANAGE) : ?>
								<a class="btn btn-success btn-sm edit" href="javascript:void(0)" title="Edit" data-id="<?= html_escape($r->id_spesifikasi) ?>"><i class="fa fa-edit"></i></a>
							<?php endif; ?>
							<?php if ($ENABLE_DELETE) : ?>
								<a class="btn btn-danger btn-sm delete" href="javascript:void(0)" title="Delete" data-id="<?= html_escape($r->id_spesifikasi) ?>"><i class="fa fa-trash"></i></a>
							<?php endif; ?>
						</td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	</div>
</div>

<div class="modal modal-default fade" id="dialog-popup" tabindex="-1" role="dialog" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
				<h4 class="modal-title"><span class="fa fa-list-alt"></span>&nbsp;Spesifikasi</h4>
			</div>
			<div class="modal-body" id="ModalView">...</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-danger" data-dismiss="modal"><span class="glyphicon glyphicon-remove"></span> Close</button>
			</div>
		</div>
	</div>
</div>

<script src="<?= base_url('assets/plugins/datatables/jquery.dataTables.min.js') ?>"></script>
<script src="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.min.js') ?>"></script>
<script src="<?= base_url('assets/plugins/sweetalert/dist/sweetalert.js') ?>"></script>

<script type="text/javascript">
	function openPopup(url) {
		$.ajax({
			type: 'POST',
			url: siteurl + url,
			success: function(data) {
				$("#ModalView").html(data);
				$("#dialog-popup").modal();
			}
		});
	}

	$(document).on('click', '.add', function() {
		openPopup('master_spesifikasi/addSpesifikasi');
	});
	$(document).on('click', '.view', function() {
		openPopup('master_spesifikasi/viewSpesifikasi/' + $(this).data('id'));
	});
	$(document).on('click', '.edit', function() {
		openPopup('master_spesifikasi/editSpesifikasi/' + $(this).data('id'));
	});

	// Simpan (tambah / ubah): form di modal memakai atribut data-url
	$(document).on('submit', '#data_form', function(e) {
		e.preventDefault();
		var url = $(this).data('url');
		var data = $(this).serialize();
		swal({
			title: "Anda Yakin?",
			text: "Data Spesifikasi akan di simpan.",
			type: "warning",
			showCancelButton: true,
			confirmButtonClass: "btn-info",
			confirmButtonText: "Ya, Simpan!",
			cancelButtonText: "Batal",
			closeOnConfirm: false
		}, function() {
			$.ajax({
				type: 'POST',
				url: siteurl + url,
				dataType: 'json',
				data: data,
				success: function(result) {
					if (result.status == '1') {
						swal({
							title: "Sukses",
							text: result.pesan,
							type: "success"
						}, function() {
							window.location.reload(true);
						});
					} else {
						swal({
							title: "Gagal",
							text: result.pesan,
							type: "error"
						});
					}
				},
				error: function() {
					swal({
						title: "Error",
						text: "Data error. Gagal request Ajax",
						type: "error"
					});
				}
			});
		});
	});

	$(document).on('click', '.delete', function(e) {
		e.preventDefault();
		var id = $(this).data('id');
		swal({
			title: "Anda Yakin?",
			text: "Data Spesifikasi akan di hapus.",
			type: "warning",
			showCancelButton: true,
			confirmButtonClass: "btn-info",
			confirmButtonText: "Ya, Hapus!",
			cancelButtonText: "Batal",
			closeOnConfirm: false
		}, function() {
			$.ajax({
				type: 'POST',
				url: siteurl + 'master_spesifikasi/deleteSpesifikasi',
				dataType: 'json',
				data: {
					'id': id
				},
				success: function(result) {
					if (result.status == '1') {
						swal({
							title: "Sukses",
							text: result.pesan,
							type: "success"
						}, function() {
							window.location.reload(true);
						});
					} else {
						swal({
							title: "Tidak bisa dihapus",
							text: result.pesan,
							type: "error"
						});
					}
				},
				error: function() {
					swal({
						title: "Error",
						text: "Data error. Gagal request Ajax",
						type: "error"
					});
				}
			});
		});
	});

	$(function() {
		var table = $('#example1').DataTable({
			orderCellsTop: true,
			fixedHeader: true
		});
		// Kolom ke-3 (index 2) = Type Alloy
		$('#filter_alloy').on('change', function() {
			var v = $(this).val();
			table.column(2).search(v ? '^' + $.fn.dataTable.util.escapeRegex(v) + '$' : '', true, false).draw();
		});
	});
</script>
