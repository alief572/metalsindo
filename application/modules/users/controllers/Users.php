<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/*
 * @author CokesHome
 * @copyright Copyright (c) 2015, CokesHome
 * 
 * This is controller for Authentication
 */

class Users extends Front_Controller
{

    /**
     * Load the models, library, etc
     *
     * 
     */

    protected $site_key;
    protected $secret_key;

    public function __construct()
    {
        parent::__construct();
        $this->load->model(array('identitas_model'));
        $this->load->library('users/auth');
        $this->load->library('session');

        $this->site_key = '6LeOwKErAAAAAMhxTtTAamQHIajF3lrVPi9t4jnb';
        $this->secret_key = '6LeOwKErAAAAAGQCsxvNnaqpi5rIwTsruxXeUGAa';
    }

    public function index()
    {
        redirect('users/setting');
    }

    public function login()
    {
        if ($this->auth->is_login()) {
            redirect('/');
            // redirect('https://sentral.dutastudy.com/metalsindo_dev/');
        }

        //$identitas = $this->identitas_model->find(1); => ERROR variable nama_program not define krn ga ada fieldnya di tabel identitas
        $identitas = $this->identitas_model->find_by(array('ididentitas' => 1)); // By Muhaemin => Di Form Login

        if (isset($_POST['login'])) {
            $username = $this->input->post('username');
            $password = $this->input->post('password');
            $token = $this->input->post('token');

            $urlVeryfy    = "https://www.google.com/recaptcha/api/siteverify?secret=" . urlencode($this->secret_key) . "&response=" . urlencode($token);
            $resGoogle     = json_decode(file_get_contents($urlVeryfy));
            //print_r($resGoogle);

            if (!$resGoogle->success) {
                $pesan = 'Gagal validasi reCAPTCHA Google...!';
                $this->session->set_flashdata('error', $pesan);
                redirect('/');
            } else if ($resGoogle->score < 0.5 || $resGoogle->action !== 'auth') {
                $pesan = 'Gagal, terdeteksi login mencurigakan. Silahkan coba lagi...!';
                $this->session->set_flashdata('error_captcha', $pesan);
                redirect('/');
            } else if ($resGoogle->success && $resGoogle->score >= 0.5) {
                $this->auth->login($username, $password);
            } else {
                $pesan = 'Gagal login, silahkan coba lagi...!';
                $this->session->set_flashdata('error_captcha', $pesan);
                redirect('/');
            }
        }

        $siteKey = '6LeOwKErAAAAAMhxTtTAamQHIajF3lrVPi9t4jnb';
        $secretKey = '6LeOwKErAAAAAGQCsxvNnaqpi5rIwTsruxXeUGAa';

        $this->template->set('sitekey', $siteKey);
        $this->template->set('secretkey', $secretKey);
        $this->template->set('idt', $identitas);
        $this->template->set_theme('default');
        $this->template->set_layout('login');
        $this->template->title('Login');
        $this->template->render('login_animate');
    }

    public function logout()
    {
        $this->auth->logout();
    }
}
