<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<input type="hidden" name="tipe_req" value="dp">
<input type="hidden" name="id_top" value="<?= $id_top ?>">
<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label for="">Nomor PO</label>
            <input type="text" name="nomor_po" id="" class="form-control form-control-sm nomor_po" value="<?= $data_po['no_surat'] ?>" readonly>
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label for="">Nama Supplier</label>
            <input type="text" name="nm_supplier" id="" class="form-control form-control-sm" value="<?= $get_supplier['name_suplier'] ?>" readonly>
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label for="">Receive Invoice Date <span class="text-red">*</span></label>
            <input type="date" name="invoice_date" id="" class="form-control form-control-sm" required>
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label for="">Persentase Progress (%)</label>
            <input type="number" name="persen_dp" id="" class="form-control form-control-sm persen_dp" step="0.01" value="<?= number_format($sisa_persen, 2) ?>">
            <input type="hidden" class="max_persen_dp" value="<?= $sisa_persen ?>">
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label for="">Invoice Date <span class="text-red">*</span></label>
            <input type="date" name="invoice_date_real" id="" class="form-control form-control-sm invoice_date_real change_tanggal_faktur_pajak" required>
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label for="">DPP</label>
            <input type="text" name="total_pembelian" id="" class="form-control form-control-sm text-right total_pembelian" value="<?= number_format($base_dpp, 2) ?>" readonly>
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label for="">Nomor Invoice <span class="text-red">*</span></label>
            <input type="text" name="nomor_invoice" id="" class="form-control form-control-sm nomor_invoice" required>
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label for="">Nilai PPn</label>
            <input type="text" name="nilai_ppn" id="" class="form-control form-control-sm text-right auto_num nilai_ppn" value="<?= number_format($nilai_ppn, 2) ?>" readonly>
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label for="">Nilai Disc</label>
            <input type="text" name="nilai_disc" id="" class="form-control form-control-sm text-right auto_num nilai_disc" value="<?= number_format($nilai_disc, 2) ?>" readonly>
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label for="">Value Progress</label>
            <input type="text" name="value_dp" id="" class="form-control form-control-sm text-right value_dp" value="<?= number_format($sisa_value) ?>">
            <input type="hidden" class="max_value_dp" value="<?= $sisa_value ?>">
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label for="">Currency</label>
            <input type="text" name="currency" id="" class="form-control form-control-sm" value="<?= $data_po['matauang'] ?>" readonly>
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label for="">Upload Invoice <span class="text-red">*</span></label>
            <input type="file" name="upload_invoice" id="" class="form-control form-control-sm upload_invoice" required>
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label for="">Kurs <span class="text-red">*</span></label>
            <input type="text" name="kurs" id="" class="form-control form-control-sm text-right auto_num" value="1" required>
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <b>Informasi Bank :</b>
            <label for="">Bank <span class="text-red">*</span></label>
            <input type="text" name="bank" id="" class="form-control form-control-sm" placeholder="- Bank -" value="<?= $get_supplier['name_bank'] ?>" required>
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label for="">Nomor Faktur Pajak <span class="text-red">*</span></label>
            <input type="text" name="nomor_faktur_pajak" id="" class="form-control form-control-sm nomor_faktur_pajak" required>
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label for="">No. Bank <span class="text-red">*</span></label>
            <input type="text" name="no_bank" id="" class="form-control form-control-sm" placeholder="- No. Bank -" value="<?= $get_supplier['no_rekening'] ?>" required>
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label for="">Tanggal Faktur Pajak</label>
            <input type="date" name="tanggal_faktur_pajak" id="" class="form-control form-control-sm tanggal_faktur_pajak">
        </div>
    </div>
    <div class="col-md-6"></div>
    <div class="col-md-6">
        <div class="form-group">
            <label for="">Nama <span class="text-red">*</span></label>
            <input type="text" name="nm_acc_bank" id="" class="form-control form-control-sm" placeholder="- Nama Acc Bank -" value="<?= $get_supplier['nama_rekening'] ?>" required>
        </div>
    </div>
    <br>
    <div class="col-md-12" style="display: none;">
        <h4>Jurnal Receive Invoice Progress</h4>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th class="text-center">Tanggal Jurnal</th>
                    <th class="text-center">Company</th>
                    <th class="text-center">Divisi</th>
                    <th class="text-center">COA</th>
                    <th class="text-center">Deskripsi</th>
                    <th class="text-center">Debit</th>
                    <th class="text-center">Kredit</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $ttl_debit = 0;
                $ttl_kredit = 0;
                if (isset($hasil_jurnal) && !empty($hasil_jurnal)) {
                    $no_jurnal = 0;
                    foreach ($hasil_jurnal as $item_jurnal) {
                        $no_jurnal++;

                        echo '<tr>';

                        echo '<td class="text-center">';
                        echo date('d F Y', strtotime($item_jurnal['tanggal_jurnal']));
                        echo '<input type="hidden" name="jurnal[' . $no_jurnal . '][tanggal_jurnal]" value="' . $item_jurnal['tanggal_jurnal'] . '">';
                        echo '</td>';

                        echo '<td class="text-center">';
                        echo $item_jurnal['nm_company'];
                        echo '<input type="hidden" name="jurnal[' . $no_jurnal . '][id_company]" value="' . $item_jurnal['id_company'] . '">';
                        echo '<input type="hidden" name="jurnal[' . $no_jurnal . '][nm_company]" value="' . $item_jurnal['nm_company'] . '">';
                        echo '</td>';

                        echo '<td class="text-center">';
                        echo $item_jurnal['nm_div'];
                        echo '<input type="hidden" name="jurnal[' . $no_jurnal . '][id_divisi]" value="' . $item_jurnal['id_div'] . '">';
                        echo '<input type="hidden" name="jurnal[' . $no_jurnal . '][nm_divisi]" value="' . $item_jurnal['nm_div'] . '">';
                        echo '</td>';

                        echo '<td class="text-left">';
                        echo $item_jurnal['nm_coa'];
                        echo '<input type="hidden" name="jurnal[' . $no_jurnal . '][id_coa]" value="' . $item_jurnal['id_coa'] . '">';
                        echo '<input type="hidden" name="jurnal[' . $no_jurnal . '][nm_coa]" value="' . $item_jurnal['nm_coa'] . '">';
                        echo '</td>';

                        echo '<td class="text-left">';
                        echo $item_jurnal['deskripsi'];
                        echo '<input type="hidden" name="jurnal[' . $no_jurnal . '][deskripsi]" value="' . $item_jurnal['deskripsi'] . '">';
                        echo '</td>';

                        echo '<td class="text-right">';
                        echo number_format($item_jurnal['debit']);
                        echo '<input type="hidden" name="jurnal[' . $no_jurnal . '][debit]" value="' . $item_jurnal['debit'] . '">';
                        echo '</td>';

                        echo '<td class="text-right">';
                        echo number_format($item_jurnal['kredit']);
                        echo '<input type="hidden" name="jurnal[' . $no_jurnal . '][kredit]" value="' . $item_jurnal['kredit'] . '">';
                        echo '</td>';

                        echo '</tr>';

                        $ttl_debit += $item_jurnal['debit'];
                        $ttl_kredit += $item_jurnal['kredit'];
                    }
                }
                ?>
            </tbody>
            <tfoot>
                <tr>
                    <th class="text-center" colspan="5">Balancing</th>
                    <th class="text-right"><?= number_format($ttl_debit) ?></th>
                    <th class="text-right"><?= number_format($ttl_kredit) ?></th>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="<?= base_url('assets/js/autoNumeric.js') ?>"></script>

<script>
    $(document).ready(function() {
        $('.auto_num').autoNumeric('init');

        $('.select2_modal').select2({
            width: '100%',
            dropdownParent: $('#dialog-popup')
        });
    });

    $(document).on('change', '.persen_dp', function() {
        var max_persen = parseFloat($('.max_persen_dp').val());
        var persen_dp = parseFloat($(this).val());

        if (persen_dp > max_persen) {
            Swal.fire({
                title: 'Peringatan!',
                text: 'Persentase tidak boleh melebihi sisa jatah TOP (' + max_persen + '%)',
                icon: 'warning'
            });
            $(this).val(max_persen);
            persen_dp = max_persen;
        }

        var total_pembelian = $('.total_pembelian').val();
        if (total_pembelian == '' || total_pembelian == null) {
            total_pembelian = 0;
        } else {
            total_pembelian = total_pembelian.split(',').join('');
            total_pembelian = parseFloat(total_pembelian);
        }

        var value_dp = (total_pembelian * persen_dp / 100);

        $('.value_dp').val(value_dp.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
    });

    $(document).on('change', '.value_dp', function() {
        var max_value = parseFloat($('.max_value_dp').val());
        var value_dp = $(this).val();
        
        if (value_dp == '' || value_dp == null) {
            value_dp = 0;
        } else {
            value_dp = value_dp.toString().split(',').join('');
            value_dp = parseFloat(value_dp);
        }

        if (value_dp > max_value) {
            Swal.fire({
                title: 'Peringatan!',
                text: 'Nominal tidak boleh melebihi sisa jatah TOP (' + max_value.toLocaleString() + ')',
                icon: 'warning'
            });
            value_dp = max_value;
            $(this).val(max_value.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
        }

        var total_pembelian = $('.total_pembelian').val();
        if (total_pembelian == '' || total_pembelian == null) {
            total_pembelian = 0;
        } else {
            total_pembelian = total_pembelian.split(',').join('');
            total_pembelian = parseFloat(total_pembelian);
        }

        var persen_dp = parseFloat((value_dp / total_pembelian) * 100);
        $('.value_dp').val(value_dp.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
        $('.persen_dp').val(persen_dp.toFixed(2));
    });

    $(document).on('change', '.change_tanggal_faktur_pajak', function() {
        var invoice_date = $(this).val();

        $('.tanggal_faktur_pajak').val(invoice_date);
    });
</script>