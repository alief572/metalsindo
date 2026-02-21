<div id='alert_edit' class="alert alert-success alert-dismissable" style="padding: 15px; display: none;"></div>
<link rel="stylesheet" href="https://cdn.datatables.net/2.3.7/css/dataTables.dataTables.min.css">

<div class="box">
  <div class="box-header">
    <div class='form-group row'>
      <!-- <div class='col-sm-8'></div> -->
      <div class='col-sm-2'>
        <input type="text" name='date_filter' id='date_filter' class='form-control datepicker text-center' data-role="datepicker2" readonly placeholder='Change Date'>
      </div>
      <div class="col-sm-2">
        <select class="form-control form-control-sm" name="gudang_filter" id="gudang_filter">
          <!-- <option value="">All Warehouse</option> -->
          <?php foreach ($list_warehouse as $item) { ?>
            <option value="<?= $item['id'] ?>"><?= $item['wh_name'] ?></option>
          <?php } ?>
        </select>
      </div>
      <div class="col-sm-1 text-center">
        <button type="button" class="btn btn-sm btn-success download_excel">
          <i class="fa fa-download"></i> Download Excel
        </button>
      </div>
      <div class="col-sm-1">
        <button type="button" class="btn btn-sm btn-danger" onclick="UpdateStock();">Update Stock</button>
      </div>
    </div>
  </div>
  <!-- /.box-header -->
  <div class="box-body">
    <table id="example1" class="table table-bordered table-striped">
      <thead>
        <tr>
          <th class="text-center">No.</th>
          <th class="text-center">ID Material</th>
          <th class="text-center">Nama Material</th>
          <th class="text-center">Supplier</th>
          <th class="text-center">Stock Unit</th>
          <th class="text-center">Booking</th>
          <th class="text-center">Available</th>
          <th class="text-cent">History</th>
        </tr>
      </thead>

      <tbody></tbody>
      <tfoot>
        <tr>
          <th colspan="4" class="text-center">Total</th>
          <th class="text-right ttl_stock_unit"></th>
          <th class="text-right ttl_stock_booking"></th>
          <th class="text-right ttl_stock_avail"></th>
          <th></th>
        </tr>
      </tfoot>
    </table>
  </div>
  <!-- /.box-body -->
</div>

<!-- modal -->
<div class="modal fade" id="ModalView" style='overflow-y: auto;'>
  <div class="modal-dialog" style='width:95%; '>
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
<!-- modal -->

<!-- DataTables -->
<script src="https://cdn.datatables.net/2.3.7/js/dataTables.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<style>
  .datepicker {
    cursor: pointer;
  }
</style>
<!-- page script -->
<script type="text/javascript">
  $(function() {
    var date_filter = $('#date_filter').val();
    DataTables(date_filter);

    $('input[type="text"][data-role="datepicker2"]').datepicker({
      dateFormat: 'yy-mm-dd',
      changeMonth: true,
      changeYear: true,
      maxDate: '-1d',
      minDate: '2023-12-21',
      showButtonPanel: true,
      closeText: 'Clear',
      onClose: function(dateText, inst) {
        if ($(window.event.srcElement).hasClass('ui-datepicker-close')) {
          document.getElementById(this.id).value = '';
          var date_filter = $('#date_filter').val();
          DataTables(date_filter);
        }
      }
    });
  });

  $(document).on('change', '#gudang_filter', function(e) {
    e.preventDefault();
    DataTables();
  });

  $(document).on('change', '#date_filter', function(e) {
    DataTables();
  });

  $(document).on('click', '.showHistory', function(e) {
    e.preventDefault();
    var gudang = $(this).data('id_gudang');
    var material = $(this).data('id_material');

    $("#head_title").html("<b>HISTORY</b>");
    $.ajax({
      type: 'POST',
      url: base_url + active_controller + '/modal_history',
      data: {
        "gudang": gudang,
        "material": material
      },
      success: function(data) {
        $("#ModalView").modal();
        $("#view").html(data);

      },
      error: function() {
        Swal.fire({
          title: "Error Message !",
          text: 'Connection Timed Out ...',
          icon: "warning",
          timer: 5000,
          showCancelButton: false,
          showConfirmButton: false,
          allowOutsideClick: false
        });
      }
    });
  });

  $(document).on('click', '.lot_detail', function(e) {
    e.preventDefault();
    var gudang = $(this).data('gudang');
    var material = $(this).data('material');

    $("#head_title").html("<b>HISTORY</b>");
    $.ajax({
      type: 'POST',
      url: base_url + active_controller + '/modal_lot_detail',
      data: {
        "gudang": gudang,
        "material": material
      },
      success: function(data) {
        $("#ModalView").modal();
        $("#view").html(data);

      },
      error: function() {
        Swal.fire({
          title: "Error Message !",
          text: 'Connection Timed Out ...',
          icon: "warning",
          timer: 5000,
          showCancelButton: false,
          showConfirmButton: false,
          allowOutsideClick: false
        });
      }
    });
  });

  $(document).on('click', '.download_excel', function() {
    var tanggal = $('#date_filter').val();
    if (tanggal == '' || tanggal == null) {
      tanggal = '<?= date('Y-m-d') ?>';
    }

    window.open(siteurl + active_controller + 'download_excel/' + tanggal, '_blank');
  });

  function UpdateStock() {
    Swal.fire({
      title: 'Are you sure ?',
      text: 'Stock will be updated !',
      icon: 'warning',
      allowOutsideClick: false,
      allowEscapeKey: false,
      showConfirmButton: true,
      showCancelButton: true
    }).then((next) => {
      if (next.isConfirmed) {
        $.ajax({
          type: 'post',
          url: siteurl + active_controller + 'updateStock',
          cache: false,
          dataType: 'json',
          success: function(result) {
            Swal.fire({
              title: 'Success !',
              text: 'Stock has been updated',
              icon: 'success',
              showCancelButton: false,
              showConfirmButton: false,
              allowOutsideClick: false,
              allowEscapeKey: false,
              timer: 3000
            }).then(() => {
              Swal.close();
              DataTables();
            });
          },
          error: function(xhr, status, error) {
            Swal.fire({
              title: 'Error !',
              text: 'Please try again later !',
              icon: 'error',
              showCancelButton: false,
              showConfirmButton: false,
              allowOutsideClick: false,
              allowEscapeKey: false,
              timer: 3000
            }).then(() => {
              Swal.close();
              DataTables();
            });
          }
        });
      }
    });
  }

  function DataTables() {
    var datatable = $('#example1').dataTable({
      serverSide: true,
      processing: true,
      stateSave: true,
      destroy: true,
      responsive: true,
      ajax: {
        url: siteurl + active_controller + '/data_side_stock',
        type: 'POST',
        data: function(d) {
          d.date_filter = $('#date_filter').val();
          d.gudang_filter = $('#gudang_filter').val();
        },
        cache: false,
        error: function() {
          $(".my-grid-error").html("");
          $("#my-grid").append('<tbody class="my-grid-error"><tr><th colspan="3">No data found in the server</th></tr></tbody>');
          $("#my-grid_processing").css("display", "none");
        }
      },
      columns: [{
          data: 'no'
        },
        {
          data: 'id_material'
        },
        {
          data: 'nama_material'
        },
        {
          data: 'supplier'
        },
        {
          data: 'stock_unit'
        },
        {
          data: 'booking'
        },
        {
          data: 'available'
        },
        {
          data: 'history'
        }
      ],
      aaSorting: [
        [1, 'asc']
      ],
      sPaginationType: 'simple_numbers',
      iDisplayLength: 10,
      aLengthMenu: [
        [10, 25, 50, 100, 250, 500],
        [10, 25, 50, 100, 250, 500]
      ],
      drawCallback: function(settings) {
        var response = settings.json; // Ini nangkep response JSON dari PHP lu

        if (response) {
          // Helper buat format angka biar ada titik ribuan
          var formatRupiah = function(num) {
            return parseFloat(num).toLocaleString('id-ID');
          };

          // Tembak langsung ke selector ID atau Class di footer
          $('.ttl_stock_unit').html(formatRupiah(response.total_stock_unit));
          $('.ttl_stock_booking').html(formatRupiah(response.total_stock_booking));
          $('.ttl_stock_avail').html(formatRupiah(response.total_stock_avail));
        }
      },
    });
  }

  // function DataTables(date_filter = null) {
  //   var dataTable = $('#example1').DataTable({
  //     "processing": true,
  //     "serverSide": true,
  //     "stateSave": true,
  //     "bAutoWidth": true,
  //     "destroy": true,
  //     "responsive": true,
  //     "aaSorting": [
  //       [1, "asc"]
  //     ],
  //     "columnDefs": [{
  //       "targets": 'no-sort',
  //       "orderable": false,
  //     }],
  //     "sPaginationType": "simple_numbers",
  //     "iDisplayLength": 10,
  //     "aLengthMenu": [
  //       [10, 25, 50, 100, 250, 500],
  //       [10, 25, 50, 100, 250, 500]
  //     ],
  //     "ajax": {
  //       url: siteurl + active_controller + '/data_side_stock',
  //       type: "post",
  //       data: function(d) {
  //         d.date_filter = date_filter
  //       },
  //       cache: false,
  //       error: function() {
  //         $(".my-grid-error").html("");
  //         $("#my-grid").append('<tbody class="my-grid-error"><tr><th colspan="3">No data found in the server</th></tr></tbody>');
  //         $("#my-grid_processing").css("display", "none");
  //       }
  //     }
  //   });
  // }
</script>