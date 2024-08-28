
<!-- TABLE: LATEST ORDERS -->
<div class="box box-info">
  <div class="box-header with-border">
    <h3 class="box-title">Stock Type Alloy</h3> <h5><b>Updated date: <?=date('d F Y');?></b></h5>

    <div class="box-tools pull-right">
    
    </div>
  </div>
  <!-- /.box-header -->
  <div class="box-body">
    <div class="table-responsive">
      <table class="table no-margin">
        <thead>
        <tr>
          <th>Level 1</th>
          <th>Level 2</th>
          <th>Status</th>
          <th class='text-right'>Stock</th>
        </tr>
        </thead>
        <tbody>
          <?php
            $SUM=0;
            foreach(get_dashboard_stock() AS $val => $value){
              $color = ($value['status'] == 'aktif')?'success':'danger';
              $SUM += $value['berat'];
              ?>
                <tr>
                  <td><?= $value['nm_lv1'];?></td>
                  <td><?= $value['nm_lv2'];?></td>
                  <td><span class="label label-<?=$color;?>"><?=$value['status'];?></span></td>
                  <td class='text-right'><?= number_format($value['berat'],2);?></td>
                </tr>
              <?php
            }
          ?>
        </tbody>
        <tfoot>
          <tr>
            <th colspan='3'>TOTAL MATERIAL</th>
            <th class='text-right'><?= number_format($SUM,2);?></th>
          </tr>
        </tfoot>
      </table>
    </div>
    <!-- /.table-responsive -->
  </div>
  <!-- /.box-body -->
</div>