<?php
/* Detail Sheet (B2000002) */
$r   = $results['row'];
$dim = $results['dim'];
$dv  = function ($k) use ($dim) {
	return isset($dim[$k]) && $dim[$k] !== '' ? $dim[$k] : '-';
};
$num = function ($x) {
	return $x === '' || $x === null ? '-' : number_format((float) $x, 0, '.', ',');
};
?>
<div class="box box-solid">
	<div class="box-body">
		<div class="row">
			<div class="col-md-6">
				<dl class="dl-horizontal">
					<dt>Id Material</dt>
					<dd><?= html_escape($r->id_category3) ?></dd>
					<dt>Type Inventory</dt>
					<dd><?= html_escape($r->nama_type) ?></dd>
					<dt>Ferous / Non Ferous</dt>
					<dd><?= html_escape($r->nama_category1) ?></dd>
					<dt>Type Alloy</dt>
					<dd><?= html_escape($r->nama_category2) ?></dd>
					<dt>Spesifikasi</dt>
					<dd><?= html_escape($r->spek) ?></dd>
					<dt>Density</dt>
					<dd><?= html_escape($r->density) ?></dd>
					<dt>Hardness</dt>
					<dd><?= html_escape($r->hardness !== '' && $r->hardness !== '0' && $r->hardness !== null ? $r->hardness : '-') ?></dd>
					<dt>Nama Material</dt>
					<dd><?= html_escape($r->nama) ?></dd>
				</dl>
			</div>
			<div class="col-md-6">
				<dl class="dl-horizontal">
					<dt>Bentuk Material</dt>
					<dd>SHEET</dd>
					<dt>THICKNESS</dt>
					<dd><?= html_escape($dv('THICKNESS')) ?></dd>
					<dt>WIDTH</dt>
					<dd><?= html_escape($dv('WIDTH')) ?></dd>
					<dt>LENGTH</dt>
					<dd><?= html_escape($dv('LENGTH')) ?></dd>
					<dt>Berat / Sheet (Kg)</dt>
					<dd><?= $results['berat'] > 0 ? number_format($results['berat'], 2, '.', ',') : '-' ?></dd>
					<dt>Monthly Forecast</dt>
					<dd><?= $num($r->mountly_forecast) ?></dd>
					<dt>Safety Stock</dt>
					<dd><?= $num($r->safety_stock) ?></dd>
					<dt>Order Point</dt>
					<dd><?= $num($r->order_point) ?></dd>
					<dt>Maximum Stock</dt>
					<dd><?= $num($r->maksimum) ?></dd>
					<dt>Status</dt>
					<dd><?= $r->aktif === 'aktif' ? '<span class="label label-success">Aktif</span>' : '<span class="label label-danger">Non Aktif</span>' ?></dd>
				</dl>
			</div>
		</div>

		<h4 class="page-header">Supplier</h4>
		<table class="table table-bordered table-condensed">
			<thead>
				<tr>
					<th>Nama Supplier</th>
					<th>Lead Time (hari)</th>
					<th>Minimum Order</th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ($results['sup'] as $s) : ?>
					<tr>
						<td><?= html_escape($s->name_suplier) ?></td>
						<td><?= html_escape($s->lead !== '' && $s->lead !== null ? $s->lead : '-') ?></td>
						<td><?= html_escape($s->minimum !== '' && $s->minimum !== null ? $s->minimum : '-') ?></td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>

		<h4 class="page-header">Material Ekuivalen</h4>
		<?= $results['eq_html'] ?>
	</div>
</div>
