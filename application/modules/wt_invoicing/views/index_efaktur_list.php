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
			<a class="btn btn-sm btn-warning back_efaktur" href="javascript:void(0)" title="Back E-Faktur" style="float:left;margin-right:8px">Back E-Faktur</a>
		</div>
	</div>
	<div class="box-body">
		<table id="example5" class="table table-bordered table-striped">
			<thead>
				<tr>
					<th>ID Export</th>
					<th>No. Invoice</th>
					<th>Date Export</th>
					<th>Time Export</th>
					<th>Action</th>
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

	function DataTables() {
		var DataTables = $('#example5').dataTable({
			serverSide: true,
			processing: true,
			paging: true,
			destroy: true,
			ajax: {
				type: 'post',
				url: siteurl + active_controller + 'list_efaktur',
				dataType: 'json'
			},
			columns: [{
					data: 'id_export'
				},
				{
					data: 'no_invoice'
				},
				{
					data: 'date_export'
				},
				{
					data: 'time_export'
				},
				{
					data: 'action'
				}
			]
		});
	}

	// E-Faktur
	$(document).on('click', '.back_efaktur', function(e) {
		window.location.href = siteurl + active_controller + "e_faktur";
	});
	$(document).on('click', '.generate', function(e) {
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
						url: siteurl + active_controller + "generate_efaktur",
						dataType: "json",
						type: 'POST',
						data: {
							id_generate: []
						},
						success: function(result) {
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