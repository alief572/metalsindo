<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
    .panel-form {
        border-radius: 6px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.08);
        border: 1px solid #e3e6f0;
        margin-bottom: 20px;
    }
    .panel-form .panel-heading {
        background-color: #f8f9fc;
        border-bottom: 1px solid #e3e6f0;
        font-weight: bold;
        color: #4e73df;
        padding: 10px 15px;
    }
    .panel-form .panel-body {
        padding: 15px;
    }
    .form-group label {
        font-weight: 600;
        color: #333;
        font-size: 13px;
    }
    .table-detail-retur thead th {
        background-color: #f4f6f9;
        color: #333333;
        font-weight: 600;
        vertical-align: middle !important;
        border-bottom: 2px solid #d2d6de !important;
    }
    .table-detail-retur tbody td {
        vertical-align: middle !important;
    }
</style>

<div class="box box-primary">
    <div class="box-header with-border">
        <h3 class="box-title"><i class="fa fa-plus-circle text-primary"></i> Form Tambah Retur Pembelian</h3>
        <div class="box-tools pull-right">
            <a href="<?= base_url('retur_pembelian') ?>" class="btn btn-sm btn-default"><i class="fa fa-arrow-left"></i> Kembali</a>
        </div>
    </div>
    <form action="" id="frm-data" method="post" enctype="multipart/form-data">
        <div class="box-body">
            <div class="row">
                <!-- Panel Kiri: Informasi Supplier & Invoice Referensi -->
                <div class="col-md-6">
                    <div class="panel panel-default panel-form">
                        <div class="panel-heading">
                            <i class="fa fa-building-o"></i> Informasi Supplier & Referensi
                        </div>
                        <div class="panel-body">
                            <div class="form-group">
                                <label>Nama Supplier <span class="text-danger">*</span></label>
                                <select name="supplier" id="supplier" class="form-control select2 supplier" required>
                                    <option value="">-- Pilih Supplier --</option>
                                    <?php
                                    if (!empty($list_supplier)) {
                                        foreach ($list_supplier as $item_supplier) {
                                            echo '<option value="' . $item_supplier->id_suplier . '">' . $item_supplier->name_suplier . '</option>';
                                        }
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>No. Receive Invoice AP <span class="text-danger">*</span></label>
                                <select name="rec_inv_ap" class="form-control rec_inv_ap select2" required>
                                    <option value="">-- Pilih Receive Invoice AP --</option>
                                </select>
                                <input type="hidden" name="id_rec_inv_ap" id="id_rec_inv_ap_val">
                            </div>
                            <div class="form-group">
                                <label>No. Ref Invoice</label>
                                <div class="input-group">
                                    <span class="input-group-addon"><i class="fa fa-hashtag"></i></span>
                                    <input type="text" class="form-control" name="no_ref_invoice" placeholder="Otomatis terisi dari Receive Invoice AP" readonly>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Tanggal Invoice <span class="text-danger">*</span></label>
                                        <input type="date" class="form-control" name="tanggal_invoice" value="<?= date('Y-m-d') ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Tanggal Retur <span class="text-danger">*</span></label>
                                        <input type="date" class="form-control" name="tanggal_retur" value="<?= date('Y-m-d') ?>" required>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Panel Kanan: Informasi NG & Dokumen NCR -->
                <div class="col-md-6">
                    <div class="panel panel-default panel-form">
                        <div class="panel-heading">
                            <i class="fa fa-file-text-o"></i> Informasi Retur & Dokumen NCR
                        </div>
                        <div class="panel-body">
                            <div class="form-group">
                                <label>No. NG Report <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="no_ng_report" placeholder="Masukkan nomor NG report..." required>
                            </div>
                            <div class="form-group">
                                <label>Alasan Retur <span class="text-danger">*</span></label>
                                <textarea name="alasan_retur" rows="3" class="form-control" placeholder="Jelaskan alasan pengembalian barang..." required></textarea>
                            </div>
                            <div class="form-group">
                                <label>Upload File NCR / Berita Acara <span class="text-danger">*</span></label>
                                <input type="file" class="form-control" name="file_ba" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx" required>
                                <small class="text-muted"><i class="fa fa-info-circle"></i> Format yang didukung: PDF, JPG, PNG, DOC (Maks. 5MB)</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bagian Rincian Item PO / Receive Invoice AP -->
            <div class="panel panel-default panel-form">
                <div class="panel-heading">
                    <i class="fa fa-list"></i> Rincian Material yang Diretur
                </div>
                <div class="panel-body">
                    <div class="list_detail_po">
                        <div class="alert alert-info" style="margin-bottom: 0;">
                            <i class="fa fa-info-circle"></i> Silakan pilih <b>Supplier</b> dan <b>No. Receive Invoice AP</b> terlebih dahulu untuk memuat daftar material.
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="box-footer">
            <a href="<?= base_url('retur_pembelian') ?>" class="btn btn-default"><i class="fa fa-arrow-left"></i> Kembali</a>
            <button type="submit" class="btn btn-primary pull-right save_btn"><i class="fa fa-save"></i> Simpan Retur Pembelian</button>
        </div>
    </form>
</div>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="<?= base_url('assets/js/autoNumeric.js') ?>"></script>

<script>
    $(document).ready(function() {
        $('.select2').select2({
            width: '100%'
        });

        auto_num();
    });

    $(document).on('change', '.supplier', function() {
        var supplier = $(this).val();
        var $elRecInvAp = $('.rec_inv_ap'); // Simpan selector ke variabel agar lebih ringan

        // Reset dropdown Receive Invoice AP setiap kali supplier berubah
        $elRecInvAp.html('<option value="">-- Pilih Receive Invoice AP --</option>').trigger('change');
        $('#id_rec_inv_ap_val').val('');
        $('input[name="no_ref_invoice"]').val('');
        $('.list_detail_po').html('');

        if (!supplier) return; // Jangan tembak AJAX kalau supplier kosong

        $.ajax({
            type: 'get',
            url: siteurl + active_controller + 'getReceiveInvoiceAP',
            data: {
                'supplier': supplier
            },
            dataType: 'json',
            cache: false,
            success: function(response) {
                let html = '<option value="">-- Pilih Receive Invoice AP --</option>';

                // Pastikan response adalah array dan tidak kosong
                if (Array.isArray(response) && response.length > 0) {
                    response.forEach(item => {
                        html += `<option value="${item.id_rec_inv_ap}">${item.no_invoice} - ${item.tgl_bayar} (Total: ${item.total_nilai})</option>`;
                    });
                } else {
                    html = '<option value="">-- Tidak ada Receive Invoice AP --</option>';
                }

                $elRecInvAp.html(html);
                $elRecInvAp.trigger('change');
            },
            error: function(xhr, status, error) {
                // Ambil pesan error dari server jika ada
                let errorMsg = xhr.responseJSON ? xhr.responseJSON.message : error;

                Swal.fire({
                    icon: 'error',
                    title: 'Error !',
                    text: 'Gagal mengambil data Receive Invoice AP: ' + errorMsg,
                    showCancelButton: false,
                });
            }
        });
    });

    $(document).on('change', '.rec_inv_ap', function() {
        var id_rec_inv_ap = $(this).val();

        // Set hidden input value & auto populate No Ref Invoice
        $('#id_rec_inv_ap_val').val(id_rec_inv_ap);
        $('input[name="no_ref_invoice"]').val(id_rec_inv_ap);

        if (id_rec_inv_ap !== '' && id_rec_inv_ap !== null) {
            $.ajax({
                type: 'get',
                url: siteurl + active_controller + 'getDetailReceiveInvoiceAP',
                data: {
                    'id_rec_inv_ap': id_rec_inv_ap
                },
                cache: false,
                dataType: 'json',
                success: function(result) {
                    $('.list_detail_po').html(result.hasil);
                    auto_num();
                    hitungFooter();
                },
                error: function(xhr, status, error) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error !',
                        text: 'Oops! ' + error,
                        showCancelButton: false,
                        allowEscapeKey: false,
                        allowOutsideClick: false
                    });
                }
            });
        } else {
            $('.list_detail_po').html('');
        }
    });

    $(document).on('input change keyup', '.hitung_detail_total, #footer_ppn_persen', function() {
        hitungFooter();
    });

    $(document).on('submit', '#frm-data', function(e) {
        e.preventDefault();

        Swal.fire({
            icon: 'warning',
            title: 'Anda yakin ?',
            text: 'Pastikan data yang anda input sudah sesuai sebelum menyimpan !',
            showConfirmButton: true,
            showCancelButton: true,
            allowEscapeKey: false,
            allowOutsideClick: false
        }).then((next) => {
            if (next.isConfirmed) {
                var formdata = new FormData($('#frm-data')[0]);

                $.ajax({
                    type: 'post',
                    url: siteurl + active_controller + 'save_retur_pembelian',
                    data: formdata,
                    cache: false,
                    dataType: 'json',
                    contentType: false,
                    processData: false,
                    beforeSend: function(result) {
                        $('.save_btn').attr('disabled', true);
                    },
                    success: function(result) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success !',
                            text: 'Data Retur telah tersimpan !',
                            showConfirmButton: false,
                            showCancelButton: false,
                            allowEscapeKey: false,
                            allowOutsideClick: false,
                            timer: 3000
                        }).then(() => {
                            window.location.href = siteurl + active_controller;
                        });
                    },
                    error: function(xhr, status, error) {
                        $('.save_btn').attr('disabled', false);
                        Swal.fire({
                            icon: 'error',
                            title: 'Error !',
                            text: 'Oops ! ' + error
                        });
                    }
                });
            }
        });
    });

    function auto_num() {
        $('.auto_num').autoNumeric('init');
    }

    function hitungFooter() {
        var totalQtyReceive = 0;
        var totalRetur = 0;
        var subtotal = 0;

        $('.list_detail_po tbody tr').each(function() {
            var isSheetInput = $(this).find('input[data-is_sheet]');
            var isSheet = (isSheetInput.length > 0 && isSheetInput.data('is_sheet') == '1');

            var qtyReceiveInput = isSheet ? $(this).find('input[name$="[qty_sheet]"]') : $(this).find('input[name$="[qty_receive]"]');
            var returInput = isSheet ? $(this).find('input[name$="[retur_sheet]"]') : $(this).find('input[name$="[retur]"]');
            var hargaInput = $(this).find('input[name$="[harga]"]');
            var rowTotalInput = $(this).find('input.row_total_harga');

            var qtyRecVal = 0;
            if (qtyReceiveInput.length > 0 && qtyReceiveInput.val()) {
                qtyRecVal = parseFloat(qtyReceiveInput.val().split(',').join('')) || 0;
            }
            totalQtyReceive += qtyRecVal;

            var returVal = 0;
            if (returInput.length > 0 && returInput.val()) {
                returVal = parseFloat(returInput.val().split(',').join('')) || 0;
            }
            totalRetur += returVal;

            var hargaVal = 0;
            if (hargaInput.length > 0 && hargaInput.val()) {
                hargaVal = parseFloat(hargaInput.val().split(',').join('')) || 0;
            }

            var rowTotal = returVal * hargaVal;
            if (rowTotalInput.length > 0) {
                rowTotalInput.autoNumeric('set', rowTotal);
            }
            subtotal += rowTotal;
        });

        if ($('#footer_total_qty_receive').length > 0) {
            $('#footer_total_qty_receive').autoNumeric('set', totalQtyReceive);
        }
        if ($('#footer_total_retur').length > 0) {
            $('#footer_total_retur').autoNumeric('set', totalRetur);
        }
        if ($('#footer_subtotal').length > 0) {
            $('#footer_subtotal').autoNumeric('set', subtotal);
        }

        var ppnPersen = 0;
        if ($('#footer_ppn_persen').length > 0 && $('#footer_ppn_persen').val()) {
            ppnPersen = parseFloat($('#footer_ppn_persen').val()) || 0;
        }

        var nilaiPpn = (subtotal * ppnPersen) / 100;
        if ($('#footer_nilai_ppn').length > 0) {
            $('#footer_nilai_ppn').autoNumeric('set', nilaiPpn);
        }

        var grandTotal = subtotal + nilaiPpn;
        if ($('#footer_grand_total').length > 0) {
            $('#footer_grand_total').autoNumeric('set', grandTotal);
        }
    }
</script>