<?php

define('API_ACCESS_KEY', 'YourFCMKey');

function AOSPush($title, $message, $deviceToken, $datas = []) {
// prep the bundle
    $msg = array
        (
        'message' => $message,
        'title' => $title,
        'datas' => $datas
    );

    $fields = array
        (
        'registration_ids' => [$deviceToken],
        'data' => $msg
    );

    $headers = array
        (
        'Authorization: key=' . API_ACCESS_KEY,
        'Content-Type: application/json'
    );

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
    $result = curl_exec($ch);
    curl_close($ch);

    /*
     * {
     * "multicast_id": 108,
     * "success": 1,
     * "failure": 0,
     * "canonical_ids": 0,
     * "results": [
     * { "message_id": "1:08" }
     * ]
     * }
     */
    //echo $result;
    $res = json_decode($result, true);
    if (isset($res['success'])) {
        if (intval($res['success']) > 0) {
            $details = [
                "type" => "success_aos_token",
                "remark" => "sent successfully:" . json_encode($fields),
                "value" => $deviceToken
            ];
            $feedbackArray = ['feedback' => 'success', 'details' => $details];
            return json_encode($feedbackArray, JSON_UNESCAPED_SLASHES);
        } else {
            $details = [
                "type" => "fail_aos_token",
                "remark" => $result,
                "value" => $deviceToken
            ];
            $feedbackArray = ['feedback' => 'error', 'details' => $details];
            return json_encode($feedbackArray, JSON_UNESCAPED_SLASHES);
        }
    } else {
        $details = [
            "type" => "remark",
            "remark" => $result,
            "value" => $deviceToken
        ];
        $feedbackArray = ['feedback' => 'error', 'details' => $details];
        return json_encode($feedbackArray, JSON_UNESCAPED_SLASHES);
    }
}
