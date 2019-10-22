<?php
include '/opt/lampp/htdocs/push-queue/core/global.php';
include '/opt/lampp/htdocs/push-queue/core/push.php';

if (!empty($_REQUEST['token']) && !empty($_REQUEST['type']) && (in_array($_REQUEST['type'], ['aos', 'ios']))) {
    $sql = "select id from devices where type='" . ConvertString($_REQUEST['type']) . "' and token='" . ConvertString($_REQUEST['token']) . "'";
    $result = mysqli_query($conn, $sql);
    $device_id = false;
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $device_id = $row['id'];
        }
    }
    if (!$device_id) {
        $sql = "insert into devices(type,token,created) values('"
            . ConvertString($_REQUEST['type']) . "','"
            . ConvertString($_REQUEST['token']) . "','"
            . date('Y-m-d H:i:s') . "')";
        $result = mysqli_query($conn, $sql);
        $device_id = mysqli_insert_id($conn);
        if ($device_id) {
            $feedbackArray = ['feedback' => 'success', 'feedback_msg' => "Added", "device_id" => $device_id];
            echo json_encode($feedbackArray, JSON_UNESCAPED_SLASHES);
        } else {
            $feedbackArray = ['feedback' => 'error', 'feedback_msg' => "Error:Add Device Failed"];
            echo json_encode($feedbackArray, JSON_UNESCAPED_SLASHES);
        }
    } else {
        $feedbackArray = ['feedback' => 'success', 'feedback_msg' => "Existed", "device_id" => $device_id];
        echo json_encode($feedbackArray, JSON_UNESCAPED_SLASHES);
    }
} else {
    $feedbackArray = ['feedback' => 'error', 'feedback_msg' => "Error:Error Parameters"];
    echo json_encode($feedbackArray, JSON_UNESCAPED_SLASHES);
}

mysqli_close($conn);
