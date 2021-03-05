<?php

function iOSPush($message, $deviceToken, $datas = [], $pem, $passphrase)
{
    $API_URL = 'https://api.push.apple.com:443/3/device/';
    //Production:https://api.push.apple.com:443/3/device/
    //Development:https://api.development.push.apple.com:443/3/device/
    $apns_topic = "com.xxx.app"; //app bundle id

    $curl = curl_init();
    $payload = [
        "aps" => [
            'alert' => $message,
            'badge' => 1,
            'sound' => 'default',
            'datas' => $datas
        ]
    ];
    $post_fields = json_encode($payload);
    curl_setopt_array($curl, array(
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_2_0,
        CURLOPT_SSLCERT => $pem,
        CURLOPT_SSLCERTPASSWD => $passphrase,
        CURLOPT_SSLKEYTYPE => "PEM",
        CURLOPT_TIMEOUT => 30,
        CURLOPT_URL => $API_URL . $deviceToken,
        CURLOPT_POSTFIELDS => $post_fields,
        CURLOPT_RETURNTRANSFER => 1,
        CURLOPT_HTTPHEADER => array(
            "apns-topic:" . $apns_topic,
            "apns-priority:10",
            "apns-expiration:0"
        ),
    ));

    curl_exec($curl);
    $info = curl_getinfo($curl);
    // print_r($info);
    $body = [
        'post' => $payload,
        'info' => $info
    ];
    if ($info['http_code'] == 200) {
        curl_close($curl);
        $details = [
            "type" => "success_ios_token",
            "remark" => "sent successfully:" . json_encode($body),
            "value" => $deviceToken
        ];
        $feedbackArray = ['feedback' => 'success', 'details' => $details];
        return json_encode($feedbackArray, JSON_UNESCAPED_SLASHES);
    } else {
        curl_close($curl);
        $details = [
            "type" => "fail_ios_token",
            "remark" => json_encode($body),
            "value" => $deviceToken
        ];
        $feedbackArray = ['feedback' => 'error', 'details' => $details];
        return json_encode($feedbackArray, JSON_UNESCAPED_SLASHES);
    }
}

