<?php

defined('BASEPATH') || exit('No direct script access allowed');
/**
 * @Author  : Suwito
 * @Email   : suwito.lt@gmail.com
 * @Date    : 2017-01-26 13:36:42
 * @Last Modified by    : Yunaz
 * @Last Modified time  : 2017-01-26 22:15:59
 */

/**
 * A simple helper method for checking menu items against the current class/controller.
 * This function copied from cibonfire
 * <code>
 *   <a href="<?php echo site_url(SITE_AREA . '/content'); ?>" <?php echo check_class(SITE_AREA . '/content'); ?> >
 *    Admin Home
 *  </a>.
 *
 * </code>
 *
 * @param string $item       the name of the class to check against
 * @param bool   $class_only If true, will only return 'active'. If false, will
 *                           return 'class="active"'.
 *
 * @return string either 'active'/'class="active"' or an empty string
 */
function get_supplier($id = false)
{
    $CI = &get_instance();
    $CI->db->where('id_supplier', $id);
    $result = $CI->db->get('supplier')->row();

    return $result->group_produk;
}
function getColsChar($colums)
{
    // Palleng by jester

    if ($colums > 26) {
        $modCols = floor($colums / 26);
        $ExCols = $modCols * 26;
        $totCols = $colums - $ExCols;

        if ($totCols == 0) {
            $modCols = $modCols - 1;
            $totCols += 26;
        }

        $lets1 = getLetColsLetter($modCols);
        $lets2 = getLetColsLetter($totCols);
        return $letsi = $lets1 . $lets2;
    } else {
        $lets = getLetColsLetter($colums);
        return $letsi = $lets;
    }
}
function getLetColsLetter($numbs)
{
    // Palleng by jester
    switch ($numbs) {
        case 1:
            $Chars = 'A';
            break;
        case 2:
            $Chars = 'B';
            break;
        case 3:
            $Chars = 'C';
            break;
        case 4:
            $Chars = 'D';
            break;
        case 5:
            $Chars = 'E';
            break;
        case 6:
            $Chars = 'F';
            break;
        case 7:
            $Chars = 'G';
            break;
        case 8:
            $Chars = 'H';
            break;
        case 9:
            $Chars = 'I';
            break;
        case 10:
            $Chars = 'J';
            break;
        case 11:
            $Chars = 'K';
            break;
        case 12:
            $Chars = 'L';
            break;
        case 13:
            $Chars = 'M';
            break;
        case 14:
            $Chars = 'N';
            break;
        case 15:
            $Chars = 'O';
            break;
        case 16:
            $Chars = 'P';
            break;
        case 17:
            $Chars = 'Q';
            break;
        case 18:
            $Chars = 'R';
            break;
        case 19:
            $Chars = 'S';
            break;
        case 20:
            $Chars = 'T';
            break;
        case 21:
            $Chars = 'U';
            break;
        case 22:
            $Chars = 'V';
            break;
        case 23:
            $Chars = 'W';
            break;
        case 24:
            $Chars = 'X';
            break;
        case 25:
            $Chars = 'Y';
            break;
        case 26:
            $Chars = 'Z';
            break;
    }

    return $Chars;
}
function get_invoice($id = false)
{
    $CI = &get_instance();
    $CI->db->where('no_po', $id);
    $result = $CI->db->get('trans_po_invoice')->row();

    return $result->no_invoice;
}

function check_class($item = '', $class_only = false)
{
    if (strtolower(get_instance()->router->class) == strtolower($item)) {
        return $class_only ? 'active' : 'class="active"';
    }

    return '';
}

/**
 * A simple helper method for checking menu items against the current method
 * (controller action) (as far as the Router knows).
 *
 * @param string $item       The name of the method to check against. Can be an array of names.
 * @param bool   $class_only If true, will only return 'active'. If false, will return 'class="active"'.
 *
 * @return string either 'active'/'class="active"' or an empty string
 */
function check_method($item, $class_only = false)
{
    $items = is_array($item) ? $item : array($item);
    if (in_array(get_instance()->router->method, $items)) {
        return $class_only ? 'active' : 'class="active"';
    }

    return '';
}

/**
 * Check if the logged user has permission or not.
 *
 * @param string $permission_name
 *
 * @return bool True if has permission and false if not
 */
function has_permission($permission_name = '')
{
    $ci = &get_instance();

    $return = $ci->auth->has_permission($permission_name);

    return $return;
}

/**
 * @param string $kode_tambahan
 *
 * @return string generated code
 */
function gen_primary($kode_tambahan = '')
{
    $CI = &get_instance();

    $tahun = intval(date('Y'));
    $bulan = intval(date('m'));
    $hari = intval(date('d'));
    $jam = intval(date('H'));
    $menit = intval(date('i'));
    $detik = intval(date('s'));
    $temp_ip = ($CI->input->ip_address()) == '::1' ? '127.0.0.1' : $CI->input->ip_address();
    $temp_ip = explode('.', $temp_ip);
    $ipval = $temp_ip[0] + $temp_ip[1] + $temp_ip[2] + $temp_ip[3];

    $kode_rand = mt_rand(1, 1000) + $ipval;
    $letter1 = chr(mt_rand(65, 90));
    $letter2 = chr(mt_rand(65, 90));

    $kode_primary = $tahun . $bulan . $hari . $jam . $menit . $detik . $letter1 . $kode_rand . $letter2;

    return $kode_tambahan . $kode_primary;
}

if (!function_exists('gen_idcustomer')) {
    function gen_idcustomer($kode_tambahan = '')
    {
        $CI = &get_instance();
        $CI->load->model('Customer/Customer_model');

        $query = $CI->Customer_model->generate_id($kode_tambahan);
        if (empty($query)) {
            return 'Error';
        } else {
            return $query;
        }
    }
}

if (!function_exists('gen_id_toko')) {
    function gen_id_toko($kode_tambahan = '')
    {
        $CI = &get_instance();
        $CI->load->model('Customer/Toko_model');

        $query = $CI->Toko_model->generate_id($kode_tambahan);
        if (empty($query)) {
            return 'Error';
        } else {
            return $query;
        }
    }
}

if (!function_exists('get_id_pnghn')) {
    function get_id_pnghn($kode_tambahan = '')
    {
        $CI = &get_instance();
        $CI->load->model('Customer/Penagihan_model');

        $query = $CI->Penagihan_model->generate_id($kode_tambahan);
        if (empty($query)) {
            return 'Error';
        } else {
            return $query;
        }
    }
}

if (!function_exists('get_id_pmbyr')) {
    function get_id_pmbyr($kode_tambahan = '')
    {
        $CI = &get_instance();
        $CI->load->model('Customer/Pembayaran_model');

        $query = $CI->Pembayaran_model->generate_id($kode_tambahan);
        if (empty($query)) {
            return 'Error';
        } else {
            return $query;
        }
    }
}

if (!function_exists('get_id_pic')) {
    function get_id_pic($kode_tambahan = '')
    {
        $CI = &get_instance();
        $CI->load->model('Customer/Pic_model');

        $query = $CI->Pic_model->generate_id($kode_tambahan);
        if (empty($query)) {
            return 'Error';
        } else {
            return $query;
        }
    }
}

if (!function_exists('gen_idsupplier')) {
    function gen_idsupplier($kode_tambahan = '')
    {
        $CI = &get_instance();
        $CI->load->model('Supplier/Supplier_model');

        $query = $CI->Supplier_model->generate_id($kode_tambahan);
        if (empty($query)) {
            return 'Error';
        } else {
            return $query;
        }
    }
}

if (!function_exists('simpan_aktifitas')) {
    function simpan_aktifitas($nm_hak_akses = '', $kode_universal = '', $keterangan = '', $jumlah = 0, $sql = '', $status = null)
    {
        $CI = &get_instance();

        $CI->load->model('aktifitas/aktifitas_model');

        $result = $CI->aktifitas_model->simpan_aktifitas($nm_hak_akses, $kode_universal, $keterangan, $jumlah, $sql, $status);

        return $result;
    }
}

/*
* $date_from is the date with format dd/mm/yyyy H:i:s / dd/mm/yyyy
*/
if (!function_exists('date_ymd')) {
    function date_ymd($date_from)
    {
        $error = false;
        if (strlen($date_from) <= 10) {
            list($dd, $mm, $yyyy) = explode('/', $date_from);

            if (!checkdate(intval($mm), intval($dd), intval($yyyy))) {
                $error = true;
            }
        } else {
            list($dd, $mm, $yyyy) = explode('/', $date_from);
            list($yyyy, $hhii) = explode(' ', $yyyy);

            if (!checkdate($mm, $dd, $yyyy)) {
                $error = true;
            }
        }

        if ($error) {
            return false;
        }

        if (strlen($date_from) <= 10) {
            $date_from = DateTime::createFromFormat('d/m/Y', $date_from);
            $date_from = $date_from->format('Y-m-d');
        } else {
            $date_from = DateTime::createFromFormat('d/m/Y H:i', $date_from);
            $date_from = $date_from->format('Y-m-d H:i');
        }

        return $date_from;
    }
}

if (!function_exists('simpan_alurkas')) {
    function simpan_alurkas($kode_accountKas = null, $ket = '', $total = null, $status = null, $nm_hak_akses = '')
    {
        $CI = &get_instance();

        $CI->load->model('kas/kas_model');

        $result = $CI->kas_model->simpan_alurKas($kode_accountKas, $ket, $total, $status, $nm_hak_akses);

        return $result;
    }
}

if (!function_exists('buatrp')) {
    function buatrp($angka)
    {
        $jadi = 'Rp ' . number_format($angka, 0, ',', '.');

        return $jadi;
    }
}

if (!function_exists('formatnomor')) {
    function formatnomor($angka)
    {
        if ($angka) {
            $jadi = number_format($angka, 0, ',', '.');

            return $jadi;
        }
    }
}

if (!function_exists('separator')) {
    function separator($angka)
    {
        if ($angka) {
            $jadi = number_format($angka, 0, '.', '.');

            return $jadi;
        }
    }
}

if (!function_exists('ynz_terbilang_format')) {
    function ynz_terbilang_format($x)
    {
        $x = abs($x);
        $angka = array('', 'satu', 'dua', 'tiga', 'empat', 'lima', 'enam', 'tujuh', 'delapan', 'sembilan', 'sepuluh', 'sebelas');
        $temp = '';
        if ($x < 12) {
            $temp = ' ' . $angka[$x];
        } elseif ($x < 20) {
            $temp = ynz_terbilang_format($x - 10) . ' belas';
        } elseif ($x < 100) {
            $temp = ynz_terbilang_format($x / 10) . ' puluh' . ynz_terbilang_format($x % 10);
        } elseif ($x < 200) {
            $temp = ' seratus' . ynz_terbilang_format($x - 100);
        } elseif ($x < 1000) {
            $temp = ynz_terbilang_format($x / 100) . ' ratus' . ynz_terbilang_format($x % 100);
        } elseif ($x < 2000) {
            $temp = ' seribu' . ynz_terbilang_format($x - 1000);
        } elseif ($x < 1000000) {
            $temp = ynz_terbilang_format($x / 1000) . ' ribu' . ynz_terbilang_format($x % 1000);
        } elseif ($x < 1000000000) {
            $temp = ynz_terbilang_format($x / 1000000) . ' juta' . ynz_terbilang_format($x % 1000000);
        } elseif ($x < 1000000000000) {
            $temp = ynz_terbilang_format($x / 1000000000) . ' milyar' . ynz_terbilang_format(fmod($x, 1000000000));
        } elseif ($x < 1000000000000000) {
            $temp = ynz_terbilang_format($x / 1000000000000) . ' trilyun' . ynz_terbilang_format(fmod($x, 1000000000000));
        }

        return $temp;
    }
}

if (!function_exists('ynz_terbilang')) {
    function ynz_terbilang($x, $style = 1)
    {
        if ($x < 0) {
            $hasil = 'minus ' . trim(ynz_terbilang_format($x));
        } else {
            $hasil = trim(ynz_terbilang_format($x));
        }
        switch ($style) {
            case 1:
                $hasil = strtoupper($hasil);
                break;
            case 2:
                $hasil = strtolower($hasil);
                break;
            case 3:
                $hasil = ucwords($hasil);
                break;
            default:
                $hasil = ucfirst($hasil);
                break;
        }

        return $hasil;
    }
}

if (!function_exists('tipe_pengiriman')) {
    function tipe_pengiriman($ket = false)
    {
        $uu = array(
            'SENDIRI' => 'MILIK SENDIRI',
            'SEWA' => 'SEWA',
            'EKSPEDISI' => 'EKSPEDISI',
            'PELANGGAN' => 'PELANGGAN AMBIL SENDIRI',
        );
        if ($ket == true) {
            return $uu[$ket];
        } else {
            return $uu;
        }
    }
}

if (!function_exists('selisih_hari')) {
    function selisih_hari($tgl, $now)
    {
        $aw = new DateTime($tgl);
        $ak = new DateTime($now);
        $interval = $aw->diff($ak);

        return $interval->days;
    }
}

if (!function_exists('kategori_umur_piutang')) {
    function kategori_umur_piutang($ket = false)
    {
        $uu = array(
            '0|14' => '0-14',
            '15|29' => '15-29',
            '30|59' => '30-59',
            '60|89' => '60-89',
            '90' => '>90',
        );
        if ($ket == true) {
            return $uu[$ket];
        } else {
            return $uu;
        }
    }
}

if (!function_exists('the_bulan')) {
    function the_bulan($time = false)
    {
        $a = array(
            '1' => 'Januari',
            '2' => 'Februari',
            '3' => 'Maret',
            '4' => 'April',
            '5' => 'Mei',
            '6' => 'Juni',
            '7' => 'Juli',
            '8' => 'Agustus',
            '9' => 'September',
            '10' => 'Oktober',
            '11' => 'November',
            '12' => 'Desember',
        );

        return $time == false ? $a : $a[$time];
    }
}

if (!function_exists('bulan')) {
    function bulan($time = false)
    {
        $a = array(
            '01' => 'Januari',
            '02' => 'Februari',
            '03' => 'Maret',
            '04' => 'April',
            '05' => 'Mei',
            '06' => 'Juni',
            '07' => 'Juli',
            '08' => 'Agustus',
            '09' => 'September',
            '10' => 'Oktober',
            '11' => 'November',
            '12' => 'Desember',
        );

        return $time == false ? $a : $a[$time];
    }
}

if (!function_exists('is_jenis_bayar')) {
    function is_jenis_bayar($ket = false)
    {
        $uu = array(
            'CASH' => 'CASH',
            'TRANSFER' => 'TRANSFER',
            'BG' => 'GIRO',
        );
        if ($ket == true) {
            return $uu[$ket];
        } else {
            return $uu;
        }
    }
}

if (!function_exists('is_status_giro')) {
    function is_status_giro($ket = false)
    {
        $uu = array(
            'OPEN' => 'OPEN',
            'INV' => 'INVOICE',
            'CAIR' => 'CAIR',
            'TOLAK' => 'TOLAK',
        );
        if ($ket == true) {
            return $uu[$ket];
        } else {
            return $uu;
        }
    }
}

if (!function_exists('is_filter_report_jual')) {
    function is_filter_report_jual($ket = false)
    {
        $uu = array(
            'by_customer' => 'Per Customer',
            'by_sales' => 'Per Sales',
        );
        if ($ket == true) {
            return $uu[$ket];
        } else {
            return $uu;
        }
    }
}

if (!function_exists('is_filter_detail_jual')) {
    function is_filter_detail_jual($ket = false)
    {
        $uu = array(
            'by_produk' => 'Per Produk',
            'by_customer' => 'Per Customer',
            'by_sales' => 'Per Sales',
        );
        if ($ket == true) {
            return $uu[$ket];
        } else {
            return $uu;
        }
    }
}

function tgl_indo($tgl)
{
    $tanggal = substr($tgl, 8, 2);
    $bulan = substr($tgl, 5, 2);
    $tahun = substr($tgl, 0, 4);
    return $tanggal . '-' . $bulan . '-' . $tahun;
}
function get10hari($id)
{
    $hariini = date('Y-m-d H:i:s');
    $sepuluh_hari = mktime(0, 0, 0, date('n'), date('j') - 10, date('Y'));
    $tendays = date("Y-m-d H:i:s", $sepuluh_hari);
    $this->db->select('AVG(nominal) avg_nominal');
    $this->db->from('child_history_lme');
    $this->db->where('id_category1', $id);
    $this->db->where('created_on >=', $tendays);
    $this->db->where('created_on <=', $hariini);
    $query = $this->db->get();
    $tenrata['avg_nominal'];
}

function checkApprove($id)
{
    $CI     = &get_instance();
    $query    = $CI->db->get_where('dt_spkmarketing', array('deal' => '1', 'id_spkmarketing' => $id))->result_array();
    return $query;
}

function get_dashboard_stock()
{
    $CI     = &get_instance();
    $query    = $CI->db
        ->select('
                            a.nama AS nm_lv2,
                            a.id_category2 AS category_lv2,
                            b.nama AS nm_lv1,
                            a.aktif AS status,
                            c.berat
                        ')
        ->from('ms_inventory_category2 a')
        ->join('ms_inventory_category1 b', 'a.id_category1=b.id_category1', 'left')
        ->join('stock_lv2 c', 'a.id_category2=c.id2', 'left')
        ->where('a.deleted', '0')
        ->get()
        ->result_array();
    return $query;
}

function get_name($table, $field, $where, $value)
{
    $CI = &get_instance();
    $query = "SELECT " . $field . " FROM " . $table . " WHERE " . $where . "='" . $value . "' LIMIT 1";
    $result = $CI->db->query($query)->result();
    $hasil = (!empty($result)) ? $result[0]->$field : '';
    if (empty($result)) {
        $hasil = $value;
    }
    return $hasil;
}

function get_kebutuhanPerMonth()
{
    $CI = &get_instance();
    $listGetCategory = $CI->db->select('SUM(kebutuhan_month) AS sum_keb, id_barang')->group_by('id_barang')->get('budget_rutin_detail')->result_array();
    $ArrGetCategory = [];
    foreach ($listGetCategory as $key => $value) {
        $KEY = $value['id_barang'];
        $ArrGetCategory[$KEY]['kebutuhan'] = (!empty($value['sum_keb'])) ? $value['sum_keb'] : 0;
    }
    return $ArrGetCategory;
}

function getStokBarangAll()
{
    $CI = &get_instance();
    $listGetCategory =     $CI->db
        ->select('
											a.id_material,
											SUM(a.qty_stock) AS qty_stock,
											b.konversi
										')
        ->group_by('a.id_material')
        ->where_in('a.id_gudang', [17, 19, 21])
        ->join('accessories b', 'a.id_material=b.id')
        ->get('warehouse_stock a')
        ->result_array();
    $ArrGetCategory     = [];
    foreach ($listGetCategory as $key => $value) {
        $stok_packing = 0;
        if ($value['qty_stock'] > 0 and $value['konversi'] > 0) {
            $stok_packing = $value['qty_stock'] / $value['konversi'];
        }
        $ArrGetCategory[$value['id_material']]['stok']     = $value['qty_stock'];
        $ArrGetCategory[$value['id_material']]['stok_packing']     = $stok_packing;
        $ArrGetCategory[$value['id_material']]['konversi']     = $value['konversi'];
    }
    return $ArrGetCategory;
}

function generateNoPR()
{
    $CI = &get_instance();
    $Ym         = date('ym');
    $qIPP       = "SELECT MAX(no_pr) as maxP FROM material_planning_base_on_produksi WHERE no_pr LIKE 'PR" . $Ym . "%' ";
    $resultIPP  = $CI->db->query($qIPP)->result_array();
    $angkaUrut2 = $resultIPP[0]['maxP'];
    $urutan2    = (int)substr($angkaUrut2, 6, 5);
    $urutan2++;
    $urut2      = sprintf('%05s', $urutan2);
    $no_pr      = "PR" . $Ym . $urut2;
    return $no_pr;
}

function get_list_user()
{
    $CI = &get_instance();
    $listGetCategory = $CI->db->get('users')->result_array();
    $ArrGetCategory = [];
    foreach ($listGetCategory as $key => $value) {
        $ArrGetCategory[$value['id_user']]['username']     = $value['username'];
        $ArrGetCategory[$value['id_user']]['nama']         = $value['nm_lengkap'];
    }
    return $ArrGetCategory;
}

function move_warehouse_stok($ArrUpdateStock = null, $id_gudang_dari = null, $id_gudang_ke = null, $kode_trans = null, $costcenter = null)
{
    $CI     = &get_instance();
    $dateTime        = date('Y-m-d H:i:s');
    $UserName         = $CI->auth->user_id();
    $kd_gudang_dari = strtoupper(get_name('warehouse', 'nm_gudang', 'id', $id_gudang_dari));
    $kd_gudang_ke    = (!empty($id_gudang_ke)) ? $id_gudang_ke : $costcenter;
    if ($id_gudang_ke != null) {
        $kd_gudang_ke     = strtoupper(get_name('warehouse', 'nm_gudang', 'id', $id_gudang_ke));
    }
    //grouping sum
    $temp = [];
    foreach ($ArrUpdateStock as $value) {
        if (!array_key_exists($value['id'], $temp)) {
            $temp[$value['id']] = 0;
        }
        $temp[$value['id']] += $value['qty'];
    }

    $ArrStock = array();
    $ArrHist = array();
    $ArrStockInsert = array();
    $ArrHistInsert = array();

    $ArrStock2 = array();
    $ArrHist2 = array();
    $ArrStockInsert2 = array();
    $ArrHistInsert2 = array();

    $ArrHistPerDay = array();
    $ArrHistPerDay2 = array();

    foreach ($temp as $key => $value) {
        //PENGURANGAN GUDANG
        if ($id_gudang_dari != null) {
            $rest_pusat = $CI->db->get_where('warehouse_stock', array('id_gudang' => $id_gudang_dari, 'id_material' => $key))->result();

            if (!empty($rest_pusat)) {
                $ArrStock[$key]['id']             = $rest_pusat[0]->id;
                $ArrStock[$key]['qty_stock']     = $rest_pusat[0]->qty_stock - $value;
                $ArrStock[$key]['update_by']     = $UserName;
                $ArrStock[$key]['update_date']     = $dateTime;

                $ArrHist[$key]['id_material']     = $key;
                $ArrHist[$key]['nm_material']     = $rest_pusat[0]->nm_material;
                $ArrHist[$key]['id_gudang']         = $id_gudang_dari;
                $ArrHist[$key]['kd_gudang']         = $kd_gudang_dari;
                $ArrHist[$key]['id_gudang_dari']     = $id_gudang_dari;
                $ArrHist[$key]['kd_gudang_dari']     = $kd_gudang_dari;
                $ArrHist[$key]['id_gudang_ke']         = $id_gudang_ke;
                $ArrHist[$key]['kd_gudang_ke']         = $kd_gudang_ke;
                $ArrHist[$key]['qty_stock_awal']     = $rest_pusat[0]->qty_stock;
                $ArrHist[$key]['qty_stock_akhir']     = $rest_pusat[0]->qty_stock - $value;
                $ArrHist[$key]['qty_booking_awal']     = $rest_pusat[0]->qty_booking;
                $ArrHist[$key]['qty_booking_akhir'] = $rest_pusat[0]->qty_booking;
                $ArrHist[$key]['qty_rusak_awal']     = $rest_pusat[0]->qty_rusak;
                $ArrHist[$key]['qty_rusak_akhir']     = $rest_pusat[0]->qty_rusak;
                $ArrHist[$key]['no_ipp']             = $kode_trans;
                $ArrHist[$key]['jumlah_mat']         = $value;
                $ArrHist[$key]['ket']                 = 'pengurangan gudang';
                $ArrHist[$key]['update_by']         = $UserName;
                $ArrHist[$key]['update_date']         = $dateTime;

                $ArrHistPerDay[$key]['id_material'] = $key;
                $ArrHistPerDay[$key]['nm_material'] = $rest_pusat[0]->nm_material;
                $ArrHistPerDay[$key]['id_gudang'] = $id_gudang_dari;
                $ArrHistPerDay[$key]['qty_stock'] = $rest_pusat[0]->qty_stock - $value;
                $ArrHistPerDay[$key]['qty_booking'] = 0;
                $ArrHistPerDay[$key]['qty_rusak'] = 0;
                $ArrHistPerDay[$key]['hist_date'] = date('Y-m-d H:i:s');
            } else {
                $restMat    = $CI->db->get_where('accessories', array('id' => $key))->result();

                $ArrStockInsert[$key]['id_material']     = $key;
                $ArrStockInsert[$key]['nm_material']     = $restMat[0]->stock_name;
                $ArrStockInsert[$key]['id_gudang']         = $id_gudang_dari;
                $ArrStockInsert[$key]['kd_gudang']         = $kd_gudang_dari;
                $ArrStockInsert[$key]['qty_stock']         = 0 - $value;
                $ArrStockInsert[$key]['update_by']         = $UserName;
                $ArrStockInsert[$key]['update_date']     = $dateTime;

                $ArrHistInsert[$key]['id_material']     = $key;
                $ArrHistInsert[$key]['nm_material']     = $restMat[0]->stock_name;
                $ArrHistInsert[$key]['id_gudang']         = $id_gudang_dari;
                $ArrHistInsert[$key]['kd_gudang']         = $kd_gudang_dari;
                $ArrHistInsert[$key]['id_gudang_dari']     = $id_gudang_dari;
                $ArrHistInsert[$key]['kd_gudang_dari']     = $kd_gudang_dari;
                $ArrHistInsert[$key]['id_gudang_ke']     = $id_gudang_ke;
                $ArrHistInsert[$key]['kd_gudang_ke']     = $kd_gudang_ke;
                $ArrHistInsert[$key]['qty_stock_awal']         = 0;
                $ArrHistInsert[$key]['qty_stock_akhir']     = 0 - $value;
                $ArrHistInsert[$key]['qty_booking_awal']    = 0;
                $ArrHistInsert[$key]['qty_booking_akhir']   = 0;
                $ArrHistInsert[$key]['qty_rusak_awal']         = 0;
                $ArrHistInsert[$key]['qty_rusak_akhir']     = 0;
                $ArrHistInsert[$key]['no_ipp']             = $kode_trans;
                $ArrHistInsert[$key]['jumlah_mat']         = $value;
                $ArrHistInsert[$key]['ket']             = 'pengeluaran gudang stok (insert new)';
                $ArrHistInsert[$key]['update_by']         = $UserName;
                $ArrHistInsert[$key]['update_date']     = $dateTime;


                $ArrHistPerDay[$key]['id_material'] = $key;
                $ArrHistPerDay[$key]['nm_material'] = $restMat[0]->stock_name;
                $ArrHistPerDay[$key]['id_gudang'] = $id_gudang_dari;
                $ArrHistPerDay[$key]['qty_stock'] = 0 - $value;
                $ArrHistPerDay[$key]['qty_booking'] = 0;
                $ArrHistPerDay[$key]['qty_rusak'] = 0;
                $ArrHistPerDay[$key]['hist_date'] = date('Y-m-d H:i:s');
            }
        }

        //PENAMBAHAN GUDANG
        if ($id_gudang_ke !== null) {
            $rest_pusat = $CI->db->get_where('warehouse_stock', array('id_gudang' => $id_gudang_ke, 'id_material' => $key))->result();

            if (!empty($rest_pusat)) {
                $ArrStock2[$key]['id']             = $rest_pusat[0]->id;
                $ArrStock2[$key]['qty_stock']     = $rest_pusat[0]->qty_stock + $value;
                $ArrStock2[$key]['update_by']     =  $UserName;
                $ArrStock2[$key]['update_date']     = $dateTime;

                $ArrHist2[$key]['id_material']     = $key;
                $ArrHist2[$key]['nm_material']     = $rest_pusat[0]->nm_material;
                $ArrHist2[$key]['id_gudang']         = $id_gudang_ke;
                $ArrHist2[$key]['kd_gudang']         = $kd_gudang_ke;
                $ArrHist2[$key]['id_gudang_dari']     = $id_gudang_dari;
                $ArrHist2[$key]['kd_gudang_dari']     = $kd_gudang_dari;
                $ArrHist2[$key]['id_gudang_ke']     = $id_gudang_ke;
                $ArrHist2[$key]['kd_gudang_ke']     = $kd_gudang_ke;
                $ArrHist2[$key]['qty_stock_awal']     = $rest_pusat[0]->qty_stock;
                $ArrHist2[$key]['qty_stock_akhir']     = $rest_pusat[0]->qty_stock + $value;
                $ArrHist2[$key]['qty_booking_awal'] = $rest_pusat[0]->qty_booking;
                $ArrHist2[$key]['qty_booking_akhir'] = $rest_pusat[0]->qty_booking;
                $ArrHist2[$key]['qty_rusak_awal']     = $rest_pusat[0]->qty_rusak;
                $ArrHist2[$key]['qty_rusak_akhir']     = $rest_pusat[0]->qty_rusak;
                $ArrHist2[$key]['no_ipp']             = $kode_trans;
                $ArrHist2[$key]['jumlah_mat']         = $value;
                $ArrHist2[$key]['ket']                 = 'penambahan gudang';
                $ArrHist2[$key]['update_by']         = $UserName;
                $ArrHist2[$key]['update_date']         = $dateTime;

                $ArrHistPerDay2[$key]['id_material'] = $key;
                $ArrHistPerDay2[$key]['nm_material'] = $rest_pusat[0]->nm_material;
                $ArrHistPerDay2[$key]['id_gudang'] = $id_gudang_ke;
                $ArrHistPerDay2[$key]['qty_stock'] = $rest_pusat[0]->qty_stock + $value;
                $ArrHistPerDay2[$key]['qty_booking'] = 0;
                $ArrHistPerDay2[$key]['qty_rusak'] = 0;
                $ArrHistPerDay2[$key]['hist_date'] = date('Y-m-d H:i:s');
            } else {
                $restMat    = $CI->db->get_where('accessories', array('id' => $key))->result();

                $ArrStockInsert2[$key]['id_material']     = $key;
                $ArrStockInsert2[$key]['nm_material']     = $restMat[0]->stock_name;
                $ArrStockInsert2[$key]['id_gudang']     = $id_gudang_ke;
                $ArrStockInsert2[$key]['kd_gudang']     = $kd_gudang_ke;
                $ArrStockInsert2[$key]['qty_stock']     = $value;
                $ArrStockInsert2[$key]['update_by']     = $UserName;
                $ArrStockInsert2[$key]['update_date']     = $dateTime;

                $ArrHistInsert2[$key]['id_material']     = $key;
                $ArrHistInsert2[$key]['nm_material']     = $restMat[0]->stock_name;
                $ArrHistInsert2[$key]['id_gudang']         = $id_gudang_ke;
                $ArrHistInsert2[$key]['kd_gudang']         = $kd_gudang_ke;
                $ArrHistInsert2[$key]['id_gudang_dari'] = $id_gudang_dari;
                $ArrHistInsert2[$key]['kd_gudang_dari'] = $kd_gudang_dari;
                $ArrHistInsert2[$key]['id_gudang_ke']     = $id_gudang_ke;
                $ArrHistInsert2[$key]['kd_gudang_ke']     = $kd_gudang_ke;
                $ArrHistInsert2[$key]['qty_stock_awal']     = 0;
                $ArrHistInsert2[$key]['qty_stock_akhir']     = $value;
                $ArrHistInsert2[$key]['qty_booking_awal']     = 0;
                $ArrHistInsert2[$key]['qty_booking_akhir']  = 0;
                $ArrHistInsert2[$key]['qty_rusak_awal']     = 0;
                $ArrHistInsert2[$key]['qty_rusak_akhir']     = 0;
                $ArrHistInsert2[$key]['no_ipp']             = $kode_trans;
                $ArrHistInsert2[$key]['jumlah_mat']         = $value;
                $ArrHistInsert2[$key]['ket']                 = 'penambahan gudang stok (insert new)';
                $ArrHistInsert2[$key]['update_by']             = $UserName;
                $ArrHistInsert2[$key]['update_date']         = $dateTime;

                $ArrHistPerDay2[$key]['id_material'] = $key;
                $ArrHistPerDay2[$key]['nm_material'] = $restMat[0]->stock_name;
                $ArrHistPerDay2[$key]['id_gudang'] = $id_gudang_ke;
                $ArrHistPerDay2[$key]['qty_stock'] = $value;
                $ArrHistPerDay2[$key]['qty_booking'] = 0;
                $ArrHistPerDay2[$key]['qty_rusak'] = 0;
                $ArrHistPerDay2[$key]['hist_date'] = date('Y-m-d H:i:s');
                // print_r($ArrHistPerDay);
            }
        }
    }

    // print_r($ArrStock);
    // print_r($ArrHist);
    // print_r($ArrStockInsert);
    // print_r($ArrHistInsert);
    // print_r($ArrStock2);
    // print_r($ArrHist2);
    // print_r($ArrStockInsert2);
    // print_r($ArrHistInsert2);
    // print_r($ArrHistPerDay);
    // print_r($id_gudang_ke);
    // exit;

    if (!empty($ArrStock)) {
        $CI->db->update_batch('warehouse_stock', $ArrStock, 'id');
    }
    if (!empty($ArrHist)) {
        $CI->db->insert_batch('warehouse_history', $ArrHist);
    }

    if (!empty($ArrStockInsert)) {
        $CI->db->insert_batch('warehouse_stock', $ArrStockInsert);
    }
    if (!empty($ArrHistInsert)) {
        $CI->db->insert_batch('warehouse_history', $ArrHistInsert);
    }

    if (!empty($ArrStock2)) {
        $CI->db->update_batch('warehouse_stock', $ArrStock2, 'id');
    }
    if (!empty($ArrHist2)) {
        $CI->db->insert_batch('warehouse_history', $ArrHist2);
    }

    if (!empty($ArrStockInsert2)) {
        $CI->db->insert_batch('warehouse_stock', $ArrStockInsert2);
    }
    if (!empty($ArrHistInsert2)) {
        $CI->db->insert_batch('warehouse_history', $ArrHistInsert2);
    }

    if (!empty($ArrHistPerDay)) {
        $CI->db->insert_batch('warehouse_stock_per_day', $ArrHistPerDay);
    }
    if (!empty($ArrHistPerDay2)) {
        $CI->db->insert_batch('warehouse_stock_per_day', $ArrHistPerDay2);
    }
}

function getPembedaAccessories($id_gudang)
{
    $Category = 0;
    if ($id_gudang == '17') {
        $Category = 'general';
    }
    if ($id_gudang == '19') {
        $Category = 'sparepart';
    }
    if ($id_gudang == '21') {
        $Category = 'atk';
    }
    return $Category;
}

function generateNoTransaksiLainnya()
{
    $CI = &get_instance();
    $Ym             = date('ym');
    $srcMtr            = "SELECT MAX(kode_trans) as maxP FROM warehouse_adjustment WHERE kode_trans LIKE 'TRA" . $Ym . "%' ";
    $resultMtr        = $CI->db->query($srcMtr)->result_array();
    $angkaUrut2        = $resultMtr[0]['maxP'];
    $urutan2        = (int)substr($angkaUrut2, 7, 4);
    $urutan2++;
    $urut2            = sprintf('%04s', $urutan2);
    $kode_trans        = "TRA" . $Ym . $urut2;

    return $kode_trans;
}

function get_accessories()
{
    $CI = &get_instance();
    $listGetCategory = $CI->db->get('accessories')->result_array();
    $ArrGetCategory = [];
    foreach ($listGetCategory as $key => $value) {
        $ArrGetCategory[$value['id']]['nama_full'] = $value['stock_name'] . ' ' . $value['brand'] . ' ' . $value['spec'];
        $ArrGetCategory[$value['id']]['konversi'] = $value['konversi'];
        $ArrGetCategory[$value['id']]['nama'] = $value['stock_name'];
        $ArrGetCategory[$value['id']]['code'] = $value['id_stock'];
        $ArrGetCategory[$value['id']]['id_unit'] = $value['id_unit'];
        $ArrGetCategory[$value['id']]['id_packing'] = $value['id_unit_gudang'];
    }
    return $ArrGetCategory;
}

function generate_no_costbook($lebih = null)
{
    $CI = &get_instance();
    $generate_id = $CI->db->query("SELECT MAX(id) AS max_id FROM tr_cost_book WHERE id LIKE '%CBO-" . date('Y-m-') . "%'")->row();
    $kodeBarang = $generate_id->max_id;
    $urutan = (int) substr($kodeBarang, 13, 5);
    $urutan++;
    if ($lebih !== null) {
        $urutan++;
    }
    $tahun = date('Y-m-');
    $huruf = "CBO-";
    $kodecollect = $huruf . $tahun . sprintf("%06s", $urutan);

    return $kodecollect;
}

function get_list_satuan()
{
    $CI = &get_instance();
    $listGetCategory = $CI->db->get_where('ms_satuan', array('deleted_date' => NULL))->result_array();
    $ArrGetCategory = [];
    foreach ($listGetCategory as $key => $value) {
        $ArrGetCategory[$value['id']]['code']     = $value['code'];
    }
    return $ArrGetCategory;
}

function getStokBarang($id_gudang)
{
    $CI = &get_instance();
    $listGetCategory =     $CI->db
        ->select('
											a.id_material,
											a.qty_stock,
											a.qty_booking,
											b.konversi
										')
        ->join('accessories b', 'a.id_material=b.id')
        ->get_where('warehouse_stock a', array('a.id_gudang' => $id_gudang))->result_array();
    $ArrGetCategory     = [];
    foreach ($listGetCategory as $key => $value) {
        $stok_packing = 0;
        if ($value['qty_stock'] > 0 and $value['konversi'] > 0) {
            $stok_packing = $value['qty_stock'] / $value['konversi'];
        }

        $id_material = $value['id_material'];
        $ArrGetCategory[$id_material]['stok']     = $value['qty_stock'];
        $ArrGetCategory[$id_material]['stok_packing']     = $stok_packing;
        $ArrGetCategory[$id_material]['konversi']     = $value['konversi'];
    }
    return $ArrGetCategory;
}

function generateNoTransaksiStok()
{
    $CI = &get_instance();
    $Ym             = date('ym');
    $srcMtr            = "SELECT MAX(kode_trans) as maxP FROM warehouse_adjustment WHERE kode_trans LIKE 'TRM" . $Ym . "%' ";
    $resultMtr        = $CI->db->query($srcMtr)->result_array();
    $angkaUrut2        = $resultMtr[0]['maxP'];
    $urutan2        = (int)substr($angkaUrut2, 7, 4);
    $urutan2++;
    $urut2            = sprintf('%04s', $urutan2);
    $kode_trans        = "TRM" . $Ym . $urut2;

    return $kode_trans;
}

function getStokBarangHistory($id_gudang, $date_filter)
{
    $CI = &get_instance();
    $listGetCategory =     $CI->db
        ->select('
											a.id_material,
											a.qty_stock,
											a.qty_booking,
											b.konversi
										')
        ->join('accessories b', 'a.id_material=b.id')
        ->get_where('warehouse_stock_per_day a', array('a.id_gudang' => $id_gudang, 'DATE(a.hist_date)' => $date_filter))->result_array();
    $ArrGetCategory     = [];
    foreach ($listGetCategory as $key => $value) {
        $stok_packing = 0;
        if ($value['qty_stock'] > 0 and $value['konversi'] > 0) {
            $stok_packing = $value['qty_stock'] / $value['konversi'];
        }
        $ArrGetCategory[$value['id_material']]['stok']     = $value['qty_stock'];
        $ArrGetCategory[$value['id_material']]['stok_packing']     = $stok_packing;
        $ArrGetCategory[$value['id_material']]['konversi']     = $value['konversi'];
    }
    return $ArrGetCategory;
}

function whiteCenterBold()
{
    $styleArray = array(
        'alignment' => array(
            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER
        ),
        'font' => array(
            'bold' => true,
        ),
        'borders' => array(
            'allborders' => array(
                'style' => PHPExcel_Style_Border::BORDER_THIN,
                'color' => array('rgb' => '000000')
            )
        )
    );
    return $styleArray;
}

function whiteRightBold()
{
    $styleArray = array(
        'alignment' => array(
            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT
        ),
        'font' => array(
            'bold' => true,
        ),
        'borders' => array(
            'allborders' => array(
                'style' => PHPExcel_Style_Border::BORDER_THIN,
                'color' => array('rgb' => '000000')
            )
        )
    );
    return $styleArray;
}

function whiteCenter()
{
    $styleArray = array(
        'alignment' => array(
            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER
        ),
        'borders' => array(
            'allborders' => array(
                'style' => PHPExcel_Style_Border::BORDER_THIN,
                'color' => array('rgb' => '000000')
            )
        )
    );
    return $styleArray;
}

function mainTitle()
{
    $styleArray = array(
        'fill' => array(
            'type' => PHPExcel_Style_Fill::FILL_SOLID,
            'color' => array('rgb' => 'e0e0e0'),
        ),
        'font' => array(
            'bold' => true,
        ),
        'alignment' => array(
            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
            'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
        )
    );
    return $styleArray;
}

function tableHeader()
{
    $styleArray = array(
        'borders' => array(
            'allborders' => array(
                'style' => PHPExcel_Style_Border::BORDER_THIN,
                'color' => array('rgb' => '000000')
            )
        ),
        'fill' => array(
            'type' => PHPExcel_Style_Fill::FILL_SOLID,
            'color' => array('rgb' => 'e0e0e0'),
        ),
        'font' => array(
            'bold' => true,
        ),
        'alignment' => array(
            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
            'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
        )
    );
    return $styleArray;
}

function tableBodyCenter()
{
    $styleArray = array(
        'alignment' => array(
            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER
        ),
        'borders' => array(
            'allborders' => array(
                'style' => PHPExcel_Style_Border::BORDER_THIN,
                'color' => array('rgb' => '000000')
            )
        )
    );
    return $styleArray;
}

function tableBodyLeft()
{
    $styleArray = array(
        'alignment' => array(
            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT
        ),
        'borders' => array(
            'allborders' => array(
                'style' => PHPExcel_Style_Border::BORDER_THIN,
                'color' => array('rgb' => '000000')
            )
        )
    );
    return $styleArray;
}

function tableBodyRight()
{
    $styleArray = array(
        'alignment' => array(
            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT
        ),
        'borders' => array(
            'allborders' => array(
                'style' => PHPExcel_Style_Border::BORDER_THIN,
                'color' => array('rgb' => '000000')
            )
        )
    );
    return $styleArray;
}

function move_warehouse_stok_adjustment($ArrUpdateStock = null, $id_gudang_dari = null, $id_gudang_ke = null, $kode_trans = null, $keterangan = null)
{
    $CI     = &get_instance();
    $dateTime        = date('Y-m-d H:i:s');
    $UserName         = $CI->auth->user_id();

    $kd_gudang_ke         = strtoupper(get_name('warehouse', 'nm_gudang', 'id', $id_gudang_ke));
    $kd_gudang_dari     = 'adjustment ' . $keterangan;

    if ($id_gudang_dari != null) {
        $kd_gudang_dari    = strtoupper(get_name('warehouse', 'nm_gudang', 'id', $id_gudang_dari));
    }
    //grouping sum
    $temp = [];
    foreach ($ArrUpdateStock as $value) {
        if (!array_key_exists($value['id'], $temp)) {
            $temp[$value['id']] = 0;
        }
        $temp[$value['id']] += $value['qty'];
    }

    $ArrStock = array();
    $ArrHist = array();
    $ArrStockInsert = array();
    $ArrHistInsert = array();

    $ArrStock2 = array();
    $ArrHist2 = array();
    $ArrStockInsert2 = array();
    $ArrHistInsert2 = array();

    foreach ($temp as $key => $value) {
        //PENGURANGAN GUDANG
        if ($id_gudang_dari != null) {
            $rest_pusat = $CI->db->get_where('warehouse_stock', array('id_gudang' => $id_gudang_dari, 'id_material' => $key))->result();

            if (!empty($rest_pusat)) {
                $ArrStock[$key]['id']             = $rest_pusat[0]->id;
                $ArrStock[$key]['qty_stock']     = $rest_pusat[0]->qty_stock - $value;
                $ArrStock[$key]['update_by']     = $UserName;
                $ArrStock[$key]['update_date']     = $dateTime;

                $ArrHist[$key]['id_material']     = $key;
                $ArrHist[$key]['nm_material']     = $rest_pusat[0]->nm_material;
                $ArrHist[$key]['id_gudang']         = $id_gudang_dari;
                $ArrHist[$key]['kd_gudang']         = $kd_gudang_dari;
                $ArrHist[$key]['id_gudang_dari']     = $id_gudang_dari;
                $ArrHist[$key]['kd_gudang_dari']     = $kd_gudang_dari;
                $ArrHist[$key]['id_gudang_ke']         = $id_gudang_ke;
                $ArrHist[$key]['kd_gudang_ke']         = $kd_gudang_ke;
                $ArrHist[$key]['qty_stock_awal']     = $rest_pusat[0]->qty_stock;
                $ArrHist[$key]['qty_stock_akhir']     = $rest_pusat[0]->qty_stock - $value;
                $ArrHist[$key]['qty_booking_awal']     = $rest_pusat[0]->qty_booking;
                $ArrHist[$key]['qty_booking_akhir'] = $rest_pusat[0]->qty_booking;
                $ArrHist[$key]['qty_rusak_awal']     = $rest_pusat[0]->qty_rusak;
                $ArrHist[$key]['qty_rusak_akhir']     = $rest_pusat[0]->qty_rusak;
                $ArrHist[$key]['no_ipp']             = $kode_trans;
                $ArrHist[$key]['jumlah_mat']         = $value;
                $ArrHist[$key]['ket']                 = 'pengurangan gudang ' . $keterangan;
                $ArrHist[$key]['update_by']         = $UserName;
                $ArrHist[$key]['update_date']         = $dateTime;
            } else {
                $restMat    = $CI->db->select('stock_name AS nama')->get_where('accessories', array('id' => $key))->result();

                $ArrStockInsert[$key]['id_material']     = $key;
                $ArrStockInsert[$key]['nm_material']     = $restMat[0]->nama;
                $ArrStockInsert[$key]['id_gudang']         = $id_gudang_dari;
                $ArrStockInsert[$key]['kd_gudang']         = $kd_gudang_dari;
                $ArrStockInsert[$key]['qty_stock']         = 0 - $value;
                $ArrStockInsert[$key]['update_by']         = $UserName;
                $ArrStockInsert[$key]['update_date']     = $dateTime;

                $ArrHistInsert[$key]['id_material']     = $key;
                $ArrHistInsert[$key]['nm_material']     = $restMat[0]->nama;
                $ArrHistInsert[$key]['id_gudang']         = $id_gudang_dari;
                $ArrHistInsert[$key]['kd_gudang']         = $kd_gudang_dari;
                $ArrHistInsert[$key]['id_gudang_dari']     = $id_gudang_dari;
                $ArrHistInsert[$key]['kd_gudang_dari']     = $kd_gudang_dari;
                $ArrHistInsert[$key]['id_gudang_ke']     = $id_gudang_ke;
                $ArrHistInsert[$key]['kd_gudang_ke']     = $kd_gudang_ke;
                $ArrHistInsert[$key]['qty_stock_awal']         = 0;
                $ArrHistInsert[$key]['qty_stock_akhir']     = 0 - $value;
                $ArrHistInsert[$key]['qty_booking_awal']    = 0;
                $ArrHistInsert[$key]['qty_booking_akhir']   = 0;
                $ArrHistInsert[$key]['qty_rusak_awal']         = 0;
                $ArrHistInsert[$key]['qty_rusak_akhir']     = 0;
                $ArrHistInsert[$key]['no_ipp']             = $kode_trans;
                $ArrHistInsert[$key]['jumlah_mat']         = $value;
                $ArrHistInsert[$key]['ket']             = 'pengurangan gudang (insert new) ' . $keterangan;
                $ArrHistInsert[$key]['update_by']         = $UserName;
                $ArrHistInsert[$key]['update_date']     = $dateTime;
            }
        }

        //PENAMBAHAN GUDANG
        if ($id_gudang_ke != null) {
            $rest_pusat = $CI->db->get_where('warehouse_stock', array('id_gudang' => $id_gudang_ke, 'id_material' => $key))->result();

            if (!empty($rest_pusat)) {
                $ArrStock2[$key]['id']             = $rest_pusat[0]->id;
                if ($keterangan == 'minus') {
                    $ArrStock2[$key]['qty_stock']     = $rest_pusat[0]->qty_stock - $value;
                } else {
                    $ArrStock2[$key]['qty_stock']     = $rest_pusat[0]->qty_stock + $value;
                }
                $ArrStock2[$key]['update_by']     =  $UserName;
                $ArrStock2[$key]['update_date']     = $dateTime;

                $ArrHist2[$key]['id_material']         = $key;
                $ArrHist2[$key]['nm_material']         = $rest_pusat[0]->nm_material;
                $ArrHist2[$key]['id_gudang']         = $id_gudang_ke;
                $ArrHist2[$key]['kd_gudang']         = $kd_gudang_ke;
                $ArrHist2[$key]['id_gudang_dari']     = $id_gudang_dari;
                $ArrHist2[$key]['kd_gudang_dari']     = $kd_gudang_dari;
                $ArrHist2[$key]['id_gudang_ke']     = $id_gudang_ke;
                $ArrHist2[$key]['kd_gudang_ke']     = $kd_gudang_ke;
                $ArrHist2[$key]['qty_stock_awal']     = $rest_pusat[0]->qty_stock;
                if ($keterangan == 'minus') {
                    $ArrHist2[$key]['qty_stock_akhir']     = $rest_pusat[0]->qty_stock - $value;
                } else {
                    $ArrHist2[$key]['qty_stock_akhir']     = $rest_pusat[0]->qty_stock + $value;
                }

                $ArrHist2[$key]['qty_booking_awal'] = $rest_pusat[0]->qty_booking;
                $ArrHist2[$key]['qty_booking_akhir'] = $rest_pusat[0]->qty_booking;
                $ArrHist2[$key]['qty_rusak_awal']     = $rest_pusat[0]->qty_rusak;
                $ArrHist2[$key]['qty_rusak_akhir']     = $rest_pusat[0]->qty_rusak;
                $ArrHist2[$key]['no_ipp']             = $kode_trans;
                $ArrHist2[$key]['jumlah_mat']         = $value;
                if ($keterangan == 'minus') {
                    $ArrHist2[$key]['ket']                 = 'pengurangan gudang ' . $keterangan;
                } else {
                    $ArrHist2[$key]['ket']                 = 'penambahan gudang ' . $keterangan;
                }

                $ArrHist2[$key]['update_by']         = $UserName;
                $ArrHist2[$key]['update_date']         = $dateTime;
            } else {
                $restMat    = $CI->db->select('stock_name AS nama')->get_where('accessories', array('id' => $key))->result();

                $ArrStockInsert2[$key]['id_material']     = $key;
                $ArrStockInsert2[$key]['nm_material']     = $restMat[0]->nama;
                $ArrStockInsert2[$key]['id_gudang']     = $id_gudang_ke;
                $ArrStockInsert2[$key]['kd_gudang']     = $kd_gudang_ke;
                if ($keterangan == 'minus') {
                    $ArrStockInsert2[$key]['qty_stock']     = $value * -1;
                } else {
                    $ArrStockInsert2[$key]['qty_stock']     = $value;
                }

                $ArrStockInsert2[$key]['update_by']     = $UserName;
                $ArrStockInsert2[$key]['update_date']     = $dateTime;

                $ArrHistInsert2[$key]['id_material']     = $key;
                $ArrHistInsert2[$key]['nm_material']     = $restMat[0]->nama;
                $ArrHistInsert2[$key]['id_gudang']         = $id_gudang_ke;
                $ArrHistInsert2[$key]['kd_gudang']         = $kd_gudang_ke;
                $ArrHistInsert2[$key]['id_gudang_dari'] = $id_gudang_dari;
                $ArrHistInsert2[$key]['kd_gudang_dari'] = $kd_gudang_dari;
                $ArrHistInsert2[$key]['id_gudang_ke']     = $id_gudang_ke;
                $ArrHistInsert2[$key]['kd_gudang_ke']     = $kd_gudang_ke;
                $ArrHistInsert2[$key]['qty_stock_awal']     = 0;
                if ($keterangan == 'minus') {
                    $ArrHistInsert2[$key]['qty_stock_akhir']     = $value * -1;
                } else {
                    $ArrHistInsert2[$key]['qty_stock_akhir']     = $value;
                }

                $ArrHistInsert2[$key]['qty_booking_awal']     = 0;
                $ArrHistInsert2[$key]['qty_booking_akhir']  = 0;
                $ArrHistInsert2[$key]['qty_rusak_awal']     = 0;
                $ArrHistInsert2[$key]['qty_rusak_akhir']     = 0;
                $ArrHistInsert2[$key]['no_ipp']             = $kode_trans;
                $ArrHistInsert2[$key]['jumlah_mat']         = $value;
                if ($keterangan == 'minus') {
                    $ArrHistInsert2[$key]['ket']                 = 'pengurangan gudang (insert new) ' . $keterangan;
                } else {
                    $ArrHistInsert2[$key]['ket']                 = 'penambahan gudang (insert new) ' . $keterangan;
                }

                $ArrHistInsert2[$key]['update_by']             = $UserName;
                $ArrHistInsert2[$key]['update_date']         = $dateTime;
            }
        }
    }

    // print_r($ArrStock);
    // print_r($ArrHist);
    // print_r($ArrStockInsert);
    // print_r($ArrHistInsert);
    // print_r($ArrStock2);
    // print_r($ArrHist2);
    // print_r($ArrStockInsert2);
    // print_r($ArrHistInsert2);
    // exit;

    if (!empty($ArrStock)) {
        $CI->db->update_batch('warehouse_stock', $ArrStock, 'id');
    }
    if (!empty($ArrHist)) {
        $CI->db->insert_batch('warehouse_history', $ArrHist);
    }

    if (!empty($ArrStockInsert)) {
        $CI->db->insert_batch('warehouse_stock', $ArrStockInsert);
    }
    if (!empty($ArrHistInsert)) {
        $CI->db->insert_batch('warehouse_history', $ArrHistInsert);
    }

    if (!empty($ArrStock2)) {
        $CI->db->update_batch('warehouse_stock', $ArrStock2, 'id');
    }
    if (!empty($ArrHist2)) {
        $CI->db->insert_batch('warehouse_history', $ArrHist2);
    }

    if (!empty($ArrStockInsert2)) {
        $CI->db->insert_batch('warehouse_stock', $ArrStockInsert2);
    }
    if (!empty($ArrHistInsert2)) {
        $CI->db->insert_batch('warehouse_history', $ArrHistInsert2);
    }
}

function insert_jurnal_department($ArrData, $GudangFrom, $GudangTo, $kode_trans, $category, $ket_min, $ket_plus)
{
    $CI     = &get_instance();
    $UserName        = $CI->auth->user_name();
    $DateTime        = date('Y-m-d H:i:s');

    $getHeaderAdjust = $CI->db->get_where('warehouse_adjustment', array('kode_trans' => $kode_trans))->result();
    $DATE_JURNAL = (!empty($getHeaderAdjust[0]->tanggal)) ? $getHeaderAdjust[0]->tanggal : $getHeaderAdjust[0]->created_date;

    $SUM_PRICE = 0;
    $ArrDetail = [];
    $ArrDetailNew = [];
    foreach ($ArrData as $key => $value) {
        $PRICE     = $value['unit_price'];
        $QTY     = $value['qty'];
        $TOTAL     = $PRICE * $QTY;
        $SUM_PRICE += $TOTAL;

        $ArrDetail[$key]['kode_trans']         = $kode_trans;
        $ArrDetail[$key]['id_material']     = $value['id_barang'];
        $ArrDetail[$key]['price_book']         = $PRICE;
        $ArrDetail[$key]['berat']             = $QTY;
        $ArrDetail[$key]['amount']             = $TOTAL;
        $ArrDetail[$key]['updated_by']         = $UserName;
        $ArrDetail[$key]['updated_date']     = $DateTime;

        $ArrDetailNew[$key]['kode_trans']     = $kode_trans;
        $ArrDetailNew[$key]['no_ipp']         = $value['no_po'];
        $ArrDetailNew[$key]['category']     = $category;
        $ArrDetailNew[$key]['gudang_dari']     = $GudangFrom;
        $ArrDetailNew[$key]['gudang_ke']     = $GudangTo;
        $ArrDetailNew[$key]['tanggal']         = date('Y-m-d', strtotime($DATE_JURNAL));
        $ArrDetailNew[$key]['id_material']     = $value['id_barang'];
        $ArrDetailNew[$key]['nm_material']     = $value['nm_barang'];
        $ArrDetailNew[$key]['cost_book']     = $PRICE;
        $ArrDetailNew[$key]['qty']             = $QTY;
        $ArrDetailNew[$key]['total_nilai']     = $TOTAL;
        $ArrDetailNew[$key]['created_by']     = $UserName;
        $ArrDetailNew[$key]['created_date'] = $DateTime;
    }

    //DEBET
    $ArrJurnal[0]['category'] = $category;
    $ArrJurnal[0]['posisi'] = 'DEBIT';
    $ArrJurnal[0]['amount'] = $SUM_PRICE;
    $ArrJurnal[0]['gudang'] = $GudangTo;
    $ArrJurnal[0]['keterangan'] = $ket_plus;
    $ArrJurnal[0]['kode_trans'] = $kode_trans;
    $ArrJurnal[0]['updated_by'] = $UserName;
    $ArrJurnal[0]['updated_date'] = $DateTime;

    //KREDIT
    $ArrJurnal[1]['category'] = $category;
    $ArrJurnal[1]['posisi'] = 'KREDIT';
    $ArrJurnal[1]['amount'] = $SUM_PRICE;
    $ArrJurnal[1]['gudang'] = $GudangFrom;
    $ArrJurnal[1]['keterangan'] = $ket_min;
    $ArrJurnal[1]['kode_trans'] = $kode_trans;
    $ArrJurnal[1]['updated_by'] = $UserName;
    $ArrJurnal[1]['updated_date'] = $DateTime;

    $CI->db->insert_batch('jurnal_temp', $ArrJurnal);
    $CI->db->insert_batch('jurnal_temp_detail', $ArrDetail);
    $CI->db->insert_batch('jurnal', $ArrDetailNew);
    // if ($category == 'incoming department') {
    //     auto_jurnal_product($kode_trans, $category);
    // }
    // if ($category == 'incoming asset') {
    //     auto_jurnal_product($kode_trans, $category);
    // }
}

// function auto_jurnal_product($ArrDetailProduct, $ket)
// {
//     $CI     = &get_instance();
//     $CI->load->model('Jurnal_model');
//     $CI->load->model('Acc_model');
//     $data_session    = $CI->session->userdata;
//     $UserName        = $data_session['ORI_User']['username'];
//     $DateTime        = date('Y-m-d H:i:s');


//     if ($ket == 'WIP - FINISH GOOD') {
//         $kodejurnal = 'JV005';
//         foreach ($ArrDetailProduct as $keys => $values) {
//             $id = $values['id_detail'];
//             $datajurnal = $CI->db->query("SELECT a.*, b.coa,b.coa_fg FROM jurnal_product a join product_parent b on a.product=b.product_parent WHERE a.category='quality control' and a.status_jurnal='0' and a.id_detail ='$id' limit 1")->row();
//             $id = $datajurnal->id;
//             $tgl_voucher = $datajurnal->tanggal;
//             $no_request = $id;

//             //$datasodetailheader = $CI->db->query("SELECT * FROM so_detail_header WHERE id ='".$datajurnal->id_milik."' limit 1" )->row();
//             $datasodetailheader = $CI->db->query("SELECT * FROM laporan_per_hari_action WHERE id_milik ='" . $datajurnal->id_milik . "' limit 1")->row();

//             // print_r($datajurnal->id_milik);
//             // exit;


//             $kurs = 1;
//             $sqlkurs = "select * from ms_kurs where tanggal <='" . $datajurnal->tanggal . "' and mata_uang='USD' order by tanggal desc limit 1";
//             $dtkurs    = $CI->db->query($sqlkurs)->row();
//             if (!empty($dtkurs)) $kurs = $dtkurs->kurs;
//             $data_pro_det = $CI->db->query("SELECT * FROM production_detail WHERE id='" . $datajurnal->id_detail . "' and id_milik ='" . $datajurnal->id_milik . "' limit 1")->row();
//             $dataprodet = "";
//             if (!empty($data_pro_det)) {
//                 if ($data_pro_det->finish_good > 0) {
//                     $dataprodet = $data_pro_det->id;
//                     $wip_material = $data_pro_det->wip_material;
//                     $pe_direct_labour = $data_pro_det->wip_dl;
//                     $foh = $data_pro_det->wip_foh;
//                     $pe_indirect_labour = $data_pro_det->wip_il;
//                     $pe_consumable = $data_pro_det->wip_consumable;
//                     $finish_good = $data_pro_det->finish_good;
//                 }
//             }
//             if ($dataprodet == "") {
//                 $wip_material = $datajurnal->total_nilai;
//                 $pe_direct_labour = (($datasodetailheader->direct_labour * $datasodetailheader->man_hours) * $kurs);
//                 $pe_indirect_labour = (($datasodetailheader->indirect_labour * $datasodetailheader->man_hours) * $kurs);
//                 $foh = (($datasodetailheader->machine + $datasodetailheader->mould_mandrill + $datasodetailheader->foh_depresiasi + $datasodetailheader->biaya_rutin_bulanan + $datasodetailheader->foh_consumable) * $kurs);
//                 $pe_consumable = ($datasodetailheader->consumable * $kurs);
//                 $finish_good = ($wip_material + $pe_direct_labour + $foh + $pe_indirect_labour + $pe_consumable);

//                 $CI->db->query("update production_detail set wip_kurs='" . $kurs . "', wip_material='" . $wip_material . "' , wip_dl='" . $pe_direct_labour . "' , wip_foh='" . $foh . "', wip_il='" . $pe_indirect_labour . "', wip_consumable='" . $pe_consumable . "', finish_good='" . $finish_good . "' WHERE id='" . $datajurnal->id_detail . "' and id_milik ='" . $datajurnal->id_milik . "' limit 1");
//             }
//             $masterjurnal    = $CI->Acc_model->GetTemplateJurnal($kodejurnal);
//             $totaldebit = 0;
//             $totalkredit = 0;
//             $coa_cogm = '';
//             $no_spk = $datajurnal->id_spk;
//             $det_Jurnaltes = [];
//             foreach ($masterjurnal as $record) {
//                 $debit = 0;
//                 $kredit = 0;
//                 $nokir      = $record->no_perkiraan;
//                 $posisi     = $record->posisi;
//                 $parameter  = $record->parameter_no;
//                 $keterangan = $record->keterangan;
//                 if ($parameter == '1') {
//                     $debit = ($wip_material);
//                     $det_Jurnaltes[]  = array(
//                         'nomor'         => '',
//                         'tanggal'       => $tgl_voucher,
//                         'tipe'          => 'JV',
//                         'no_perkiraan'  => $nokir,
//                         'keterangan'    => $keterangan . ' ' . $datajurnal->id_spk,
//                         'no_reff'       => $id,
//                         'debet'         => $debit,
//                         'kredit'        => 0,
//                         'jenis_jurnal'  => 'wip finishgood',
//                         'no_request'    => $no_request,
//                         'stspos'          => 1
//                     );
//                 }
//                 if ($parameter == '2') {
//                     $debit = ($pe_direct_labour);
//                     $det_Jurnaltes[]  = array(
//                         'nomor'         => '',
//                         'tanggal'       => $tgl_voucher,
//                         'tipe'          => 'JV',
//                         'no_perkiraan'  => $nokir,
//                         'keterangan'    => $keterangan . ' ' . $datajurnal->id_spk,
//                         'no_reff'       => $id,
//                         'debet'         => $debit,
//                         'kredit'        => 0,
//                         'jenis_jurnal'  => 'wip finishgood',
//                         'no_request'    => $no_request,
//                         'stspos'          => 1
//                     );
//                 }
//                 if ($parameter == '3') {
//                     $debit = ($pe_indirect_labour);
//                     $det_Jurnaltes[]  = array(
//                         'nomor'         => '',
//                         'tanggal'       => $tgl_voucher,
//                         'tipe'          => 'JV',
//                         'no_perkiraan'  => $nokir,
//                         'keterangan'    => $keterangan . ' ' . $datajurnal->id_spk,
//                         'no_reff'       => $id,
//                         'debet'         => $debit,
//                         'kredit'        => 0,
//                         'jenis_jurnal'  => 'wip finishgood',
//                         'no_request'    => $no_request,
//                         'stspos'          => 1
//                     );
//                 }
//                 if ($parameter == '4') {
//                     $debit = ($pe_consumable);
//                     $det_Jurnaltes[]  = array(
//                         'nomor'         => '',
//                         'tanggal'       => $tgl_voucher,
//                         'tipe'          => 'JV',
//                         'no_perkiraan'  => $nokir,
//                         'keterangan'    => $keterangan . ' ' . $datajurnal->id_spk,
//                         'no_reff'       => $id,
//                         'debet'         => $debit,
//                         'kredit'        => 0,
//                         'jenis_jurnal'  => 'wip finishgood',
//                         'no_request'    => $no_request,
//                         'stspos'          => 1
//                     );
//                 }
//                 if ($parameter == '5') {
//                     $debit = ($foh);
//                     $det_Jurnaltes[]  = array(
//                         'nomor'         => '',
//                         'tanggal'       => $tgl_voucher,
//                         'tipe'          => 'JV',
//                         'no_perkiraan'  => $nokir,
//                         'keterangan'    => $keterangan . ' ' . $datajurnal->id_spk,
//                         'no_reff'       => $id,
//                         'debet'         => ($debit),
//                         'kredit'        => 0,
//                         'jenis_jurnal'  => 'wip finishgood',
//                         'no_request'    => $no_request,
//                         'stspos'          => 1
//                     );
//                 }
//                 if ($parameter == '6') {
//                     $kredit = ($wip_material);
//                     $det_Jurnaltes[]  = array(
//                         'nomor'         => '',
//                         'tanggal'       => $tgl_voucher,
//                         'tipe'          => 'JV',
//                         'no_perkiraan'  => $datajurnal->coa,
//                         'keterangan'    => $keterangan . ' ' . $datajurnal->id_spk,
//                         'no_reff'       => $id,
//                         'debet'         => 0,
//                         'kredit'        => $kredit,
//                         'jenis_jurnal'  => 'wip finishgood',
//                         'no_request'    => $no_request,
//                         'stspos'          => 1
//                     );
//                 }
//                 if ($parameter == '7') {
//                     $kredit = ($pe_direct_labour);
//                     $det_Jurnaltes[]  = array(
//                         'nomor'         => '',
//                         'tanggal'       => $tgl_voucher,
//                         'tipe'          => 'JV',
//                         'no_perkiraan'  => $datajurnal->coa,
//                         'keterangan'    => $keterangan . ' ' . $datajurnal->id_spk,
//                         'no_reff'       => $id,
//                         'debet'         => 0,
//                         'kredit'        => $kredit,
//                         'jenis_jurnal'  => 'wip finishgood',
//                         'no_request'    => $no_request,
//                         'stspos'          => 1
//                     );
//                 }
//                 if ($parameter == '8') {
//                     $kredit = ($pe_indirect_labour);
//                     $det_Jurnaltes[]  = array(
//                         'nomor'         => '',
//                         'tanggal'       => $tgl_voucher,
//                         'tipe'          => 'JV',
//                         'no_perkiraan'  => $datajurnal->coa,
//                         'keterangan'    => $keterangan . ' ' . $datajurnal->id_spk,
//                         'no_reff'       => $id,
//                         'debet'         => 0,
//                         'kredit'        => $kredit,
//                         'jenis_jurnal'  => 'wip finishgood',
//                         'no_request'    => $no_request,
//                         'stspos'          => 1
//                     );
//                 }
//                 if ($parameter == '9') {
//                     $kredit = ($pe_consumable);
//                     $det_Jurnaltes[]  = array(
//                         'nomor'         => '',
//                         'tanggal'       => $tgl_voucher,
//                         'tipe'          => 'JV',
//                         'no_perkiraan'  => $datajurnal->coa,
//                         'keterangan'    => $keterangan . ' ' . $datajurnal->id_spk,
//                         'no_reff'       => $id,
//                         'debet'         => 0,
//                         'kredit'        => $kredit,
//                         'jenis_jurnal'  => 'wip finishgood',
//                         'no_request'    => $no_request,
//                         'stspos'          => 1
//                     );
//                 }
//                 if ($parameter == '10') {
//                     $kredit = ($foh);
//                     $det_Jurnaltes[]  = array(
//                         'nomor'         => '',
//                         'tanggal'       => $tgl_voucher,
//                         'tipe'          => 'JV',
//                         'no_perkiraan'  => $datajurnal->coa,
//                         'keterangan'    => $keterangan . ' ' . $datajurnal->id_spk,
//                         'no_reff'       => $id,
//                         'debet'         => 0,
//                         'kredit'        => $kredit,
//                         'jenis_jurnal'  => 'wip finishgood',
//                         'no_request'    => $no_request,
//                         'stspos'          => 1
//                     );
//                 }
//                 if ($parameter == '11') {
//                     $coa_cogm = $nokir;
//                 }
//                 $totaldebit += $debit;
//                 $totalkredit += $kredit;
//             }
//             $Keterangan_INV = ($ket) . ' (' . $datajurnal->no_so . ' - ' . $datajurnal->product . ' - ' . $no_spk . ')';
//             $nilaibayar = $datajurnal->total_nilai;
//             $det_Jurnaltes[]  = array(
//                 'nomor'         => '',
//                 'tanggal'       => $tgl_voucher,
//                 'tipe'          => 'JV',
//                 'no_perkiraan'  => $coa_cogm,
//                 'keterangan'    => $Keterangan_INV,
//                 'no_reff'       => $id,
//                 'debet'         => 0,
//                 'kredit'        => $totalkredit,
//                 'jenis_jurnal'  => 'wip finishgood',
//                 'no_request'    => $no_request,
//                 'stspos'        => 1
//             );
//             $det_Jurnaltes[]  = array(
//                 'nomor'         => '',
//                 'tanggal'       => $tgl_voucher,
//                 'tipe'          => 'JV',
//                 'no_perkiraan'  => $datajurnal->coa_fg,
//                 'keterangan'    => $Keterangan_INV,
//                 'no_reff'       => $id,
//                 'debet'         => $totaldebit,
//                 'kredit'        => 0,
//                 'jenis_jurnal'  => 'wip finishgood',
//                 'no_request'    => $no_request,
//                 'stspos'        => 1
//             );
//             $CI->db->query("delete from jurnaltras WHERE jenis_jurnal='wip finishgood' and no_reff ='$id'");
//             $CI->db->insert_batch('jurnaltras', $det_Jurnaltes);
//             $Nomor_JV = $CI->Jurnal_model->get_Nomor_Jurnal_Sales('101', $tgl_voucher);
//             $Bln    = substr($tgl_voucher, 5, 2);
//             $Thn    = substr($tgl_voucher, 0, 4);
//             $dataJVhead = array('nomor' => $Nomor_JV, 'tgl' => $tgl_voucher, 'jml' => $totalkredit, 'koreksi_no' => '-', 'kdcab' => '101', 'jenis' => 'JV', 'keterangan' => $Keterangan_INV . '-' . $id, 'bulan' => $Bln, 'tahun' => $Thn, 'user_id' => $UserName, 'memo' => $id, 'tgl_jvkoreksi' => $tgl_voucher, 'ho_valid' => '');
//             $CI->db->insert(DBACC . '.javh', $dataJVhead);
//             $datadetail = array();
//             foreach ($det_Jurnaltes as $vals) {
//                 $datadetail = array(
//                     'tipe'            => 'JV',
//                     'nomor'            => $Nomor_JV,
//                     'tanggal'        => $tgl_voucher,
//                     'no_perkiraan'    => $vals['no_perkiraan'],
//                     'keterangan'    => $vals['keterangan'],
//                     'no_reff'        => $vals['no_reff'],
//                     'debet'            => $vals['debet'],
//                     'kredit'        => $vals['kredit'],
//                 );
//                 $CI->db->insert(DBACC . '.jurnal', $datadetail);
//             }
//             $CI->db->query("UPDATE jurnal_product SET status_jurnal='1',approval_by='" . $UserName . "',approval_date='" . $DateTime . "' WHERE id ='$id'");
//             unset($det_Jurnaltes);
//             unset($datadetail);
//         }
//     }
//     if ($ket == 'FINISH GOOD - TRANSIT') {
//         $kodejurnal = 'JV006';
//         foreach ($ArrDetailProduct as $keys => $values) {
//             $id = $values['id_detail'];
//             $datajurnal = $CI->db->query("SELECT a.*, b.coa,b.coa_fg FROM jurnal_product a join product_parent b on a.product=b.product_parent WHERE a.category='delivery' and a.status_jurnal='0' and a.id_detail ='$id' limit 1")->row();
//             $id = $datajurnal->id;
//             $tgl_voucher = $datajurnal->tanggal;
//             $no_request = $id;

//             $dataproductiondetail = $CI->db->query("select * from production_detail where id='" . $datajurnal->id_detail . "' and id_milik ='" . $datajurnal->id_milik . "' limit 1")->row();
//             if ($dataproductiondetail->finish_good == 0) {
//                 //$datasodetailheader = $CI->db->query("SELECT * FROM so_detail_header WHERE id ='".$datajurnal->id_milik."' limit 1" )->row();
//                 $datasodetailheader = $CI->db->query("SELECT * FROM laporan_per_hari_action WHERE id_milik ='" . $datajurnal->id_milik . "' limit 1")->row();


//                 $kurs = 1;
//                 $sqlkurs = "select * from ms_kurs where tanggal <='" . $datajurnal->tanggal . "' and mata_uang='USD' order by tanggal desc limit 1";
//                 $dtkurs    = $CI->db->query($sqlkurs)->row();
//                 if (!empty($dtkurs)) $kurs = $dtkurs->kurs;
//                 $wip_material = $datajurnal->total_nilai;
//                 $pe_direct_labour = (($datasodetailheader->direct_labour * $datasodetailheader->man_hours) * $kurs);
//                 $pe_indirect_labour = (($datasodetailheader->indirect_labour * $datasodetailheader->man_hours) * $kurs);
//                 $foh = (($datasodetailheader->machine + $datasodetailheader->mould_mandrill + $datasodetailheader->foh_depresiasi + $datasodetailheader->biaya_rutin_bulanan + $datasodetailheader->foh_consumable) * $kurs);
//                 $pe_consumable = ($datasodetailheader->consumable * $kurs);
//                 $finish_good = ($wip_material + $pe_direct_labour + $foh + $pe_indirect_labour + $pe_consumable);

//                 $CI->db->query("update production_detail set wip_kurs='" . $kurs . "', wip_material='" . $wip_material . "' , wip_dl='" . $pe_direct_labour . "' , wip_foh='" . $foh . "', wip_il='" . $pe_indirect_labour . "', wip_consumable='" . $pe_consumable . "', finish_good='" . $finish_good . "' WHERE id='" . $datajurnal->id_detail . "' and id_milik ='" . $datajurnal->id_milik . "' limit 1");

//                 $totalall = $finish_good;
//             } else {
//                 $totalall = $dataproductiondetail->finish_good;
//             }
//             $no_spk = $datajurnal->id_spk;
//             $Keterangan_INV = ($ket) . ' (' . $datajurnal->no_so . ' - ' . $datajurnal->product . ' - ' . $no_spk . ' - ' . $datajurnal->no_surat_jalan . ')';
//             $datajurnal       = $CI->Acc_model->GetTemplateJurnal($kodejurnal);
//             $det_Jurnaltes = [];
//             foreach ($datajurnal as $record) {
//                 $tabel  = $record->menu;
//                 $posisi = $record->posisi;
//                 $field  = $record->field;
//                 $nokir  = $record->no_perkiraan;

//                 $totalall2 = (!empty($totalall)) ? $totalall : 0;
//                 $param  = 'id';
//                 if ($posisi == 'D') {
//                     $value_param  = $id;
//                     $val = $CI->Acc_model->GetData($tabel, $field, $param, $value_param);
//                     $nilaibayar = $val[0]->$field;
//                     $det_Jurnaltes[]  = array(
//                         'nomor'         => '',
//                         'tanggal'       => $tgl_voucher,
//                         'tipe'          => 'JV',
//                         'no_perkiraan'  => $nokir,
//                         'keterangan'    => $Keterangan_INV,
//                         'no_reff'       => $no_request,
//                         'debet'         => $totalall2,
//                         'kredit'        => 0,
//                         'jenis_jurnal'  => 'finish good intransit',
//                         'no_request'    => $no_request,
//                         'stspos'        => 1
//                     );
//                 } elseif ($posisi == 'K') {
//                     $coa =     $CI->db->query("SELECT a.*, b.coa,b.coa_fg FROM jurnal_product a join product_parent b on a.product=b.product_parent WHERE a.id ='$id'")->result();
//                     $nokir = $coa[0]->coa_fg;
//                     $det_Jurnaltes[]  = array(
//                         'nomor'         => '',
//                         'tanggal'       => $tgl_voucher,
//                         'tipe'          => 'JV',
//                         'no_perkiraan'  => $nokir,
//                         'keterangan'    => $Keterangan_INV,
//                         'no_reff'       => $no_request,
//                         'debet'         => 0,
//                         'kredit'        => $totalall2,
//                         'jenis_jurnal'  => 'finish good intransit',
//                         'no_request'    => $no_request,
//                         'stspos'        => 1
//                     );
//                 }
//             }
//             $CI->db->query("delete from jurnaltras WHERE jenis_jurnal='finish good intransit' and no_reff ='$id'");
//             $CI->db->insert_batch('jurnaltras', $det_Jurnaltes);
//             $Nomor_JV = $CI->Jurnal_model->get_Nomor_Jurnal_Sales('101', $tgl_voucher);
//             $Bln    = substr($tgl_voucher, 5, 2);
//             $Thn    = substr($tgl_voucher, 0, 4);
//             $dataJVhead = array('nomor' => $Nomor_JV, 'tgl' => $tgl_voucher, 'jml' => $totalall2, 'koreksi_no' => '-', 'kdcab' => '101', 'jenis' => 'JV', 'keterangan' => $Keterangan_INV . '-' . $id, 'bulan' => $Bln, 'tahun' => $Thn, 'user_id' => $UserName, 'memo' => $id, 'tgl_jvkoreksi' => $tgl_voucher, 'ho_valid' => '');
//             $CI->db->insert(DBACC . '.javh', $dataJVhead);
//             $datadetail = array();
//             foreach ($det_Jurnaltes as $vals) {
//                 $datadetail = array(
//                     'tipe'            => 'JV',
//                     'nomor'            => $Nomor_JV,
//                     'tanggal'        => $tgl_voucher,
//                     'no_perkiraan'    => $vals['no_perkiraan'],
//                     'keterangan'    => $vals['keterangan'],
//                     'no_reff'        => $vals['no_reff'],
//                     'debet'            => $vals['debet'],
//                     'kredit'        => $vals['kredit'],
//                 );
//                 $CI->db->insert(DBACC . '.jurnal', $datadetail);
//             }
//             $CI->db->query("UPDATE jurnal_product SET status_jurnal='1',approval_by='" . $UserName . "',approval_date='" . $DateTime . "' WHERE id ='$id'");
//             unset($det_Jurnaltes);
//             unset($datadetail);
//         }
//     }
//     if ($ket == 'TRANSIT - CUSTOMER') {
//         $kodejurnal = 'JV007';
//         foreach ($ArrDetailProduct as $keys => $values) {
//             $id = $values['id_detail'];

//             $datajurnal = $CI->db->query("SELECT a.*, b.coa,b.coa_fg FROM jurnal_product a join product_parent b on a.product=b.product_parent WHERE a.category='diterima customer' and a.status_jurnal='0' and a.id_detail ='$id' limit 1")->row();
//             $id = (!empty($datajurnal->id)) ? $datajurnal->id : 0;
//             $tgl_voucher = (!empty($datajurnal->tanggal)) ? $datajurnal->tanggal : date('Y-m-d');
//             $no_request = $id;

//             $id_detail = (!empty($datajurnal->id_detail)) ? $datajurnal->id_detail : 0;
//             $id_milik = (!empty($datajurnal->id_milik)) ? $datajurnal->id_milik : 0;
//             $total_nilai = (!empty($datajurnal->total_nilai)) ? $datajurnal->total_nilai : 0;
//             $id_spk = (!empty($datajurnal->id_spk)) ? $datajurnal->id_spk : 0;
//             $no_so = (!empty($datajurnal->no_so)) ? $datajurnal->no_so : 0;
//             $product = (!empty($datajurnal->product)) ? $datajurnal->product : 0;
//             $no_surat_jalan = (!empty($datajurnal->no_surat_jalan)) ? $datajurnal->no_surat_jalan : 0;

//             $dataproductiondetail = $CI->db->query("select * from production_detail where id='" . $id_detail . "' and id_milik ='" . $id_milik . "' limit 1")->row();


//             if (!empty($dataproductiondetail->finish_good)) {
//                 if ($dataproductiondetail->finish_good == 0) {
//                     $datasodetailheader = $CI->db->query("SELECT * FROM laporan_per_hari_action WHERE id_milik ='" . $datajurnal->id_milik . "' limit 1")->row();


//                     $kurs = 1;
//                     $sqlkurs = "select * from ms_kurs where tanggal <='" . $datajurnal->tanggal . "' and mata_uang='USD' order by tanggal desc limit 1";
//                     $dtkurs    = $CI->db->query($sqlkurs)->row();
//                     if (!empty($dtkurs)) $kurs = $dtkurs->kurs;
//                     $wip_material = $datajurnal->total_nilai;
//                     $pe_direct_labour = (($datasodetailheader->direct_labour * $datasodetailheader->man_hours) * $kurs);
//                     $pe_indirect_labour = (($datasodetailheader->indirect_labour * $datasodetailheader->man_hours) * $kurs);
//                     $foh = (($datasodetailheader->machine + $datasodetailheader->mould_mandrill + $datasodetailheader->foh_depresiasi + $datasodetailheader->biaya_rutin_bulanan + $datasodetailheader->foh_consumable) * $kurs);
//                     $pe_consumable = ($datasodetailheader->consumable * $kurs);
//                     $finish_good = ($wip_material + $pe_direct_labour + $foh + $pe_indirect_labour + $pe_consumable);

//                     $CI->db->query("update production_detail set wip_kurs='" . $kurs . "', wip_material='" . $wip_material . "' , wip_dl='" . $pe_direct_labour . "' , wip_foh='" . $foh . "', wip_il='" . $pe_indirect_labour . "', wip_consumable='" . $pe_consumable . "', finish_good='" . $finish_good . "' WHERE id='" . $datajurnal->id_detail . "' and id_milik ='" . $datajurnal->id_milik . "' limit 1");

//                     $totalall = $finish_good;
//                 } else {
//                     $totalall = (!empty($dataproductiondetail->finish_good)) ? $dataproductiondetail->finish_good : 0;
//                 }
//             }

//             // print_r($totalall);
//             // exit;

//             $no_spk = $id_spk;
//             $Keterangan_INV = ($ket) . ' (' . $no_so . ' - ' . $product . ' - ' . $no_spk . ' - ' . $no_surat_jalan . ')';
//             $datajurnal       = $CI->Acc_model->GetTemplateJurnal($kodejurnal);
//             $det_Jurnaltes = [];
//             if (!empty($datajurnal)) {
//                 foreach ($datajurnal as $record) {
//                     $tabel  = $record->menu;
//                     $posisi = $record->posisi;
//                     $field  = $record->field;
//                     $nokir  = $record->no_perkiraan;
//                     $totalall2 = (!empty($totalall)) ? $totalall : 0;
//                     $param  = 'id';
//                     if ($posisi == 'D') {
//                         $value_param  = $id;
//                         $val = $CI->Acc_model->GetData($tabel, $field, $param, $value_param);
//                         $nilaibayar = (!empty($val[0]->$field)) ? $val[0]->$field : 0;
//                         $det_Jurnaltes[]  = array(
//                             'nomor'         => '',
//                             'tanggal'       => $tgl_voucher,
//                             'tipe'          => 'JV',
//                             'no_perkiraan'  => $nokir,
//                             'keterangan'    => $Keterangan_INV,
//                             'no_reff'       => $no_request,
//                             'debet'         => $totalall2,
//                             'kredit'        => 0,
//                             'jenis_jurnal'  => 'intransit incustomer',
//                             'no_request'    => $no_request,
//                             'stspos'        => 1
//                         );
//                     } elseif ($posisi == 'K') {
//                         $det_Jurnaltes[]  = array(
//                             'nomor'         => '',
//                             'tanggal'       => $tgl_voucher,
//                             'tipe'          => 'JV',
//                             'no_perkiraan'  => $nokir,
//                             'keterangan'    => $Keterangan_INV,
//                             'no_reff'       => $no_request,
//                             'debet'         => 0,
//                             'kredit'        => $totalall2,
//                             'jenis_jurnal'  => 'intransit incustomer',
//                             'no_request'    => $no_request,
//                             'stspos'        => 1
//                         );
//                     }
//                 }
//             }
//             $CI->db->query("delete from jurnaltras WHERE jenis_jurnal='diterima customer' and no_reff ='$id'");
//             $CI->db->insert_batch('jurnaltras', $det_Jurnaltes);
//             $Nomor_JV = $CI->Jurnal_model->get_Nomor_Jurnal_Sales('101', $tgl_voucher);
//             $Bln    = substr($tgl_voucher, 5, 2);
//             $Thn    = substr($tgl_voucher, 0, 4);
//             $dataJVhead = array('nomor' => $Nomor_JV, 'tgl' => $tgl_voucher, 'jml' => $totalall2, 'koreksi_no' => '-', 'kdcab' => '101', 'jenis' => 'JV', 'keterangan' => $Keterangan_INV . '-' . $id, 'bulan' => $Bln, 'tahun' => $Thn, 'user_id' => $UserName, 'memo' => $id, 'tgl_jvkoreksi' => $tgl_voucher, 'ho_valid' => '');
//             $CI->db->insert(DBACC . '.javh', $dataJVhead);
//             $datadetail = array();
//             foreach ($det_Jurnaltes as $vals) {
//                 $datadetail = array(
//                     'tipe'            => 'JV',
//                     'nomor'            => $Nomor_JV,
//                     'tanggal'        => $tgl_voucher,
//                     'no_perkiraan'    => $vals['no_perkiraan'],
//                     'keterangan'    => $vals['keterangan'],
//                     'no_reff'        => $vals['no_reff'],
//                     'debet'            => $vals['debet'],
//                     'kredit'        => $vals['kredit'],
//                 );
//                 $CI->db->insert(DBACC . '.jurnal', $datadetail);
//             }
//             $CI->db->query("UPDATE jurnal_product SET status_jurnal='1',approval_by='" . $UserName . "',approval_date='" . $DateTime . "' WHERE id ='$id'");
//             unset($det_Jurnaltes);
//             unset($datadetail);
//         }
//     }
//     if ($ket == 'incoming stok') {
//         $kodejurnal = 'JV035';
//         $id = $ArrDetailProduct;
//         $Keterangan_INV = "INCOMING STOCK " . $id;
//         $datajurnal = $CI->db->query("select sum(total_nilai) as nilaibayar, tanggal, no_ipp from jurnal where kode_trans='" . $id . "' limit 1")->row();
//         $tgl_voucher = $datajurnal->tanggal;
//         $no_ipp = $datajurnal->no_ipp;
//         $no_request = $id;
//         $nilaibayar    = 0;
//         $totalbayar    = 0;
//         $masterjurnal       = $CI->Acc_model->GetTemplateJurnal($kodejurnal);
//         $det_Jurnaltes = [];
//         $unbill_coa = '';

//         // print_r($id);
//         // exit;

//         foreach ($masterjurnal as $record) {
//             $posisi = $record->posisi;
//             $nokir  = $record->no_perkiraan;
//             $param  = 'id';
//             $value_param  = $id;
//             $jenisjurnal = $ket;
//             $totalall = $datajurnal->nilaibayar;
//             if ($posisi == 'D') {
//                 $det_Jurnaltes[]  = array(
//                     'nomor'         => '',
//                     'tanggal'       => $tgl_voucher,
//                     'tipe'          => 'JV',
//                     'no_perkiraan'  => $nokir,
//                     'keterangan'    => $Keterangan_INV,
//                     'no_reff'       => $id,
//                     'debet'         => $totalall,
//                     'kredit'        => 0,
//                     'jenis_jurnal'  => $jenisjurnal,
//                     'no_request'    => $no_request,
//                     'stspos'          => 1
//                 );
//             } elseif ($posisi == 'K') {
//                 $unbill_coa = $nokir;
//                 $det_Jurnaltes[]  = array(
//                     'nomor'         => '',
//                     'tanggal'       => $tgl_voucher,
//                     'tipe'          => 'JV',
//                     'no_perkiraan'  => $nokir,
//                     'keterangan'    => $Keterangan_INV,
//                     'no_reff'       => $id,
//                     'debet'         => 0,
//                     'kredit'        => $totalall,
//                     'jenis_jurnal'  => $jenisjurnal,
//                     'no_request'    => $no_request,
//                     'stspos'          => 1
//                 );
//             }
//         }
//         $CI->db->query("UPDATE jurnal SET status_jurnal='1',approval_by='" . $UserName . "',approval_date='" . $DateTime . "' WHERE kode_trans ='$id' and category='" . $jenisjurnal . "'");
//         $CI->db->insert_batch('jurnaltras', $det_Jurnaltes);
//         $Nomor_JV = $CI->Jurnal_model->get_Nomor_Jurnal_Sales('101', $tgl_voucher);
//         $Bln    = substr($tgl_voucher, 5, 2);
//         $Thn    = substr($tgl_voucher, 0, 4);
//         $dataJVhead = array('nomor' => $Nomor_JV, 'tgl' => $tgl_voucher, 'jml' => $totalall, 'koreksi_no' => '-', 'kdcab' => '101', 'jenis' => 'JV', 'keterangan' => $Keterangan_INV, 'bulan' => $Bln, 'tahun' => $Thn, 'user_id' => $UserName, 'memo' => $id, 'tgl_jvkoreksi' => $tgl_voucher, 'ho_valid' => '');
//         $CI->db->insert(DBACC . '.javh', $dataJVhead);
//         $datadetail = array();
//         foreach ($det_Jurnaltes as $vals) {
//             $datadetail = array(
//                 'tipe'            => 'JV',
//                 'nomor'            => $Nomor_JV,
//                 'tanggal'        => $tgl_voucher,
//                 'no_perkiraan'    => $vals['no_perkiraan'],
//                 'keterangan'    => $vals['keterangan'],
//                 'no_reff'        => $vals['no_reff'],
//                 'debet'            => $vals['debet'],
//                 'kredit'        => $vals['kredit'],
//             );
//             $CI->db->insert(DBACC . '.jurnal', $datadetail);
//         }
//         $data_po = $CI->db->query("select * from tran_po_header where no_po in (select no_ipp from warehouse_adjustment where kode_trans='" . $id . "') limit 1")->row();
//         if ($data_po->mata_uang != 'IDR') $unbill_coa = '2101-01-04';
//         $datahutang = array(
//             'tipe'            => 'JV',
//             'nomor'            => $Nomor_JV,
//             'tanggal'        => $tgl_voucher,
//             'no_perkiraan'   => $unbill_coa,
//             'keterangan'     => $Keterangan_INV,
//             'no_reff'          => $data_po->no_po,
//             'kredit'           => $datajurnal->nilaibayar,
//             'debet'          => 0,
//             'id_supplier'    => $data_po->id_supplier,
//             'nama_supplier'  => $data_po->nm_supplier,
//             'no_request'     => $id,
//         );
//         $CI->db->insert('tr_kartu_hutang', $datahutang);
//         unset($det_Jurnaltes);
//         unset($datadetail);
//         unset($datahutang);
//     }
//     if ($ket == 'outgoing stok') {
//         $kodejurnal = 'JV039';
//         $id = $ArrDetailProduct;
//         $Keterangan_INV = "OUTGOING STOCK " . $id;
//         $datajurnal = $CI->db->query("select sum(ROUND(total_nilai)) as nilaibayar, tanggal from jurnal where kode_trans='" . $id . "' limit 1")->row();
//         $tgl_voucher = $datajurnal->tanggal;
//         $no_request = $id;
//         $nilaibayar    = 0;
//         $totalbayar    = 0;
//         $sql = "SELECT * FROM warehouse_adjustment where kode_trans='" . $id . "'";
//         $wh = $CI->db->query($sql)->row();
//         $kode_gudang = $wh->id_gudang_ke;
//         $coa_deffered = '';
//         if ($kode_gudang == '17') {
//             $sql_deff = "select c.nm_customer, c.coa_deffered from so_number a 
//             left join table_sales_order b on a.id_bq=b.id_bq 
//             left join customer c on b.id_customer=c.id_customer
//             where a.so_number='" . $wh->no_so . "'";
//             $dt_coa = $CI->db->query($sql_deff)->row();
//             if (!empty($dt_coa)) {
//                 $coa_deffered = $dt_coa->coa_deffered;
//             }
//             if ($coa_deffered == "") {
//                 $sql_deff = "select coa_biaya from costcenter where id='" . $wh->id_gudang_ke . "'";
//                 $dt_coa = $CI->db->query($sql_deff)->row();
//                 $coa_deffered = $dt_coa->coa_biaya;
//             }
//         }
//         $masterjurnal       = $CI->Acc_model->GetTemplateJurnal($kodejurnal);
//         $det_Jurnaltes = [];
//         foreach ($masterjurnal as $record) {
//             $posisi = $record->posisi;
//             $nokir  = $record->no_perkiraan;
//             $param  = 'id';
//             $value_param  = $id;
//             $jenisjurnal = $ket;
//             $totalall = $datajurnal->nilaibayar;
//             if ($posisi == 'D') {
//                 if ($kode_gudang == '17') {
//                     $val = $CI->db->query("select ROUND(a.total_nilai) total_nilai,a.id_material,a.nm_material, a.gudang_ke, '" . $coa_deffered . "' coa_biaya from jurnal a  where a.kode_trans='" . $id . "'")->result();
//                 } else {
//                     $val = $CI->db->query("select ROUND(a.total_nilai) total_nilai,a.id_material,a.nm_material, a.gudang_ke, b.category_awal, c.coa_biaya from jurnal a left join con_nonmat_new b on a.id_material=b.code_group left join con_nonmat_category_costcenter c on a.gudang_ke=c.costcenter and b.category_awal=c.category where a.kode_trans='" . $id . "'")->result();
//                 }
//                 foreach ($val as $rec) {
//                     $nilaibayar = $rec->total_nilai;
//                     $totalbayar = ($totalbayar + $nilaibayar);
//                     $dtcoa_biaya = $rec->coa_biaya;
//                     if ($dtcoa_biaya != "") {
//                     } else {
//                         $dtcoa_biaya = $nokir;
//                     }
//                     $det_Jurnaltes[]  = array(
//                         'nomor'         => '',
//                         'tanggal'       => $tgl_voucher,
//                         'tipe'          => 'JV',
//                         'no_perkiraan'  => $dtcoa_biaya,
//                         'keterangan'    => $rec->nm_material . ' ' . $id,
//                         'no_reff'       => $id,
//                         'debet'         => $nilaibayar,
//                         'kredit'        => 0,
//                         'jenis_jurnal'  => $jenisjurnal,
//                         'no_request'    => $no_request,
//                         'stspos'          => 1
//                     );
//                 }
//             } elseif ($posisi == 'K') {
//                 $det_Jurnaltes[]  = array(
//                     'nomor'         => '',
//                     'tanggal'       => $tgl_voucher,
//                     'tipe'          => 'JV',
//                     'no_perkiraan'  => $nokir,
//                     'keterangan'    => $Keterangan_INV,
//                     'no_reff'       => $id,
//                     'debet'         => 0,
//                     'kredit'        => $totalall,
//                     'jenis_jurnal'  => $jenisjurnal,
//                     'no_request'    => $no_request,
//                     'stspos'          => 1
//                 );
//             }
//         }
//         $CI->db->query("UPDATE jurnal SET status_jurnal='1',approval_by='" . $UserName . "',approval_date='" . $DateTime . "' WHERE kode_trans ='$id' and category='" . $jenisjurnal . "'");
//         $CI->db->insert_batch('jurnaltras', $det_Jurnaltes);
//         $Nomor_JV = $CI->Jurnal_model->get_Nomor_Jurnal_Sales('101', $tgl_voucher);
//         $Bln    = substr($tgl_voucher, 5, 2);
//         $Thn    = substr($tgl_voucher, 0, 4);
//         $dataJVhead = array('nomor' => $Nomor_JV, 'tgl' => $tgl_voucher, 'jml' => $totalall, 'koreksi_no' => '-', 'kdcab' => '101', 'jenis' => 'JV', 'keterangan' => $Keterangan_INV, 'bulan' => $Bln, 'tahun' => $Thn, 'user_id' => $UserName, 'memo' => $id, 'tgl_jvkoreksi' => $tgl_voucher, 'ho_valid' => '');
//         $CI->db->insert(DBACC . '.javh', $dataJVhead);
//         $datadetail = array();
//         foreach ($det_Jurnaltes as $vals) {
//             $datadetail = array(
//                 'tipe'            => 'JV',
//                 'nomor'            => $Nomor_JV,
//                 'tanggal'        => $tgl_voucher,
//                 'no_perkiraan'    => $vals['no_perkiraan'],
//                 'keterangan'    => $vals['keterangan'],
//                 'no_reff'        => $vals['no_reff'],
//                 'debet'            => $vals['debet'],
//                 'kredit'        => $vals['kredit'],
//             );
//             $CI->db->insert(DBACC . '.jurnal', $datadetail);
//         }
//         unset($det_Jurnaltes);
//         unset($datadetail);
//     }
//     if ($ket == 'incoming department') {
//         $kodejurnal = 'JV036';
//         $id = $ArrDetailProduct;
//         $Keterangan_INV = "INCOMING DEPARTMENT " . $id;
//         $datajurnal    = $CI->Acc_model->GetTemplateJurnal($kodejurnal);
//         $nilaibayar    = 0;
//         $totalbayar    = 0;
//         $unbill_coa = '';
//         $no_po = '';
//         foreach ($datajurnal as $record) {
//             $nokir1 = $record->no_perkiraan;
//             $tabel  = $record->menu;
//             $posisi = $record->posisi;
//             $field  = $record->field;
//             $nokir  = $record->no_perkiraan;
//             $kd_bayar = $id;
//             $param  = 'id';
//             $value_param  = $id;
//             $jenisjurnal = 'incoming department';
//             if ($posisi == 'D') {
//                 $val = $CI->db->query("select a.no_ipp, a.tanggal, a.total_nilai,a.id_material,a.nm_material, c.coa from jurnal a left join rutin_non_planning_detail b on a.id_material=b.id left join rutin_non_planning_header c on b.no_pr=c.no_pr where a.kode_trans='" . $kd_bayar . "'")->result();
//                 foreach ($val as $rec) {
//                     $tgl_voucher = $rec->tanggal;
//                     $no_po = $rec->no_ipp;
//                     $nilaibayar = $rec->total_nilai;
//                     $totalbayar = ($totalbayar + $nilaibayar);
//                     $det_Jurnaltes[]  = array(
//                         'nomor'         => '',
//                         'tanggal'       => $tgl_voucher,
//                         'tipe'          => 'JV',
//                         'no_perkiraan'  => $rec->coa,
//                         'keterangan'    => $rec->nm_material . ' ' . $kd_bayar . ', ' . $no_po,
//                         'no_reff'       => $id,
//                         'debet'         => $nilaibayar,
//                         'kredit'        => 0,
//                         'jenis_jurnal'  => $jenisjurnal,
//                         'no_request'    => $id
//                     );
//                 }
//             } elseif ($posisi == 'K') {
//                 $unbill_coa = $nokir;
//                 $det_Jurnaltes[]  = array(
//                     'nomor'         => '',
//                     'tanggal'       => $tgl_voucher,
//                     'tipe'          => 'JV',
//                     'no_perkiraan'  => $nokir,
//                     'keterangan'    => $Keterangan_INV,
//                     'no_reff'       => $id,
//                     'debet'         => 0,
//                     'kredit'        => $totalbayar,
//                     'jenis_jurnal'  => $jenisjurnal,
//                     'no_request'    => $id
//                 );
//             }
//         }

//         $CI->db->query("UPDATE jurnal SET status_jurnal='1',approval_by='" . $UserName . "',approval_date='" . $DateTime . "' WHERE kode_trans ='$id' and category='" . $jenisjurnal . "'");
//         $CI->db->insert_batch('jurnaltras', $det_Jurnaltes);
//         $Nomor_JV = $CI->Jurnal_model->get_Nomor_Jurnal_Sales('101', $tgl_voucher);
//         $Bln    = substr($tgl_voucher, 5, 2);
//         $Thn    = substr($tgl_voucher, 0, 4);
//         $dataJVhead = array('nomor' => $Nomor_JV, 'tgl' => $tgl_voucher, 'jml' => $totalbayar, 'koreksi_no' => '-', 'kdcab' => '101', 'jenis' => 'JV', 'keterangan' => $Keterangan_INV, 'bulan' => $Bln, 'tahun' => $Thn, 'user_id' => $UserName, 'memo' => $id, 'tgl_jvkoreksi' => $tgl_voucher, 'ho_valid' => '');
//         $CI->db->insert(DBACC . '.javh', $dataJVhead);
//         $datadetail = array();
//         foreach ($det_Jurnaltes as $vals) {
//             $datadetail = array(
//                 'tipe'            => 'JV',
//                 'nomor'            => $Nomor_JV,
//                 'tanggal'        => $tgl_voucher,
//                 'no_perkiraan'    => $vals['no_perkiraan'],
//                 'keterangan'    => $vals['keterangan'],
//                 'no_reff'        => $vals['no_reff'],
//                 'debet'            => $vals['debet'],
//                 'kredit'        => $vals['kredit'],
//             );
//             $CI->db->insert(DBACC . '.jurnal', $datadetail);
//         }
//         $data_po = $CI->db->query("select * from tran_po_header where no_po='" . $no_po . "' limit 1")->row();
//         if ($data_po->mata_uang != 'IDR') $unbill_coa = '2101-01-04';
//         $datahutang = array(
//             'tipe'            => 'JV',
//             'nomor'            => $Nomor_JV,
//             'tanggal'        => $tgl_voucher,
//             'no_perkiraan'   => $unbill_coa,
//             'keterangan'     => $Keterangan_INV,
//             'no_reff'          => $no_po,
//             'kredit'           => $totalbayar,
//             'debet'          => 0,
//             'id_supplier'    => $data_po->id_supplier,
//             'nama_supplier'  => $data_po->nm_supplier,
//             'no_request'     => $id,
//         );
//         $CI->db->insert('tr_kartu_hutang', $datahutang);
//         unset($det_Jurnaltes);
//         unset($datadetail);
//         unset($datahutang);
//     }
//     if ($ket == 'incoming asset') {
//         $kodejurnal = 'JV038';
//         $id = $ArrDetailProduct;
//         $Keterangan_INV = "INCOMING ASSET " . $id;
//         $datajurnal    = $CI->Acc_model->GetTemplateJurnal($kodejurnal);
//         $nilaibayar    = 0;
//         $totalbayar    = 0;
//         $unbill_coa = '';
//         $no_po = '';
//         foreach ($datajurnal as $record) {
//             $nokir1 = $record->no_perkiraan;
//             $tabel  = $record->menu;
//             $posisi = $record->posisi;
//             $field  = $record->field;
//             $nokir  = $record->no_perkiraan;
//             $kd_bayar = $id;
//             $param  = 'id';
//             $value_param  = $id;
//             $jenisjurnal = 'incoming asset';
//             if ($posisi == 'D') {
//                 $val = $CI->db->query("select a.no_ipp, a.tanggal, a.total_nilai,a.id_material,a.nm_material, b.coa from jurnal a left join asset_planning b on a.id_material=b.code_plan where a.kode_trans='" . $kd_bayar . "'")->result();
//                 foreach ($val as $rec) {
//                     $tgl_voucher = $rec->tanggal;
//                     $no_po = $rec->no_ipp;
//                     $nilaibayar = $rec->total_nilai;
//                     $totalbayar = ($totalbayar + $nilaibayar);
//                     $det_Jurnaltes[]  = array(
//                         'nomor'         => '',
//                         'tanggal'       => $tgl_voucher,
//                         'tipe'          => 'JV',
//                         'no_perkiraan'  => $rec->coa,
//                         'keterangan'    => $rec->nm_material . ' ' . $kd_bayar . ', ' . $no_po,
//                         'no_reff'       => $id,
//                         'debet'         => $nilaibayar,
//                         'kredit'        => 0,
//                         'jenis_jurnal'  => $jenisjurnal,
//                         'no_request'    => $id
//                     );
//                 }
//             } elseif ($posisi == 'K') {
//                 $unbill_coa = $nokir;
//                 $det_Jurnaltes[]  = array(
//                     'nomor'         => '',
//                     'tanggal'       => $tgl_voucher,
//                     'tipe'          => 'JV',
//                     'no_perkiraan'  => $nokir,
//                     'keterangan'    => $Keterangan_INV,
//                     'no_reff'       => $id,
//                     'debet'         => 0,
//                     'kredit'        => $totalbayar,
//                     'jenis_jurnal'  => $jenisjurnal,
//                     'no_request'    => $id
//                 );
//             }
//         }

//         $CI->db->query("UPDATE jurnal SET status_jurnal='1',approval_by='" . $UserName . "',approval_date='" . $DateTime . "' WHERE kode_trans ='$id' and category='" . $jenisjurnal . "'");
//         $CI->db->insert_batch('jurnaltras', $det_Jurnaltes);
//         $Nomor_JV = $CI->Jurnal_model->get_Nomor_Jurnal_Sales('101', $tgl_voucher);
//         $Bln    = substr($tgl_voucher, 5, 2);
//         $Thn    = substr($tgl_voucher, 0, 4);
//         $dataJVhead = array('nomor' => $Nomor_JV, 'tgl' => $tgl_voucher, 'jml' => $totalbayar, 'koreksi_no' => '-', 'kdcab' => '101', 'jenis' => 'JV', 'keterangan' => $Keterangan_INV, 'bulan' => $Bln, 'tahun' => $Thn, 'user_id' => $UserName, 'memo' => $id, 'tgl_jvkoreksi' => $tgl_voucher, 'ho_valid' => '');
//         $CI->db->insert(DBACC . '.javh', $dataJVhead);
//         $datadetail = array();
//         foreach ($det_Jurnaltes as $vals) {
//             $datadetail = array(
//                 'tipe'            => 'JV',
//                 'nomor'            => $Nomor_JV,
//                 'tanggal'        => $tgl_voucher,
//                 'no_perkiraan'    => $vals['no_perkiraan'],
//                 'keterangan'    => $vals['keterangan'],
//                 'no_reff'        => $vals['no_reff'],
//                 'debet'            => $vals['debet'],
//                 'kredit'        => $vals['kredit'],
//             );
//             $CI->db->insert(DBACC . '.jurnal', $datadetail);
//         }
//         $data_po = $CI->db->query("select * from tran_po_header where no_po='" . $no_po . "' limit 1")->row();
//         if ($data_po->mata_uang != 'IDR') $unbill_coa = '2101-01-05';
//         $datahutang = array(
//             'tipe'            => 'JV',
//             'nomor'            => $Nomor_JV,
//             'tanggal'        => $tgl_voucher,
//             'no_perkiraan'   => $unbill_coa,
//             'keterangan'     => $Keterangan_INV,
//             'no_reff'          => $no_po,
//             'kredit'           => $totalbayar,
//             'debet'          => 0,
//             'id_supplier'    => $data_po->id_supplier,
//             'nama_supplier'  => $data_po->nm_supplier,
//             'no_request'     => $id,
//         );
//         $CI->db->insert('tr_kartu_hutang', $datahutang);
//         unset($det_Jurnaltes);
//         unset($datadetail);
//     }

//     if ($ket == 'incoming project') {
//         $kodejurnal = 'JV078';
//         $id = $ArrDetailProduct;
//         $Keterangan_INV = "INCOMING PROJECT " . $id;
//         $datajurnal = $CI->db->query("select sum(total_nilai) as nilaibayar, tanggal, no_ipp from jurnal where kode_trans='" . $id . "' limit 1")->row();
//         $tgl_voucher = $datajurnal->tanggal;
//         $no_ipp = $datajurnal->no_ipp;
//         $no_request = $id;
//         $nilaibayar    = 0;
//         $totalbayar    = 0;
//         $masterjurnal       = $CI->Acc_model->GetTemplateJurnal($kodejurnal);
//         $det_Jurnaltes = [];
//         $unbill_coa = '';



//         foreach ($masterjurnal as $record) {
//             $posisi = $record->posisi;
//             $nokir  = $record->no_perkiraan;
//             $param  = 'id';
//             $value_param  = $id;
//             $jenisjurnal = $ket;
//             $totalall = $datajurnal->nilaibayar;
//             if ($posisi == 'D') {
//                 $det_Jurnaltes[]  = array(
//                     'nomor'         => '',
//                     'tanggal'       => $tgl_voucher,
//                     'tipe'          => 'JV',
//                     'no_perkiraan'  => $nokir,
//                     'keterangan'    => $Keterangan_INV,
//                     'no_reff'       => $id,
//                     'debet'         => $totalall,
//                     'kredit'        => 0,
//                     'jenis_jurnal'  => $jenisjurnal,
//                     'no_request'    => $no_request,
//                     'stspos'          => 1
//                 );
//             } elseif ($posisi == 'K') {
//                 $unbill_coa = $nokir;
//                 $det_Jurnaltes[]  = array(
//                     'nomor'         => '',
//                     'tanggal'       => $tgl_voucher,
//                     'tipe'          => 'JV',
//                     'no_perkiraan'  => $nokir,
//                     'keterangan'    => $Keterangan_INV,
//                     'no_reff'       => $id,
//                     'debet'         => 0,
//                     'kredit'        => $totalall,
//                     'jenis_jurnal'  => $jenisjurnal,
//                     'no_request'    => $no_request,
//                     'stspos'          => 1
//                 );
//             }
//         }
//         $CI->db->query("UPDATE jurnal SET status_jurnal='1',approval_by='" . $UserName . "',approval_date='" . $DateTime . "' WHERE kode_trans ='$id' and category='" . $jenisjurnal . "'");
//         $CI->db->insert_batch('jurnaltras', $det_Jurnaltes);
//         $Nomor_JV = $CI->Jurnal_model->get_Nomor_Jurnal_Sales('101', $tgl_voucher);
//         $Bln    = substr($tgl_voucher, 5, 2);
//         $Thn    = substr($tgl_voucher, 0, 4);
//         $dataJVhead = array('nomor' => $Nomor_JV, 'tgl' => $tgl_voucher, 'jml' => $totalall, 'koreksi_no' => '-', 'kdcab' => '101', 'jenis' => 'JV', 'keterangan' => $Keterangan_INV, 'bulan' => $Bln, 'tahun' => $Thn, 'user_id' => $UserName, 'memo' => $id, 'tgl_jvkoreksi' => $tgl_voucher, 'ho_valid' => '');
//         $CI->db->insert(DBACC . '.javh', $dataJVhead);
//         $datadetail = array();
//         foreach ($det_Jurnaltes as $vals) {
//             $datadetail = array(
//                 'tipe'            => 'JV',
//                 'nomor'            => $Nomor_JV,
//                 'tanggal'        => $tgl_voucher,
//                 'no_perkiraan'    => $vals['no_perkiraan'],
//                 'keterangan'    => $vals['keterangan'],
//                 'no_reff'        => $vals['no_reff'],
//                 'debet'            => $vals['debet'],
//                 'kredit'        => $vals['kredit'],
//             );
//             $CI->db->insert(DBACC . '.jurnal', $datadetail);
//         }
//         $data_po = $CI->db->query("select * from tran_po_header where no_po in (select no_ipp from warehouse_adjustment where kode_trans='" . $id . "') limit 1")->row();
//         if ($data_po->mata_uang != 'IDR') $unbill_coa = '2101-01-04';
//         $datahutang = array(
//             'tipe'            => 'JV',
//             'nomor'            => $Nomor_JV,
//             'tanggal'        => $tgl_voucher,
//             'no_perkiraan'   => $unbill_coa,
//             'keterangan'     => $Keterangan_INV,
//             'no_reff'          => $data_po->no_po,
//             'kredit'           => $datajurnal->nilaibayar,
//             'debet'          => 0,
//             'id_supplier'    => $data_po->id_supplier,
//             'nama_supplier'  => $data_po->nm_supplier,
//             'no_request'     => $id,
//         );
//         $CI->db->insert('tr_kartu_hutang', $datahutang);
//         unset($det_Jurnaltes);
//         unset($datadetail);
//         unset($datahutang);
//     }
// }
