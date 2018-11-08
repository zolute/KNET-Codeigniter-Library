<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class Knet_lib {

    private $CI;

    function __construct() {
        $this->CI = & get_instance();
        $this->CI->load->config('knet');
    }

    private function getAccessToken() {
        $postfield = array("ClientId" => $this->CI->config->item('knet_ClientId'),
            "ClientSecret" => $this->CI->config->item('knet_ClientSecret'),
            "ENCRP_KEY" => $this->CI->config->item('knet_ENCRP_KEY'));
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://pg.cbk.com/ePay/api/cbk/online/pg/merchant/Authenticate",
            CURLOPT_ENCODING => "",
            CURLOPT_FOLLOWLOCATION => 1,
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_POSTFIELDS => json_encode($postfield),
            CURLOPT_HTTPHEADER => array(
                'Authorization: Basic ' . base64_encode($this->CI->config->item('knet_ClientId') . ":" . $this->CI->config->item('knet_ClientSecret')),
                "Content-Type: application/json",
                "cache-control: no-cache"
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);


        if (isJson($response)) {
            // var_dump(json_decode($response));
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

    function renderPG($amount, $transactionID, $referenceID, $udf1 = '', $udf2 = '', $udf3 = '', $paymentType = '', $lang = 'en') {

        //get access token 
        if ($AccessToken = $this->getAccessToken()) {
            //generate pg page 
            $formData = array(
                'tij_MerchantEncryptCode' => $this->CI->config->item('knet_ENCRP_KEY'),
                'tij_MerchAuthKeyApi' => $AccessToken,
                'tij_MerchantPaymentLang' => $lang,
                'tij_MerchantPaymentAmount' => $amount,
                'tij_MerchantPaymentTrack' => $transactionID,
                'tij_MerchantPaymentRef' => $referenceID,
                'tij_MerchantUdf1' => $udf1,
                'tij_MerchantUdf2' => $udf2,
                'tij_MerchantUdf3' => $udf3,
                'tij_MerchPayType' => $paymentType
            );
            $url = "https://pg.cbk.com/ePay/pg/epay?_v=" . $AccessToken;
            $form = "<form id='pgForm' method='post' action='$url'>";
            foreach ($formData as $k => $v) {
                $form .= "<input type='hidden' name='$k' value='$v'>";
            }
            $form .= "</form><script type='text/javascript'>
    document.getElementById('pgForm').submit();
</script>";

            return $form;
        } else {
            return "Authentication Failed";
        }
    }

    function pgResponse($encrp) {
        //returns the unencrypted data
        //get access token 
        if ($AccessToken = $this->getAccessToken()) {
            $url = "https://pg.cbk.com/ePay/api/cbk/online/pg/GetTransactions/" . $encrp . "/" . $AccessToken;
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
                    'Authorization: Basic ' . base64_encode($this->CI->config->item('knet_ClientId') . ":" . $this->CI->config->item('knet_ClientSecret')),
                    "Content-Type: application/json",
                    "cache-control: no-cache"
                ),
            ));

            $response = curl_exec($curl);
            $err = curl_error($curl);
            curl_close($curl);


            if (isJson($response)) {
                // var_dump(json_decode($response));
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
