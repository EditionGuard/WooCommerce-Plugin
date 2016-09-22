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
            CURLOPT_URL => "https://staging.editionguard.com:443/api/v2/obtain-auth-token-by-shared-secret",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => array('email' => $email, 'shared_secret' => $secret),
        ));

        $response = curl_exec($curl);

        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            echo "cURL Error #:" . $err;
        } else {
            $this->token = json_decode($response)->token;
        }
    }

    public function getBookList() {
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://staging.editionguard.com:443/api/v2/book",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array(
                "authorization: Token $this->token"
            ),
        ));

        $response = curl_exec($curl);


        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            echo "cURL Error #:" . $err;
        } else {
            return json_decode($response)->results;
        }
    }

}
