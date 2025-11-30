<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/*
 * @author Arwant Json
 * @copyright Copyright (c) 2021, Arwant Json
 */

class Serverside_model extends BF_Model
{

	public function getDataJSON()
	{

		$requestData	= $_REQUEST;
		$fetch			= $this->queryDataJSON(
			$requestData['kategori'],
			$requestData['search']['value'],
			$requestData['order'][0]['column'],
			$requestData['order'][0]['dir'],
			$requestData['start'],
			$requestData['length']
		);
		$totalData		= $fetch['totalData'];
		$totalFiltered	= $fetch['totalFiltered'];
		$query			= $fetch['query'];
		$totalAset		= $fetch['totalAset'];

		$data	= array();
		$urut1  = 1;
		$urut2  = 0;
		$sumx	= 0;
		foreach ($query->result_array() as $row) {
			$total_data     = $totalData;
			$start_dari     = $requestData['start'];
			$asc_desc       = $requestData['order'][0]['dir'];
			if ($asc_desc == 'asc') {
				$nomor = $urut1 + $start_dari;
			}
			if ($asc_desc == 'desc') {
				$nomor = ($total_data - $start_dari) - $urut2;
			}

			$SUP = "";
			//supp;ier
			$sup  = $this->db->get_where('child_inven_suplier', array('id_category3' => $row['id_category3']))->result();
			foreach ($sup as $sp) {
				$kodesup = $sp->id_suplier;
				$sup2  = $this->db->get_where('master_supplier', array('id_suplier' => $kodesup))->result();
				foreach ($sup2 as $sp2) {
					$SUP .= strtoupper($sp2->name_suplier) . ",";
				}
			}

			$nestedData 	= array();
			$nestedData[]	= "<div align='center'>" . $nomor . "</div>";
			$nestedData[]	= "<div align='center'>" . strtoupper(strtolower($row['nama_category1'])) . "</div>";
			$nestedData[]	= "<div align='left'>" . strtoupper(strtolower($row['id_category3'])) . "</div>";
			$nestedData[]	= "<div align='left'>" . strtoupper(strtolower($row['nama_category3'])) . "</div>";
			$nestedData[]	= "<div align='left'>" . $row['nm_bentuk'] . "</div>";
			$nestedData[]	= "<div align='left'>" . $SUP . "</div>";
			$nestedData[]	= "<div align='right'>" . number_format($row['weight'], 2) . "</div>";
			$nestedData[]	= "<div align='center'>
                                <a class='btn btn-warning btn-sm' href='" . base_url('/stock_material/detail/' . $row['id_category3']) . "' title='Detail' data-no_inquiry='" . $row->no_inquiry . "'><i class='fa fa-info-circle'></i></a>
                                </div>";
			$data[] = $nestedData;
			$urut1++;
			$urut2++;
		}

		$json_data = array(
			"draw"            	=> intval($requestData['draw']),
			"recordsTotal"    	=> intval($totalData),
			"recordsFiltered" 	=> intval($totalFiltered),
			"data"            	=> $data,
			"recordsAset"		=> $totalAset,
		);

		echo json_encode($json_data);
	}

	public function queryDataJSON($kategori, $like_value = NULL, $column_order = NULL, $column_dir = NULL, $limit_start = NULL, $limit_length = NULL)
	{

		$where_kategori = "";
		if (!empty($kategori)) {
			$where_kategori = " AND a.id_category2 = '" . $kategori . "' ";
		}

		$sql = "SELECT
                    a.*
                FROM
					view_stock_material a
                WHERE 1=1
                    " . $where_kategori . "
                    AND (
                        a.nama LIKE '%" . $this->db->escape_like_str($like_value) . "%'
                        OR a.nama LIKE '%" . $this->db->escape_like_str($like_value) . "%'
                        OR a.id_category3 LIKE '%" . $this->db->escape_like_str($like_value) . "%'
                        OR a.nama LIKE '%" . $this->db->escape_like_str($like_value) . "%'
                        OR a.hardness LIKE '%" . $this->db->escape_like_str($like_value) . "%'
                        OR a.nilai_dimensi LIKE '%" . $this->db->escape_like_str($like_value) . "%'
                        OR a.maker LIKE '%" . $this->db->escape_like_str($like_value) . "%'
                        OR a.nm_bentuk LIKE '%" . $this->db->escape_like_str($like_value) . "%'
                        OR a.nama LIKE '%" . $this->db->escape_like_str($like_value) . "%'
                        OR a.nilai_dimensi LIKE '%" . $this->db->escape_like_str($like_value) . "%'
                    )
                ";
		// echo $sql; exit;

		$Total_Aset	= 0;
		$get_query = $this->db->query($sql)->result();
		foreach ($get_query as $item) {
			$Total_Aset += $item->weight;
		}


		$data['totalData'] 	= $this->db->query($sql)->num_rows();
		$data['totalAset'] 	= $Total_Aset;
		$data['totalFiltered'] = $this->db->query($sql)->num_rows();
		$columns_order_by = array(
			0 => 'id',
			1 => 'nama',
			2 => 'nama',
			3 => 'nm_bentuk',
			4 => 'nama',
			5 => 'nilai_dimensi'
		);

		$sql .= " ORDER BY a.nama,  " . $columns_order_by[$column_order] . " " . $column_dir . " ";
		$sql .= " LIMIT " . $limit_start . " ," . $limit_length . " ";

		$data['query'] = $this->db->query($sql);
		return $data;
	}

	public function getDataJSON_GRW()
	{
		$requestData	= $_REQUEST;
		$fetch			= $this->queryDataJSON_GRW(
			$requestData['series'],
			$requestData['komponen'],
			$requestData['gudang'],
			$requestData['search']['value'],
			$requestData['order'][0]['column'],
			$requestData['order'][0]['dir'],
			$requestData['start'],
			$requestData['length']
		);
		$totalData		= $fetch['totalData'];
		$totalFiltered	= $fetch['totalFiltered'];
		$query			= $fetch['query'];
		$totalAset		= $fetch['totalAset'];

		$data	= array();
		$urut1  = 1;
		$urut2  = 0;
		$sumx	= 0;
		foreach ($query->result_array() as $row) {
			$total_data     = $totalData;
			$start_dari     = $requestData['start'];
			$asc_desc       = $requestData['order'][0]['dir'];
			if ($asc_desc == 'asc') {
				$nomor = $urut1 + $start_dari;
			}
			if ($asc_desc == 'desc') {
				$nomor = ($total_data - $start_dari) - $urut2;
			}

			$get_material = $this->db->get_where('ms_inventory_category3', array('id_category3' => $row['id_category3']))->row();
			$get_bentuk = $this->db->get_where('ms_bentuk', array('id_bentuk' => $get_material->id_bentuk))->row();

			$satuan = $get_bentuk->nm_bentuk;

			$total_weight_material = (!empty($get_material->total_weight)) ? $get_material->total_weight : 1;

			$total_sheet = 0;
			if ($get_material->id_bentuk == 'B2000002') {
				$total_sheet = round($row['totalweight'] / $total_weight_material);
			}

			if ($get_material->id_bentuk !== 'B2000002') {
				$jumlah_item = number_format($row['qty'], 2) . " " . $satuan;
			} else {
				$jumlah_item = "";
			}

			$nestedData 	= array();
			$nestedData[]	= "<div align='center'>" . $nomor . "</div>";
			$nestedData[]	= "<div align='left'>" . strtoupper(strtolower($row['id_category3'])) . "</div>";
			$nestedData[]	= "<div align='left'>" . strtoupper(strtolower($row['lotno'])) . "</div>";
			$nestedData[]	= "<div align='left'>" . strtoupper(strtolower($row['nama_material'])) . "</div>";
			$nestedData[]	= "<div align='left'>" . strtoupper(strtolower($row['maker'])) . "</div>";
			$nestedData[]	= "<div align='right'>" . number_format($row['width'], 2) . "</div>";
			$nestedData[]	= "<div align='right'>" . $jumlah_item . "</div>";
			$nestedData[]	= "<div align='right'>" . number_format($row['sisa_spk'], 2) . "</div>";
			$nestedData[]	= "<div align='right'>" . number_format($row['sisa_spk'] * $row['qty'], 2) . "</div>";
			$nestedData[]	= "<div align='right'>" . number_format($total_sheet) . "</div>";
			$nestedData[]	= "<div align='left'>" . $row['no_surat'] . "</div>";
			$nestedData[]	= "<div align='left'>" . strtoupper(strtolower($row['nama_gudang'])) . "</div>";
			$nestedData[]	= "<div align='left'>" . strtoupper(strtolower($row['customer'])) . "</div>";

			$data[] = $nestedData;
			$urut1++;
			$urut2++;
		}

		$json_data = array(
			"draw"            	=> intval($requestData['draw']),
			"recordsTotal"    	=> intval($totalData),
			"recordsFiltered" 	=> intval($totalFiltered),
			"data"            	=> $data,
			"recordsAset"		=> $totalAset,
		);

		echo json_encode($json_data);
	}

	public function queryDataJSON_GRW($series, $komponen, $gudang, $like_value = NULL, $column_order = NULL, $column_dir = NULL, $limit_start = NULL, $limit_length = NULL)
	{

		$where_kategori = "";
		if (!empty($gudang)) {
			$where_kategori = " AND a.id_gudang = '" . $gudang . "' ";
		}

		$where_series = "";
		if (!empty($series)) {
			$where_series = " AND width = '" . $series . "' ";
		}

		$where_komponen = "";
		if (!empty($komponen)) {
			$where_komponen = " AND thickness = '" . $komponen . "' ";
		}

		$where_search = '';
		if (!empty($like_value)) {
			$where_search = "AND (
                        b.nama_gudang LIKE '%" . $this->db->escape_like_str($like_value) . "%'
                        OR a.lot_slitting LIKE '%" . $this->db->escape_like_str($like_value) . "%'
                        OR a.lotno LIKE '%" . $this->db->escape_like_str($like_value) . "%'
                        OR a.nama_material LIKE '%" . $this->db->escape_like_str($like_value) . "%'
						OR a.id_category3 LIKE '%" . $this->db->escape_like_str($like_value) . "%'
						OR a.no_surat LIKE '%" . $this->db->escape_like_str($like_value) . "%'
                    )";
		}

		$sql = "SELECT
                    a.id_category3,
					a.lotno,
					a.nama_material,
					a.width,
					a.sisa_spk,
					a.qty,
					a.no_surat,
					a.customer, 
					a.totalweight,
                    b.nama_gudang as nama_gudang,
					c.maker
                FROM
                    stock_material a
                    JOIN ms_gudang b ON b.id_gudang =a.id_gudang
                    LEFT JOIN ms_inventory_category3 c ON a.id_category3 =c.id_category3
                    LEFT JOIN ms_inventory_type d ON c.id_type=d.id_type
                    LEFT JOIN ms_inventory_category1 e ON c.id_category1 =e.id_category1
                    LEFT JOIN ms_inventory_category2 f ON c.id_category2 =f.id_category2
                WHERE 1=1
                    " . $where_kategori . "
					" . $where_series . "
					" . $where_komponen . "
                    " . $where_search . "
					AND a.aktif='Y' AND a.id_gudang = '" . $gudang . "' AND sisa_spk > 0 AND sisa_spk > IF(delivery_book IS NULL, 0, delivery_book)
                ";
		// echo $sql; exit;

		$Total_Aset	= 0;
		$Hasil_SUM		   = $this->db->query($sql)->result_array();
		foreach ($Hasil_SUM as $item) {
			$Total_Aset		+= ($item['sisa_spk'] * $item['qty']);
		}
		$data['totalData'] 	= $this->db->query($sql)->num_rows();
		$data['totalAset'] 	= $Total_Aset;
		$data['totalFiltered'] = $this->db->query($sql)->num_rows();
		$columns_order_by = array(
			0 => 'id_stock'
		);

		$sql .= " ORDER BY d.nama,  " . $columns_order_by[$column_order] . " " . $column_dir . " ";
		$sql .= " LIMIT " . $limit_start . " ," . $limit_length . " ";

		$data['query'] = $this->db->query($sql);
		return $data;
	}

	public function getDataJSON_booking()
	{

		$requestData	= $_REQUEST;
		$fetch			= $this->queryDataJSON_booking(
			$requestData['gudang'],
			$requestData['search']['value'],
			$requestData['order'][0]['column'],
			$requestData['order'][0]['dir'],
			$requestData['start'],
			$requestData['length']
		);
		$totalData		= $fetch['totalData'];
		$totalFiltered	= $fetch['totalFiltered'];
		$query			= $fetch['query'];
		$totalAset		= $fetch['totalAset'];

		$data	= array();
		$urut1  = 1;
		$urut2  = 0;
		$sumx	= 0;
		foreach ($query->result_array() as $row) {
			$total_data     = $totalData;
			$start_dari     = $requestData['start'];
			$asc_desc       = $requestData['order'][0]['dir'];
			if ($asc_desc == 'asc') {
				$nomor = $urut1 + $start_dari;
			}
			if ($asc_desc == 'desc') {
				$nomor = ($total_data - $start_dari) - $urut2;
			}

			$nestedData 	= array();
			$nestedData[]	= "<div align='center'>" . $nomor . "</div>";
			$nestedData[]	= "<div align='left'>" . $row['lotno'] . "</div>";
			$nestedData[]	= "<div align='left'>" . $row['nama'] . "</div>";
			$nestedData[]	= "<div align='right'>" . $row['width'] . "</div>";
			$nestedData[]	= "<div align='right'>" . $row['length'] . "</div>";
			$nestedData[]	= "<div align='right'>" . $row['qty'] . "</div>";
			$nestedData[]	= "<div align='right'>" . $row['weight'] . "</div>";
			$nestedData[]	= "<div align='right'>" . $row['totalweight'] . "</div>";
			$nestedData[]	= "<div align='left'>" . $row['keterangan'] . "</div>";
			$nestedData[]	= "<div align='left'>" . strtoupper($row['name_customer']) . "</div>";

			$data[] = $nestedData;
			$urut1++;
			$urut2++;
		}

		$json_data = array(
			"draw"            	=> intval($requestData['draw']),
			"recordsTotal"    	=> intval($totalData),
			"recordsFiltered" 	=> intval($totalFiltered),
			"data"            	=> $data,
			"recordsAset"		=> $totalAset,
		);

		echo json_encode($json_data);
	}

	public function queryDataJSON_booking($gudang, $like_value = NULL, $column_order = NULL, $column_dir = NULL, $limit_start = NULL, $limit_length = NULL)
	{

		// $where_kategori = "";
		// if(!empty($gudang)){
		// 	$where_kategori = " AND a.id_gudang = '".$gudang."' ";
		// }

		$sql = "SELECT
                    c.width,
					a.id_dt_spkmarketing,
					c.qty,
					c.weight,
					c.totalweight,
					c.length,
					c.lotno,
					c.nama_material,
                    b.name_customer as name_customer,
					e.no_surat,
					f.nama
                FROM
                    stock_material_customer a
                    LEFT JOIN master_customers b ON b.id_customer =a.id_customer
                    LEFT JOIN stock_material c ON a.id_stock =c.id_stock
                    LEFT JOIN dt_spkmarketing d ON a.id_dt_spkmarketing =d.id_dt_spkmarketing
					LEFT JOIN ms_inventory_category3 f ON d.id_material =f.id_category3
                    LEFT JOIN tr_spk_marketing e ON d.id_spkmarketing =e.id_spkmarketing
                WHERE 1=1
                    
                    AND (
                        b.name_customer LIKE '%" . $this->db->escape_like_str($like_value) . "%'
                    )
                ";
		// echo $sql; exit;

		$Query_Sum	= "SELECT
                            SUM(a.berat) AS weight
                        FROM
							stock_material_customer a
							LEFT JOIN master_customers b ON b.id_customer =a.id_customer
							LEFT JOIN stock_material c ON a.id_stock =c.id_stock
							LEFT JOIN dt_spkmarketing d ON a.id_dt_spkmarketing =d.id_dt_spkmarketing
							LEFT JOIN tr_spk_marketing e ON d.id_spkmarketing =e.id_spkmarketing
                        WHERE 1=1
                            
                            AND (
                                b.name_customer LIKE '%" . $this->db->escape_like_str($like_value) . "%'
                            )
                        ";
		$Total_Aset	= 0;
		$Hasil_SUM		   = $this->db->query($Query_Sum)->result_array();
		if ($Hasil_SUM) {
			$Total_Aset		= $Hasil_SUM[0]['weight'];
		}
		$data['totalData'] 	= $this->db->query($sql)->num_rows();
		$data['totalAset'] 	= $Total_Aset;
		$data['totalFiltered'] = $this->db->query($sql)->num_rows();
		$columns_order_by = array(
			0 => 'a.id'

		);

		$sql .= " ORDER BY  " . $columns_order_by[$column_order] . " " . $column_dir . " ";
		$sql .= " LIMIT " . $limit_start . " ," . $limit_length . " ";

		$data['query'] = $this->db->query($sql);
		return $data;
	}
}
