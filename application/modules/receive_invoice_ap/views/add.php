<link rel="stylesheet" href="https://cdn.datatables.net/2.1.8/css/dataTables.dataTables.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/chosen/1.8.7/chosen.min.css" integrity="sha512-yVvxUQV0QESBt1SyZbNJMAwyKvFTLMyXSyBHDO4BG5t7k/Lw34tyqlSDlKIrIENIzCl+RVUNjmCPG+V/GMesRw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
<form id="form-header-mutasi" method="post">
    <div class="nav-tabs-salesorder">
        <div class="tab-content">
            <div class="tab-pane active" id="salesorder">
                <div class="box box-primary">
                    <?php //print_r($kode_customer)
                    ?>
                    <div class="box-body">
                        <div class="col-sm-6 form-horizontal">

                            <div class="row">
                                <div class="form-group ">
                                    <?php
                                    $tglinv = date('Y-m-d');
                                    ?>
                                    <label for="tgl_bayar" class="col-sm-4 control-label">Tgl Bayar :</label>
                                    <div class="col-sm-6">
                                        <input type="date" name="tgl_bayar" id="tgl_bayar" class="form-control input-sm tanggal" value="<?php echo date('Y-m-d') ?>">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="form-group">
                                    <label for="ket_bayar" class="col-sm-4 control-label">Keterangan Pembayaran </font></label>
                                    <div class="col-sm-6">
                                        <textarea name="ket_bayar" class="form-control input-sm" id="ket_bayar"></textarea>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="form-group">
                                    <label for="ket_bayar" class="col-sm-4 control-label">No. Invoice </font></label>
                                    <div class="col-sm-6">
                                        <input name="no_invoice" class="form-control input-sm" id="no_invoice">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6 form-horizontal">
                            <div class="row">
                                <div class="form-group">
                                    <label class="col-sm-4 control-label">Nama Supplier </font></label>
                                    <div class="col-sm-6">
                                        <select class="form-control input-sm chosen_select" name="supplier" id="supplier">
                                            <option value="">Pilih Supplier</option>
                                            <?php
                                            foreach ($list_supplier as $item) {
                                                echo '<option value="' . $item->id_suplier . '">' . $item->name_suplier . '</option>';
                                            }
                                            ?>
                                        </select>
                                        <input type="hidden" name="id_suplier" id="id_suplier" class="form-control input-sm" readonly>
                                        <input type="hidden" name="nm_suplier" id="nm_suplier" class="form-control input-sm" readonly>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="box box-default ">
        <div class="box-header">
            <div class="nav-tabs-custom">
                <div class="box active ">
                    <ul class="nav nav-tabs">
                        <li class="add"><a href="#" data-toggle="tab" id="tambah2">Add PO</a></li>
                        <!--<li class="createunlocated"><a href="#" data-toggle="tab" id="createunlocated">Create Deposit</a></li>
			<li class="lebihbayar"><a href="#" data-toggle="tab" id="lebihbayar">Add Lebih Bayar</a></li>-->
                    </ul>
                </div>
                <div id="scroll">
                    <div class="box box-primary" id="data">
                    </div>
                </div>
            </div>
            <!--<div class="box-tools">
			<button class="btn btn-sm btn-success add" id="tambah2" type="button" style="width: 100%;">
				<i class="fa fa-plus"></i> Add Invoice
			</button>
			<button class="btn btn-sm btn-success createunlocated " id="createunlocated" type="button" style="width: 100%;">
				<i class="fa fa-plus"></i> Create Unlocated
			</button>
			<button class="btn btn-sm btn-success lebih " id="lebih" type="button" style="width: 100%;">
				<i class="fa fa-plus"></i> Lebih Bayar
			</button>
		</div>-->
        </div>
        <div class="box-body">
            <table class="table table-bordered" width="100%" id="tabel-detail-mutasi">
                <thead>
                    <tr class="bg-blue">
                        <th class="text-center">No. Incoming</th>
                        <th class="text-center">No. PO</th>
                        <th class="text-center">Tgl Incoming</th>
                        <th class="text-center">Nama Supplier</th>
                        <th class="text-center">Nilai</th>
                        <th class="text-center">No. Faktur Pajak</th>
                        <th class="text-center">PPn</th>
                        <th class="text-center">Total</th>
                        <th class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody id="list_item_mutasi">
                </tbody>
            </table>

            <br><br>

            <a href="<?= base_url('receive_invoice_ap') ?>" class="btn btn-sm btn-danger">
                <i class="fa fa-arrow-left"></i> Back
            </a>
            <button type="button" class="btn btn-sm btn-success" onclick="savemutasi()">
                <i class="fa fa-save"></i> Save
            </button>
        </div>
    </div>
</form>

<div class="modal modal-default fade" id="dialog-popup" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Tutup</span></button>
                <h4 class="modal-title" id="myModalLabel"><span class="fa fa-users"></span>&nbsp;List Incoming</h4>
            </div>
            <div class="modal-body" id="ModalView">
                ...
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-dismiss="modal">
                    <span class="glyphicon glyphicon-remove"></span> Tutup</button>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.datatables.net/2.1.8/js/dataTables.min.js"></script>
<script src="<?= base_url('assets/js/autoNumeric.js') ?>"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/chosen/1.8.7/chosen.jquery.min.js" integrity="sha512-rMGGF4wg1R73ehtnxXBt5mbUfN9JUJwbk21KMlnLZDJh7BkPmeovBuddZCENJddHYYMkCh9hPFnPmS9sspki8g==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script>
    var no_list = 1;

    $(document).ready(function() {
        $('.chosen_select').chosen({
            width: '100%'
        });
        swal.close();
        $('#incomplete').hide();
        $('#pakailebihbayar').hide();
        $("#list_item_unlocated").DataTable({
            lengthMenu: [10, 15, 25, 30]
        }).draw();
        $(".divide").autoNumeric();
    });

    function savemutasi() {
        swal({
                title: "Peringatan !",
                text: "Pastikan data sudah lengkap dan benar",
                type: "warning",
                showCancelButton: true,
                confirmButtonColor: "#DD6B55",
                confirmButtonText: "Ya, simpan!",
                cancelButtonText: "Batal!",
                closeOnConfirm: false,
                closeOnCancel: true
            },
            function(isConfirm) {
                if (isConfirm) {
                    $('#simpanpenerimaan').hide();
                    var formdata = $("#form-header-mutasi").serialize();
                    $.ajax({
                        url: siteurl + active_controller + "save_receive_invoice_ap",
                        dataType: "json",
                        type: 'POST',
                        data: formdata,
                        success: function(data) {
                            if (data.status == 1) {
                                swal({
                                    title: "Save Success!",
                                    text: data.pesan,
                                    type: "success",
                                    showCancelButton: false,
                                }, function(lanjut) {
                                    window.location.href = base_url + active_controller;
                                });
                            } else {

                                if (data.status == 2) {
                                    swal({
                                        title: "Save Failed!",
                                        text: data.pesan,
                                        type: "warning",
                                        showCancelButton: false
                                    });
                                } else {
                                    swal({
                                        title: "Save Failed!",
                                        text: data.pesan,
                                        type: "warning",
                                        showCancelButton: false
                                    });
                                }

                            }
                        },
                        error: function() {
                            swal({
                                title: "Gagal!",
                                text: "Batal Proses, Data bisa diproses nanti",
                                type: "error",
                                timer: 1500,
                                showConfirmButton: false
                            });
                        }
                    });
                }
            });
    }

    function kembali_inv() {

        window.location.href = base_url + active_controller;
    }

    function cekall() {
        var total_bank = $("#total_bank").val();
        var total_invoice = $("#total_invoice").val();
        var selisih = (parseFloat(total_bank) - parseFloat(total_invoice));
        $("#selisih").val(selisih);
        var biaya_adm = $("#biaya_adm").val();
        var biaya_pph = $("#biaya_pph").val();
        var tambah_lebih_bayar = $("#tambah_lebih_bayar").val();
        var control = (parseFloat(selisih) + parseFloat(biaya_adm) + parseFloat(biaya_pph) - parseFloat(tambah_lebih_bayar));
        $("#control").val(control);
        var total_terima = (parseFloat(total_invoice) - parseFloat(biaya_adm) - parseFloat(biaya_pph) + parseFloat(tambah_lebih_bayar));
        $("#total_terima").val(total_terima);
    }
    // $(document).on('blur', '#total_bank', function(){
    // var dataTotal	  = $(this).val().split(",").join("");
    // var adm			  = parseFloat($('#biaya_adm').val().split(",").join(""));
    // var pph			  =	parseFloat($('#biaya_pph').val().split(",").join(""));
    // var totalBank     = parseFloat(dataTotal).toFixed(0);
    // var Total         = parseFloat(dataTotal-adm-pph).toFixed(0);
    // $('#total_bank').val(num2(totalBank));
    // $('#total_terima').val(num2(Total));
    // });

    /*
    	$(document).on('keyup', '#biaya_adm, #total_bank, #biaya_pph, #tambah_lebih_bayar', function(){
    		var pakai_lebih_bayar   = parseFloat($('#pakai_lebih_bayar').val().split(",").join(""))
    		var tambah_lebih_bayar   = parseFloat($('#tambah_lebih_bayar').val().split(",").join(""))
    	    var biaya_adm   = parseFloat($('#biaya_adm').val().split(",").join(""))
    		var total_bank	= parseFloat($('#total_bank').val().split(",").join(""));
            var biaya_pph	= parseFloat($('#biaya_pph').val().split(",").join(""));
    		var Total       = parseInt(biaya_adm)+parseInt(total_bank)+parseInt(biaya_pph)+parseInt(pakai_lebih_bayar)-parseInt(tambah_lebih_bayar);
    		$('#total_terima').val(number_format(Total));
    	});
    */
    // $(document).on('blur', '#biaya_pph', function(){
    // var dataTotal	  = $(this).val().split(",").join("");
    // var bank		    = $('#total_bank').val().split(",").join("");
    // var adm			    =	$('#biaya_adm').val().split(",").join("");

    // var totalBank     = parseFloat(dataTotal).toFixed(0);
    // var Total         = parseInt(bank)+parseInt(dataTotal)+parseInt(adm).toFixed(0);
    // $('#biaya_pph').val(num2(totalBank));
    // $('#total_terima').val(num2(Total));
    // });

    // $(document).on('blur', '#total_terima', function(){

    // var dataTotal	  = $(this).val().split(",").join("");
    // var totalBank     = parseFloat(dataTotal).toFixed(0);
    // $('#total_terima').val(num2(totalBank));
    // });

    $("#tambah").click(function() {
        $('#dialog-data-stok').modal('show');
        //        $("#list_item_unlocated").DataTable({lengthMenu:[10,15,25,30]}).draw();
    });

    function startmutasi(id, surat, nm, avl, ppn, real, real4) {
        var avl2 = numx(avl);
        var ppn2 = numx(ppn);
        var real2 = numx(real);
        var real3 = numx(real4);


        //  Cek Ada Data Gagal
        var Cek_OK = 1;
        var Urut = 1;
        var total_row = $('#list_item_mutasi').find('tr').length;
        if (total_row > 0) {
            var kode_tr_akhir = $('#list_item_mutasi tr:last').attr('id');
            var row_akhir = kode_tr_akhir.split('_');
            var Urut = parseInt(row_akhir[1]) + 1;
            $('#list_item_mutasi').find('tr').each(function() {
                var kode_row = $(this).attr('id');
                var id_row = kode_row.split('_');
                var kode_produknya = $('#kode_produk_' + id_row[1]).val();
                if (id == kode_produknya) {
                    Cek_OK = 0;
                }
            });
        }
        if (Cek_OK == 1) {
            var idnya = "'" + id + "'";
            html = '<tr id="tr_' + Urut + '">' +
                '<td style="padding:3px;">' +
                '<input type="text" class="form-control input-sm kode-produk" name="kode_produk[]" id="kode_produk_' + Urut + '" readonly value="' + id + '">' +
                '</td>' +
                '<td style="padding:3px;"><input type="text" class="form-control input-sm" name="no_surat[]" id="no_surat' + Urut + '" readonly value="' + surat + '"></td>' +
                '<td style="padding:3px;"><input type="text" class="form-control input-sm" name="nm_customer2[]" id="nm_customer2' + Urut + '" readonly value="' + nm + '"></td>' +
                '<td style="padding:3px;"><input type="text" class="form-control input-sm" name="jml_invoice[]" id="jml_invoice' + Urut + '" style="text-align:center;" readonly value="' + avl2 + '"></td>' +
                '<td style="padding:3px;"><input type="text" class="form-control input-sm" name="ppn[]" id="ppn' + Urut + '" style="text-align:center;" readonly value="' + ppn2 + '"></td>' +
                '<td style="padding:3px;"><input type="text" class="form-control input-sm" name="sisa_invoice_kurs[]" id="sisa_invoice_kurs' + Urut + '" style="text-align:center;" readonly value="' + real3 + '"></td>' +
                '<td style="padding:3px;"><input type="text" class="form-control input-sm" name="sisa_invoice[]" id="sisa_invoice' + Urut + '" style="text-align:center;" readonly value="' + real2 + '"></td>' +
                '<td style="padding:3px;"><input type="text" class="form-control input-sm sum_change_bayar divide" name="jml_bayar[]" id="jml_bayar' + Urut + '" style="text-align:right;" value="' + number_format(real) + '" onchange="cekall()" ></td>' +
                '<td style="padding:3px;"><center><div class="btn-group" style="margin:0px;">' +
                '<button type="button" onclick="deleterow(' + Urut + ',' + idnya + ')" id="delete-row" class="btn btn-sm btn-danger delete_bayar"><i class="fa fa-trash"></i> Hapus</button>' +
                '</div></center></td>' +
                '</tr>';
            $("#tabel-detail-mutasi").append(html);
            $("#btn-" + id).removeClass('btn-warning');
            $("#btn-" + id).addClass('btn-danger');
            $("#btn-" + id).attr('disabled', true);
            $("#btn-" + id).text('Sudah');
            sumchangebayar();
        }
    }

    function deleterow(tr, id) {
        $('#tr_' + tr).remove();
        $("#btn-" + id).removeClass('btn-danger');
        $("#btn-" + id).addClass('btn-warning');
        $("#btn-" + id).attr('disabled', false);
        $("#btn-" + id).text('Pilih');
        sumchangebayar();
    }

    //ARWANT
    $(document).on('keyup', '.sum_change_bayar', function() {
        var jumlah_bayar = 0;
        $(".sum_change_bayar").each(function() {
            jumlah_bayar += getNum($(this).val().split(",").join(""));
        });
        $('#total_invoice').val(number_format(jumlah_bayar));
    });

    //SYAM
    $(document).on('keyup', '.sum_change_pph', function() {
        var jumlah_bayar = 0;
        $(".sum_change_pph").each(function() {
            jumlah_bayar += getNum($(this).val().split(",").join(""));
        });
        $('#biaya_pph').val(number_format(jumlah_bayar));
        //totalterima();
    });

    function sumchangebayar() {
        var jumlah_bayar = 0;
        $(".sum_change_bayar").each(function() {
            jumlah_bayar += getNum($(this).val().split(",").join(""));
        });
        $('#total_invoice').val(number_format(jumlah_bayar));
    }

    function getNum(val) {
        if (isNaN(val) || val == '') {
            return 0;
        }
        return parseFloat(val);
    }

    function num(n) {
        return (n).toFixed(0).replace(/\d(?=(\d{3})+\.)/g, '$&,');
    }

    function num2(n) {
        return (n).replace(/\d(?=(\d{3})+\.)/g, '$&,');
    }

    function num3(n) {
        return (n).toFixed(0);
    }

    function numx(n) {
        return (n).replace(/\d(?=(\d{3})+\.)/g, '$&,');
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

    function get_num(nilai = null) {
        if (nilai !== '' && nilai !== null) {
            nilai = nilai.split(',').join('');
            nilai = parseFloat(nilai);
        } else {
            nilai = 0;
        }

        return nilai;
    }

    $(document).on('change', '#bank', function() {
        var dataCoa = $(this).val();
        if (dataCoa == '2101-07-01') {
            $('#incomplete').show();
        } else {
            $('#incomplete').hide();
        }
    });

    $("#incomplete").click(function() {
        $('#dialog-data-incomplete').modal('show');
        //        $("#list_item_stok").DataTable({lengthMenu:[10,15,25,30]}).draw();
    });

    $("#lebihbayar-1").click(function() {
        $('#dialog-data-lebihbayar').modal('show');
        $('#pakailebihbayar').show();
        //        $("#list_item_stok").DataTable({lengthMenu:[10,15,25,30]}).draw();
    });

    function startunlocated(id, value) {

        $("#total_bank").val(value);
        $("#id_unlocated").val(id);
        $("#btn-" + id).removeClass('btn-warning');
        $("#btn-" + id).addClass('btn-danger');
        $("#btn-" + id).attr('disabled', true);
        $("#btn-" + id).text('Sudah');
        var totalBank = parseFloat(value).toFixed(0);
        $('#total_bank').val(number_format(totalBank));
        //		totalterima();
        cekall();
    }

    function startlebihbayar(id, value) {

        $("#pakai_lebih_bayar").val(value);
        $("#id_lebihbayar").val(id);
        $("#btn-" + id).removeClass('btn-warning');
        $("#btn-" + id).addClass('btn-danger');
        $("#btn-" + id).attr('disabled', true);
        $("#btn-" + id).text('Sudah');
        var totalBank = parseFloat(value).toFixed(0);
        $('#pakai_lebih_bayar').val(number_format(totalBank));
        //		totalterima();
        cekall();
    }

    $(document).on('click', '.add', function() {
        var id_suplier = $("#supplier").val();

        if (id_suplier == "") {
            swal({
                title: "SUPPLIER TIDAK BOLEH KOSONG!",
                text: "ISI SUPPLIER INVOICE!",
                type: "warning",
                showCancelButton: false,
                showConfirmButton: false,
                allowOutsideClick: true
            });
        } else {

            $("#head_title").html("<i class='fa fa-list-alt'></i><b>Tambah Request</b>");
            $.ajax({
                type: 'POST',
                url: siteurl + active_controller + 'TambahRequest',
                data: {
                    'id_suplier': id_suplier
                },
                success: function(data) {
                    $("#dialog-popup").modal();
                    $("#ModalView").html(data);

                    // $('#list_item_stok').DataTable();
                }
            })
        }
    });

    $(document).on('click', '#lebihbayar', function() {
        // $('#dialog-data-lebihbayar').modal('show');
        $('#pakailebihbayar').show();
        var id_customer = $("#customer").val();
        $("#head_title").html("<i class='fa fa-list-alt'></i><b>Tambah Lebih Bayar</b>");
        $.ajax({
            type: 'POST',
            url: siteurl + 'penerimaan/TambahLebihBayar/' + id_customer,
            data: {
                'id_customer': id_customer
            },
            success: function(data) {
                $("#dialog-data-lebihbayar").modal();
                $("#MyModalBodyLebihbayar").html(data);
            }
        })
    });

    $(document).on('click', '.lebih', function() {
        var id_customer = $("#customer").val();
        $("#head_title").html("<i class='fa fa-list-alt'></i><b>Tambah Lebih Bayar</b>");
        $.ajax({
            type: 'POST',
            url: siteurl + 'penerimaan/lebihbayar',
            data: {
                'id_customer': id_customer
            },
            success: function(data) {
                $("#dialog-popup").modal();
                $("#ModalView").html(data);
            }
        })
    });

    $(document).on('change', '#suplier', function() {
        var id_customer = $("#suplier").val();
        $("#id_suplier").val(id_customer);
    });

    function totalterima() {
        cekall();
        /*
		var pakai_lebih_bayar   = parseFloat($('#pakai_lebih_bayar').val().split(",").join(""))
		var tambah_lebih_bayar   = parseFloat($('#tambah_lebih_bayar').val().split(",").join(""))
	    var biaya_adm   = parseFloat($('#biaya_adm').val().split(",").join(""))
		var total_bank	= parseFloat($('#total_bank').val().split(",").join(""));
        var biaya_pph	= parseFloat($('#biaya_pph').val().split(",").join(""));
		var Total       = parseInt(biaya_adm)+parseInt(total_bank)+parseInt(biaya_pph)+parseInt(pakai_lebih_bayar)-parseInt(tambah_lebih_bayar);
		$('#total_terima').val(number_format(Total));
		*/
    }

    $(document).on('click', '.createunlocated', function() {
        var id_customer = $("#customer").val();
        $("#head_title").html("<i class='fa fa-list-alt'></i><b>Tambah Unlocated</b>");
        $.ajax({
            type: 'POST',
            url: siteurl + 'penerimaan/createunlocated',
            data: {
                'id_customer': id_customer
            },
            success: function(data) {
                $("#dialog-popup").modal();
                $("#ModalView").html(data);
            }
        })
    });

    $(document).on('click', '.add_incoming', function() {
        var id_incoming = $(this).data('id_incoming');
        var no_po = $(this).data('no_po');
        var id_suplier = $(this).data('id_suplier');
        var name_suplier = $(this).data('name_suplier');
        var nilai = $(this).data('nilai');
        var tanggal_incoming = $(this).data('tanggal_incoming');
        var no = $(this).data('no');

        var Rows = '<tr class="tr_inc_add_' + no_list + '">';

        Rows += '<td class="text-center">';
        Rows += id_incoming;
        Rows += '<input type="hidden" name="kp[' + no_list + '][id_incoming]" value="' + id_incoming + '">';
        Rows += '</td>';

        Rows += '<td class="text-center">';
        Rows += no_po;
        Rows += '<input type="hidden" name="kp[' + no_list + '][no_po]" value="' + no_po + '">';
        Rows += '</td>';

        Rows += '<td class="text-center">';
        Rows += tanggal_incoming;
        Rows += '<input type="hidden" name="kp[' + no_list + '][tanggal_incoming]" value="' + tanggal_incoming + '">';
        Rows += '</td>';

        Rows += '<td class="text-center">';
        Rows += name_suplier;
        Rows += '<input type="hidden" name="kp[' + no_list + '][id_suplier]" value="' + id_suplier + '">';
        Rows += '<input type="hidden" name="kp[' + no_list + '][nm_suplier]" value="' + name_suplier + '">';
        Rows += '</td>';

        Rows += '<td class="text-right">';
        Rows += number_format(nilai, 2);
        Rows += '<input type="hidden" name="kp[' + no_list + '][nilai]" value="' + nilai + '">';
        Rows += '</td>';

        Rows += '<td class="text-center">';
        Rows += '<input type="text" class="form-control form-control-sm" name="kp[' + no_list + '][no_faktur_pajak]">';
        Rows += '</td>';

        Rows += '<td class="text-center">';
        Rows += '<input type="text" class="form-control form-control-sm text-right hitung_total divide" name="kp[' + no_list + '][ppn]" data-no="' + no_list + '">';
        Rows += '</td>';

        Rows += '<td class="text-center">';
        Rows += '<input type="text" class="form-control form-control-sm text-right divide" name="kp[' + no_list + '][total]" value="' + nilai + '" readonly>';
        Rows += '</td>';

        Rows += '<td class="text-center">';
        Rows += '<button type="button" class="btn btn-sm btn-danger del_added_inc" data-no="' + no_list + '" title="Remove Incoming">';
        Rows += '<i class="fa fa-trash"></i>';
        Rows += '</button>';
        Rows += '</td>';

        Rows += '</tr>';



        $('#list_item_mutasi').append(Rows);
        $('.divide').autoNumeric();

        $('.add_incoming_' + no).html('Added !');
        $('.add_incoming_' + no).attr('disabled', true);

        no_list = no_list + 1;
    });

    $(document).on('click', '.del_added_inc', function() {
        var no = $(this).data('no');

        $('.tr_inc_add_' + no).remove();
    });

    $(document).on('change', '.hitung_total', function() {
        var no = $(this).data('no');
        var nilai_ppn = get_num($(this).val());
        var nilai = get_num($('input[name="kp[' + no + '][nilai]"]').val());

        var total = parseFloat(nilai + nilai_ppn);

        $('input[name="kp[' + no + '][total]"]').val(number_format(total, 2));
    });

    // $('#tgl_bayar').datepicker({
    // format: 'yyyy-mm-dd',
    // todayHighlight: true
    // });
</script>