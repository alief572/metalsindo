<?php

if (!defined('BASEPATH')) {
	exit('No direct script access allowed');
}

/*
 * Master Spesifikasi
 * Pilihan spesifikasi (mis. C2680, 304, SPCC-SD) per Type Alloy (Level 3),
 * dipakai di Level 4 - Material (inventory_4).
 */

class Master_spesifikasi extends Admin_Controller
{
	//Permission
	protected $viewPermission 	= 'Master_Spesifikasi.View';
	protected $addPermission  	= 'Master_Spesifikasi.Add';
	protected $managePermission = 'Master_Spesifikasi.Manage';
	protected $deletePermission = 'Master_Spesifikasi.Delete';

	public function __construct()
	{
		parent::__construct();

		$this->load->model('master_spesifikasi/Spesifikasi_model');
		$this->template->title('Master Spesifikasi');
		$this->template->page_icon('fa fa-list-alt');

		date_default_timezone_set('Asia/Bangkok');
	}

	public function index()
	{
		$this->auth->restrict($this->viewPermission);
		$this->template->set('results', $this->Spesifikasi_model->get_all());
		$this->template->set('alloys', $this->Spesifikasi_model->get_alloy());
		$this->template->title('Master Spesifikasi');
		$this->template->render('index');
	}

	public function addSpesifikasi()
	{
		$this->auth->restrict($this->addPermission);
		$this->template->set('results', array(
			'alloys' => $this->Spesifikasi_model->get_alloy(true),
		));
		$this->template->render('add_spesifikasi');
	}

	public function editSpesifikasi($id)
	{
		$this->auth->restrict($this->managePermission);
		$row = $this->Spesifikasi_model->get_one($id);
		if (!$row) {
			show_404();
		}
		$this->template->set('results', array(
			'row'    => $row,
			'alloys' => $this->Spesifikasi_model->get_alloy(true, $row->id_category2),
		));
		$this->template->render('edit_spesifikasi');
	}

	public function viewSpesifikasi($id)
	{
		$this->auth->restrict($this->viewPermission);
		$row = $this->Spesifikasi_model->get_one($id);
		if (!$row) {
			show_404();
		}
		$this->template->set('results', array(
			'row'      => $row,
			'material' => $this->Spesifikasi_model->get_material($row),
		));
		$this->template->render('view_spesifikasi');
	}

	public function saveNewSpesifikasi()
	{
		$this->auth->restrict($this->addPermission);
		$alloy = trim((string) $this->input->post('id_category2'));
		$nama  = $this->Spesifikasi_model->normalize($this->input->post('nama'));

		$err = $this->validate($alloy, $nama, '');
		if ($err) {
			return $this->respond(0, $err);
		}

		$this->db->trans_begin();
		$this->db->insert('ms_spesifikasi', array(
			'id_spesifikasi' => $this->Spesifikasi_model->generate_id(),
			'id_category2'   => $alloy,
			'nama'           => $nama,
			'aktif'          => 'aktif',
			'created_on'     => date('Y-m-d H:i:s'),
			'created_by'     => $this->auth->user_id(),
			'deleted'        => 0,
		));

		if ($this->db->trans_status() === false) {
			$this->db->trans_rollback();
			return $this->respond(0, 'Gagal menyimpan data spesifikasi.');
		}
		$this->db->trans_commit();
		return $this->respond(1, 'Data Spesifikasi berhasil disimpan.');
	}

	public function saveEditSpesifikasi()
	{
		$this->auth->restrict($this->managePermission);
		$id    = trim((string) $this->input->post('id_spesifikasi'));
		$alloy = trim((string) $this->input->post('id_category2'));
		$nama  = $this->Spesifikasi_model->normalize($this->input->post('nama'));
		$aktif = $this->input->post('aktif') === 'aktif' ? 'aktif' : 'nonaktif';

		$old = $this->Spesifikasi_model->get_one($id);
		if (!$old) {
			return $this->respond(0, 'Data spesifikasi tidak ditemukan.');
		}

		$err = $this->validate($alloy, $nama, $id, $old);
		if ($err) {
			return $this->respond(0, $err);
		}

		// Type Alloy tidak boleh diganti bila sudah dipakai material:
		// id_type/id_category1/alloy di material akan tidak sinkron.
		$dipakai = $this->Spesifikasi_model->count_used($old);
		if ($alloy !== $old->id_category2 && $dipakai > 0) {
			return $this->respond(0, 'Type Alloy tidak bisa diubah karena spesifikasi masih dipakai ' . $dipakai . ' material.');
		}

		$this->db->trans_begin();
		$this->db->where('id_spesifikasi', $id)->update('ms_spesifikasi', array(
			'id_category2' => $alloy,
			'nama'         => $nama,
			'aktif'        => $aktif,
			'modified_on'  => date('Y-m-d H:i:s'),
			'modified_by'  => $this->auth->user_id(),
		));
		// Nama baru ikut ke material yang memakai spesifikasi ini.
		if ($dipakai > 0 && $nama !== $old->nama) {
			$this->db->where('deleted', 0)
				->group_start()
					->where('id_spesifikasi', $old->id_spesifikasi)
					->or_group_start()
						->where('id_category2', $old->id_category2)
						->where('spek', $old->nama)
					->group_end()
				->group_end()
				->update('ms_inventory_category3', array('spek' => $nama, 'id_spesifikasi' => $old->id_spesifikasi));
		}

		if ($this->db->trans_status() === false) {
			$this->db->trans_rollback();
			return $this->respond(0, 'Gagal menyimpan data spesifikasi.');
		}
		$this->db->trans_commit();
		return $this->respond(1, 'Data Spesifikasi berhasil disimpan.');
	}

	public function deleteSpesifikasi()
	{
		$this->auth->restrict($this->deletePermission);
		$id  = trim((string) $this->input->post('id'));
		$row = $this->Spesifikasi_model->get_one($id);
		if (!$row) {
			return $this->respond(0, 'Data spesifikasi tidak ditemukan.');
		}
		$dipakai = $this->Spesifikasi_model->count_used($row);
		if ($dipakai > 0) {
			return $this->respond(0, 'Spesifikasi ' . $row->nama . ' masih dipakai ' . $dipakai . ' material. Non-aktifkan saja.');
		}

		$this->db->trans_begin();
		$this->db->where('id_spesifikasi', $id)->update('ms_spesifikasi', array(
			'deleted'    => 1,
			'deleted_by' => $this->auth->user_id(),
		));
		if ($this->db->trans_status() === false) {
			$this->db->trans_rollback();
			return $this->respond(0, 'Gagal menghapus data spesifikasi.');
		}
		$this->db->trans_commit();
		return $this->respond(1, 'Data Spesifikasi berhasil dihapus.');
	}

	/**
	 * Validasi input. Mengembalikan pesan error, atau '' bila valid.
	 */
	private function validate($alloy, $nama, $exclude_id, $old = null)
	{
		if ($alloy === '' || $nama === '') {
			return 'Type Alloy dan Nama Spesifikasi wajib diisi.';
		}
		if (mb_strlen($nama) > 50) {
			return 'Nama Spesifikasi maksimal 50 karakter.';
		}
		// Alloy harus ada; alloy baru harus aktif (alloy lama boleh tetap bila sudah nonaktif).
		$ok = $this->Spesifikasi_model->alloy_valid($alloy, $old ? $old->id_category2 : '');
		if (!$ok) {
			return 'Type Alloy tidak valid.';
		}
		if ($this->Spesifikasi_model->is_duplicate($alloy, $nama, $exclude_id)) {
			return 'Spesifikasi ini sudah ada untuk alloy tersebut.';
		}
		return '';
	}

	private function respond($status, $pesan)
	{
		$this->output
			->set_content_type('application/json')
			->set_output(json_encode(array('status' => $status, 'pesan' => $pesan)));
	}
}
