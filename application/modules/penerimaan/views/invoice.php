<div class="box box-primary">
	<div class="box-body">
		<form id="data-form" method="post">
			<div class="form-group row">
				<input type="hidden" id="filter_customer" value="<?php echo $results['detail']; ?>">
				<table class="table table-bordered" width="100%" id="tbl_invoice_modal">
					<thead>
						<tr>
							<th width="15%">Code</th>
							<th width="20%">No Invoice</th>
							<th width="25%">Nama Customer</th>
							<th width="15%">Total Invoice</th>
							<th width="15%">Sisa Invoice</th>
							<th width="10%" class="text-center">Aksi</th>
						</tr>
					</thead>
					<tbody></tbody>
				</table>
			</div>
		</form>
	</div>
</div>

<script>
	$(function() {
		var id_customer = $('#filter_customer').val();

		$('#tbl_invoice_modal').DataTable({
			serverSide: true,
			processing: true,
			paging: true,
			destroy: true,
			pageLength: 10,
			ajax: {
				type: 'POST',
				url: siteurl + 'penerimaan/get_invoice_serverside',
				data: function(d) {
					d.id_customer = id_customer;
				},
				dataType: 'json'
			},
			columns: [{
					data: 'code'
				},
				{
					data: 'no_invoice'
				},
				{
					data: 'nama_customer'
				},
				{
					data: 'total_invoice',
					className: 'text-center'
				},
				{
					data: 'sisa_invoice',
					className: 'text-center'
				},
				{
					data: 'action',
					className: 'text-center',
					orderable: false,
					searchable: false
				}
			],
			order: [
				[0, 'desc']
			]
		});
	});
</script>