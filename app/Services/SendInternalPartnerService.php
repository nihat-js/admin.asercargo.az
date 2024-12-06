<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class SendInternalPartnerService
{

    private $apiUrl = 'https://aser.titr.az/api/aser/parcel-user-update';
    private $apiKey = 'ZymBOVo8nTY0mLs87ztm7fa_SILxAF1JQDns6kqu4qON4';

    public function ChangePackageUser($trackNumber, $userId) : void {
        $postData = array(
            "track" => $trackNumber,
            "user_id" => $userId
        );

        $response = $this->makeCurlRequest($this->apiUrl, $postData);

        Log::info([
            'update_package_user',
            'response' => $response,
            "track" => $trackNumber,
            "user_id" => $userId
        ]);
    }

    private function makeCurlRequest($url, $postData) {
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode($postData),
            CURLOPT_HTTPHEADER => array(
                'x-api-key: ' . $this->apiKey,
                'Content-Type: application/json'
            ),
        ));

        $response = curl_exec($curl);

        if (curl_errno($curl)) {
            echo 'Curl error: ' . curl_error($curl);
        }

        curl_close($curl);

        return $response;
    }
}