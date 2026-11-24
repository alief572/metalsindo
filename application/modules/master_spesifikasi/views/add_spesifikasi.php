<form id="data_form" data-url="master_spesifikasi/saveNewSpesifikasi">
	<div class="form-group">
		<label>Type Alloy <span class="text-red">*</span></label>
		<select name="id_category2" class="form-control" required>
			<option value="">-- Pilih Alloy --</option>
			<?php foreach ($results['alloys'] as $a) : ?>
				<option value="<?= html_escape($a->id_category2) ?>"><?= html_escape($a->nama) ?></option>
			<?php endforeach; ?>
		</select>
		<span class="help-block">Level 3. Spesifikasi hanya muncul di Level 4 untuk alloy yang sama.</span>
	</div>
	<div class="form-group">
		<label>Nama Spesifikasi <span class="text-red">*</span></label>
		<input type="text" name="nama" class="form-control" maxlength="50" required placeholder="Contoh: C2680, 304, SPCC-SD" style="text-transform:uppercase">
	</div>
	<button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Save</button>
</form>
