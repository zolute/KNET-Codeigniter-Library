<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Knet extends CI_Controller {

    function __construct() {
        parent::__construct();
        $this->load->library('knet_lib');
    }

    public function request() {

        echo $this->knet_lib->request(1, uniqid(), "abc");
    }

    function response() {
        $encrp = $this->input->get('encrp');

        var_dump($this->knet_lib->response($encrp));
    }

}
