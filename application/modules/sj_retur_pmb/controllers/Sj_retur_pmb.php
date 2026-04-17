<?php

if (!defined('BASEPATH')) {
	exit('No direct script access allowed');
}

/*
 * @author Ichsan
 * @copyright Copyright (c) 2019, Ichsan
 *
 * This is controller for Master Supplier
 */

class Sj_retur_pmb extends Admin_Controller
{
	//Permission
	protected $viewPermission 	= 'SJ_Retur_Pembelian.View';
	protected $addPermission  	= 'SJ_Retur_Pembelian.Add';
	protected $managePermission = 'SJ_Retur_Pembelian.Manage';
	protected $deletePermission = 'SJ_Retur_Pembelian.Delete';

	public function __construct()
	{
		parent::__construct();
		$this->load->library(array('Mpdf', 'upload', 'Image_lib'));
		$this->load->model(array(
			'Sj_retur_pmb/Sj_retur_pmb_model',
			'Aktifitas/aktifitas_model',
		));
		$this->template->title('SJ Retur Pembelian');
		$this->template->page_icon('fa fa-building-o');

		date_default_timezone_set('Asia/Bangkok');
	}

	public function index() {}
}
