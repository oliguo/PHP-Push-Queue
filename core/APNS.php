<?php
//$pem, absolute path
$pem = "/xxx/xxxx/xxx.pem";
$passphrase = "xxx";
$deviceToken = "xxx";
echo iOSPush("Final", $deviceToken, [], $pem, $passphrase);
function iOSPush($message, $deviceToken, $datas = [], $pem, $passphrase)
{
    $API_URL = 'https://api.push.apple.com:443/3/device/';
    //Production:https://api.push.apple.com:443/3/device/
    //Development:https://api.development.push.apple.com:443/3/device/
    $apns_topic = "com.xxx.app"; //app bundle id

    $payload = [
        "aps" => [
            'alert' => $message,
            'badge' => 1,
        ]
    ];
    if (count($datas) > 0) {
        $payload['aps']['datas'] = $datas;
    }
    $post_fields = json_encode($payload);
    $is_success = false;
    $extra=[];
    if (
        defined("CURL_VERSION_HTTP2") &&
        (curl_version()["features"] & CURL_VERSION_HTTP2) !== 0
    ) {
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_2_0,
            CURLOPT_SSLCERT => $pem,
            CURLOPT_SSLCERTPASSWD => $passphrase,
            CURLOPT_SSLKEYTYPE => "PEM",
            CURLOPT_URL => $API_URL . $deviceToken,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => $post_fields,
            CURLOPT_HTTPHEADER => array(
                "apns-topic:" . $apns_topic,
                "apns-priority:10",
                "apns-expiration:0"
            ),
        ));

        curl_exec($curl);
        $info = curl_getinfo($curl);
        // print_r($info);
        if ($info['http_code'] == 200) {
            $is_success=true;
        }
        $extra=array_merge($info);
        curl_close($curl);
    } else {
        // xampp php curl not support, run by os latest curl , please check "curl -V | grep HTTP2"
        $cli = 'curl -o /dev/null -s -w "%{http_code}\n" \
            --header "apns-topic: ' . $apns_topic . '" --header "apns-push-type: alert" \
            --cert "' . $pem . ':' . $passphrase . '" \
            --data ' . "'" . $post_fields . "'" . ' \
            --http2 https://api.push.apple.com:443/3/device/4F15267EE72CAA67DBDC3F6DE2173544E2F16A4FA3521C4EB3F3DBCA974AD331 -k';
        // echo $cli . "\n";
        $result=shell_exec($cli);
        if($result=='200'){
            $is_success=true;
        }
        $extra=array_merge(['cli_result'=>$result]);
    }
    $body = [
        'post' => $payload,
        'extra' => $extra
    ];
    if ($is_success) {
        $details = [
            "type" => "success_ios_token",
            "remark" => "sent successfully:" . json_encode($body),
            "value" => $deviceToken
        ];
        $feedbackArray = ['feedback' => 'success', 'details' => $details];
        return json_encode($feedbackArray, JSON_UNESCAPED_SLASHES);
    } else {
        $details = [
            "type" => "fail_ios_token",
            "remark" => json_encode($body),
            "value" => $deviceToken
        ];
        $feedbackArray = ['feedback' => 'error', 'details' => $details];
        return json_encode($feedbackArray, JSON_UNESCAPED_SLASHES);
    }
}
