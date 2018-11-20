<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class Knet_lib {

    private $CI;
    private $ClientId;
    private $ClientSecret;
    private $ENCRP_KEY;
    private $URL;
    

    function __construct() {
        $this->CI = & get_instance();
        $this->CI->load->config('knet');
        if($this->CI->config->item('knet_sandbox')=== true){
            $this->ClientId = $this->CI->config->item('knet_test_ClientId');
            $this->ClientSecret = $this->CI->config->item('knet_test_ClientSecret');
            $this->ENCRP_KEY = $this->CI->config->item('knet_test_ENCRP_KEY');
            $this->URL = $this->CI->config->item('knet_test_url');
        }else{
            $this->ClientId = $this->CI->config->item('knet_live_ClientId');
            $this->ClientSecret = $this->CI->config->item('knet_live_ClientSecret');
            $this->ENCRP_KEY = $this->CI->config->item('knet_live_ENCRP_KEY');
            $this->URL = $this->CI->config->item('knet_live_url');
        }
    }

    private function getAccessToken() {
        $postfield = array("ClientId" => $this->ClientId,
            "ClientSecret" => $this->ClientSecret,
            "ENCRP_KEY" => $this->ENCRP_KEY);
        
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $this->URL."/ePay/api/cbk/online/pg/merchant/Authenticate",
            CURLOPT_ENCODING => "",
            CURLOPT_FOLLOWLOCATION => 1,
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYHOST=>0,
            CURLOPT_SSL_VERIFYPEER=>0,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_FRESH_CONNECT => true,
            CURLOPT_POSTFIELDS => json_encode($postfield),
            CURLOPT_HTTPHEADER => array(
                'Authorization: Basic ' . base64_encode($this->ClientId. ":" . $this->ClientSecret),
                "Content-Type: application/json",
                "cache-control: no-cache"
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);
        
        curl_close($curl);
        
        if (isJson($response)) {
          
            $authenticateData = json_decode($response);
            
            if ($authenticateData->Status == "1") {
                return $authenticateData->AccessToken;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    function request($amount, $transactionID, $referenceID, $udf1 = '', $udf2 = '', $udf3 = '', $paymentType = '', $lang = 'en') {

        //get access token 
        if ($AccessToken = $this->getAccessToken()) {
            //generate pg page 
            $formData = array(
                'tij_MerchantEncryptCode' => $this->ENCRP_KEY,
                'tij_MerchAuthKeyApi' => $AccessToken,
                'tij_MerchantPaymentLang' => $lang,
                'tij_MerchantPaymentAmount' => $amount,
                'tij_MerchantPaymentTrack' => $transactionID,
                'tij_MerchantPaymentRef' => $referenceID,
                'tij_MerchantUdf1' => $udf1,
                'tij_MerchantUdf2' => $udf2,
                'tij_MerchantUdf3' => $udf3,
                'tij_MerchPayType' => 1
            );
            $url = $this->URL."/ePay/pg/epay?_v=" . $AccessToken;
            $form = "<form id='pgForm' method='post' action='$url' enctype='application/x-www-form-urlencoded'>";
            foreach ($formData as $k => $v) {
                $form .= "<input type='hidden' name='$k' value='$v'>";
            }
            $form .= "</form><div style='position: fixed;top: 50%;left: 50%;transform: translate(-50%, -50%;text-align:center'>Redirecting to PG ... <br> <b> DO NOT REFRESH</b></div><script type='text/javascript'>
    document.getElementById('pgForm').submit();
</script>";

            return $form;
        } else {
            return "Authentication Failed";
        }
    }

    function response($encrp) {
        //returns the unencrypted data
        //get access token 
        if ($AccessToken = $this->getAccessToken()) {
            $url = $this->URL."/ePay/api/cbk/online/pg/GetTransactions/" . $encrp . "/" . $AccessToken;
            $curl = curl_init();

            curl_setopt_array($curl, array(
                CURLOPT_URL => $url,
                CURLOPT_ENCODING => "",
                CURLOPT_FOLLOWLOCATION => 1,
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "GET",
                CURLOPT_RETURNTRANSFER => 1,
                CURLOPT_HTTPHEADER => array(
                    'Authorization: Basic ' .base64_encode($this->ClientId. ":" . $this->ClientSecret),
                    "Content-Type: application/json",
                    "cache-control: no-cache"
                ),
            ));

            $response = curl_exec($curl);
            $err = curl_error($curl);
            curl_close($curl);


            if (isJson($response)) {
                
                $paymentDetails = json_decode($response);
                return $paymentDetails;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

}
