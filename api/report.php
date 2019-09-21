<?php
include '/opt/lampp/htdocs/push-queue/core/global.php';
include '/opt/lampp/htdocs/push-queue/core/push.php';

if (!empty($_REQUEST['msg_ids'])) {
    $sql = "select * from _log_push where message_id in(" . ConvertString($_REQUEST['msg_ids']) . ")";
    $result = mysqli_query($conn, $sql);
    if ($result) {
        $data = [];
        while ($row = mysqli_fetch_assoc($result)) {
            if (!isset($data[$row['message_id']])) {
                $data[$row['message_id']] = [
                    "message_id" => $row['message_id'],
                    "success" => [
                        "aos" => [],
                        "ios" => []
                    ],
                    "fail" => [
                        "aos" => [],
                        "ios" => []
                    ]
                ];
            }
            if($row['type']==='success_aos_token'){
                array_push($data[$row['message_id']]['success']['aos'],$row['value']);
            }
            if($row['type']==='success_ios_token'){
                array_push($data[$row['message_id']]['success']['ios'],$row['value']);
            }
            if($row['type']==='fail_aos_token'){
                array_push($data[$row['message_id']]['fail']['aos'],$row['value']);
            }
            if($row['type']==='fail_ios_token'){
                array_push($data[$row['message_id']]['fail']['ios'],$row['value']);
            }
        }
        $feedbackArray = ['feedback' => 'success', 'feedback_msg' => "Queried","data"=>array_values($data)];
        echo json_encode($feedbackArray, JSON_UNESCAPED_SLASHES);
    } else {
        $feedbackArray = ['feedback' => 'error', 'feedback_msg' => "Error:Queried Failed"];
        echo json_encode($feedbackArray, JSON_UNESCAPED_SLASHES);
    }
} else {
    $feedbackArray = ['feedback' => 'error', 'feedback_msg' => "Error:Missing Parameters"];
    echo json_encode($feedbackArray, JSON_UNESCAPED_SLASHES);
}

mysqli_close($conn);
