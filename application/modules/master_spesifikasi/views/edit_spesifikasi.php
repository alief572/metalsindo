<?php $row = $results['row']; ?>
<form id="data_form" data-url="master_spesifikasi/saveEditSpesifikasi">
	<input type="hidden" name="id_spesifikasi" value="<?= html_escape($row->id_spesifikasi) ?>">
	<div class="form-group">
		<label>Id Spesifikasi</label>
		<input type="text" class="form-control" value="<?= html_escape($row->id_spesifikasi) ?>" readonly>
	</div>
	<div class="form-group">
		<label>Type Alloy <span class="text-red">*</span></label>
		<select name="id_category2" class="form-control" required>
			<option value="">-- Pilih Alloy --</option>
			<?php foreach ($results['alloys'] as $a) : ?>
				<option value="<?= html_escape($a->id_category2) ?>" <?= $a->id_category2 === $row->id_category2 ? 'selected' : '' ?>><?= html_escape($a->nama) ?></option>
			<?php endforeach; ?>
		</select>
		<span class="help-block">Tidak bisa diganti bila spesifikasi sudah dipakai material.</span>
	</div>
	<div class="form-group">
		<label>Nama Spesifikasi <span class="text-red">*</span></label>
		<input type="text" name="nama" class="form-control" maxlength="50" required value="<?= html_escape($row->nama) ?>" style="text-transform:uppercase">
	</div>
	<div class="form-group">
		<label>Status</label><br>
		<label class="radio-inline"><input type="radio" name="aktif" value="aktif" <?= $row->aktif === 'aktif' ? 'checked' : '' ?>> Aktif</label>
		<label class="radio-inline"><input type="radio" name="aktif" value="nonaktif" <?= $row->aktif !== 'aktif' ? 'checked' : '' ?>> Non Aktif</label>
	</div>
	<button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Save</button>
</form>
