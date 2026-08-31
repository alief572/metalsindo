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
        color: #31708f;
        padding: 10px 15px;
    }
    .panel-form .panel-body {
        padding: 15px;
    }
    .table-detail-retur thead th {
        background-color: #3c8dbc;
        color: #ffffff;
        font-weight: 600;
        vertical-align: middle !important;
    }
    .table-detail-retur tbody td {
        vertical-align: middle !important;
    }
    .table-info-view {
        margin-bottom: 0;
    }
    .table-info-view tr td {
        padding: 7px 10px !important;
        border-top: 1px solid #f4f4f4 !important;
    }
    .table-info-view tr td:first-child {
        width: 35%;
        font-weight: 600;
        color: #555;
    }
</style>

<div class="box box-info">
    <div class="box-header with-border">
        <h3 class="box-title">
            <i class="fa fa-info-circle text-info"></i> Detail Retur Pembelian: 
            <span class="badge bg-aqua" style="font-size: 14px; font-weight: bold;"><?= $header->no_surat ?></span>
        </h3>
        <div class="box-tools pull-right">
            <a href="<?= base_url('retur_pembelian') ?>" class="btn btn-sm btn-default"><i class="fa fa-arrow-left"></i> Kembali</a>
        </div>
    </div>
    <div class="box-body">
        <div class="row">
            <!-- Panel Kiri: Informasi Supplier & Invoice Referensi -->
            <div class="col-md-6">
                <div class="panel panel-default panel-form">
                    <div class="panel-heading">
                        <i class="fa fa-building-o"></i> Informasi Supplier & Referensi
                    </div>
                    <div class="panel-body" style="padding: 0;">
                        <table class="table table-info-view">
                            <tr>
                                <td>Nama Supplier</td>
                                <td>: <b><?= $header->nm_supplier ?></b></td>
                            </tr>
                            <?php if (!empty($id_rec_inv_ap)) : ?>
                                <tr>
                                    <td>No. Receive Invoice AP</td>
                                    <td>: <span class="label label-primary" style="font-size: 12px;"><?= isset($no_invoice_rec_inv_ap) ? $no_invoice_rec_inv_ap : $id_rec_inv_ap ?></span></td>
                                </tr>
                            <?php else : ?>
                                <tr>
                                    <td>No. PO</td>
                                    <td>: <?= $header->no_po ?></td>
                                </tr>
                            <?php endif; ?>
                            <tr>
                                <td>No. Ref Invoice</td>
                                <td>: <?= (!empty($header->id_rec_inv_ap) ? $header->id_rec_inv_ap : $header->no_ref_invoice) ?></td>
                            </tr>
                            <tr>
                                <td>Tanggal Invoice</td>
                                <td>: <?= date('d F Y', strtotime($header->tgl_invoice)) ?></td>
                            </tr>
                            <tr>
                                <td>Tanggal Retur</td>
                                <td>: <?= date('d F Y', strtotime($header->tgl_retur)) ?></td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Panel Kanan: Informasi Retur & Dokumen NCR -->
            <div class="col-md-6">
                <div class="panel panel-default panel-form">
                    <div class="panel-heading">
                        <i class="fa fa-file-text-o"></i> Informasi Retur & Dokumen NCR
                    </div>
                    <div class="panel-body" style="padding: 0;">
                        <table class="table table-info-view">
                            <tr>
                                <td>No. NG Report</td>
                                <td>: <span class="label label-warning" style="font-size: 12px;"><?= $header->no_ng_report ?></span></td>
                            </tr>
                            <tr>
                                <td>Alasan Retur</td>
                                <td>: <?= nl2br(htmlspecialchars($header->alasan_retur)) ?></td>
                            </tr>
                            <tr>
                                <td>Dokumen NCR</td>
                                <td>: 
                                    <?php if (!empty($header->file_ba) && file_exists($header->file_ba)) : ?>
                                        <a href="<?= base_url($header->file_ba) ?>" class="btn btn-sm btn-primary" title="Download file NCR" target="_blank" download>
                                            <i class="fa fa-download"></i> Unduh Berkas NCR
                                        </a>
                                    <?php else : ?>
                                        <span class="text-muted"><i class="fa fa-times-circle text-danger"></i> Tidak ada file lampiran</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bagian Rincian Item Material -->
        <div class="panel panel-default panel-form">
            <div class="panel-heading">
                <i class="fa fa-list"></i> Rincian Material yang Diretur
            </div>
            <div class="panel-body">
                <div class="table-responsive">
                    <?php
                    $matauang = (!empty($header->matauang)) ? $header->matauang : 'IDR';
                    $curr_label = (strtoupper(trim($matauang)) === 'USD') ? 'USD' : ((strtoupper(trim($matauang)) === 'IDR' || strtoupper(trim($matauang)) === 'RP') ? 'Rp' : strtoupper(trim($matauang)));
                    ?>
                    <?php if (!empty($id_rec_inv_ap)) : ?>
                        <table class="table table-striped table-bordered table-hover table-detail-retur" style="font-size: 13px;">
                            <thead style="background-color: #2c3e50; color: #fff;">
                                <tr>
                                    <th class="text-center" style="width: 10%; vertical-align: middle;">Tgl Incoming</th>
                                    <th class="text-center" style="width: 12%; vertical-align: middle;">Lot Number</th>
                                    <th class="text-center" style="width: 18%; vertical-align: middle;">Nama Material</th>
                                    <th class="text-center" style="width: 7%; vertical-align: middle;">Width</th>
                                    <th class="text-center" style="width: 8%; vertical-align: middle;">Qty Order</th>
                                    <th class="text-center" style="width: 12%; vertical-align: middle;">Qty Rec</th>
                                    <th class="text-center" style="width: 12%; vertical-align: middle;">Qty Retur</th>
                                    <th class="text-center" style="width: 11%; vertical-align: middle;">Harga Satuan</th>
                                    <th class="text-center" style="width: 10%; vertical-align: middle;">Total Harga</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $total_rec_kg = 0;
                                $total_ret_kg = 0;
                                $calc_subtotal = 0;

                                foreach ($detail as $item_detail) {
                                    $material = !empty($item_detail->id_material) ? $this->db->select('id_bentuk')->get_where('ms_inventory_category3', ['id_category3' => $item_detail->id_material])->row() : null;
                                    $is_sheet = (!empty($material) && $material->id_bentuk == 'B2000002');
                                    $unit_label = $is_sheet ? 'Sheet' : 'KGS';
                                    $price_label = $is_sheet ? '/Sheet' : '/Kg';
                                    $qty_rec_val = $is_sheet ? $item_detail->qty_sheet : $item_detail->qty_receive;
                                    $qty_retur_val = $is_sheet ? $item_detail->qty_sheet_retur : $item_detail->jumlah_retur;
                                    $harga = (float) $item_detail->harga_satuan;
                                    $total_harga_item = !empty($item_detail->grand_total) ? (float) $item_detail->grand_total : ($qty_retur_val * $harga);

                                    $total_rec_kg += $qty_rec_val;
                                    $total_ret_kg += $qty_retur_val;
                                    $calc_subtotal += $total_harga_item;
                                ?>
                                    <tr>
                                        <td class="text-center" style="vertical-align: middle;"><?= date('d/m/Y', strtotime($header->tgl_retur)) ?></td>
                                        <td class="text-center" style="vertical-align: middle;"><span class="badge bg-gray text-bold" style="font-size: 11px;"><?= $item_detail->lotno ?></span></td>
                                        <td style="vertical-align: middle;"><b><?= $item_detail->nama_material ?></b></td>
                                        <td class="text-right" style="vertical-align: middle;"><?= number_format($item_detail->width, 2) ?></td>
                                        <td class="text-right" style="vertical-align: middle;"><?= number_format($item_detail->qty_order, 2) ?></td>
                                        <td class="text-right" style="vertical-align: middle;">
                                            <span class="badge bg-gray" style="font-size: 11px; font-weight: 500;">
                                                <?= ($is_sheet ? number_format($qty_rec_val) : number_format($qty_rec_val, 2)) ?> <?= $unit_label ?>
                                            </span>
                                        </td>
                                        <td class="text-right" style="vertical-align: middle;">
                                            <span class="badge bg-blue" style="font-size: 12px; font-weight: bold;">
                                                <?= ($is_sheet ? number_format($qty_retur_val) : number_format($qty_retur_val, 2)) ?> <?= $unit_label ?>
                                            </span>
                                        </td>
                                        <td class="text-right" style="vertical-align: middle;">
                                            <?= $curr_label ?> <?= number_format($harga, 2) ?>
                                            <small class="text-muted"><?= $price_label ?></small>
                                        </td>
                                        <td class="text-right text-bold" style="vertical-align: middle;"><?= $curr_label ?> <?= number_format($total_harga_item, 2) ?></td>
                                    </tr>
                                <?php
                                }

                                $subtotal_val = (!empty($header->subtotal)) ? (float) $header->subtotal : $calc_subtotal;
                                $ppn_persen_val = isset($header->ppn_persen) ? (float) $header->ppn_persen : 11;
                                $nilai_ppn_val = (!empty($header->nilai_ppn)) ? (float) $header->nilai_ppn : (($subtotal_val * $ppn_persen_val) / 100);
                                $grand_total_val = (!empty($header->grand_total)) ? (float) $header->grand_total : ($subtotal_val + $nilai_ppn_val);
                                ?>
                            </tbody>
                            <tfoot style="background-color: #fcfcfc;">
                                <tr>
                                    <td colspan="5" class="text-right text-bold" style="vertical-align: middle;">Total Qty</td>
                                    <td class="text-right text-bold" style="vertical-align: middle;"><?= number_format($total_rec_kg, 2) ?></td>
                                    <td class="text-right text-bold text-primary" style="vertical-align: middle;"><?= number_format($total_ret_kg, 2) ?></td>
                                    <td class="text-right text-bold" style="vertical-align: middle;">Subtotal</td>
                                    <td class="text-right text-bold" style="vertical-align: middle;"><?= $curr_label ?> <?= number_format($subtotal_val, 2) ?></td>
                                </tr>
                                <tr>
                                    <td colspan="8" class="text-right text-bold" style="vertical-align: middle;">PPN (<?= $ppn_persen_val ?>%)</td>
                                    <td class="text-right text-bold" style="vertical-align: middle;"><?= $curr_label ?> <?= number_format($nilai_ppn_val, 2) ?></td>
                                </tr>
                                <tr style="background-color: #f0f7fd; border-top: 2px solid #3c8dbc;">
                                    <td colspan="8" class="text-right text-bold" style="font-size: 15px; vertical-align: middle; color: #2c3e50;">Grand Total</td>
                                    <td class="text-right text-bold" style="font-size: 15px; vertical-align: middle; color: #2c3e50;"><?= $curr_label ?> <?= number_format($grand_total_val, 2) ?></td>
                                </tr>
                            </tfoot>
                        </table>
                    <?php else : ?>
                        <?php
                        // Backward compatibility
                        foreach (explode(',', $header->no_po) as $item_po) {
                            if (empty(trim($item_po))) continue;
                            $get_po = $this->db->get_where('tr_purchase_order', ['no_po' => trim($item_po)])->row();
                            if (empty($get_po)) continue;
                            $po_detail = $this->Retur_pembelian_model->get_po_detail($get_po->no_po);
                            if (empty($po_detail)) continue;

                            $po_matauang = (!empty($get_po->matauang)) ? $get_po->matauang : 'IDR';
                            $po_curr_label = (strtoupper(trim($po_matauang)) === 'USD') ? 'USD' : ((strtoupper(trim($po_matauang)) === 'IDR' || strtoupper(trim($po_matauang)) === 'RP') ? 'Rp' : strtoupper(trim($po_matauang)));
                        ?>
                            <h4>No. PO: <?= $get_po->no_surat ?> <span class="badge bg-blue"><?= $po_matauang ?></span></h4>
                            <table class="table table-striped table-bordered table-hover table-detail-retur" style="font-size: 13px;">
                                <thead style="background-color: #2c3e50; color: #fff;">
                                    <tr>
                                        <th class="text-center" style="width: 10%; vertical-align: middle;">Tanggal PO</th>
                                        <th class="text-center" style="width: 12%; vertical-align: middle;">Lot Number</th>
                                        <th class="text-center" style="width: 18%; vertical-align: middle;">Nama Material</th>
                                        <th class="text-center" style="width: 7%; vertical-align: middle;">Width</th>
                                        <th class="text-center" style="width: 8%; vertical-align: middle;">Qty Order</th>
                                        <th class="text-center" style="width: 12%; vertical-align: middle;">Qty Rec</th>
                                        <th class="text-center" style="width: 12%; vertical-align: middle;">Qty Retur</th>
                                        <th class="text-center" style="width: 11%; vertical-align: middle;">Harga Satuan</th>
                                        <th class="text-center" style="width: 10%; vertical-align: middle;">Total Harga</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $total_rec_kg = 0;
                                    $total_ret_kg = 0;
                                    $calc_subtotal = 0;

                                    $no_detail = 0;
                                    foreach ($po_detail as $item_po_detail) {
                                        $no_detail++;

                                        $qty_receive = (!empty($arr_detail[$item_po_detail->id]->qty_receive)) ? $arr_detail[$item_po_detail->id]->qty_receive : 0;
                                        $qty_sheet = (!empty($arr_detail[$item_po_detail->id]->qty_sheet)) ? $arr_detail[$item_po_detail->id]->qty_sheet : 0;
                                        $jumlah_retur = (!empty($arr_detail[$item_po_detail->id]->jumlah_retur)) ? $arr_detail[$item_po_detail->id]->jumlah_retur : 0;
                                        $qty_sheet_retur = (!empty($arr_detail[$item_po_detail->id]->qty_sheet_retur)) ? $arr_detail[$item_po_detail->id]->qty_sheet_retur : 0;
                                        $lotno = (!empty($arr_detail[$item_po_detail->id]->lotno)) ? $arr_detail[$item_po_detail->id]->lotno : '';
                                        $harga = (!empty($arr_detail[$item_po_detail->id]->harga_satuan)) ? $arr_detail[$item_po_detail->id]->harga_satuan : (float) $item_po_detail->hargasatuan;

                                        $material = !empty($item_po_detail->idmaterial) ? $this->db->select('id_bentuk')->get_where('ms_inventory_category3', ['id_category3' => $item_po_detail->idmaterial])->row() : null;
                                        $is_sheet = (!empty($material) && $material->id_bentuk == 'B2000002');
                                        $unit_label = $is_sheet ? 'Sheet' : 'KGS';
                                        $price_label = $is_sheet ? '/Sheet' : '/Kg';
                                        $qty_rec_val = $is_sheet ? $qty_sheet : $qty_receive;
                                        $qty_retur_val = $is_sheet ? $qty_sheet_retur : $jumlah_retur;
                                        $total_harga_item = $qty_retur_val * $harga;

                                        $total_rec_kg += $qty_rec_val;
                                        $total_ret_kg += $qty_retur_val;
                                        $calc_subtotal += $total_harga_item;
                                    ?>
                                        <tr>
                                            <td class="text-center" style="vertical-align: middle;"><?= date('d/m/Y', strtotime($get_po->tanggal)) ?></td>
                                            <td class="text-center" style="vertical-align: middle;"><span class="badge bg-gray text-bold" style="font-size: 11px;"><?= $lotno ?></span></td>
                                            <td style="vertical-align: middle;"><b><?= $item_po_detail->namamaterial ?></b></td>
                                            <td class="text-right" style="vertical-align: middle;"><?= number_format($item_po_detail->width, 2) ?></td>
                                            <td class="text-right" style="vertical-align: middle;"><?= number_format($item_po_detail->totalwidth, 2) ?></td>
                                            <td class="text-right" style="vertical-align: middle;">
                                                <span class="badge bg-gray" style="font-size: 11px; font-weight: 500;">
                                                    <?= ($is_sheet ? number_format($qty_rec_val) : number_format($qty_rec_val, 2)) ?> <?= $unit_label ?>
                                                </span>
                                            </td>
                                            <td class="text-right" style="vertical-align: middle;">
                                                <span class="badge bg-blue" style="font-size: 12px; font-weight: bold;">
                                                    <?= ($is_sheet ? number_format($qty_retur_val) : number_format($qty_retur_val, 2)) ?> <?= $unit_label ?>
                                                </span>
                                            </td>
                                            <td class="text-right" style="vertical-align: middle;">
                                                <?= $po_curr_label ?> <?= number_format($harga, 2) ?>
                                                <small class="text-muted"><?= $price_label ?></small>
                                            </td>
                                            <td class="text-right text-bold" style="vertical-align: middle;"><?= $po_curr_label ?> <?= number_format($total_harga_item, 2) ?></td>
                                        </tr>
                                    <?php
                                    }

                                    $subtotal_val = (!empty($header->subtotal)) ? (float) $header->subtotal : $calc_subtotal;
                                    $ppn_persen_val = isset($header->ppn_persen) ? (float) $header->ppn_persen : 11;
                                    $nilai_ppn_val = (!empty($header->nilai_ppn)) ? (float) $header->nilai_ppn : (($subtotal_val * $ppn_persen_val) / 100);
                                    $grand_total_val = (!empty($header->grand_total)) ? (float) $header->grand_total : ($subtotal_val + $nilai_ppn_val);
                                    ?>
                                </tbody>
                                <tfoot style="background-color: #fcfcfc;">
                                    <tr>
                                        <td colspan="5" class="text-right text-bold" style="vertical-align: middle;">Total Qty</td>
                                        <td class="text-right text-bold" style="vertical-align: middle;"><?= number_format($total_rec_kg, 2) ?></td>
                                        <td class="text-right text-bold text-primary" style="vertical-align: middle;"><?= number_format($total_ret_kg, 2) ?></td>
                                        <td class="text-right text-bold" style="vertical-align: middle;">Subtotal</td>
                                        <td class="text-right text-bold" style="vertical-align: middle;"><?= $po_curr_label ?> <?= number_format($subtotal_val, 2) ?></td>
                                    </tr>
                                    <tr>
                                        <td colspan="8" class="text-right text-bold" style="vertical-align: middle;">PPN (<?= $ppn_persen_val ?>%)</td>
                                        <td class="text-right text-bold" style="vertical-align: middle;"><?= $po_curr_label ?> <?= number_format($nilai_ppn_val, 2) ?></td>
                                    </tr>
                                    <tr style="background-color: #f0f7fd; border-top: 2px solid #3c8dbc;">
                                        <td colspan="8" class="text-right text-bold" style="font-size: 15px; vertical-align: middle; color: #2c3e50;">Grand Total</td>
                                        <td class="text-right text-bold" style="font-size: 15px; vertical-align: middle; color: #2c3e50;"><?= $po_curr_label ?> <?= number_format($grand_total_val, 2) ?></td>
                                    </tr>
                                </tfoot>
                            </table>
                        <?php } ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <div class="box-footer">
        <a href="<?= base_url('retur_pembelian') ?>" class="btn btn-default"><i class="fa fa-arrow-left"></i> Kembali</a>
    </div>
</div>