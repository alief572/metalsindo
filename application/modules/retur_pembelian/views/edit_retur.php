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
        <input type="hidden" name="id" value="<?= $header->id ?>">
        <input type="hidden" name="no_surat" value="<?= $header->no_surat ?>">
        <input type="hidden" name="no_po" value="<?= $header->no_po ?>">
        <div class="box-body">
            <div class="row">
                <div class="col-md-2">
                    <span class="text-bold">Nama Supplier <span class="text-red">*</span></span>
                </div>
                <div class="col-md-4">
                    <select name="supplier" id="" class="form-control select2 supplier" disabled>
                        <option value="">- Select Supplier -</option>
                        <?php
                        if (!empty($list_supplier)) {
                            foreach ($list_supplier as $item_supplier) {
                                if ($header->id_supplier == $item_supplier->id_suplier) {
                                    echo '<option value="' . $item_supplier->id_suplier . '" selected>' . $item_supplier->name_suplier . '</option>';
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
                    <input type="text" class="form-control" name="no_ref_invoice" placeholder="No. Reference Invoice" value="<?= $header->no_ref_invoice ?>">
                </div>
                <?php if (!empty($id_rec_inv_ap)) : ?>
                    <?php
                    // Requirement 6.1: show disabled Receive Invoice AP info
                    $rec_inv_ap_header = $this->db->get_where('tr_receive_invoice_ap_header', ['id_rec_inv_ap' => $id_rec_inv_ap])->row();
                    $no_invoice_edit = (!empty($rec_inv_ap_header)) ? $rec_inv_ap_header->no_invoice : $id_rec_inv_ap;
                    ?>
                    <div class="col-md-2">
                        <span class="text-bold">No. Receive Invoice AP</span>
                    </div>
                    <div class="col-md-4">
                        <input type="hidden" name="id_rec_inv_ap" value="<?= $id_rec_inv_ap ?>">
                        <input type="text" class="form-control" value="<?= $no_invoice_edit ?>" disabled>
                    </div>
                <?php else : ?>
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
                        </select>
                    </div>
                <?php endif; ?>
                <div class="col-md-2">
                    <span class="text-bold">Tanggal Invoice</span>
                </div>
                <div class="col-md-4">
                    <input type="date" class="form-control" name="tanggal_invoice" value="<?= date('Y-m-d', strtotime($header->tgl_invoice)) ?>" required>
                </div>
                <div class="col-md-2">
                    <span class="text-bold">Tanggal Retur</span>
                </div>
                <div class="col-md-4">
                    <input type="date" class="form-control" name="tanggal_retur" value="<?= date('Y-m-d', strtotime($header->tgl_retur)) ?>" required>
                </div>
            </div>
            <div class="row">
                <div class="col-md-2">
                    <span class="text-bold">No. NG Report</span>
                </div>
                <div class="col-md-4">
                    <input type="text" class="form-control" name="no_ng_report" placeholder="No. NG Report" value="<?= $header->no_ng_report ?>" required>
                </div>
            </div>
            <div class="row">
                <div class="col-md-2">
                    <span class="text-bold">Alasan Retur</span>
                </div>
                <div class="col-md-4">
                    <textarea name="alasan_retur" id="" class="form-control" required><?= $header->alasan_retur ?></textarea>
                </div>
            </div>
            <div class="row">
                <div class="col-md-2">
                    <span class="text-bold">File NCR</span>
                </div>
                <div class="col-md-4">
                    <input type="file" class="form-control" name="file_ba">
                </div>
            </div>

            <div class="col-12-md list_detail_po">
                <?php if (!empty($id_rec_inv_ap)) : ?>
                    <?php
                    // Requirement 6.2: render detail from dt_retur_pembelian (already stored) — editable fields
                    echo '<table class="table table-striped table-bordered">';
                    echo '<thead>';
                    echo '<tr>';
                    echo '<th class="text-center">Tanggal Incoming</th>';
                    echo '<th class="text-center">Lot Number</th>';
                    echo '<th class="text-center">Nama Material</th>';
                    echo '<th class="text-center">Width</th>';
                    echo '<th class="text-center">Qty Order</th>';
                    echo '<th class="text-center">Qty Rec (Kg)</th>';
                    echo '<th class="text-center">Qty Rec (Sheet)</th>';
                    echo '<th class="text-center">Retur (Kg)</th>';
                    echo '<th class="text-center">Retur (Sheet)</th>';
                    echo '</tr>';
                    echo '</thead>';
                    echo '<tbody>';

                    $no_detail = 0;
                    foreach ($detail as $item_detail) {
                        $no_detail++;

                        $material = $this->db->select('id_shapes, id_bentuk')->get_where('ms_inventory_category3', ['id_category3' => $item_detail->id_material])->row();
                        $is_sheet = (!empty($material) && $material->id_bentuk == 'B2000002');
                        $readonly_sheet = $is_sheet ? '' : 'readonly';

                        echo '<tr>';
                        echo '<td class="text-center">';
                        echo '<input type="hidden" name="dt_' . $item_detail->no_po . '[' . $no_detail . '][id]" value="' . $item_detail->id_detail_po . '">';
                        echo '<input type="hidden" name="dt_' . $item_detail->no_po . '[' . $no_detail . '][no_po]" value="' . $item_detail->no_po . '">';
                        echo '<input type="hidden" name="dt_' . $item_detail->no_po . '[' . $no_detail . '][id_pr]" value="' . $item_detail->id_pr . '">';
                        echo '<input type="hidden" name="dt_' . $item_detail->no_po . '[' . $no_detail . '][idmaterial]" value="' . $item_detail->id_material . '">';
                        echo '<input type="hidden" name="dt_' . $item_detail->no_po . '[' . $no_detail . '][namamaterial]" value="' . $item_detail->nama_material . '">';
                        echo '<input type="hidden" name="dt_' . $item_detail->no_po . '[' . $no_detail . '][width]" value="' . $item_detail->width . '">';
                        echo '<input type="hidden" name="dt_' . $item_detail->no_po . '[' . $no_detail . '][qty_order]" value="' . $item_detail->qty_order . '">';
                        echo '<input type="hidden" name="dt_' . $item_detail->no_po . '[' . $no_detail . '][lotno]" value="' . $item_detail->lotno . '">';
                        echo '<input type="hidden" name="dt_' . $item_detail->no_po . '[' . $no_detail . '][harga]" value="' . $item_detail->harga_satuan . '">';
                        // tanggal_incoming is not stored in dt_retur_pembelian; use tgl_retur as fallback
                        echo date('d F Y', strtotime($header->tgl_retur));
                        echo '</td>';
                        echo '<td>' . $item_detail->lotno . '</td>';
                        echo '<td>' . $item_detail->nama_material . '</td>';
                        echo '<td class="text-right">' . $item_detail->width . '</td>';
                        echo '<td class="text-right">' . $item_detail->qty_order . '</td>';
                        echo '<td>';
                        echo '<input type="text" class="form-control form-control-sm text-right auto_num" name="dt_' . $item_detail->no_po . '[' . $no_detail . '][qty_receive]" value="' . $item_detail->qty_receive . '" readonly>';
                        echo '</td>';
                        echo '<td>';
                        echo '<input type="text" class="form-control form-control-sm text-right auto_num" name="dt_' . $item_detail->no_po . '[' . $no_detail . '][qty_sheet]" value="' . $item_detail->qty_sheet . '" readonly>';
                        echo '</td>';
                        echo '<td>';
                        echo '<input type="text" class="form-control form-control-sm text-right auto_num hitung_detail_total" name="dt_' . $item_detail->no_po . '[' . $no_detail . '][retur]" data-no_po="' . $item_detail->no_po . '" data-no="' . $no_detail . '" value="' . $item_detail->jumlah_retur . '">';
                        echo '</td>';
                        echo '<td>';
                        echo '<input type="text" class="form-control form-control-sm text-right auto_num hitung_detail_total" name="dt_' . $item_detail->no_po . '[' . $no_detail . '][retur_sheet]" data-no_po="' . $item_detail->no_po . '" data-no="' . $no_detail . '" value="' . $item_detail->qty_sheet_retur . '" ' . $readonly_sheet . '>';
                        echo '</td>';
                        echo '</tr>';
                    }

                    echo '</tbody>';
                    echo '<tfoot>';
                    echo '<tr>';
                    echo '<td colspan="5" class="text-right text-bold">Grand Total</td>';
                    echo '<td><input type="text" class="form-control form-control-sm text-right auto_num" id="footer_total_qty_receive" readonly></td>';
                    echo '<td><input type="text" class="form-control form-control-sm text-right auto_num" id="footer_total_qty_receive_sheet" readonly></td>';
                    echo '<td><input type="text" class="form-control form-control-sm text-right auto_num" id="footer_total_retur" readonly></td>';
                    echo '<td><input type="text" class="form-control form-control-sm text-right auto_num" id="footer_total_retur_sheet" readonly></td>';
                    echo '</tr>';
                    echo '</tfoot>';
                    echo '</table>';
                    ?>
                <?php else : ?>
                    <?php
                    // Requirement 6.2 (backward compat): render from tr_purchase_order (old logic)
                    foreach (explode(',', $header->no_po) as $item_po) {
                        $get_po = $this->db->get_where('tr_purchase_order', ['no_po' => $item_po])->row();

                        $po_detail = $this->Retur_pembelian_model->get_po_detail($get_po->no_po);

                        echo  '<h4>No. PO: ' . $get_po->no_surat . '</h4>';
                        echo  '<table class="table table-striped table-bordered">';
                        echo  '<thead>';
                        echo  '<tr>';
                        echo  '<th class="text-center">Tanggal PO</th>';
                        echo  '<th class="text-center">Lot Number</th>';
                        echo  '<th class="text-center">Nama Material</th>';
                        echo  '<th class="text-center">Width</th>';
                        echo  '<th class="text-center">Qty Order</th>';
                        echo  '<th class="text-center">Qty Rec (Kg)</th>';
                        echo  '<th class="text-center">Qty Rec (Sheet)</th>';
                        echo  '<th class="text-center">Retur (Kg)</th>';
                        echo  '<th class="text-center">Retur (Sheet)</th>';
                        echo  '</tr>';
                        echo  '</thead>';
                        echo  '<tbody>';

                        $no_detail = 0;
                        foreach ($po_detail as $item_po_detail) {
                            $no_detail++;

                            $qty_receive = (!empty($arr_detail[$item_po_detail->id]->qty_receive)) ? $arr_detail[$item_po_detail->id]->qty_receive : 0;
                            $qty_sheet = (!empty($arr_detail[$item_po_detail->id]->qty_sheet)) ? $arr_detail[$item_po_detail->id]->qty_sheet : 0;
                            $jumlah_retur = (!empty($arr_detail[$item_po_detail->id]->jumlah_retur)) ? $arr_detail[$item_po_detail->id]->jumlah_retur : 0;
                            $qty_sheet_retur = (!empty($arr_detail[$item_po_detail->id]->qty_sheet_retur)) ? $arr_detail[$item_po_detail->id]->qty_sheet_retur : 0;
                            $lotno = (!empty($arr_detail[$item_po_detail->id]->lotno)) ? $arr_detail[$item_po_detail->id]->lotno : '';

                            $material = $this->db->select('id_shapes, id_bentuk')->get_where('ms_inventory_category3', ['id_category3' => $item_po_detail->idmaterial])->row();
                            $is_sheet = (!empty($material) && $material->id_bentuk == 'B2000002');
                            $readonly_sheet = $is_sheet ? '' : 'readonly';

                            echo '<tr>';
                            echo '<td class="text-center">';
                            echo '<input type="hidden" name="dt_' . $item_po_detail->no_po . '[' . $no_detail . '][id]" value="' . $item_po_detail->id . '">';
                            echo '<input type="hidden" name="dt_' . $item_po_detail->no_po . '[' . $no_detail . '][no_po]" value="' . $item_po_detail->no_po . '">';
                            echo '<input type="hidden" name="dt_' . $item_po_detail->no_po . '[' . $no_detail . '][id_pr]" value="' . $item_po_detail->idpr . '">';
                            echo '<input type="hidden" name="dt_' . $item_po_detail->no_po . '[' . $no_detail . '][idmaterial]" value="' . $item_po_detail->idmaterial . '">';
                            echo '<input type="hidden" name="dt_' . $item_po_detail->no_po . '[' . $no_detail . '][namamaterial]" value="' . $item_po_detail->namamaterial . '">';
                            echo '<input type="hidden" name="dt_' . $item_po_detail->no_po . '[' . $no_detail . '][width]" value="' . $item_po_detail->width . '">';
                            echo '<input type="hidden" name="dt_' . $item_po_detail->no_po . '[' . $no_detail . '][qty_order]" value="' . $item_po_detail->totalwidth . '">';
                            echo '<input type="hidden" name="dt_' . $item_po_detail->no_po . '[' . $no_detail . '][lotno]" value="' . $lotno . '">';
                            echo '<input type="hidden" name="dt_' . $item_po_detail->no_po . '[' . $no_detail . '][harga]" value="' . $harga_satuan . '">';
                            echo date('d F Y', strtotime($get_po->tanggal));
                            echo '</td>';
                            echo '<td>' . $lotno . '</td>';
                            echo '<td>' . $item_po_detail->namamaterial . '</td>';
                            echo '<td class="text-right">' . $item_po_detail->width . '</td>';
                            echo '<td class="text-right">' . $item_po_detail->totalwidth . '</td>';
                            echo '<td>';
                            echo '<input type="text" class="form-control form-control-sm text-right auto_num" name="dt_' . $item_po_detail->no_po . '[' . $no_detail . '][qty_receive]" value="' . $qty_receive . '" readonly>';
                            echo '</td>';
                            echo '<td>';
                            echo '<input type="text" class="form-control form-control-sm text-right auto_num" name="dt_' . $item_po_detail->no_po . '[' . $no_detail . '][qty_sheet]" value="' . $qty_sheet . '" readonly>';
                            echo '</td>';
                            echo '<td>';
                            echo '<input type="text" class="form-control form-control-sm text-right auto_num hitung_detail_total" name="dt_' . $item_po_detail->no_po . '[' . $no_detail . '][retur]" data-no_po="' . $item_po_detail->no_po . '" data-no="' . $no_detail . '" value="' . $jumlah_retur . '">';
                            echo '</td>';
                            echo '<td>';
                            echo '<input type="text" class="form-control form-control-sm text-right auto_num hitung_detail_total" name="dt_' . $item_po_detail->no_po . '[' . $no_detail . '][retur_sheet]" data-no_po="' . $item_po_detail->no_po . '" data-no="' . $no_detail . '" value="' . $qty_sheet_retur . '" ' . $readonly_sheet . '>';
                            echo '</td>';
                            echo '</tr>';
                        }

                        echo '</tbody>';
                        echo '<tfoot>';
                        echo '<tr>';
                        echo '<td colspan="5" class="text-right text-bold">Grand Total</td>';
                        echo '<td><input type="text" class="form-control form-control-sm text-right auto_num" id="footer_total_qty_receive" readonly></td>';
                        echo '<td><input type="text" class="form-control form-control-sm text-right auto_num" id="footer_total_qty_receive_sheet" readonly></td>';
                        echo '<td><input type="text" class="form-control form-control-sm text-right auto_num" id="footer_total_retur" readonly></td>';
                        echo '<td><input type="text" class="form-control form-control-sm text-right auto_num" id="footer_total_retur_sheet" readonly></td>';
                        echo '</tr>';
                        echo '</tfoot>';
                        echo '</table>';
                    }
                    ?>
                <?php endif; ?>
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
        hitungFooter();
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
                    url: siteurl + active_controller + 'update_retur_pembelian',
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