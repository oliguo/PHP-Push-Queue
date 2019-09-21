<?php

function iOSPush($message, $deviceToken, $datas=[], $pem,$passphrase) {
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
