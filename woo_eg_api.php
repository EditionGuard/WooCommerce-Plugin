<?php

/*
 * Copyright 
 * Vladislav Khomenko
 */

/**
 * API Interface for editionguard service
 *
 * @author vlad
 */
class Woo_eg_api {

    public $token;

    public function __construct($email, $secret) {
        $curl = curl_init();
        

        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://staging.editionguard.com/api/v2/obtain-auth-token-by-shared-secret ",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => array('email' => $email, 'shared_secret' => $secret),
        ));

        $response = json_decode(curl_exec($curl));
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            echo "cURL Error #:" . $err;
        } else {
            $this->token = $response->token;
        }
    }

}
