<?php
/* Form Sheet (B2000002): tambah / ubah / salin. Mengikuti specs/ui-mockup/inventory_4.html */
$mode = $results['mode'];
$row  = $results['row'];
$dim  = $results['dim'];
$edit = $mode === 'edit';
$v = function ($k) use ($row) {
	return $row && isset($row->$k) && $row->$k !== null ? $row->$k : '';
};
$d = function ($k) use ($dim) {
	return isset($dim[$k]) ? $dim[$k] : '';
};
$opt = function ($list, $sel, $idk = 'id', $placeholder = '-- Pilih --') {
	$h = '<option value="">' . html_escape($placeholder) . '</option>';
	foreach ($list as $o) {
		$id = $o->$idk;
		$h .= '<option value="' . html_escape($id) . '"' . ((string) $id === (string) $sel ? ' selected' : '') . '>' . html_escape($o->nama) . '</option>';
	}
	return $h;
};
?>
<div class="box box-primary">
	<div class="box-body">
		<form id="sheet-form" autocomplete="off" novalidate>
			<?php if ($edit) : ?>
				<input type="hidden" name="id_category3" value="<?= html_escape($v('id_category3')) ?>">
			<?php endif; ?>
			<div class="row">
				<div class="col-md-6">
					<h4 class="page-header" style="margin-top:0">Klasifikasi</h4>
					<div class="form-group">
						<label>Type Inventory <span class="text-red">*</span></label>
						<select name="t" id="f_t" class="form-control">
							<?= $opt($results['types'], $v('id_type'), 'id_type', '-- Pilih Type --') ?>
						</select>
					</div>
					<div class="form-group">
						<label>Ferous / Non Ferous <span class="text-red">*</span></label>
						<select name="c1" id="f_c1" class="form-control">
							<?= $opt($results['c1'], $v('id_category1'), 'id') ?>
						</select>
					</div>
					<div class="form-group">
						<label>Type Alloy <span class="text-red">*</span></label>
						<select name="c2" id="f_c2" class="form-control">
							<?= $opt($results['c2'], $v('id_category2'), 'id') ?>
						</select>
					</div>
					<div class="form-group">
						<label>Spesifikasi <span class="text-red">*</span></label>
						<select name="spek" id="f_spek" class="form-control">
							<?= $opt($results['speks'], $v('id_spesifikasi'), 'id', $v('id_category2') !== '' ? '-- Pilih Spesifikasi --' : '-- Pilih Type Alloy dulu --') ?>
						</select>
						<span class="help-block">Pilihan dari <a href="<?= base_url('master_spesifikasi') ?>" target="_blank">Master Spesifikasi</a>, sesuai Type Alloy.</span>
					</div>
					<div class="form-group">
						<label>Density <span class="text-red">*</span></label>
						<input type="number" step="any" min="0" name="density" id="f_density" class="form-control" placeholder="Density" value="<?= html_escape($v('density')) ?>">
						<span class="help-block">Wajib diisi (menentukan Berat/Sheet)</span>
					</div>
					<div class="form-group">
						<label>Hardness</label>
						<input type="text" name="hardness" id="f_hardness" maxlength="11" class="form-control" placeholder="Hardness (opsional)" value="<?= html_escape($v('hardness') === '0' ? '' : $v('hardness')) ?>">
						<span class="help-block">Opsional; bila diisi masuk Nama Material Sheet.</span>
					</div>
					<div class="form-group">
						<label>Nama Material <span class="text-red">*</span></label>
						<input type="text" name="nama" id="f_nama" maxlength="50" class="form-control" placeholder="Otomatis" value="<?= html_escape($v('nama')) ?>">
						<span class="help-block">Terisi otomatis: Spesifikasi-Hardness-Thickness-Width-Length-Type Alloy (bagian kosong dilewati). Boleh diedit, maks 50 karakter.</span>
					</div>
				</div>
				<div class="col-md-6">
					<h4 class="page-header" style="margin-top:0">Bentuk &amp; Dimensi</h4>
					<div class="form-group">
						<label>Bentuk Material</label>
						<input type="text" class="form-control" value="SHEET" readonly>
					</div>
					<?php foreach ($results['dimensi'] as $ds) : $k = strtoupper($ds->nm_dimensi); ?>
						<div class="form-group">
							<label><?= html_escape($k) ?> (mm) <span class="text-red">*</span></label>
							<input type="number" step="any" min="0" name="dim[<?= html_escape($k) ?>]" class="form-control f-dim" data-k="<?= html_escape($k) ?>" value="<?= html_escape($d($k)) ?>">
							<?php if ($k === 'THICKNESS') : ?><span class="help-block">Wajib diisi (menentukan Berat/Sheet)</span><?php endif; ?>
						</div>
					<?php endforeach; ?>
					<div class="form-group">
						<label>Berat / Sheet (Kg)</label>
						<input type="text" id="f_weight" class="form-control" readonly>
						<span class="help-block">Thickness (mm) &times; Width (mm) &times; Length (mm) &times; Berat jenis (g/cm&sup3;) &divide; 1.000.000</span>
					</div>
					<h4 class="page-header">Stok</h4>
					<div class="row">
						<div class="col-sm-6 form-group"><label>Monthly Forecast</label><input type="number" min="0" name="forecast" class="form-control" value="<?= html_escape($v('mountly_forecast')) ?>"></div>
						<div class="col-sm-6 form-group"><label>Safety Stock</label><input type="number" min="0" name="safety" class="form-control" value="<?= html_escape($v('safety_stock')) ?>"></div>
						<div class="col-sm-6 form-group"><label>Order Point</label><input type="number" min="0" name="order" class="form-control" value="<?= html_escape($v('order_point')) ?>"></div>
						<div class="col-sm-6 form-group"><label>Maximum Stock</label><input type="number" min="0" name="max" class="form-control" value="<?= html_escape($v('maksimum')) ?>"></div>
					</div>
					<?php if ($edit) : ?>
						<div class="form-group">
							<label>Status</label><br>
							<label class="radio-inline"><input type="radio" name="aktif" value="aktif" <?= $v('aktif') === 'aktif' ? 'checked' : '' ?>> Aktif</label>
							<label class="radio-inline"><input type="radio" name="aktif" value="nonaktif" <?= $v('aktif') !== 'aktif' ? 'checked' : '' ?>> Non Aktif</label>
						</div>
					<?php endif; ?>
				</div>
			</div>

			<h4 class="page-header">Supplier
				<button type="button" class="btn btn-sm btn-success pull-right" id="sup-add"><i class="fa fa-plus"></i> Add</button>
			</h4>
			<table class="table table-bordered table-striped">
				<thead>
					<tr class="bg-blue">
						<th>Nama Supplier</th>
						<th>Lead Time (hari)</th>
						<th>Minimum Order</th>
						<th width="70">Action</th>
					</tr>
				</thead>
				<tbody id="sup-body"></tbody>
			</table>

			<h4 class="page-header">Material Ekuivalen
				<button type="button" class="btn btn-sm btn-success pull-right" id="eq-toggle"><i class="fa fa-plus"></i> Tambah Material Ekuivalen</button>
			</h4>
			<div id="eq-pick" class="well well-sm" style="display:none">
				<input type="text" id="eq-q" class="form-control input-sm" placeholder="Cari id / nama / spesifikasi / maker...">
				<div id="eq-res" style="margin-top:8px"></div>
			</div>
			<div id="eq-list"></div>

			<hr>
			<div class="text-center">
				<button type="submit" class="btn btn-success" id="sheet-save"><i class="fa fa-save"></i> Simpan</button>
			</div>
		</form>
	</div>
</div>

<script type="text/javascript">
	(function() {
		var $f = $('#sheet-form');
		var SELF = <?= json_encode($edit ? $v('id_category3') : '') ?>;
		var MODE = <?= json_encode($mode) ?>;
		var manual = <?= json_encode(array_values($results['eq']['manual'])) ?>;
		var excl = <?= json_encode(array_values($results['eq']['exclude'])) ?>;
		var suppliers = <?= json_encode(array_map(function ($s) {
							return array('id' => $s->id_suplier, 'name' => strtoupper($s->name_suplier));
						}, $results['suppliers'])) ?>;
		var savedSup = <?= json_encode(array_map(function ($s) {
							return array('s' => $s->id_suplier, 'lead' => $s->lead, 'min' => $s->minimum);
						}, $results['sup'])) ?>;
		var namaDirty = false;
		var eqTimer = null;

		function esc(s) {
			return $('<div>').text(s == null ? '' : s).html();
		}

		function opts(list, ph) {
			var h = '<option value="">' + esc(ph) + '</option>';
			$.each(list, function(i, o) {
				h += '<option value="' + esc(o.id) + '">' + esc(o.nama) + '</option>';
			});
			return h;
		}

		function load(level, parent, $sel, ph, cb) {
			if (!parent) {
				$sel.html(opts([], ph));
				if (cb) cb();
				return;
			}
			$.getJSON(siteurl + 'inventory_4/sheet_opsi', {
				level: level,
				parent: parent
			}, function(list) {
				$sel.html(opts(list, ph));
				if (cb) cb();
			});
		}

		// ---- Supplier ----
		function supRow(s) {
			var o = '<option value="">-- Pilih Supplier --</option>';
			$.each(suppliers, function(i, x) {
				o += '<option value="' + esc(x.id) + '"' + (s && s.s == x.id ? ' selected' : '') + '>' + esc(x.name) + '</option>';
			});
			return '<tr><td><select name="__s" class="form-control input-sm">' + o + '</select></td>' +
				'<td><input type="text" name="__lead" class="form-control input-sm" value="' + esc(s ? s.lead : '') + '"></td>' +
				'<td><input type="text" name="__min" class="form-control input-sm" value="' + esc(s ? s.min : '') + '"></td>' +
				'<td align="center"><button type="button" class="btn btn-sm btn-danger sup-del"><i class="fa fa-trash-o"></i></button></td></tr>';
		}
		if (savedSup.length) {
			$.each(savedSup, function(i, s) {
				$('#sup-body').append(supRow(s));
			});
		} else {
			$('#sup-body').append(supRow());
		}
		$('#sup-add').on('click', function() {
			$('#sup-body').append(supRow());
		});
		$('#sup-body').on('click', '.sup-del', function() {
			$(this).closest('tr').remove();
		});

		// ---- Nama & berat ----
		function dimVal(k) {
			return $.trim($f.find('.f-dim[data-k="' + k + '"]').val());
		}

		function buildNama() {
			var spek = $('#f_spek option:selected').val() ? $.trim($('#f_spek option:selected').text()) : '';
			var alloy = $('#f_c2').val() ? $.trim($('#f_c2 option:selected').text()) : '';
			var parts = [spek, $.trim($('#f_hardness').val()), dimVal('THICKNESS'), dimVal('WIDTH'), dimVal('LENGTH'), alloy];
			return $.grep(parts, function(x) {
				return x !== '';
			}).join('-');
		}

		function auto() {
			if (!namaDirty) $('#f_nama').val($('#f_c2').val() ? buildNama() : '');
		}

		function calc() {
			var n = [dimVal('THICKNESS'), dimVal('WIDTH'), dimVal('LENGTH'), $.trim($('#f_density').val())].map(function(x) {
				return parseFloat(String(x).replace(/,/g, ''));
			});
			var bad = n.some(function(x) {
				return isNaN(x) || !x;
			});
			$('#f_weight').val(bad ? '-' : (n[0] * n[1] * n[2] * n[3] / 1000000).toLocaleString('en-US', {
				minimumFractionDigits: 3,
				maximumFractionDigits: 3
			}));
		}

		// ---- Material Ekuivalen ----
		function eqData(extra) {
			return $.extend({
				self: SELF,
				id_spek: $('#f_spek').val() || '',
				thickness: dimVal('THICKNESS'),
				is_sheet: '1',
				manual: manual,
				excl: excl
			}, extra || {});
		}

		function showEq() {
			$.post(siteurl + 'inventory_4/ekuivalen_list', eqData(), function(h) {
				$('#eq-list').html(h);
			});
			pick();
		}

		function showEqSoon() {
			clearTimeout(eqTimer);
			eqTimer = setTimeout(showEq, 250);
		}

		function pick() {
			if (!$('#eq-pick').is(':visible')) return;
			$.post(siteurl + 'inventory_4/ekuivalen_cari', eqData({
				q: $('#eq-q').val()
			}), function(h) {
				$('#eq-res').html(h);
			});
		}
		$('#eq-toggle').on('click', function() {
			$('#eq-pick').toggle();
			pick();
			if ($('#eq-pick').is(':visible')) $('#eq-q').focus();
		});
		$('#eq-q').on('input', pick);
		$('#eq-res').on('click', '.eq-add', function() {
			var id = String($(this).data('id'));
			excl = $.grep(excl, function(x) {
				return x !== id;
			});
			if ($.inArray(id, manual) < 0) manual.push(id);
			showEq();
		});
		$('#eq-list').on('click', '.eq-rm', function() {
			var id = String($(this).data('id'));
			manual = $.grep(manual, function(x) {
				return x !== id;
			});
			if ($.inArray(id, excl) < 0) excl.push(id);
			showEq();
		});

		// ---- Cascade klasifikasi ----
		$('#f_t').on('change', function() {
			load('c1', $(this).val(), $('#f_c1'), '-- Pilih --', function() {
				$('#f_c1').trigger('change');
			});
		});
		$('#f_c1').on('change', function() {
			load('c2', $(this).val(), $('#f_c2'), '-- Pilih --', function() {
				$('#f_c2').trigger('change');
			});
		});
		$('#f_c2').on('change', function() {
			load('spek', $(this).val(), $('#f_spek'), $(this).val() ? '-- Pilih Spesifikasi --' : '-- Pilih Type Alloy dulu --', function() {
				$('#f_spek').trigger('change');
			});
		});
		$('#f_spek').on('change', function() {
			auto();
			showEqSoon();
		});
		$('#f_hardness').on('input', auto);
		$f.find('.f-dim').on('input', function() {
			calc();
			auto();
			showEqSoon();
		});
		$('#f_density').on('input', calc);
		$('#f_nama').on('input', function() {
			namaDirty = true;
		});

		// Nama yang sudah ada dan beda dari pola otomatis dianggap diedit manual
		calc();
		if (MODE !== 'add' && $('#f_nama').val() !== buildNama()) {
			namaDirty = true;
		} else {
			auto();
		}
		showEq();

		// ---- Simpan ----
		function fail(msg) {
			swal({
				title: "Save Failed!",
				text: msg,
				type: "warning"
			});
		}
		$f.on('submit', function(e) {
			e.preventDefault();
			var need = [
				['#f_t', 'Type Inventory'],
				['#f_c1', 'Ferous / Non Ferous'],
				['#f_c2', 'Type Alloy'],
				['#f_spek', 'Spesifikasi'],
				['#f_density', 'Density']
			];
			for (var i = 0; i < need.length; i++) {
				if (!$.trim($(need[i][0]).val())) return fail(need[i][1] + ' wajib diisi.');
			}
			var bad = false;
			$f.find('.f-dim').each(function() {
				if (!(parseFloat($(this).val()) > 0)) {
					bad = $(this).data('k');
					return false;
				}
			});
			if (bad) return fail(bad + ' wajib diisi dengan angka lebih dari 0.');
			if (!(parseFloat($('#f_density').val()) > 0)) return fail('Density wajib diisi dengan angka lebih dari 0.');
			if (!$.trim($('#f_nama').val())) return fail('Nama Material wajib diisi.');

			var data = $f.serializeArray();
			$('#sup-body tr').each(function(i) {
				var s = $(this).find('[name=__s]').val();
				if (!s) return;
				data.push({
					name: 'sup[' + i + '][s]',
					value: s
				}, {
					name: 'sup[' + i + '][lead]',
					value: $(this).find('[name=__lead]').val()
				}, {
					name: 'sup[' + i + '][min]',
					value: $(this).find('[name=__min]').val()
				});
			});
			$.each(manual, function(i, id) {
				data.push({
					name: 'eq_manual[]',
					value: id
				});
			});
			$.each(excl, function(i, id) {
				data.push({
					name: 'eq_excl[]',
					value: id
				});
			});
			// Field sementara tidak ikut dikirim
			data = $.grep(data, function(x) {
				return x.name.indexOf('__') !== 0;
			});

			swal({
				title: "Anda Yakin?",
				text: "Data akan diproses dan tidak dapat diulang.",
				type: "warning",
				showCancelButton: true,
				confirmButtonText: "Ya, Proses!",
				cancelButtonText: "Batal",
				closeOnConfirm: false
			}, function() {
				$.ajax({
					url: siteurl + 'inventory_4/' + (MODE === 'edit' ? 'saveEditSheet' : 'saveNewSheet'),
					type: 'POST',
					dataType: 'json',
					data: $.param(data),
					success: function(r) {
						if (r.status == 1) {
							swal({
								title: "Save Success!",
								text: r.pesan,
								type: "success",
								timer: 3000,
								showConfirmButton: false
							});
							window.location.href = siteurl + 'inventory_4/detail/' + r.code;
						} else {
							fail(r.pesan);
						}
					},
					error: function() {
						fail('An Error Occured During Process. Please try again..');
					}
				});
			});
		});
	})();
</script>
