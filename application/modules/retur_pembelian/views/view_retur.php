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
    <div class="box-body">
        <div class="row">
            <div class="col-md-2">
                <span class="text-bold">Nama Supplier <span class="text-red">*</span></span>
            </div>
            <div class="col-md-4">
                <select name="supplier" id="" class="form-control select2 supplier" disabled>
                    <!-- <option value="">- Select Supplier -</option> -->
                    <?php
                    if (!empty($list_supplier)) {
                        foreach ($list_supplier as $item_supplier) {
                            if ($header->id_supplier == $item_supplier->id_suplier) {
                                echo '<option value="' . $item_supplier->id_suplier . '">' . $item_supplier->name_suplier . '</option>';
                            }
                        }
                    }
                    ?>
                </select>
            </div>
            <div class="col-md-2">
                <span class="text-bold">No. Ref Invoice</span>
            </div>
            <div class="col-md-4">
                <input type="text" class="form-control" name="no_ref_invoice" placeholder="No. Reference Invoice" value="<?= $header->no_ref_invoice ?>" readonly>
            </div>
            <div class="col-md-2">
                <span class="text-bold">No. PO</span>
            </div>
            <div class="col-md-4">
                <select class="form-control no_po select2" multiple="multiple" disabled>
                    <?php
                    if (strpos($header->no_po, ',') !== false) {
                        foreach (explode(',', $header->no_po) as $item_po) {
                            $get_po = $this->db->get_where('tr_purchase_order', ['no_po' => $item_po])->row();

                            $no_poo = (!empty($get_po)) ? $get_po->no_surat : '';
                            echo '<option value="' . $no_poo . '" selected>' . $no_poo . '</option>';
                        }
                    }
                    ?>
                    <!-- <option value="">- No. PO -</option> -->
                </select>
            </div>
            <div class="col-md-2">
                <span class="text-bold">Tanggal Invoice</span>
            </div>
            <div class="col-md-4">
                <input type="date" class="form-control" name="tanggal_invoice" value="<?= date('Y-m-d', strtotime($header->tgl_invoice)) ?>" readonly>
            </div>
            <div class="col-md-2">
                <span class="text-bold">Tanggal Retur</span>
            </div>
            <div class="col-md-4">
                <input type="date" class="form-control" name="tanggal_retur" value="<?= date('Y-m-d', strtotime($header->tgl_retur)) ?>" readonly>
            </div>
        </div>
        <div class="row">
            <div class="col-md-2">
                <span class="text-bold">No. NG Report</span>
            </div>
            <div class="col-md-4">
                <input type="text" class="form-control" name="no_ng_report" placeholder="No. NG Report" value="<?= $header->no_ng_report ?>" readonly>
            </div>
        </div>
        <div class="row">
            <div class="col-md-2">
                <span class="text-bold">Alasan Retur</span>
            </div>
            <div class="col-md-4">
                <textarea name="alasan_retur" id="" class="form-control" readonly><?= $header->alasan_retur ?></textarea>
            </div>
        </div>
        <div class="row">
            <div class="col-md-2">
                <span class="text-bold">File BA</span>
            </div>
            <div class="col-md-4">
                <input type="file" class="form-control" name="file_ba" readonly>
                <?php
                if (file_exists($header->file_ba)) {
                    echo '<a href="' . base_url($header->file_ba) . '" class="btn btn-sm btn-primary" title="Download file BA" target="_blank" download><i class="fa fa-download"></i> Download BA</a>';
                }
                ?>
            </div>
        </div>

        <div class="col-12-md list_detail_po">
            <?php
            foreach (explode(',', $header->no_po) as $item_po) {
                $get_po = $this->db->get_where('tr_purchase_order', ['no_po' => $item_po])->row();

                $po_detail = $this->Retur_pembelian_model->get_po_detail($get_po->no_po);

                $type_sheet = $this->Retur_pembelian_model->get_po_check_sheet($get_po->no_po);

                $satuan = ($type_sheet > 0) ? '(Sheet)' : '(Kg)';

                echo  '<h4>No. PO: ' . $get_po->no_surat . '</h4>';
                echo  '<table class="table table-striped table-bordered">';
                echo  '<thead>';
                echo  '<tr>';
                echo  '<th class="text-center">Tanggal PO</th>';
                echo  '<th class="text-center">Nama Material</th>';
                echo  '<th class="text-center">Width</th>';
                echo  '<th class="text-center">Qty Order ' . $satuan . '</th>';
                echo  '<th class="text-center">Qty Receive ' . $satuan . '</th>';
                echo  '<th class="text-center">Retur ' . $satuan . '</th>';
                echo  '<th class="text-center">Harga</th>';
                echo  '<th class="text-center">Total</th>';
                echo  '</tr>';
                echo  '</thead>';
                echo  '<tbody>';

                $no_detail = 0;
                foreach ($po_detail as $item_po_detail) {
                    $no_detail++;



                    echo '<tr>';
                    echo '<td class="text-center">';
                    echo '<input type="hidden" name="dt_' . $item_po_detail->no_po . '[' . $no_detail . '][id]" value="' . $item_po_detail->id . '">';
                    echo '<input type="hidden" name="dt_' . $item_po_detail->no_po . '[' . $no_detail . '][no_po]" value="' . $item_po_detail->no_po . '">';
                    echo '<input type="hidden" name="dt_' . $item_po_detail->no_po . '[' . $no_detail . '][id_pr]" value="' . $item_po_detail->idpr . '">';
                    echo '<input type="hidden" name="dt_' . $item_po_detail->no_po . '[' . $no_detail . '][idmaterial]" value="' . $item_po_detail->idmaterial . '">';
                    echo '<input type="hidden" name="dt_' . $item_po_detail->no_po . '[' . $no_detail . '][namamaterial]" value="' . $item_po_detail->namamaterial . '">';
                    echo '<input type="hidden" name="dt_' . $item_po_detail->no_po . '[' . $no_detail . '][width]" value="' . $item_po_detail->width . '">';
                    echo '<input type="hidden" name="dt_' . $item_po_detail->no_po . '[' . $no_detail . '][qty_order]" value="' . $item_po_detail->totalwidth . '">';
                    echo date('d F Y', strtotime($get_po->tanggal));
                    echo '</td>';
                    echo '<td>' . $item_po_detail->namamaterial . '</td>';
                    echo '<td class="text-right">' . $item_po_detail->width . '</td>';
                    echo '<td class="text-right">' . $item_po_detail->totalwidth . '</td>';
                    echo '<td class="text-right">';
                    echo number_format($arr_detail[$item_po_detail->id]->qty_receive, 2);
                    echo '</td>';
                    echo '<td class="text-right">';
                    echo number_format($arr_detail[$item_po_detail->id]->jumlah_retur, 2);
                    echo '</td>';
                    echo '<td class="text-right">';
                    echo number_format($arr_detail[$item_po_detail->id]->harga_satuan, 2);
                    echo '</td>';
                    echo '<td class="text-right">';
                    echo number_format($arr_detail[$item_po_detail->id]->grand_total, 2);
                    echo '</td>';
                    echo '</tr>';
                }

                echo '</tbody>';
                echo '</table>';
            }
            ?>
        </div>

        <a href="<?= base_url('retur_pembelian') ?>" class="btn btn-sm btn-danger"><i class="fa fa-arrow-left"></i> Back</a>
    </div>


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
        var $elNoPo = $('.no_po'); // Simpan selector ke variabel agar lebih ringan

        // Reset dropdown PO setiap kali supplier berubah
        $elNoPo.html('<option value="">-- Pilih PO --</option>').trigger('change');

        if (!supplier) return; // Jangan tembak AJAX kalau supplier kosong

        $.ajax({
            type: 'get',
            url: siteurl + active_controller + 'getPO',
            data: {
                'supplier': supplier
            },
            dataType: 'json', // Pastikan jQuery tahu kita ekspek JSON
            cache: false,
            success: function(response) {
                let html = '<option value="">-- Pilih PO --</option>';

                // Pastikan response adalah array dan tidak kosong
                if (Array.isArray(response) && response.length > 0) {
                    response.forEach(item => {
                        html += `<option value="${item.no_po}">${item.no_surat}</option>`;
                    });
                } else {
                    html = '<option value="">-- Tidak ada PO --</option>';
                }

                $elNoPo.html(html);
                $elNoPo.trigger('change');
            },
            error: function(xhr, status, error) {
                // Ambil pesan error dari server jika ada
                let errorMsg = xhr.responseJSON ? xhr.responseJSON.message : error;

                Swal.fire({
                    icon: 'error',
                    title: 'Error !',
                    text: 'Gagal mengambil data PO: ' + errorMsg,
                    showCancelButton: false,
                });
            }
        });
    });

    $(document).on('change', '.no_po', function() {
        var no_po = $(this).val();

        if (no_po !== '' && no_po !== null) {
            $.ajax({
                type: 'get',
                url: siteurl + active_controller + 'getDetailPO',
                data: {
                    'no_po': no_po
                },
                cache: false,
                dataType: 'json',
                success: function(result) {
                    $('.list_detail_po').html(result.hasil);
                    auto_num();
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
        }
    });

    $(document).on('change', '.hitung_detail_total', function() {
        var no_po = $(this).data('no_po');
        var no = $(this).data('no');

        var qty_retur = $('input[name="dt_' + no_po + '[' + no + '][retur]"]').val();
        if (qty_retur.length > 0) {
            qty_retur = qty_retur.split(',').join('');
            qty_retur = parseFloat(qty_retur);
        } else {
            qty_retur = 0;
        }

        var harga = $('input[name="dt_' + no_po + '[' + no + '][harga]"]').val();
        if (harga.length > 0) {
            harga = harga.split(',').join('');
            harga = parseFloat(harga);
        } else {
            harga = 0;
        }

        var total_harga = (qty_retur * harga);

        $('input[name="dt_' + no_po + '[' + no + '][total_harga]"]').autoNumeric('set', total_harga);
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
</script>