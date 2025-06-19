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

class Control_po extends Admin_Controller
{
    //Permission
    protected $viewPermission     = 'Control_PO.View';
    protected $addPermission      = 'Control_PO.Add';
    protected $managePermission = 'Control_PO.Manage';
    protected $deletePermission = 'Control_PO.Delete';

    public function __construct()
    {
        parent::__construct();
        $this->load->library(array('Mpdf', 'upload', 'Image_lib'));
        $this->load->model(array(
            'Control_po/Control_po_model',
        ));
        $this->template->title('Control PO');
        $this->template->page_icon('fa fa-building-o');

        date_default_timezone_set('Asia/Bangkok');
    }
    public function index()
    {
        $this->auth->restrict($this->viewPermission);
        $session = $this->session->userdata('app_session');
        $this->template->page_icon('fa fa-users');
       
        $this->template->title('Control PO');
        $this->template->render('index');
    }

    public function modal_detail()
    {
        $id = $this->uri->segment(3);

        $detail = $this->db->get_where('dt_incoming', array('id_dt_po' => $id))->result_array();
        $data = [
            'detail' => $detail
        ];
        $this->template->set('results', $data);
        $this->template->title('Detail');
        $this->template->render('detail');
    }

    public function close_po()
    {
        $id_po = $this->input->post('id_po');

        $data = [
            'close_po' => 'Y',
            'close_date' => date('Y-m-d H:i:s'),
            'close_by' => $this->auth->user_id()
        ];

        $this->db->trans_begin();
        $this->db->where('id_dt_po', $id_po)->update("dt_trans_po", $data);
        $this->db->trans_complete();

        if ($this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
            $status    = array(
                'pesan'        => 'Failed Process Data. Try Again ...',
                'status'    => 0
            );
        } else {
            $this->db->trans_commit();
            $status    = array(
                'pesan'        => 'Success Process Data. Thanks ...',
                'status'    => 1
            );
        }
        echo json_encode($status);
    }

    public function get_control_po() {
        $this->Control_po_model->get_control_po();
    }
}
