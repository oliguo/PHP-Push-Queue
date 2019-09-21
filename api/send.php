<?php

include '/opt/lampp/htdocs/push-queue/core/global.php';
include '/opt/lampp/htdocs/push-queue/core/push.php';
include '/opt/lampp/htdocs/push-queue/core/FCM.php';
include '/opt/lampp/htdocs/push-queue/core/APNS.php';

//business logic

$flag_1 = isset($_POST['deviceType']) && (!empty($_POST['deviceType']));
$flag_2 = isset($_POST['deviceTokens']) && (!empty($_POST['deviceTokens']));
$flag_3 = isset($_POST['message']) && (!empty($_POST['message']));
$flag_4 = isset($_POST['datas'])&&(!empty($_POST['datas']));

$error_msg = "";
$datas = $flag_4?json_decode($_POST['datas'],true):[];
$sent = [];
$fail = [];

if($flag_1&&$flag_2&&$flag_3){
   
}else{
    $error_msg="Missing Parameters";
}

if (empty($error_msg)) {
    $deviceTokens = explode(',', $_POST['deviceTokens']);
    $deviceType = trim($_POST['deviceType']);;
    $aos_app_title="Android App Title";
    //print_r($deviceTokens);
    foreach ($deviceTokens as $deviceToken) {
        if ($deviceType === 'aos') {
            $res = AOSPush($aos_app_title, $_POST['message'], $deviceToken, $datas);
            $resp= json_decode($res,true);
            //print_r($res);
            if ($resp['feedback'] === 'success') {
                array_push($sent,$resp['details']['value']);
            } else {
                array_push($fail,$resp['details']['value']);
            }
            _log_push_insert($conn, $resp['details']);
        }
        if ($deviceType === 'ios') {
            $pem = APNS_PEM;
            $res = iOSPush($_POST['message'], $deviceToken, $datas, $pem,'yourAPNSPemPassword');
            $resp= json_decode($res,true);
            //print_r($res);
            if ($resp['feedback'] === 'success') {
                array_push($sent,$resp['details']['value']);
            } else {
                array_push($fail,$resp['details']['value']);
            }
            _log_push_insert($conn, $resp['details']);
        }
    }
    $feedbackArray = ['feedback' => 'success', 'success' => $sent,'fail'=>$fail];
    echo json_encode($feedbackArray, JSON_UNESCAPED_SLASHES);
} else {
    $feedbackArray = ['feedback' => 'error', 'feedback_msg' => "Error:" . $error_msg];
    echo json_encode($feedbackArray, JSON_UNESCAPED_SLASHES);
}

mysqli_close($conn);

//business logic
            
