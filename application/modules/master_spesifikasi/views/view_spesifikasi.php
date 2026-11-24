<?php $row = $results['row'];
$material = $results['material']; ?>
<dl class="dl-horizontal">
	<dt>Id Spesifikasi</dt>
	<dd><?= html_escape($row->id_spesifikasi) ?></dd>
	<dt>Type Alloy</dt>
	<dd><?= html_escape($row->nama_alloy) ?></dd>
	<dt>Nama Spesifikasi</dt>
	<dd><?= html_escape($row->nama) ?></dd>
	<dt>Status</dt>
	<dd><?= $row->aktif === 'aktif' ? '<label class="label label-success">Aktif</label>' : '<label class="label label-danger">Non Aktif</label>' ?></dd>
</dl>
<h4>Dipakai di material (<?= count($material) ?>)</h4>
<?php if ($material) : ?>
	<div style="max-height:300px;overflow:auto">
		<table class="table table-bordered table-condensed">
			<thead>
				<tr>
					<th>Id Material</th>
					<th>Nama Material</th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ($material as $m) : ?>
					<tr>
						<td><?= html_escape($m->id_category3) ?></td>
						<td><?= html_escape($m->nama) ?></td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	</div>
<?php else : ?>
	<em>Belum dipakai.</em>
<?php endif; ?>
