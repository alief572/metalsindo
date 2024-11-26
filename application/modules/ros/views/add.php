<?php
$ENABLE_ADD     = has_permission('ROS.Add');
$ENABLE_MANAGE  = has_permission('ROS.Manage');
$ENABLE_VIEW    = has_permission('ROS.View');
$ENABLE_DELETE  = has_permission('ROS.Delete');

$no_ros = (isset($header_ros)) ? $header_ros['id'] : 'new';
$no_po = (isset($header_ros)) ? $header_ros['no_po'] : null;
$nm_supplier = (isset($header_ros)) ? $header_ros['nm_supplier'] : null;
$awb_bl_date = (isset($header_ros)) ? $header_ros['awb_bl_date'] : null;
$awb_bl_number = (isset($header_ros)) ? $header_ros['awb_bl_number'] : null;
$eta_warehouse = (isset($header_ros)) ? $header_ros['eta_warehouse'] : null;
$kurs_pib = (isset($header_ros)) ? $header_ros['kurs_pib'] : 0;
$cost_bm = (isset($header_ros)) ? $header_ros['cost_bm'] : 0;
$cost_ppn = (isset($header_ros)) ? $header_ros['cost_ppn'] : 0;
$cost_pph = (isset($header_ros)) ? $header_ros['cost_pph'] : 0;
$freight_cost_persen = (isset($header_ros)) ? $header_ros['freight_cost'] : 0;
$no_pengajuan_pib = (isset($header_ros)) ? $header_ros['no_pengajuan_pib'] : null;
$no_billing = (isset($header_ros)) ? $header_ros['no_biling'] : null;
$id_supplier = (isset($header_ros)) ? $header_ros['id_supplier'] : null;
$standard_logic_cost = (isset($header_ros)) ? $header_ros['standard_logic_cost'] : 0.125;

$edit = ($no_ros !== 'new') ? 1 : 0;
?>
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style type="text/css">
    thead input {
        width: 100%;
    }
</style>
<div id='alert_edit' class="alert alert-success alert-dismissable" style="padding: 15px; display: none;"></div>
<link rel="stylesheet" href="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.css') ?>">

<input type="hidden" name="edit" class="edit" value="<?= $edit ?>">

<div class="box">
    <div class="box-body">
        <form action="" method="post" id="frm-data" enctype="multipart/form-data">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="">Nomor PO</label>
                        <?php
                        if ($no_ros == 'new') {
                        ?>
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th class="text-center">No. PO</th>
                                        <th class="text-center">Tipe PO</th>
                                        <th class="text-center">Action</th>
                                    </tr>
                                </thead>
                                <tbody class="no_po">
                                </tbody>
                            </table>
                        <?php
                        } else {
                            echo '<input type="text" name="no_po" class="form-control form-control-sm no_po" value="' . str_replace(',', ', ', $no_po) . '" readonly>';
                        }
                        ?>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="">Supplier Name</label>
                        <select name="supplier_name" id="" class="form-control form-control-sm select2 get_supplier">
                            <?php

                            if ($no_ros == 'new') {
                                echo '<option value="">- Supplier Name -</option>';
                            }

                            foreach ($list_supplier as $item) {
                                $selected = '';
                                if ($item->id_suplier == $id_supplier) {
                                    $selected = 'selected';
                                }

                                if ($no_ros !== 'new') {
                                    if ($selected !== '') {
                                        echo '<option value="' . $item->id_suplier . '" ' . $selected . '>' . $item->name_suplier . '</option>';
                                    }
                                } else {
                                    echo '<option value="' . $item->id_suplier . '" ' . $selected . '>' . $item->name_suplier . '</option>';
                                }
                            }
                            ?>
                        </select>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="">No. ROS</label>
                        <input type="text" name="no_ros" id="" class="form-control form-control-sm no_ros" value="<?= $no_ros ?>" readonly>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="">AWB / BL Date</label>
                        <input type="date" name="awb_bl_date" id="" class="form-control form-control-sm" value="<?= $awb_bl_date ?>" required>
                    </div>
                </div>
                <div class="col-md-6"></div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="">AWB / BL Number</label>
                        <input type="text" name="awb_bl_number" id="" class="form-control form-control-sm" value="<?= $awb_bl_number ?>">
                    </div>
                </div>
                <div class="col-md-6"></div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="">ETA Warehouse</label>
                        <input type="date" name="eta_warehouse" id="" class="form-control form-control-sm" value="<?= $eta_warehouse ?>" required>
                    </div>
                </div>
                <div class="col-md-6"></div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="">ATA POD</label>
                        <input type="date" name="ata_pod" id="" class="form-control form-control-sm" value="<?= $eta_warehouse ?>" required>
                    </div>
                </div>
                <div class="col-md-6"></div>
                <div class="col-md-6">
                    <label for="">Kurs PIB</label>
                    <input type="text" name="kurs_pib" id="" class="form-control form-control-sm auto_num kurs_pib" value="<?= $kurs_pib ?>" <?= ($no_ros !== 'new') ? 'readonly' : '' ?>>
                </div>
                <div class="col-md-6"></div>
                <div class="col-md-6">
                    <label for="">Standard Logic Cost</label>
                    <input type="number" name="standard_logic_cost" id="" class="form-control form-control-sm standard_logic_cost" step="0.000001" value="<?= $standard_logic_cost ?>" <?= ($no_ros !== 'new') ? 'readonly' : '' ?>>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12" style="margin-top: 1vh;">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th class="text-center">No.</th>
                                <th class="text-center">Nama Barang</th>
                                <th class="text-center">Unit Packing</th>
                                <th class="text-center">Currency</th>
                                <th class="text-center">Price/Unit</th>
                                <th class="text-center">Price/Unit (Rp)</th>
                                <th class="text-center">Weight</th>
                                <th class="text-center">In</th>
                                <th class="text-center">Sisa</th>
                                <th class="text-center">Weight Packing</th>
                                <th class="text-center">Bea Masuk</th>
                                <th class="text-center">Freight</th>
                                <th class="text-center">Total Price (Rp)</th>
                            </tr>
                        </thead>
                        <tbody class="list_detail_po">
                            <?php
                            $ttl_price_detail = 0;
                            $ttl_bm = 0;
                            $ttl_freight = 0;
                            if (isset($detail_ros)) {
                                $no = 1;
                                foreach ($detail_ros as $item) {
                                    $nilai_pengurang = 0;
                                    $this->db->select('IF(SUM(a.qty_packing_list) IS NULL, 0, SUM(a.qty_packing_list)) as nilai_pengurang');
                                    $this->db->from('tr_ros_detail a');
                                    $this->db->where('a.id_po_detail', $item['id_po_detail']);
                                    $this->db->where('a.no_ros <>', $item['no_ros']);
                                    $get_nilai_ros_used = $this->db->get()->row_array();
                                    if (!empty($get_nilai_ros_used)) {
                                        $nilai_pengurang += $get_nilai_ros_used['nilai_pengurang'];
                                    }

                                    echo '<tr>';
                                    echo '<td class="text-center">' . $no . '</td>';
                                    echo '<td class="text-center">' . $item['nm_barang'] . '</td>';
                                    echo '<td class="text-center">' . ucfirst($item['unit_satuan']) . '</td>';
                                    echo '<td class="text-center">' . strtoupper($item['currency']) . '</td>';
                                    echo '<td class="text-right">' . number_format($item['price_unit'], 2) . '</td>';
                                    echo '<td class="text-right">' . number_format($item['price_unit'] * $kurs_pib, 2) . '</td>';
                                    echo '<td class="text-center">' . $item['qty_po'] . '</td>';
                                    echo '<td class="text-center">';
                                    echo number_format($item['in_qty'], 2);
                                    echo '<input type="hidden" name="in_qty_' . $item['id_po_detail'] . '" value="' . $item['in_qty'] . '">';
                                    echo '</td>';
                                    echo '<td class="text-center">';
                                    echo number_format($item['sisa_qty'], 2);
                                    echo '<input type="hidden" name="sisa_qty_' . $item['id_po_detail'] . '" value="' . $item['sisa_qty'] . '">';
                                    echo '</td>';
                                    echo '<td class="text-center">';
                                    echo '<input type="text" class="form-control form-control-sm text-right weight_packing auto_num" name="weight_packing_' . $item['id_po_detail'] . '" value="' . $item['qty_packing_list'] . '" data-id="' . $item['id_po_detail'] . '">';
                                    echo '<input type="hidden" name="harga_satuan_' . $item['id_po_detail'] . '" value="' . ($item['price_unit']) . '">';
                                    echo '<input type="hidden" class="grand_total grand_total_' . $item['id_po_detail'] . '" value="' . (($item['qty_packing_list'] * $item['price_unit']) * $kurs_pib) . '">';
                                    echo '</td>';
                                    echo '<td>';
                                    echo '<input type="hidden" class="total_price" value="' . (($item['price_unit'] * $kurs_pib) * $item['qty_packing_list']) . '">';
                                    echo '<input type="hidden" name="weight_' . $item['id_po_detail'] . '" class="weight_' . $no . '" value="' . $item['qty_packing_list'] . '">';
                                    echo '<input type="text" class="form-control form-control-sm text-right auto_num nilai_bm" name="nilai_bm_' . $item['id_po_detail'] . '" value="' . $item['nilai_bm'] . '">';
                                    echo '</td>';
                                    echo '<td>';
                                    echo '<input type="text" class="form-control form-control-sm text-right auto_num nilai_freight" name="nilai_freight_' . $item['id_po_detail'] . '" value="' . $item['nilai_freight'] . '" readonly>';
                                    echo '</td>';
                                    echo '<td class="text-right total_price_' . $item['id_po_detail'] . '">' . number_format(($item['price_unit'] * $kurs_pib) * $item['qty_packing_list'], 2) . '</td>';
                                    echo '</tr>';

                                    $ttl_price_detail += (($item['price_unit'] * $kurs_pib) * $item['qty_packing_list']);
                                    $ttl_bm += $item['nilai_bm'];
                                    $ttl_freight += $item['nilai_freight'];
                                    $no++;
                                }
                            }
                            ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="10" align="right">
                                    <b>Grand Total</b>
                                </td>
                                <td align="right" class="ttl_bm_col"><?= number_format($ttl_bm, 2) ?></td>
                                <td align="right" class="ttl_freight_col"><?= number_format($ttl_freight, 2) ?></td>
                                <td align="right" class="ttl_price_detail_col"><?= number_format($ttl_price_detail, 2) ?></td>
                            </tr>
                        </tfoot>
                    </table>

                    <input type="hidden" name="ttl_total_price" class="ttl_total_price" value="<?= $ttl_price_detail ?>">
                    <input type="hidden" name="ttl_freight_cost" class="ttl_freight_cost" value="<?= $ttl_freight ?>">
                </div>
            </div>

            <div class="row">
                <div class="col-md-5" style="margin-top: 2vh;">
                    <h4>F&C Cost Estimation</h4>
                    <br>
                    <h4>Pemberitahuan Import Barang</h4>
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th class="text-center">No.</th>
                                <th class="text-center">Item Pembiayaan</th>
                                <th class="text-center">Cost</th>
                                <th class="text-center"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="text-center">1</td>
                                <td class="text-center">BM</td>
                                <td class="">
                                    <input type="text" name="cost_bm" id="" class="form-control form-control-sm input_bm text-right auto_num" value="<?= $cost_bm ?>" readonly>
                                </td>
                                <td></td>
                            </tr>
                            <tr>
                                <td class="text-center">2</td>
                                <td class="text-center">PPN</td>
                                <td class="">
                                    <input type="text" name="cost_ppn" id="" class="form-control form-control-sm input_ppn text-right auto_num" value="<?= $cost_ppn ?>">
                                </td>
                                <td></td>
                            </tr>
                            <tr>
                                <td class="text-center">3</td>
                                <td class="text-center">PPH</td>
                                <td class="">
                                    <input type="text" name="cost_pph" id="" class="form-control form-control-sm input_pph text-right auto_num" value="<?= $cost_pph ?>">
                                </td>
                                <td></td>
                            </tr>
                        </tbody>
                        <tbody class="list_custom_pib">
                            <?php
                            $no = 4;
                            $ttl_custom_pib = 0;
                            foreach ($list_custom_pib as $item) {
                                echo '<tr>';
                                echo '<td class="text-center">' . $no . '</td>';
                                echo '<td class="text-center">' . $item['nm_item_pembiayaan'] . '</td>';
                                echo '<td class="text-center">
                                        <input type="text" name="" id="" class="form-control form-control-sm text-right auto_num cost_pib_custom cost_pib_custom_' . $item['id'] . '" data-id="' . $item['id'] . '" value="' . $item['nilai_cost'] . '">
                                    </td>';
                                echo '<td class="text-center">
                                        <button type="button" class="btn btn-sm btn-danger del_custom_pib" data-id="' . $item['id'] . '"><i class="fa fa-trash"></i></button>
                                    </td>';
                                echo '</tr>';

                                $ttl_custom_pib += $item['nilai_cost'];

                                $no++;
                            }
                            ?>
                        </tbody>
                        <tbody>
                            <tr>
                                <td class="text-center">

                                </td>
                                <td>
                                    <input type="text" name="" id="" class="form-control form-control-sm biaya_name">
                                </td>
                                <td>
                                    <input type="text" name="" id="" class="form-control form-control-sm auto_num text-right cost_biaya">
                                </td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-success add_custom_pembiayaan">
                                        <i class="fa fa-plus"></i>
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                        <tbody>
                            <tr>
                                <td class="text-center" colspan="2">
                                    <b>TOTAL</b>
                                </td>
                                <td class="text-right total_pib"><?= number_format($cost_bm + $cost_ppn + $cost_pph + $ttl_custom_pib) ?></td>
                                <td></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="col-md-4">
                    <br><br><br>
                    <table class="w-100" border="0">
                        <tr>
                            <th>Upload PIB</th>
                            <th>
                                <input type="file" name="upload_pib" id="" class="form-control form-control-sm">
                            </th>
                        </tr>
                    </table>
                </div>
                <div class="col-md-7" style="margin-top: 2vh;">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="no_pengajuan_pib">Nomor Pengajuan PIB</label>
                                <input type="text" name="no_pengajuan_pib" id="no_pengajuan_pib" class="form-control form-control-sm" value="<?= $no_pengajuan_pib ?>">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="no_billing">Nomor Billing</label>
                                <input type="text" name="no_billing" id="no_billing" class="form-control form-control-sm" value="<?= $no_billing ?>">
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="">Keterangan</label>
                                <textarea name="keterangan" id="" cols="30" rows="5" class="form-control form-control-sm"><?= isset($header_ros) ? $header_ros['keterangan'] : null ?></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-10"></div>

                <div class="col-md-12">
                    <a href="<?= base_url('./ros') ?>" class="btn btn-sm btn-danger"><i class="fa fa-arrow-left"></i> Back</a>
                    <button type="submit" class="btn btn-sm btn-success" name="save"><i class="fa fa-save"></i> Save</button>
                </div>
            </div>
        </form>
    </div>
</div>


<div class="modal modal-default fade" id="dialog-popup" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" style='width:90%; '>
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title" id="myModalLabel">Default</h4>
            </div>
            <div class="modal-body" id="ModalView">
                ...
            </div>
        </div>
    </div>

    <!-- DataTables -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="<?= base_url('assets/plugins/datatables/jquery.dataTables.min.js') ?>"></script>
    <script src="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.min.js') ?>"></script>
    <script src="<?= base_url('assets/js/autoNumeric.js') ?>"></script>

    <!-- page script -->
    <script type="text/javascript">
        $('.select2').select2();
        $('.auto_num').autoNumeric('init');



        $(document).on('click', '.no_po', function() {
            var no_po = [];
            $('.no_po').each(function() {
                var val = $(this).val();
                if ($(this).prop('checked')) {
                    no_po.push(val);
                }
            });
            var no_po = no_po.join(',');

            var kurs_pib = $('.kurs_pib').val();
            if (kurs_pib == '' || kurs_pib == null) {
                kurs_pib = 0;
            } else {
                kurs_pib = kurs_pib.split(",").join("");
                kurs_pib = parseFloat(kurs_pib);
            }

            get_list_detail_po(no_po, kurs_pib);
            ttl_price();
        });

        $(document).on('change', '.qty_packing_list', function() {
            var id = $(this).data('id');
            var harga_satuan = $(this).data('harga_satuan');
            var kurs_pib = $('.kurs_pib').val();
            if (kurs_pib == '' || kurs_pib == null) {
                kurs_pib = 1
            } else {
                kurs_pib = kurs_pib.split(',').join('');
                kurs_pib = parseFloat(kurs_pib);
            }

            var nilai = $(this).val();
            if (nilai == '' || nilai == null) {
                nilai = 0;
            } else {
                nilai = nilai.split(",").join("");
                nilai = parseFloat(nilai);
            }

            var total = ((harga_satuan * kurs_pib) * nilai);
            var totala = total.toLocaleString('en-US', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });

            $('.total_price_' + id).html(totala);
            ttl_price();
        });

        $(document).on('change', '.kurs_pib', function() {
            var no_po = [];
            $('.no_po').each(function() {
                var val = $(this).val();
                if ($(this).prop('checked')) {
                    no_po.push(val);
                }
            });
            var no_po = no_po.join(',');
            var kurs_pib = $(this).val();
            if (kurs_pib == '' || kurs_pib == null) {
                kurs_pib = 1
            } else {
                kurs_pib = kurs_pib.split(',').join('');
                kurs_pib = parseFloat(kurs_pib);
            }

            get_list_detail_po(no_po, kurs_pib);
        });

        $(document).on('change', '.input_bm', function() {
            hitung_pib();
        });
        $(document).on('change', '.input_ppn', function() {
            hitung_pib();
        });
        $(document).on('change', '.input_pph', function() {
            hitung_pib();
        });

        $(document).on('click', '.add_custom_pembiayaan', function(e) {
            e.preventDefault();

            var no_ros = $('.no_ros').val();
            var biaya_name = $('.biaya_name').val();
            var cost_biaya = $('.cost_biaya').val();
            if (cost_biaya == '' || cost_biaya == null) {
                cost_biaya = 0
            } else {
                cost_biaya = cost_biaya.split(',').join('');
                cost_biaya = parseFloat(cost_biaya);
            }

            $.ajax({
                type: "POST",
                url: siteurl + active_controller + '/add_custom_pembiayaan',
                data: {
                    'no_ros': no_ros,
                    'biaya_name': biaya_name,
                    'cost_biaya': cost_biaya
                },
                cache: false,
                dataType: 'json',
                beforeSend: function(result) {
                    $('.add_custom_pembiayaan').html('<i class="fa fa-spinner fa-spin"></i>');
                },
                success: function(result) {
                    // if (result.status == '1') {
                    //     swal({
                    //         title: 'Success !',
                    //         text: 'Data has been saved !',
                    //         type: 'success'
                    //     });
                    // } else {
                    //     swal({
                    //         title: 'Failed !',
                    //         text: 'Data has not been saved !',
                    //         type: 'error'
                    //     });
                    // }
                    refresh_list_pib();
                    hitung_pib();

                    $('.add_custom_pembiayaan').html('<i class="fa fa-plus"></i>');
                },
                error: function(result) {
                    swal({
                        title: 'Error !',
                        text: 'Please try again later !',
                        type: 'error'
                    });

                    $('.add_custom_pembiayaan').html('<i class="fa fa-plus"></i>');
                }
            });
        });

        $(document).on('click', '.del_custom_pib', function() {
            var id = $(this).data('id');
            var no_ros = $('.no_ros').val();

            $.ajax({
                type: 'POST',
                url: siteurl + active_controller + '/del_custom_pib',
                data: {
                    'id': id,
                    'no_ros': no_ros
                },
                cache: false,
                dataType: 'json',
                success: function(result) {

                    refresh_list_pib();
                    hitung_pib();
                },
                error: function(result) {
                    swal({
                        title: 'Error !',
                        text: 'Please try again later !',
                        type: 'error'
                    });
                }
            });
        });

        $(document).on('change', '.freight_cost_persen', function() {
            var ttl_total_price = $('.ttl_total_price').val();
            if (ttl_total_price == '' || ttl_total_price == null) {
                ttl_total_price = 1;
            } else {
                ttl_total_price = ttl_total_price.split(',').join('');
                ttl_total_price = parseFloat(ttl_total_price);
            }

            var persen = $(this).val();

            var nilai_freight = 0;
            if (persen > 0) {
                var nilai_freight = (ttl_total_price * persen / 100);
            }

            $('.freight_cost').val(nilai_freight);
            $('.freight_cost_val').html(nilai_freight.toLocaleString('en-US', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }));
        });

        $(document).on('change', '.get_supplier', function() {
            var supplier = $(this).val();

            $.ajax({
                type: 'POST',
                url: siteurl + active_controller + 'get_po_by_supplier',
                data: {
                    'supplier': supplier
                },
                cache: false,
                success: function(result) {
                    $('.no_po').html(result);
                    // $('.select2').select2();
                },
                error: function(result) {
                    swal({
                        title: 'Error !',
                        text: 'Please try again later !',
                        type: 'error'
                    });
                }
            });
        });

        $(document).on('submit', '#frm-data', function(e) {
            e.preventDefault();

            var kurs_pib = $('.kurs_pib').val();
            if (kurs_pib == '' || kurs_pib == null) {
                kurs_pib = 0;
            } else {
                kurs_pib = kurs_pib.split(',').join('');
                kurs_pib = parseFloat(kurs_pib);
            }

            var valid = 1;

            // var test_bm = 0;
            // $('.nilai_bm').each(function() {
            //     var nilai = $(this).val();

            //     if (nilai !== '') {
            //         nilai = nilai.split(',').join();
            //         nilai = parseFloat(nilai);
            //     } else {
            //         nilai = 0;
            //     }

            //     test_bm += nilai;
            // });

            // var test_freight = 0;
            // $('.nilai_freight').each(function() {
            //     var nilai = $(this).val();

            //     if (nilai !== '') {
            //         nilai = nilai.split(',').join();
            //         nilai = parseFloat(nilai);
            //     } else {
            //         nilai = 0;
            //     }

            //     test_freight += nilai;
            // });

            // if (test_bm > 0 && test_freight > 0) {
            //     valid = 1;
            // }


            if (valid <= 0) {
                swal({
                    title: 'Warning !',
                    text: 'Please make sure all BM and Freight Cost has been inputed !',
                    type: 'warning'
                });
            } else {
                swal({
                        title: "Warning !",
                        text: "Data will be saved !",
                        type: "warning",
                        showCancelButton: true,
                        confirmButtonColor: "#DD6B55",
                        confirmButtonText: "Save",
                        cancelButtonText: "Cancel",
                        closeOnConfirm: false,
                        closeOnCancel: true
                    },
                    function(isConfirm) {
                        if (isConfirm) {
                            // $('#simpanpenerimaan').hide();
                            var formdata = new FormData($('#frm-data')[0]);
                            $.ajax({
                                type: 'POST',
                                url: siteurl + active_controller + '/save_ros',
                                data: formdata,
                                cache: false,
                                dataType: 'json',
                                processData: false,
                                contentType: false,
                                success: function(result) {
                                    if (result.status == '1') {
                                        swal({
                                            title: 'Success !',
                                            text: 'Success, ROS has been saved !',
                                            type: 'success'
                                        });

                                        window.location.href = siteurl + active_controller;
                                    } else {
                                        swal({
                                            title: 'Failed !',
                                            text: result.msg,
                                            type: 'error'
                                        });
                                    }
                                },
                                error: function(result) {
                                    swal({
                                        title: 'Error !',
                                        text: 'Please try again later !',
                                        type: 'error'
                                    });
                                }
                            });
                        }
                    });
            }
        });

        $(document).on('change', '.nilai_bm', function() {
            ttl_price();
        });

        $(document).on('change', '.weight_packing', function() {
            var id = $(this).data('id');

            var weight_packing = $(this).val();
            if (weight_packing !== '') {
                weight_packing = weight_packing.split(',').join('');
                weight_packing = parseFloat(weight_packing);
            } else {
                weight_packing = 0;
            }

            var harga_satuan = $('input[name="harga_satuan_' + id + '"]').val();

            var kurs_pib = $('.kurs_pib').val();
            if (kurs_pib !== '') {
                kurs_pib = kurs_pib.split(',').join('');
                kurs_pib = parseFloat(kurs_pib);
            } else {
                kurs_pib = 0;
            }

            var grand_total = ((harga_satuan * kurs_pib) * weight_packing);

            $('.total_price_' + id).html(number_format(grand_total, 2));
            $('.grand_total_' + id).val(grand_total);

            var ttl_grand_total = 0;
            $('.grand_total').each(function() {
                var nilai = $(this).val();
                nilai = parseFloat(nilai);

                ttl_grand_total += nilai;
            });

            $('.ttl_price_detail_col').html(number_format(ttl_grand_total, 2));
        })

        function get_list_detail_po(no_po = null, kurs_pib = 1) {
            var standard_logic_cost = $('.standard_logic_cost').val();

            var edit = $('.edit').val();

            $.ajax({
                type: "POST",
                url: siteurl + active_controller + 'get_no_po_detail',
                data: {
                    'no_po': no_po,
                    'kurs_pib': kurs_pib,
                    'standard_logic_cost': standard_logic_cost,
                    'weight_packing': 0,
                    'edit': edit
                },
                cache: false,
                dataType: 'json',
                success: function(result) {
                    $('.list_detail_po').html(result.list_detail_pr);
                    $('.ttl_price_detail_col').html(number_format(result.ttl_price_detail, 2));

                    $('.ttl_freight_col').html(number_format(result.ttl_nilai_freight, 2));
                    $('.ttl_freight_cost').val(result.ttl_nilai_freight);

                    $('.auto_num').autoNumeric();
                },
                error: function(result) {
                    swal({
                        title: 'Error !',
                        text: 'Please try again later !',
                        type: 'error'
                    });
                }
            });

            ttl_price();
        }

        function hitung_pib() {
            var no_ros = $('.no_ros').val();

            var bm = $('.input_bm').val();
            if (bm == '' || bm == null) {
                bm = 0
            } else {
                bm = bm.split(',').join('');
                bm = parseFloat(bm);
            }

            var ppn = $('.input_ppn').val();
            if (ppn == '' || ppn == null) {
                ppn = 0
            } else {
                ppn = ppn.split(',').join('');
                ppn = parseFloat(ppn);
            }

            var pph = $('.input_pph').val();
            if (pph == '' || pph == null) {
                pph = 0
            } else {
                pph = pph.split(',').join('');
                pph = parseFloat(pph);
            }

            var total_pib = (bm + ppn + pph);
            var total_pib_custom = 0;
            $.ajax({
                type: 'POST',
                url: siteurl + active_controller + '/hitung_custom_pib',
                data: {
                    'no_ros': no_ros
                },
                cache: false,
                dataType: 'json',
                success: function(result) {
                    total_pib += result.ttl_custom_pib;
                    var totalpib = total_pib.toLocaleString('en-US', {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    });

                    $('.total_pib').html(totalpib);
                },
                error: function(result) {
                    swal({
                        title: 'Error !',
                        text: 'Please try again later !',
                        type: 'error'
                    });
                }
            });




        }

        function refresh_list_pib() {
            var no_ros = $('.no_ros').val();

            $.ajax({
                type: 'POST',
                url: siteurl + active_controller + '/refresh_list_pib',
                data: {
                    'no_ros': no_ros
                },
                cache: false,
                dataType: 'json',
                success: function(result) {
                    $('.list_custom_pib').html(result.hasil);
                    hitung_pib();

                    $('.auto_num').autoNumeric();
                },
                error: function(result) {
                    swal({
                        title: 'Error !',
                        text: 'Please try again later !',
                        type: 'error'
                    });
                }
            });
        }

        function ttl_price() {
            var kurs_pib = $('.kurs_pib').val();
            // alert(kurs_pib);
            if (kurs_pib == '' || kurs_pib == null) {
                kurs_pib = 1;
            } else {
                kurs_pib = kurs_pib.split(',').join('');
                kurs_pib = parseFloat(kurs_pib);
            }

            var ttl_price = 0;
            $('.total_price').each(function() {
                var total_price = $(this).val();

                if (total_price == '') {
                    total_price = 0;
                } else {
                    total_price = total_price.split(',').join('');
                    total_price = parseFloat(total_price);
                }

                ttl_price += total_price;
            });

            var ttl_bm = 0;
            $('.nilai_bm').each(function() {
                var nilai = $(this).val();

                if (nilai !== '') {
                    nilai = nilai.split(',').join('');
                    nilai = parseFloat(nilai);
                } else {
                    nilai = 0;
                }

                ttl_bm += nilai;
            });

            var ttl_freight = 0;
            $('.nilai_freight').each(function() {
                var nilai = $(this).val();

                if (nilai !== '') {
                    nilai = nilai.split(',').join('');
                    nilai = parseFloat(nilai);
                } else {
                    nilai = 0;
                }

                ttl_freight += nilai;
            });

            // alert(ttl_freight);

            $('.ttl_total_price').val(ttl_price);
            $('.ttl_price_detail_col').html(ttl_price.toLocaleString('en-US', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }));

            $('.ttl_bm_col').html(ttl_bm.toLocaleString('en-US', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }));

            $('.ttl_freight_col').html(ttl_freight.toLocaleString('en-US', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }));


            $('.ttl_freight_cost').val(ttl_freight);

            $('.input_bm').val(ttl_bm.toLocaleString('en-US', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }));

            hitung_pib();
        }

        function number_format(number, decimals, dec_point, thousands_sep) {
            // Strip all characters but numerical ones.
            number = (number + '').replace(/[^0-9+\-Ee.]/g, '');
            var n = !isFinite(+number) ? 0 : +number,
                prec = !isFinite(+decimals) ? 0 : Math.abs(decimals),
                sep = (typeof thousands_sep === 'undefined') ? ',' : thousands_sep,
                dec = (typeof dec_point === 'undefined') ? '.' : dec_point,
                s = '',
                toFixedFix = function(n, prec) {
                    var k = Math.pow(10, prec);
                    return '' + Math.round(n * k) / k;
                };
            // Fix for IE parseFloat(0.55).toFixed(0) = 0;
            s = (prec ? toFixedFix(n, prec) : '' + Math.round(n)).split('.');
            if (s[0].length > 3) {
                s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, sep);
            }
            if ((s[1] || '').length < prec) {
                s[1] = s[1] || '';
                s[1] += new Array(prec - s[1].length + 1).join('0');
            }
            return s.join(dec);
        }
    </script>