<?php
    $ENABLE_ADD     = has_permission('Tools.Add');
    $ENABLE_MANAGE  = has_permission('Tools.Manage');
    $ENABLE_VIEW    = has_permission('Tools.View');
    $ENABLE_DELETE  = has_permission('Tools.Delete');
?>

<link rel="stylesheet" href="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.css')?>">
<div class="box box-primary"> 
  <div class="box-body table-responsive">
	<!--<select name='type' id='type' class='form-control input-sm' style='width:25%; float:right;'>
		<option value='RUTIN'>RUTIN</option>
		<option value='NONRUTIN'>NON RUTIN</option>
		<option value='ASET'>ASET</option>
	</select>-->
	<input type="hidden" id="status_payment" name="status_payment" value="<?=(isset($status_payment)?$status_payment:'0')?>" />
    <table id="tableset" class="table-condensed table-bordered table-striped" width="100%">
      <thead>
        <tr>
			<th class="text-center" width='4%'>No</th>
			<th class="text-center">No Voucher</th>
			<th class="text-center">No PO</th>
			<th class="text-center">Nama Supplier</th>
			<th class="text-center">Jumlah</th>
			<th class="text-center">Admin Bank Oleh</th>
			<th class="text-center" width='11%'>Option</th>
        </tr>
      </thead>
      <tbody id="tbody-detail" class="text-center">
      </tbody>
    </table>
<!--
-->
  </div>
</div>
<form id="form-modal" action="" method="post">
  <div class="modal fade" id="ModalView">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span></button>
          <h4 class="modal-title" id="head_title"></h4>
        </div>
        <div class="modal-body" id="view">
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-default " data-dismiss="modal">Close</button>
        </div>
      </div>
    </div>
  </div>
  <div class="modal fade" id="ModalView2">
    <div class="modal-dialog" style='width:30%; '>
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span></button>
          <h4 class="modal-title" id="head_title2"></h4>
        </div>
        <div class="modal-body" id="view2">
        </div>
        <div class="modal-footer">
          <!-- <button type="button" class="btn btn-primary">Save</button> -->
          <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        </div>
      </div>
    </div>
  </div>
  <div class="modal fade" id="ModalView3">
    <div class="modal-dialog" style='width:30%; '>
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span></button>
          <h4 class="modal-title" id="head_title3"></h4>
        </div>
        <div class="modal-body" id="view3">
        </div>
        <div class="modal-footer">
          <!--<button type="button" class="btn btn-primary">Save</button>-->
          <button type="button" class="btn btn-default close3" data-dismiss="modal">Close</button>
        </div>
      </div>
    </div>
  </div>
  <!-- modal -->
</form>

<!-- DataTables -->
<script src="<?= base_url('assets/plugins/datatables/jquery.dataTables.min.js')?>"></script>
<script src="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.min.js')?>"></script>

<script type="text/javascript">
    $(document).ready(function() {
		var type = $("#type").val();		
		DataTables(type);	 
		$(document).on('change', '#type', function(e) {
			var type = $("#type").val();
			DataTables(type);
		})
	})

	$(document).on('click', '.create_bayar', function(e) {
      var id = $(this).data('id_quotation');
	  var id_term = $(this).data('id');
      newId = id.replace(/\//g, '-');
      window.location.href = base_url + active_controller +'modal_detail_voucher/'+id+'/'+id_term;  
    });

	$(document).on('click', '.create1', function(e) {
      var id = $(this).data('id_quotation');
	  var id_term = $(this).data('id');
      newId = id.replace(/\//g, '-');
      // alert(newId)
      $.ajax({
        type: "post",
        url: siteurl + active_controller + 'upload_po1', 
        //data: {
        // 'id': newId+'&id':id_term
        // },
	    data: "id="+newId+"&id_term="+id_term,
		
        success: function(result) {
          $(".modal-dialog").css('width', '90%');
          $("#head_title").html("<b>DATA PLAN BAYAR</b>");
          $("#view").html(result);
          $("#ModalView").modal('show');
        }
      })
    });

	function openpyament(id_term) {
            swal({
                    title: "Anda Yakin ?", text: "Data akan dapat dibuatkan invoice!", type: "warning", showCancelButton: true,
                    confirmButtonClass: "btn-danger", confirmButtonText: "Ya Lanjutkan", cancelButtonText: "Batal",
                    closeOnConfirm: false, closeOnCancel: false, showLoaderOnConfirm: true
                },
                function(isConfirm) {
                    if (isConfirm) {
                        var baseurl = base_url + active_controller + 'changestatus/'+id_term;
                        $.ajax({
                            url: baseurl,
                            type: "POST",
                            cache: false,
                            dataType: 'json',
                            processData: false,
                            contentType: false,
                            success: function(data) {
                                if (data.status == 1) {
                                    swal({
                                        title: "Save Success!",
                                        text: data.pesan,
                                        type: "success",
                                        timer: 15000,
                                        showCancelButton: false,
                                        showConfirmButton: false,
                                        allowOutsideClick: false
                                    });
                                    location.reload();
                                } else {
                                    if (data.status == 2) {
                                        swal({
                                            title: "Save Failed!",
                                            text: data.pesan,
                                            type: "warning",
                                            timer: 10000,
                                            showCancelButton: false,
                                            showConfirmButton: false,
                                            allowOutsideClick: false
                                        });
                                    } else {
                                        swal({
                                            title: "Save Failed!",
                                            text: data.pesan,
                                            type: "warning",
                                            timer: 10000,
                                            showCancelButton: false,
                                            showConfirmButton: false,
                                            allowOutsideClick: false
                                        });
                                    }

                                }
                            },
                            error: function() {
                                swal({
                                    title: "Error Message !",
                                    text: 'An Error Occured During Process. Please try again..',
                                    type: "error",
                                    timer: 7000,
                                    showCancelButton: false,
                                    showConfirmButton: false,
                                    allowOutsideClick: false
                                });
                            }
                        });
                    } else {
                        swal("Batal Proses", "Proses tidak dilanjutkan", "error");
                        return false;
                    }
                });
	};

	$(document).on('click', '.create2', function(e) {
      var id = $(this).data('id_quotation');
	  var id_term = $(this).data('id');
      newId = id.replace(/\//g, '-');
      // alert(newId)
      $.ajax({
        type: "post",
        url: siteurl + active_controller + 'upload_po2',
        //data: {
        // 'id': newId+'&id':id_term
        // },
	    data: "id="+newId+"&id_term="+id_term,
		
        success: function(result) {
          $(".modal-dialog").css('width', '90%');
          $("#head_title").html("<b>DATA QUOTATION</b>");
          $("#view").html(result);
          $("#ModalView").modal('show');
        }
      })
    });

    function DataTables(type = null) {
	var sp = $("#status_payment").val();
    var dataTable = $('#tableset').DataTable({
      "serverSide": true,
      "stateSave": true,
      "bAutoWidth": true,
      "destroy": true,
      "responsive": true,
      "oLanguage": {
        "sSearch": "<b>Search : </b>",
        "sLengthMenu": "_MENU_ &nbsp;&nbsp;<b>Records Per Page</b>&nbsp;&nbsp;",
        "sInfo": "Showing _START_ to _END_ of _TOTAL_ entries",
        "sInfoFiltered": "(filtered from _MAX_ total entries)",
        "sZeroRecords": "No matching records found",
        "sEmptyTable": "No data available",
        "sLoadingRecords": "Please wait - loading...",
        "oPaginate": {
          "sPrevious": "Prev",
          "sNext": "Next"
        }
      },
      "aaSorting": [
        [1, "asc"]
      ],
      "columnDefs": [{
        "targets": 'no-sort',
        "orderable": false,
      }],
      "sPaginationType": "simple_numbers",
      "iDisplayLength": 10,
      "aLengthMenu": [
        [5, 10, 20, 50, 100, 150],
        [5, 10, 20, 50, 100, 150]
      ],
      "ajax": {
        url: siteurl + active_controller + 'getDataJSONdtspkmarketing',
        type: "post",
        data: function(d) {
          d.status = type,
		  d.status_payment = sp
        },
        cache: false,
        error: function() {
          $(".my-grid-error").html("");
          $("#my-grid").append('<tbody class="my-grid-error"><tr><th colspan="3">No data found in the server</th></tr></tbody>');
          $("#my-grid_processing").css("display", "none");
        }
      }
    });
  } 
</script>
