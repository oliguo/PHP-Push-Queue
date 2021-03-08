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
            --http2 https://api.push.apple.com:443/3/device/'.$deviceToken.' -k';
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


function iOSPush_depreciated($message, $deviceToken, $datas=[], $pem,$passphrase) {
    $ctx = stream_context_create();
    stream_context_set_option($ctx, 'ssl', 'local_cert', $pem);
    stream_context_set_option($ctx, 'ssl', 'passphrase', $passphrase);

    // Open a connection to the APNS server
    $fp = stream_socket_client(
            'ssl://gateway.push.apple.com:2195', $err,
            //'ssl://gateway.sandbox.push.apple.com:2195',$err,
            //gateway.sandbox.push.apple.com:2195 if use development cert
            $errstr, 60, STREAM_CLIENT_CONNECT | STREAM_CLIENT_PERSISTENT, $ctx);

    //echo 'Connected to APNS' . PHP_EOL;
    // Create the payload body
    // $body['aps'] = array(
    //     'alert' => $message,
    //     'badge' => 1,
    //     'sound' => 'default',
    //     'datas' => $datas
    // );

    $body['aps'] = array(
        'alert' => $message,
        'badge' => 1,
        'sound' => 'default',
        'datas' => $datas
    );

    if (!$fp) {
        $details = [
            "type" => "remark",
            "remark" => "Failed to connect: $err $errstr",
            "value" => $deviceToken
        ];
        $feedbackArray = ['feedback' => 'error', 'details' => $details];
        return json_encode($feedbackArray, JSON_UNESCAPED_SLASHES);
    } else {
        // Encode the payload as JSON
        $payload = json_encode($body);
        // Build the binary notification
        $msg = chr(0) . pack('n', 32) . pack('H*', $deviceToken) . pack('n', strlen($payload)) . $payload;
        // Send it to the server
        $result = fwrite($fp, $msg, strlen($msg));
        if ($result) {
            // Close the connection to the server
            fclose($fp);
            $details = [
                "type" => "success_ios_token",
                "remark" => "sent successfully:" . json_encode($body),
                "value" => $deviceToken
            ];
            $feedbackArray = ['feedback' => 'success', 'details' => $details];
            return json_encode($feedbackArray, JSON_UNESCAPED_SLASHES);
        } else {
            fclose($fp);
            $details = [
                "type" => "fail_ios_token",
                "remark" => json_encode($body),
                "value" => $deviceToken
            ];
            $feedbackArray = ['feedback' => 'error', 'details' => $details];
            return json_encode($feedbackArray, JSON_UNESCAPED_SLASHES);
        }
    }
}
