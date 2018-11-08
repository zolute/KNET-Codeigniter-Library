<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Knet extends MX_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->library('knet_lib');
    }

    public function request()
    {

        echo $this->knet_lib->renderPG(1, "1234", "my reference number");
    }

    public function response()
    {
        $encrp = $this->input->get('encrp');

        var_dump($this->knet_lib->pgResponse($encrp));
    }

}
