<style type="text/css">
	thead input {
		width: 100%;
	}
</style>
<div id='alert_edit' class="alert alert-success alert-dismissable" style="padding: 15px; display: none;"></div>
<link rel="stylesheet" href="https://cdn.datatables.net/2.2.2/css/dataTables.dataTables.min.css">

<div class="box">
	<div class="box-header">
		<div style="display:inline-block;width:100%;">
			<a class="btn btn-sm btn-primary list_efaktur" href="javascript:void(0)" title="List E-Faktur" style="float:left;margin-right:8px">List E-Faktur</a>
			<a class="btn btn-sm btn-success generate" href="javascript:void(0)" title="Generate" style="float:left;margin-right:8px">Generate</a>
		</div>
	</div>
	<div class="box-body">
		<table id="example5" class="table table-bordered table-striped">
			<thead>
				<tr>
					<th><input type='checkbox' value='all' id='no_surat_all' name='checkAll'></th>
					<th>NPWP</th>
					<th>Nama Customer</th>
					<!-- <th>No. Faktur</th> -->
					<th>No. Invoice</th>
					<th>Tanggal</th>
					<th>DPP</th>
					<th>Ppn</th>
					<th>Grand Total</th>
				</tr>
			</thead>

			<tbody>

			</tbody>
		</table>
	</div>
	<!-- /.box-body -->
</div>

<!-- modal -->
<div class="modal modal-default fade" id="ModalViewX" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-md">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
				<h4 class="modal-title" id='head_title'>Information</h4>
			</div>
			<div class="modal-body" id="viewX">
				...
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
				<button type="button" class="btn btn-primary" data-dismiss="modal" id='generate'>Save</button>
			</div>
		</div>
		<!-- /.modal-content -->
	</div>
	<!-- /.modal-dialog -->
</div>
<!-- /.modal -->

<!-- DataTables -->
<script src="https://cdn.datatables.net/2.2.2/js/dataTables.min.js"></script>

<!-- page script -->
<script type="text/javascript">
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


		$("#form-area").hide();
	});


	$(function() {
		DataTables();
		$("#form-area").hide();
	});

	var current_search = '';
	var total_valid_npwp = 0;
	var id_generate = [];
	var dtTable = null;

	function DataTables() {
		dtTable = $('#example5').DataTable({
			serverSide: true,
			processing: true,
			paging: true,
			destroy: true,
			ajax: {
				type: 'post',
				url: siteurl + active_controller + 'get_efaktur',
				dataType: 'json'
			},
			columns: [{
					data: 'action',
					orderable: false
				},
				{
					data: 'npwp'
				},
				{
					data: 'nama_customer'
				},
				{
					data: 'no_invoice'
				},
				{
					data: 'tanggal_invoice'
				},
				{
					data: 'nilai_dpp'
				},
				{
					data: 'nilai_ppn'
				},
				{
					data: 'nilai_invoice'
				}
			],
			drawCallback: function(settings) {
				if (settings.json && settings.json.totalValid !== undefined) {
					total_valid_npwp = settings.json.totalValid;
				}

				// Reset pilihan hanya jika keyword search berubah
				// Ambil search value langsung dari settings request, bukan dari instance
				var apiSearch = (settings.oInit && settings.ajax) ? '' : '';
				try {
					apiSearch = settings.oPreviousSearch ? settings.oPreviousSearch.sSearch : '';
				} catch (e) {}

				if (apiSearch !== current_search) {
					current_search = apiSearch;
					id_generate = [];
					$('#no_surat_all').prop('checked', false);
				}

				// Restore state checkbox sesuai id_generate
				$('.check_nosurat').each(function() {
					$(this).prop('checked', id_generate.indexOf($(this).val()) !== -1);
				});

				updateCheckAllState();
			}
		});
	}

	function updateCheckAllState() {
		var allChecked = false;
		if (total_valid_npwp > 0 && id_generate.length === total_valid_npwp) {
			allChecked = true;
		}
		$('#no_surat_all').prop('checked', allChecked);
	}

	$(document).on('change', '#no_surat_all', function() {
		var isChecked = $(this).is(':checked');

		if (isChecked) {
			swal({
				title: "Loading...",
				text: "Mendapatkan semua data...",
				showConfirmButton: false
			});
			$.ajax({
				url: siteurl + active_controller + 'get_all_efaktur_id',
				type: 'post',
				data: {
					search: dtTable ? dtTable.search() : ''
				},
				dataType: 'json',
				success: function(result) {
					id_generate = result.data;
					$('.check_nosurat').each(function() {
						var val = $(this).val();
						if (id_generate.indexOf(val) !== -1) {
							$(this).prop('checked', true);
						}
					});
					updateCheckAllState();
					swal.close();
				},
				error: function() {
					swal("Error", "Gagal mengambil keseluruhan data", "error");
				}
			});
		} else {
			id_generate = [];
			$('.check_nosurat').prop('checked', false);
		}
	});

	$(document).on('change', '.check_nosurat', function() {
		var isChecked = $(this).is(':checked');
		var val = $(this).val();
		var npwp = String($(this).data('npwp') || '').trim();
		var index = id_generate.indexOf(val);

		if (isChecked) {
			if (npwp === '') {
				$(this).prop('checked', false);
				swal({
					title: "Peringatan",
					text: "Data dengan NPWP kosong tidak bisa dipilih!",
					type: "warning"
				});
				return false;
			}

			if (index === -1) {
				id_generate.push(val);
			}
		} else {
			if (index !== -1) {
				id_generate.splice(index, 1);
			}
		}

		updateCheckAllState();
	});

	$(document).on('click', '.list_efaktur', function(e) {
		window.location.href = siteurl + active_controller + "e_faktur_list";
	});

	$(document).on('click', '.generate', function(e) {
		// console.log('id_generate saat klik:', id_generate);
		// console.log('jumlah:', id_generate.length);

		if (id_generate.length === 0) {
			swal({
				title: "Peringatan",
				text: "Belum ada data yang dipilih!",
				type: "warning"
			});
			return false;
		}

		swal({
				title: "Generate Data E-Faktur Sekarang?",
				text: "",
				type: "warning",
				showCancelButton: true,
				confirmButtonClass: "btn-danger",
				confirmButtonText: "Ya, Proses Data",
				cancelButtonText: "Tidak",
				closeOnConfirm: false,
				closeOnCancel: false,
				showLoaderOnConfirm: true
			},
			function(isConfirm) {
				if (isConfirm) {
					$.ajax({
						type: 'POST',
						url: siteurl + active_controller + "generate_efaktur",
						data: {
							id_generate: id_generate
						},
						cache: false,
						dataType: "JSON",
						success: function(result) {
							if (result.status === 'success') {
								window.location.href = siteurl + active_controller + "export_coretax_excel";

								swal({
									title: "Generate Success!",
									text: result.msg,
									type: "success",
									timer: 1500,
									showCancelButton: false,
									showConfirmButton: false,
									allowOutsideClick: false
								});
								DataTables('set');
							} else {
								swal({
									title: "Peringatan",
									text: result.message || 'Tidak ada data yang diproses',
									type: "warning"
								});
							}
						},
						error: function(request, error) {
							console.log(arguments);
							swal({
								title: "Error Message !",
								text: 'An Error Occured During Process. Please try again..',
								type: "warning",
								timer: 5000,
								showCancelButton: false,
								showConfirmButton: false,
								allowOutsideClick: false
							});
						}
					});
				} else {
					swal("Batal Proses", "Data bisa diproses nanti", "error");
					return false;
				}
			});
	});
</script>