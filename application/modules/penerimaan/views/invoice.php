<div class="box box-primary">
	<div class="box-body">
		<form id="data-form" method="post">
			<div class="form-group row">
				<input type="hidden" id="filter_customer" value="<?php echo $results['detail']; ?>">
				<div class="col-sm-3" style="margin-bottom:10px;">
					<select id="filter_type" class="form-control input-sm">
						<option value="">Semua</option>
						<option value="invoice">Invoice</option>
						<option value="cn">Credit Note</option>
					</select>
				</div>
				<table class="table table-bordered" width="100%" id="tbl_invoice_modal">
					<thead>
						<tr>
							<th width="8%">Type</th>
							<th width="12%">Code</th>
							<th width="20%">No Invoice</th>
							<th width="22%">Nama Customer</th>
							<th width="15%">Total Invoice</th>
							<th width="13%">Sisa Invoice</th>
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

		var tbl = $('#tbl_invoice_modal').DataTable({
			serverSide: true,
			processing: true,
			paging: true,
			destroy: true,
			pageLength: 10,
			ajax: {
				type: 'POST',
				url: siteurl + 'penerimaan/get_invoice_cn_serverside',
				data: function(d) {
					d.id_customer = id_customer;
					d.filter_type = $('#filter_type').val();
				},
				dataType: 'json'
			},
			columns: [{
					data: 'type',
					orderable: false,
					searchable: false
				},
				{
					data: 'code'
				},
				{
					data: 'no_surat'
				},
				{
					data: 'nm_customer'
				},
				{
					data: 'total_invoice',
					className: 'text-right'
				},
				{
					data: 'sisa',
					className: 'text-right'
				},
				{
					data: 'action',
					className: 'text-center',
					orderable: false,
					searchable: false
				}
			],
			order: [
				[1, 'desc']
			]
		});

		// Reload table when type filter changes
		$('#filter_type').on('change', function() {
			tbl.ajax.reload();
		});
	});
</script>