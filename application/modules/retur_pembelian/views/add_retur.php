<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
    input,
    textarea,
    select {
        margin: 0.5vh;
    }

    .form-control {
        border-radius: 10px;
    }
</style>
<div class="box">
    <form action="" id="frm-data" method="post" enctype="multipart/form-data">
        <div class="box-body">
            <div class="row">
                <div class="col-md-2">
                    <span class="text-bold">Nama Supplier <span class="text-red">*</span></span>
                </div>
                <div class="col-md-4">
                    <select name="supplier" id="" class="form-control select2 supplier">
                        <option value="">- Select Supplier -</option>
                        <?php
                        if (!empty($list_supplier)) {
                            foreach ($list_supplier as $item_supplier) {
                                echo '<option value="' . $item_supplier->id_suplier . '">' . $item_supplier->name_suplier . '</option>';
                            }
                        }
                        ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <span class="text-bold">No. Ref Invoice</span>
                </div>
                <div class="col-md-4">
                    <input type="text" class="form-control" name="no_ref_invoice" placeholder="No. Reference Invoice">
                </div>
                <div class="col-md-2">
                    <span class="text-bold">No. Receive Invoice AP</span>
                </div>
                <div class="col-md-4">
                    <select name="rec_inv_ap" class="form-control rec_inv_ap select2">
                        <option value="">-- Pilih Receive Invoice AP --</option>
                    </select>
                    <input type="hidden" name="id_rec_inv_ap" id="id_rec_inv_ap_val">
                </div>
                <div class="col-md-2">
                    <span class="text-bold">Tanggal Invoice</span>
                </div>
                <div class="col-md-4">
                    <input type="date" class="form-control" name="tanggal_invoice" value="<?= date('Y-m-d') ?>" required>
                </div>
                <div class="col-md-2">
                    <span class="text-bold">Tanggal Retur</span>
                </div>
                <div class="col-md-4">
                    <input type="date" class="form-control" name="tanggal_retur" value="<?= date('Y-m-d') ?>" required>
                </div>
            </div>
            <div class="row">
                <div class="col-md-2">
                    <span class="text-bold">No. NG Report</span>
                </div>
                <div class="col-md-4">
                    <input type="text" class="form-control" name="no_ng_report" placeholder="No. NG Report" required>
                </div>
            </div>
            <div class="row">
                <div class="col-md-2">
                    <span class="text-bold">Alasan Retur</span>
                </div>
                <div class="col-md-4">
                    <textarea name="alasan_retur" id="" class="form-control" required></textarea>
                </div>
            </div>
            <div class="row">
                <div class="col-md-2">
                    <span class="text-bold">File NCR</span>
                </div>
                <div class="col-md-4">
                    <input type="file" class="form-control" name="file_ba" required>
                </div>
            </div>

            <div class="col-12-md list_detail_po">

            </div>

            <a href="<?= base_url('retur_pembelian') ?>" class="btn btn-sm btn-danger"><i class="fa fa-arrow-left"></i> Back</a>
            <button type="submit" class="btn btn-sm btn-primary save_btn"><i class="fa fa-save"></i> Save Retur</button>
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

        // Set hidden input value
        $('#id_rec_inv_ap_val').val(id_rec_inv_ap);

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

    $(document).on('change', '.hitung_detail_total', function() {
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
        var totalQtyReceiveSheet = 0;
        var totalRetur = 0;
        var totalReturSheet = 0;

        $('.list_detail_po tbody tr').each(function() {
            var qtyReceiveInput = $(this).find('input[name$="[qty_receive]"]');
            var qtySheetInput = $(this).find('input[name$="[qty_sheet]"]');
            var returInput = $(this).find('input[name$="[retur]"]');
            var returSheetInput = $(this).find('input[name$="[retur_sheet]"]');

            if (qtyReceiveInput.length > 0) {
                var qtyVal = qtyReceiveInput.val();
                if (qtyVal && qtyVal.length > 0) {
                    totalQtyReceive += parseFloat(qtyVal.split(',').join('')) || 0;
                }
            }

            if (qtySheetInput.length > 0) {
                var qtySheetVal = qtySheetInput.val();
                if (qtySheetVal && qtySheetVal.length > 0) {
                    totalQtyReceiveSheet += parseFloat(qtySheetVal.split(',').join('')) || 0;
                }
            }

            if (returInput.length > 0) {
                var returVal = returInput.val();
                if (returVal && returVal.length > 0) {
                    totalRetur += parseFloat(returVal.split(',').join('')) || 0;
                }
            }

            if (returSheetInput.length > 0) {
                var returSheetVal = returSheetInput.val();
                if (returSheetVal && returSheetVal.length > 0) {
                    totalReturSheet += parseFloat(returSheetVal.split(',').join('')) || 0;
                }
            }
        });

        if ($('#footer_total_qty_receive').length > 0) {
            $('#footer_total_qty_receive').autoNumeric('set', totalQtyReceive);
        }
        if ($('#footer_total_qty_receive_sheet').length > 0) {
            $('#footer_total_qty_receive_sheet').autoNumeric('set', totalQtyReceiveSheet);
        }
        if ($('#footer_total_retur').length > 0) {
            $('#footer_total_retur').autoNumeric('set', totalRetur);
        }
        if ($('#footer_total_retur_sheet').length > 0) {
            $('#footer_total_retur_sheet').autoNumeric('set', totalReturSheet);
        }
    }
</script>