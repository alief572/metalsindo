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
        <input type="hidden" name="id_retur" value="<?= $header->id ?>">
        <div class="box-body">

            <div class="row">
                <div class="col-md-2">
                    <label for="">No. DN</label>
                </div>
                <div class="col-md-4">
                    <input type="text" name="" id="" class="form-control form-control-sm" value="New DN" readonly>
                </div>
            </div>
            <div class="row">
                <div class="col-md-2">
                    <label for="">Nama Supplier</label>
                </div>
                <div class="col-md-4">
                    <input type="text" name="" id="" class="form-control form-control-sm" value="<?= $header->nm_supplier ?>" readonly>
                </div>
            </div>
            <div class="row">
                <div class="col-md-2">
                    <label for="">Nomor PO</label>
                </div>
                <div class="col-md-4">
                    <input type="text" name="" id="" class="form-control form-control-sm" value="<?= $no_surat_po ?>" readonly>
                </div>
            </div>
            <div class="row">
                <div class="col-md-2">
                    <label for="">Tanggal Retur</label>
                </div>
                <div class="col-md-4">
                    <input type="date" name="" id="" class="form-control form-control-sm" value="<?= date('Y-m-d', strtotime($header->tgl_retur)) ?>" readonly>
                </div>
            </div>
            <div class="row">
                <div class="col-md-2">
                    <label for="">Alasan Retur</label>
                </div>
                <div class="col-md-4">
                    <textarea name="alasan_retur" id="" class="form-control form-control-sm" readonly><?= $header->alasan_retur ?></textarea>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <table class="table table-striped table-bordered">
                        <thead class="bg-primary">
                            <tr>
                                <th class="text-center">No.</th>
                                <th class="text-center">Item</th>
                                <th class="text-center">Qty</th>
                                <th class="text-center">Unit Price</th>
                                <th class="text-center">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $no = 0;

                            $total = 0;
                            foreach ($detail as $item) {
                                $no++;

                                echo '
                                        <tr>
                                            <td class="text-center">' . $no . '</td>
                                            <td>' . $item->nama_material . '</td>
                                            <td class="text-right">' . $item->jumlah_retur . '</td>
                                            <td class="text-right">' . number_format($item->harga_satuan, 2) . '</td>
                                            <td class="text-right">' . number_format($item->grand_total, 2) . '</td>
                                        </tr>
                                    ';

                                $total += $item->grand_total;
                            }
                            ?>
                        </tbody>
                        <tfoot class="bg-primary">
                            <tr>
                                <th class="text-right" colspan="4">Total</th>
                                <th class="text-right"><?= number_format($total, 2) ?></th>
                            </tr>
                            <tr>
                                <th class="text-right" colspan="4">PPn (Nominal)</th>
                                <th>
                                    <input type="hidden" name="total" value="<?= $total ?>">
                                    <input type="text" name="ppn" id="" class="form-control form-control-sm text-right auto_num hitung_grand_total">
                                </th>
                            </tr>
                            <tr>
                                <th class="text-right" colspan="4">Grand Total</th>
                                <th>
                                    <input type="text" name="grand_total" id="" class="form-control form-control-sm text-right auto_num" readonly>
                                </th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <a href="<?= base_url('retur_pmb_cn') ?>" class="btn btn-sm btn-danger"><i class="fa fa-arrow-left"></i> Back</a>
            <button type="submit" class="btn btn-sm btn-primary save_btn"><i class="fa fa-save"></i> Save DN</button>
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

    $(document).on('change', '.hitung_grand_total', function() {
        var ppn = $(this).val();
        if (ppn.length > 0) {
            ppn = ppn.split(',').join('');
            ppn = parseFloat(ppn);
        } else {
            ppn = 0;
        }

        var total = $('input[name="total"]').val();
        if (total.length > 0) {
            total = total.split(',').join('');
            total = parseFloat(total);
        } else {
            total = 0;
        }

        var grand_total = (total + ppn);

        $('input[name="grand_total"]').autoNumeric('set', grand_total);
    })

    $(document).on('submit', '#frm-data', function(e) {
        e.preventDefault();

        Swal.fire({
            icon: 'warning',
            title: 'Anda yakin ?',
            text: 'DN Retur akan terbit !',
            showConfirmButton: true,
            showCancelButton: true,
            allowEscapeKey: false,
            allowOutsideClick: false
        }).then((next) => {
            if (next.isConfirmed) {
                var formdata = $('#frm-data').serialize();

                $.ajax({
                    type: 'post',
                    url: siteurl + active_controller + 'save_dn_retur',
                    data: formdata,
                    cache: false,
                    dataType: 'json',
                    success: function(result) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success !',
                            text: result.msg,
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
                        // Cara ambil 'msg' dari PHP:
                        var errorMsg = "Terjadi kesalahan"; // default message

                        if (xhr.responseJSON && xhr.responseJSON.msg) {
                            errorMsg = xhr.responseJSON.msg;
                        }

                        Swal.fire({
                            icon: 'error',
                            title: 'Error !',
                            text: 'Oops! ' + errorMsg
                        });
                    }
                });
            }
        });
    });

    function auto_num() {
        $('.auto_num').autoNumeric('init');
    }
</script>