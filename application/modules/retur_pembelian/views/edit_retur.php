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
        color: #e08e0b;
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

<div class="box box-warning">
    <div class="box-header with-border">
        <h3 class="box-title">
            <i class="fa fa-pencil-square-o text-warning"></i> Edit Retur Pembelian: 
            <span class="badge bg-yellow" style="font-size: 14px; font-weight: bold;"><?= $header->no_surat ?></span>
        </h3>
        <div class="box-tools pull-right">
            <a href="<?= base_url('retur_pembelian') ?>" class="btn btn-sm btn-default"><i class="fa fa-arrow-left"></i> Kembali</a>
        </div>
    </div>
    <form action="" id="frm-data" method="post" enctype="multipart/form-data">
        <input type="hidden" name="id" value="<?= $header->id ?>">
        <input type="hidden" name="no_surat" value="<?= $header->no_surat ?>">
        <input type="hidden" name="no_po" value="<?= $header->no_po ?>">
        <div class="box-body">
            <div class="row">
                <!-- Panel Kiri: Informasi Supplier & Referensi -->
                <div class="col-md-6">
                    <div class="panel panel-default panel-form">
                        <div class="panel-heading">
                            <i class="fa fa-building-o"></i> Informasi Supplier & Referensi
                        </div>
                        <div class="panel-body">
                            <div class="form-group">
                                <label>Nama Supplier</label>
                                <select name="supplier" id="" class="form-control select2 supplier" disabled>
                                    <option value="">-- Pilih Supplier --</option>
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
                            <?php if (!empty($id_rec_inv_ap)) : ?>
                                <?php
                                $rec_inv_ap_header = $this->db->get_where('tr_receive_invoice_ap_header', ['id_rec_inv_ap' => $id_rec_inv_ap])->row();
                                $no_invoice_edit = (!empty($rec_inv_ap_header)) ? $rec_inv_ap_header->no_invoice : $id_rec_inv_ap;
                                ?>
                                <div class="form-group">
                                    <label>No. Receive Invoice AP</label>
                                    <input type="hidden" name="id_rec_inv_ap" value="<?= $id_rec_inv_ap ?>">
                                    <input type="text" class="form-control" value="<?= $no_invoice_edit ?>" disabled>
                                </div>
                            <?php else : ?>
                                <div class="form-group">
                                    <label>No. PO</label>
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
                            <div class="form-group">
                                <label>No. Ref Invoice</label>
                                <div class="input-group">
                                    <span class="input-group-addon"><i class="fa fa-hashtag"></i></span>
                                    <input type="text" class="form-control" name="no_ref_invoice" placeholder="No. Reference Invoice" value="<?= (!empty($header->id_rec_inv_ap) ? $header->id_rec_inv_ap : $header->no_ref_invoice) ?>" readonly>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Tanggal Invoice <span class="text-danger">*</span></label>
                                        <input type="date" class="form-control" name="tanggal_invoice" value="<?= date('Y-m-d', strtotime($header->tgl_invoice)) ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Tanggal Retur <span class="text-danger">*</span></label>
                                        <input type="date" class="form-control" name="tanggal_retur" value="<?= date('Y-m-d', strtotime($header->tgl_retur)) ?>" required>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Panel Kanan: Informasi Retur & Dokumen NCR -->
                <div class="col-md-6">
                    <div class="panel panel-default panel-form">
                        <div class="panel-heading">
                            <i class="fa fa-file-text-o"></i> Informasi Retur & Dokumen NCR
                        </div>
                        <div class="panel-body">
                            <div class="form-group">
                                <label>No. NG Report <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="no_ng_report" placeholder="No. NG Report" value="<?= $header->no_ng_report ?>" required>
                            </div>
                            <div class="form-group">
                                <label>Alasan Retur <span class="text-danger">*</span></label>
                                <textarea name="alasan_retur" rows="3" class="form-control" required><?= $header->alasan_retur ?></textarea>
                            </div>
                            <div class="form-group">
                                <label>File NCR / Berita Acara (Kosongkan jika tidak diubah)</label>
                                <input type="file" class="form-control" name="file_ba" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx">
                                <?php if (!empty($header->file_ba) && file_exists($header->file_ba)) : ?>
                                    <div style="margin-top: 6px;">
                                        <a href="<?= base_url($header->file_ba) ?>" target="_blank" class="btn btn-xs btn-info"><i class="fa fa-download"></i> Unduh File NCR Saat Ini</a>
                                    </div>
                                <?php endif; ?>
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
                <?php
                $matauang = (!empty($header->matauang)) ? $header->matauang : 'IDR';
                $curr_label = (strtoupper(trim($matauang)) === 'USD') ? 'USD' : ((strtoupper(trim($matauang)) === 'IDR' || strtoupper(trim($matauang)) === 'RP') ? 'Rp' : strtoupper(trim($matauang)));
                echo '<input type="hidden" name="matauang" id="matauang" value="' . $matauang . '">';
                ?>
                <?php if (!empty($id_rec_inv_ap)) : ?>
                    <?php
                    echo '<div class="table-responsive">';
                    echo '<table class="table table-striped table-bordered table-hover table-detail-retur" style="font-size: 13px;">';
                    echo '<thead style="background-color: #2c3e50; color: #fff;">';
                    echo '<tr>';
                    echo '<th class="text-center" style="width: 10%; vertical-align: middle;">Tgl Incoming</th>';
                    echo '<th class="text-center" style="width: 12%; vertical-align: middle;">Lot Number</th>';
                    echo '<th class="text-center" style="width: 18%; vertical-align: middle;">Nama Material</th>';
                    echo '<th class="text-center" style="width: 7%; vertical-align: middle;">Width</th>';
                    echo '<th class="text-center" style="width: 8%; vertical-align: middle;">Qty Order</th>';
                    echo '<th class="text-center" style="width: 12%; vertical-align: middle;">Qty Rec</th>';
                    echo '<th class="text-center" style="width: 12%; vertical-align: middle;">Qty Retur</th>';
                    echo '<th class="text-center" style="width: 11%; vertical-align: middle;">Harga Satuan</th>';
                    echo '<th class="text-center" style="width: 10%; vertical-align: middle;">Total Harga</th>';
                    echo '</tr>';
                    echo '</thead>';
                    echo '<tbody>';

                    $no_detail = 0;
                    foreach ($detail as $item_detail) {
                        $no_detail++;

                        $material = !empty($item_detail->id_material) ? $this->db->select('id_bentuk')->get_where('ms_inventory_category3', ['id_category3' => $item_detail->id_material])->row() : null;
                        $is_sheet = (!empty($material) && $material->id_bentuk == 'B2000002');
                        $unit_label = $is_sheet ? 'Sheet' : 'KGS';
                        $price_label = $is_sheet ? '/Sheet' : '/Kg';
                        $qty_rec_val = $is_sheet ? $item_detail->qty_sheet : $item_detail->qty_receive;
                        $qty_retur_val = $is_sheet ? $item_detail->qty_sheet_retur : $item_detail->jumlah_retur;
                        $harga = (float) $item_detail->harga_satuan;
                        $total_harga_item = $qty_retur_val * $harga;

                        echo '<tr>';
                        echo '<td class="text-center" style="vertical-align: middle;">';
                        echo '<input type="hidden" name="dt_' . $item_detail->no_po . '[' . $no_detail . '][id]" value="' . $item_detail->id_detail_po . '">';
                        echo '<input type="hidden" name="dt_' . $item_detail->no_po . '[' . $no_detail . '][no_po]" value="' . $item_detail->no_po . '">';
                        echo '<input type="hidden" name="dt_' . $item_detail->no_po . '[' . $no_detail . '][id_pr]" value="' . $item_detail->id_pr . '">';
                        echo '<input type="hidden" name="dt_' . $item_detail->no_po . '[' . $no_detail . '][idmaterial]" value="' . $item_detail->id_material . '">';
                        echo '<input type="hidden" name="dt_' . $item_detail->no_po . '[' . $no_detail . '][namamaterial]" value="' . $item_detail->nama_material . '">';
                        echo '<input type="hidden" name="dt_' . $item_detail->no_po . '[' . $no_detail . '][width]" value="' . $item_detail->width . '">';
                        echo '<input type="hidden" name="dt_' . $item_detail->no_po . '[' . $no_detail . '][qty_order]" value="' . $item_detail->qty_order . '">';
                        echo '<input type="hidden" name="dt_' . $item_detail->no_po . '[' . $no_detail . '][lotno]" value="' . $item_detail->lotno . '">';
                        echo '<input type="hidden" name="dt_' . $item_detail->no_po . '[' . $no_detail . '][is_sheet]" value="' . ($is_sheet ? '1' : '0') . '">';
                        echo '<input type="hidden" name="dt_' . $item_detail->no_po . '[' . $no_detail . '][matauang]" value="' . $matauang . '">';
                        echo date('d/m/Y', strtotime($header->tgl_retur));
                        echo '</td>';
                        echo '<td class="text-center" style="vertical-align: middle;"><span class="badge bg-gray text-bold" style="font-size: 11px;">' . $item_detail->lotno . '</span></td>';
                        echo '<td style="vertical-align: middle;"><b>' . $item_detail->nama_material . '</b></td>';
                        echo '<td class="text-right" style="vertical-align: middle;">' . number_format($item_detail->width, 2) . '</td>';
                        echo '<td class="text-right" style="vertical-align: middle;">' . number_format($item_detail->qty_order, 2) . '</td>';
                        
                        // Qty Receive with input-group
                        echo '<td style="vertical-align: middle;">';
                        echo '<div class="input-group input-group-sm">';
                        if ($is_sheet) {
                            echo '<input type="text" class="form-control text-right auto_num" name="dt_' . $item_detail->no_po . '[' . $no_detail . '][qty_sheet]" value="' . $item_detail->qty_sheet . '" readonly style="background-color: #f9f9f9; font-weight: 500;">';
                            echo '<input type="hidden" name="dt_' . $item_detail->no_po . '[' . $no_detail . '][qty_receive]" value="' . $item_detail->qty_receive . '">';
                        } else {
                            echo '<input type="text" class="form-control text-right auto_num" name="dt_' . $item_detail->no_po . '[' . $no_detail . '][qty_receive]" value="' . $item_detail->qty_receive . '" readonly style="background-color: #f9f9f9; font-weight: 500;">';
                            echo '<input type="hidden" name="dt_' . $item_detail->no_po . '[' . $no_detail . '][qty_sheet]" value="' . $item_detail->qty_sheet . '">';
                        }
                        echo '<span class="input-group-addon" style="font-size: 10px; font-weight: bold; background: #eee; min-width: 45px;">' . $unit_label . '</span>';
                        echo '</div>';
                        echo '</td>';

                        // Qty Retur with input-group
                        echo '<td style="vertical-align: middle;">';
                        echo '<div class="input-group input-group-sm">';
                        if ($is_sheet) {
                            echo '<input type="text" class="form-control text-right auto_num hitung_detail_total" name="dt_' . $item_detail->no_po . '[' . $no_detail . '][retur_sheet]" data-no_po="' . $item_detail->no_po . '" data-no="' . $no_detail . '" data-is_sheet="1" value="' . $item_detail->qty_sheet_retur . '" style="border-color: #3c8dbc; font-weight: bold; color: #3c8dbc;">';
                            echo '<input type="hidden" name="dt_' . $item_detail->no_po . '[' . $no_detail . '][retur]" value="' . $item_detail->jumlah_retur . '">';
                        } else {
                            echo '<input type="text" class="form-control text-right auto_num hitung_detail_total" name="dt_' . $item_detail->no_po . '[' . $no_detail . '][retur]" data-no_po="' . $item_detail->no_po . '" data-no="' . $no_detail . '" data-is_sheet="0" value="' . $item_detail->jumlah_retur . '" style="border-color: #3c8dbc; font-weight: bold; color: #3c8dbc;">';
                            echo '<input type="hidden" name="dt_' . $item_detail->no_po . '[' . $no_detail . '][retur_sheet]" value="0">';
                        }
                        echo '<span class="input-group-addon" style="font-size: 10px; font-weight: bold; background: #3c8dbc; color: #fff; min-width: 45px;">' . $unit_label . '</span>';
                        echo '</div>';
                        echo '</td>';

                        // Harga Satuan with input-group
                        echo '<td style="vertical-align: middle;">';
                        echo '<div class="input-group input-group-sm">';
                        echo '<span class="input-group-addon" style="font-size: 10px; background: #eee; min-width: 32px;">' . $curr_label . '</span>';
                        echo '<input type="text" class="form-control text-right auto_num" name="dt_' . $item_detail->no_po . '[' . $no_detail . '][harga]" value="' . $harga . '" readonly style="background-color: #f9f9f9;">';
                        echo '<span class="input-group-addon" style="font-size: 10px; background: #eee; min-width: 45px;">' . $price_label . '</span>';
                        echo '</div>';
                        echo '</td>';

                        // Total Harga with input-group
                        echo '<td style="vertical-align: middle;">';
                        echo '<div class="input-group input-group-sm">';
                        echo '<span class="input-group-addon" style="font-size: 10px; background: #eee; min-width: 32px;">' . $curr_label . '</span>';
                        echo '<input type="text" class="form-control text-right auto_num row_total_harga" name="dt_' . $item_detail->no_po . '[' . $no_detail . '][total_harga]" value="' . $total_harga_item . '" readonly style="background-color: #f9f9f9; font-weight: bold;">';
                        echo '</div>';
                        echo '</td>';

                        echo '</tr>';
                    }

                    $ppn_persen_val = isset($header->ppn_persen) ? $header->ppn_persen : 11;

                    echo '</tbody>';
                    echo '<tfoot style="background-color: #fcfcfc;">';
                    echo '<tr>';
                    echo '<td colspan="5" class="text-right text-bold" style="vertical-align: middle;">Total Qty</td>';
                    echo '<td style="vertical-align: middle;"><input type="text" class="form-control form-control-sm text-right auto_num" id="footer_total_qty_receive" readonly style="background: transparent; border: none; font-weight: bold;"></td>';
                    echo '<td style="vertical-align: middle;"><input type="text" class="form-control form-control-sm text-right auto_num" id="footer_total_retur" readonly style="background: transparent; border: none; font-weight: bold; color: #3c8dbc;"></td>';
                    echo '<td class="text-right text-bold" style="vertical-align: middle;">Subtotal</td>';
                    echo '<td style="vertical-align: middle;">';
                    echo '<div class="input-group input-group-sm">';
                    echo '<span class="input-group-addon" style="font-size: 10px; background: #eee; min-width: 32px;">' . $curr_label . '</span>';
                    echo '<input type="text" class="form-control text-right auto_num text-bold" id="footer_subtotal" name="subtotal" readonly style="background-color: #f9f9f9;">';
                    echo '</div>';
                    echo '</td>';
                    echo '</tr>';
                    echo '<tr>';
                    echo '<td colspan="8" class="text-right text-bold" style="vertical-align: middle;">';
                    echo '<div style="display: flex; justify-content: flex-end; align-items: center;">';
                    echo '<span style="margin-right: 10px;">PPN</span>';
                    echo '<div class="input-group input-group-sm" style="width: 100px;">';
                    echo '<input type="number" step="any" min="0" max="100" class="form-control text-right" id="footer_ppn_persen" name="ppn_persen" value="' . $ppn_persen_val . '" style="font-weight: bold;">';
                    echo '<span class="input-group-addon">%</span>';
                    echo '</div>';
                    echo '</div>';
                    echo '</td>';
                    echo '<td style="vertical-align: middle;">';
                    echo '<div class="input-group input-group-sm">';
                    echo '<span class="input-group-addon" style="font-size: 10px; background: #eee; min-width: 32px;">' . $curr_label . '</span>';
                    echo '<input type="text" class="form-control text-right auto_num" id="footer_nilai_ppn" name="nilai_ppn" readonly style="background-color: #f9f9f9;">';
                    echo '</div>';
                    echo '</td>';
                    echo '</tr>';
                    echo '<tr style="background-color: #f0f7fd; border-top: 2px solid #3c8dbc;">';
                    echo '<td colspan="8" class="text-right text-bold" style="font-size: 15px; vertical-align: middle; color: #2c3e50;">Grand Total</td>';
                    echo '<td style="vertical-align: middle;">';
                    echo '<div class="input-group input-group-sm">';
                    echo '<span class="input-group-addon" style="font-size: 11px; background: #3c8dbc; color: #fff; font-weight: bold; min-width: 32px;">' . $curr_label . '</span>';
                    echo '<input type="text" class="form-control text-right auto_num text-bold" style="font-size: 15px; color: #2c3e50; background: #fff;" id="footer_grand_total" name="grand_total" readonly>';
                    echo '</div>';
                    echo '</td>';
                    echo '</tr>';
                    echo '</tfoot>';
                    echo '</table>';
                    echo '</div>';
                    ?>
                <?php else : ?>
                    <?php
                    // Backward compat for old records without id_rec_inv_ap
                    foreach (explode(',', $header->no_po) as $item_po) {
                        if (empty(trim($item_po))) continue;
                        $get_po = $this->db->get_where('tr_purchase_order', ['no_po' => trim($item_po)])->row();
                        if (empty($get_po)) continue;
                        $po_detail = $this->Retur_pembelian_model->get_po_detail($get_po->no_po);
                        if (empty($po_detail)) continue;

                        $po_matauang = (!empty($get_po->matauang)) ? $get_po->matauang : 'IDR';
                        $po_curr_label = (strtoupper(trim($po_matauang)) === 'USD') ? 'USD' : ((strtoupper(trim($po_matauang)) === 'IDR' || strtoupper(trim($po_matauang)) === 'RP') ? 'Rp' : strtoupper(trim($po_matauang)));

                        echo '<h4>No. PO: ' . $get_po->no_surat . ' <span class="badge bg-blue">' . $po_matauang . '</span></h4>';
                        echo '<div class="table-responsive">';
                        echo '<table class="table table-striped table-bordered table-hover table-detail-retur" style="font-size: 13px;">';
                        echo '<thead style="background-color: #2c3e50; color: #fff;">';
                        echo '<tr>';
                        echo '<th class="text-center" style="width: 10%; vertical-align: middle;">Tanggal PO</th>';
                        echo '<th class="text-center" style="width: 12%; vertical-align: middle;">Lot Number</th>';
                        echo '<th class="text-center" style="width: 18%; vertical-align: middle;">Nama Material</th>';
                        echo '<th class="text-center" style="width: 7%; vertical-align: middle;">Width</th>';
                        echo '<th class="text-center" style="width: 8%; vertical-align: middle;">Qty Order</th>';
                        echo '<th class="text-center" style="width: 12%; vertical-align: middle;">Qty Rec</th>';
                        echo '<th class="text-center" style="width: 12%; vertical-align: middle;">Qty Retur</th>';
                        echo '<th class="text-center" style="width: 11%; vertical-align: middle;">Harga Satuan</th>';
                        echo '<th class="text-center" style="width: 10%; vertical-align: middle;">Total Harga</th>';
                        echo '</tr>';
                        echo '</thead>';
                        echo '<tbody>';

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

                            echo '<tr>';
                            echo '<td class="text-center" style="vertical-align: middle;">';
                            echo '<input type="hidden" name="dt_' . $item_po_detail->no_po . '[' . $no_detail . '][id]" value="' . $item_po_detail->id . '">';
                            echo '<input type="hidden" name="dt_' . $item_po_detail->no_po . '[' . $no_detail . '][no_po]" value="' . $item_po_detail->no_po . '">';
                            echo '<input type="hidden" name="dt_' . $item_po_detail->no_po . '[' . $no_detail . '][id_pr]" value="' . $item_po_detail->idpr . '">';
                            echo '<input type="hidden" name="dt_' . $item_po_detail->no_po . '[' . $no_detail . '][idmaterial]" value="' . $item_po_detail->idmaterial . '">';
                            echo '<input type="hidden" name="dt_' . $item_po_detail->no_po . '[' . $no_detail . '][namamaterial]" value="' . $item_po_detail->namamaterial . '">';
                            echo '<input type="hidden" name="dt_' . $item_po_detail->no_po . '[' . $no_detail . '][width]" value="' . $item_po_detail->width . '">';
                            echo '<input type="hidden" name="dt_' . $item_po_detail->no_po . '[' . $no_detail . '][qty_order]" value="' . $item_po_detail->totalwidth . '">';
                            echo '<input type="hidden" name="dt_' . $item_po_detail->no_po . '[' . $no_detail . '][lotno]" value="' . $lotno . '">';
                            echo '<input type="hidden" name="dt_' . $item_po_detail->no_po . '[' . $no_detail . '][is_sheet]" value="' . ($is_sheet ? '1' : '0') . '">';
                            echo '<input type="hidden" name="dt_' . $item_po_detail->no_po . '[' . $no_detail . '][matauang]" value="' . $po_matauang . '">';
                            echo date('d/m/Y', strtotime($get_po->tanggal));
                            echo '</td>';
                            echo '<td class="text-center" style="vertical-align: middle;"><span class="badge bg-gray text-bold" style="font-size: 11px;">' . $lotno . '</span></td>';
                            echo '<td style="vertical-align: middle;"><b>' . $item_po_detail->namamaterial . '</b></td>';
                            echo '<td class="text-right" style="vertical-align: middle;">' . number_format($item_po_detail->width, 2) . '</td>';
                            echo '<td class="text-right" style="vertical-align: middle;">' . number_format($item_po_detail->totalwidth, 2) . '</td>';
                            
                            // Qty Receive with input-group
                            echo '<td style="vertical-align: middle;">';
                            echo '<div class="input-group input-group-sm">';
                            if ($is_sheet) {
                                echo '<input type="text" class="form-control text-right auto_num" name="dt_' . $item_po_detail->no_po . '[' . $no_detail . '][qty_sheet]" value="' . $qty_sheet . '" readonly style="background-color: #f9f9f9; font-weight: 500;">';
                                echo '<input type="hidden" name="dt_' . $item_po_detail->no_po . '[' . $no_detail . '][qty_receive]" value="' . $qty_receive . '">';
                            } else {
                                echo '<input type="text" class="form-control text-right auto_num" name="dt_' . $item_po_detail->no_po . '[' . $no_detail . '][qty_receive]" value="' . $qty_receive . '" readonly style="background-color: #f9f9f9; font-weight: 500;">';
                                echo '<input type="hidden" name="dt_' . $item_po_detail->no_po . '[' . $no_detail . '][qty_sheet]" value="' . $qty_sheet . '">';
                            }
                            echo '<span class="input-group-addon" style="font-size: 10px; font-weight: bold; background: #eee; min-width: 45px;">' . $unit_label . '</span>';
                            echo '</div>';
                            echo '</td>';

                            // Qty Retur with input-group
                            echo '<td style="vertical-align: middle;">';
                            echo '<div class="input-group input-group-sm">';
                            if ($is_sheet) {
                                echo '<input type="text" class="form-control text-right auto_num hitung_detail_total" name="dt_' . $item_po_detail->no_po . '[' . $no_detail . '][retur_sheet]" data-no_po="' . $item_po_detail->no_po . '" data-no="' . $no_detail . '" data-is_sheet="1" value="' . $qty_sheet_retur . '" style="border-color: #3c8dbc; font-weight: bold; color: #3c8dbc;">';
                                echo '<input type="hidden" name="dt_' . $item_po_detail->no_po . '[' . $no_detail . '][retur]" value="' . $jumlah_retur . '">';
                            } else {
                                echo '<input type="text" class="form-control text-right auto_num hitung_detail_total" name="dt_' . $item_po_detail->no_po . '[' . $no_detail . '][retur]" data-no_po="' . $item_po_detail->no_po . '" data-no="' . $no_detail . '" data-is_sheet="0" value="' . $jumlah_retur . '" style="border-color: #3c8dbc; font-weight: bold; color: #3c8dbc;">';
                                echo '<input type="hidden" name="dt_' . $item_po_detail->no_po . '[' . $no_detail . '][retur_sheet]" value="0">';
                            }
                            echo '<span class="input-group-addon" style="font-size: 10px; font-weight: bold; background: #3c8dbc; color: #fff; min-width: 45px;">' . $unit_label . '</span>';
                            echo '</div>';
                            echo '</td>';

                            // Harga Satuan with input-group
                            echo '<td style="vertical-align: middle;">';
                            echo '<div class="input-group input-group-sm">';
                            echo '<span class="input-group-addon" style="font-size: 10px; background: #eee; min-width: 32px;">' . $po_curr_label . '</span>';
                            echo '<input type="text" class="form-control text-right auto_num" name="dt_' . $item_po_detail->no_po . '[' . $no_detail . '][harga]" value="' . $harga . '" readonly style="background-color: #f9f9f9;">';
                            echo '<span class="input-group-addon" style="font-size: 10px; background: #eee; min-width: 45px;">' . $price_label . '</span>';
                            echo '</div>';
                            echo '</td>';

                            // Total Harga with input-group
                            echo '<td style="vertical-align: middle;">';
                            echo '<div class="input-group input-group-sm">';
                            echo '<span class="input-group-addon" style="font-size: 10px; background: #eee; min-width: 32px;">' . $po_curr_label . '</span>';
                            echo '<input type="text" class="form-control text-right auto_num row_total_harga" name="dt_' . $item_po_detail->no_po . '[' . $no_detail . '][total_harga]" value="' . $total_harga_item . '" readonly style="background-color: #f9f9f9; font-weight: bold;">';
                            echo '</div>';
                            echo '</td>';

                            echo '</tr>';
                        }

                        $ppn_persen_val = isset($header->ppn_persen) ? $header->ppn_persen : 11;

                        echo '</tbody>';
                        echo '<tfoot style="background-color: #fcfcfc;">';
                        echo '<tr>';
                        echo '<td colspan="5" class="text-right text-bold" style="vertical-align: middle;">Total Qty</td>';
                        echo '<td style="vertical-align: middle;"><input type="text" class="form-control form-control-sm text-right auto_num" id="footer_total_qty_receive" readonly style="background: transparent; border: none; font-weight: bold;"></td>';
                        echo '<td style="vertical-align: middle;"><input type="text" class="form-control form-control-sm text-right auto_num" id="footer_total_retur" readonly style="background: transparent; border: none; font-weight: bold; color: #3c8dbc;"></td>';
                        echo '<td class="text-right text-bold" style="vertical-align: middle;">Subtotal</td>';
                        echo '<td style="vertical-align: middle;">';
                        echo '<div class="input-group input-group-sm">';
                        echo '<span class="input-group-addon" style="font-size: 10px; background: #eee; min-width: 32px;">' . $po_curr_label . '</span>';
                        echo '<input type="text" class="form-control text-right auto_num text-bold" id="footer_subtotal" name="subtotal" readonly style="background-color: #f9f9f9;">';
                        echo '</div>';
                        echo '</td>';
                        echo '</tr>';
                        echo '<tr>';
                        echo '<td colspan="8" class="text-right text-bold" style="vertical-align: middle;">';
                        echo '<div style="display: flex; justify-content: flex-end; align-items: center;">';
                        echo '<span style="margin-right: 10px;">PPN</span>';
                        echo '<div class="input-group input-group-sm" style="width: 100px;">';
                        echo '<input type="number" step="any" min="0" max="100" class="form-control text-right" id="footer_ppn_persen" name="ppn_persen" value="' . $ppn_persen_val . '" style="font-weight: bold;">';
                        echo '<span class="input-group-addon">%</span>';
                        echo '</div>';
                        echo '</div>';
                        echo '</td>';
                        echo '<td style="vertical-align: middle;">';
                        echo '<div class="input-group input-group-sm">';
                        echo '<span class="input-group-addon" style="font-size: 10px; background: #eee; min-width: 32px;">' . $po_curr_label . '</span>';
                        echo '<input type="text" class="form-control text-right auto_num" id="footer_nilai_ppn" name="nilai_ppn" readonly style="background-color: #f9f9f9;">';
                        echo '</div>';
                        echo '</td>';
                        echo '</tr>';
                        echo '<tr style="background-color: #f0f7fd; border-top: 2px solid #3c8dbc;">';
                        echo '<td colspan="8" class="text-right text-bold" style="font-size: 15px; vertical-align: middle; color: #2c3e50;">Grand Total</td>';
                        echo '<td style="vertical-align: middle;">';
                        echo '<div class="input-group input-group-sm">';
                        echo '<span class="input-group-addon" style="font-size: 11px; background: #3c8dbc; color: #fff; font-weight: bold; min-width: 32px;">' . $po_curr_label . '</span>';
                        echo '<input type="text" class="form-control text-right auto_num text-bold" style="font-size: 15px; color: #2c3e50; background: #fff;" id="footer_grand_total" name="grand_total" readonly>';
                        echo '</div>';
                        echo '</td>';
                        echo '</tr>';
                        echo '</tfoot>';
                        echo '</table>';
                        echo '</div>';
                    }
                    ?>
                <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="box-footer">
            <a href="<?= base_url('retur_pembelian') ?>" class="btn btn-default"><i class="fa fa-arrow-left"></i> Kembali</a>
            <button type="submit" class="btn btn-warning pull-right save_btn"><i class="fa fa-save"></i> Perbarui Retur Pembelian</button>
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