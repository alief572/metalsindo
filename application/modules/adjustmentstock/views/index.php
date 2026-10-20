<?php
$ENABLE_ADD     = has_permission('Adjustment_Stock.Add');
$ENABLE_MANAGE  = has_permission('Adjustment_Stock.Manage');
$ENABLE_VIEW    = has_permission('Adjustment_Stock.View');
$ENABLE_DELETE  = has_permission('Adjustment_Stock.Delete');
$id_bentuk = $this->uri->segment(3);
?>
<link rel="stylesheet" href="<?= base_url('assets/css/adjustmentstock.css') ?>">
<div class="adjustmentstock-page">
<p class="adjustmentstock-page-intro">Koreksi Qty dan berat material, atau pindahkan stok antargudang.</p>
<link rel="stylesheet" href="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.css') ?>">
<link href="<?= base_url('assets/plugins/select2/select2.min.css') ?>" rel="stylesheet" />

<div class="adjustmentstock-tabs">
	<ul class="nav nav-tabs">
		<li class="active"><a href="#history" data-toggle="tab" aria-expanded="true">Riwayat adjustment</a></li>
		<li><a href="#adjust" data-toggle="tab" aria-expanded="false">Tambah adjustment</a></li>
	</ul>
</div>

<div class="tab-content">
	<div class="tab-pane active" id="history">
		<div class="box adjustmentstock-table-panel">

			<div class="box-body">
				<h2 class="adjustmentstock-panel-title">Riwayat perubahan stok</h2>
				<p class="adjustmentstock-panel-help">Telusuri Qty, berat, gudang, dan alasan setiap adjustment.</p>
				<table id="exampleaa" class="table table-bordered table-striped">
					<thead>
						<tr>
							<th>#</th>
							<th>No Transaksi</th>
							<th>Tanggal</th>
							<th>Material</th>
							<th>Adjust</th>
							<th>Gudang</th>
							<th>Keterangan</th>
							<th>Qty Adjustment</th>
							<th>Total Berat (kg)</th>
						</tr>
					</thead>
					<tbody>
						<!-- DataTables -->
					</tbody>
				</table>
			</div>
			<!-- /.box-body -->
		</div>
	</div>
	<div class="tab-pane" id="adjust">
		<div class="box adjustmentstock-table-panel">

			<div class="box-body">
				<h2 class="adjustmentstock-panel-title">Pilih material untuk adjustment</h2>
				<p class="adjustmentstock-panel-help">Cari material, lalu pilih Adjustment untuk menentukan lot dan nilai perubahan.</p>
				<table id="examplebb" class="table table-bordered table-striped">
					<thead>
						<tr>
							<th width="5">#</th>
							<th>Jenis material</th>
							<th>ID</th>
							<th>Nama material</th>
							<th>Bentuk</th>
							<th>Supplier</th>
							<th>Berat stok (kg)</th>
							<?php if ($ENABLE_ADD) : ?>
								<th class="adjustmentstock-actions-column">Tindakan</th>
							<?php endif; ?>
						</tr>
					</thead>
					<tbody>

					</tbody>
				</table>
			</div>
			<!-- /.box-body -->
		</div>
	</div>
</div>
<div class="modal fade adjustmentstock-modal" id="dialog-popup" tabindex="-1" role="dialog" aria-modal="true" aria-labelledby="adjustmentstock-modal-title">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Tutup form adjustment"><span aria-hidden="true">&times;</span></button>
                <h2 class="modal-title" id="adjustmentstock-modal-title">Adjustment Stock</h2>
                <p>Saldo diperbarui setelah adjustment disimpan.</p>
            </div>
            <div class="modal-body" id="ModalView" aria-live="polite"></div>
        </div>
    </div>
</div>
</div>

<!-- DataTables -->
<script src="<?= base_url('assets/plugins/datatables/jquery.dataTables.min.js') ?>"></script>
<script src="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.min.js') ?>"></script>
<script src="<?= base_url('assets/plugins/select2/select2.full.min.js') ?>"></script>

<!-- page script -->
<script type="text/javascript">
	$(document).ready(function() {
		datatable_history();
		datatable_adjust();

		$('.select2').select2({
			width: '100%'
		});
	});

	$(document).on('click', '.adjustmentstock-page .edit', function(e) {
		e.preventDefault();
		$('#dialog-popup').modal('show');
		$('#ModalView').text('Memuat form adjustment…');
		var id = $(this).data('id_inventory3');

		$.ajax({
			type: 'POST',
			url: siteurl + 'adjustmentstock/AdjustNow/' + id,
			success: function(data) {
				$("#dialog-popup").modal();
				$("#ModalView").html(data);

			},
            error: function() { $('#ModalView').text('Form adjustment belum dapat dimuat. Tutup form dan pilih material kembali.'); }
		})
	});

	$(document).on('click', '.copy', function(e) {
		var id = $(this).data('id_inventory3');
		$("#head_title").html("<i class='fa fa-list-alt'></i><b>Copy Inventory</b>");
		$.ajax({
			type: 'POST',
			url: siteurl + 'inventory_4/copyInventory/' + id,
			success: function(data) {
				$("#dialog-popup").modal();
				$("#ModalView").html(data);

			}
		})
	});

	$(document).on('click', '.view', function() {
		var id = $(this).data('id_inventory3');
		// alert(id);
		$("#head_title").html("<i class='fa fa-list-alt'></i><b>Detail Inventory</b>");
		$.ajax({
			type: 'POST',
			url: siteurl + 'inventory_4/viewInventory/' + id,
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
		var id = $(this).data('id_bentuk');
		$("#head_title").html("<i class='fa fa-list-alt'></i><b>Tambah Inventory</b>");
		$.ajax({
			type: 'POST',
			url: siteurl + 'stock_material/AddStock/' + id,
			data: {
				'id': id
			},
			success: function(data) {
				$("#dialog-popup").modal();
				$("#ModalView").html(data);

			}
		})
	});


	// DELETE DATA
	$(document).on('click', '.delete', function(e) {
		e.preventDefault()
		var id = $(this).data('id_inventory3');
		// alert(id);
		swal({
				title: "Anda Yakin?",
				text: "Data Inventory akan di hapus.",
				type: "warning",
				showCancelButton: true,
				confirmButtonClass: "btn-info",
				confirmButtonText: "Ya, Hapus!",
				cancelButtonText: "Batal",
				closeOnConfirm: false
			},

			function() {
				$.ajax({
					type: 'POST',
					url: siteurl + 'inventory_4/deleteInventory',
					dataType: "json",
					data: {
						'id': id
					},
					success: function(result) {
						if (result.status == '1') {
							swal({
									title: "Sukses",
									text: "Data Inventory berhasil dihapus.",
									type: "success"
								},
								function() {
									window.location.reload(true);
								})
						} else {
							swal({
								title: "Error",
								text: "Data error. Gagal hapus data",
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
		// $('#example1 thead tr').clone(true).appendTo( '#example1 thead' );
		// $('#example1 thead tr:eq(1) th').each( function (i) {
		// var title = $(this).text();
		//alert(title);
		// if (title == "#" || title =="Action" ) {
		// $(this).html( '' );
		// }else{
		// $(this).html( '<input type="text" />' );
		// }

		// $( 'input', this ).on( 'keyup change', function () {
		// if ( table.column(i).search() !== this.value ) {
		// table
		// .column(i)
		// .search( this.value )
		// .draw();
		// }else{
		// table
		// .column(i)
		// .search( this.value )
		// .draw();
		// }
		// } );
		// } );

		// var table = $('#exampleaa').DataTable({
		// 	orderCellsTop: true,
		// 	fixedHeader: true
		// });
		$("#form-area").hide();
	});

    function adjustmentNumber(data, type, decimals, formatted) {
        if (type !== 'display') return data;
        if (data == null || data === '') return '—';
        var value = Number(formatted ? String(data).replace(/,/g, '') : data);
        return Number.isFinite(value) ? value.toLocaleString('id-ID', { minimumFractionDigits: decimals, maximumFractionDigits: decimals }) : '—';
    }
    function adjustmentTableLanguage(subject, placeholder) {
        return {
            processing: 'Memuat ' + subject + '…', search: 'Cari', searchPlaceholder: placeholder,
            lengthMenu: 'Tampilkan _MENU_ baris', info: '_START_–_END_ dari _TOTAL_ data',
            infoEmpty: 'Belum ada data ditampilkan', infoFiltered: '(dari _MAX_ data)',
            emptyTable: 'Belum ada ' + subject + ' tercatat.',
            zeroRecords: 'Tidak ada hasil yang cocok. Ubah atau kosongkan pencarian.',
            paginate: { previous: 'Sebelumnya', next: 'Berikutnya', first: 'Awal', last: 'Akhir' }
        };
    }
    $('.adjustmentstock-tabs a[data-toggle="tab"]').on('shown.bs.tab', function () {
        $('#exampleaa,#examplebb').each(function () { $(this).DataTable().columns.adjust(); });
    });
	function datatable_history() {
		$('#exampleaa').DataTable({
			serverSide: true,
			scrollX: true,
			autoWidth: false,
			language: adjustmentTableLanguage('riwayat adjustment', 'Cari transaksi, material, atau keterangan'),
			processing: true,
			orderCellsTop: true,
			fixedHeader: true,
			destroy: true,
			stateSave: true,
			paging: true,
			ajax: {
				url: siteurl + 'adjustmentstock/history_datatable',
				type: 'POST',
				dataType: 'json'
			},
			columnDefs: [{ targets: 7, className: 'adjustmentstock-number', render: function (data, type) { return adjustmentNumber(data, type, 0, false); } }, { targets: 8, className: 'adjustmentstock-number', render: function (data, type) { return adjustmentNumber(data, type, 2, false); } }, { targets: '_all', render: function (data, type) { return type === 'display' ? $('<span>').text(data == null ? '' : data).html() : data; } }],
			columns: [{
					data: 'no'
				},
				{
					data: 'no_transaksi'
				},
				{
					data: 'tanggal_transaksi'
				},
				{
					data: 'nama_material'
				},
				{
					data: 'adjustment'
				},
				{
					data: 'gudang'
				},
				{
					data: 'keterangan'
				},
				{
					data: 'qty'
				},
				{
					data: 'jumlah_stock'
				}
			]
		});
	}

	function datatable_adjust() {
		$('#examplebb').DataTable({
			serverSide: true,
			scrollX: true,
			autoWidth: false,
			language: adjustmentTableLanguage('material', 'Cari nama material, bentuk, atau jenis'),
			columnDefs: [{ targets: [6], className: 'adjustmentstock-number', render: function (data, type) { return adjustmentNumber(data, type, 2, true); } }],
			processing: true,
			orderCellsTop: true,
			fixedHeader: true,
			destroy: true,
			stateSave: true,
			paging: true,
			ajax: {
				url: siteurl + 'adjustmentstock/adjust_datatable',
				type: 'POST',
				dataType: 'json'
			},
			columns: [{
					data: 'no'
				},
				{
					data: 'ferrous_nonferrous'
				},
				{
					data: 'id'
				},
				{
					data: 'detail_nama_material'
				},
				{
					data: 'bentuk_material'
				},
				{
					data: 'supplier'
				},
				{
					data: 'jumlah_stok'
				}
				<?php if ($ENABLE_ADD): ?>,
				{ data: 'action' }
				<?php endif; ?>
			]
		});
	}

	$(function() {
		// $('#example1 thead tr').clone(true).appendTo( '#example1 thead' );
		// $('#example1 thead tr:eq(1) th').each( function (i) {
		// var title = $(this).text();
		//alert(title);
		// if (title == "#" || title =="Action" ) {
		// $(this).html( '' );
		// }else{
		// $(this).html( '<input type="text" />' );
		// }

		// $( 'input', this ).on( 'keyup change', function () {
		// if ( table.column(i).search() !== this.value ) {
		// table
		// .column(i)
		// .search( this.value )
		// .draw();
		// }else{
		// table
		// .column(i)
		// .search( this.value )
		// .draw();
		// }
		// } );
		// } );

		// var table = $('#examplebb').DataTable({
		// 	orderCellsTop: true,
		// 	fixedHeader: true
		// });
		$("#form-area").hide();
	});
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
