<?php
include '/opt/lampp/htdocs/push-queue/core/global.php';
include '/opt/lampp/htdocs/push-queue/core/push.php';

if (!empty($_REQUEST['message'])) {
    $sql="insert into messages(content,date) values('".ConvertString($_REQUEST['message'])."','".date('Y-m-d H:i:s')."')";
    $result=mysqli_query($conn,$sql);
    $msg_id = mysqli_insert_id($conn);
    if ($msg_id) {
        for ($i = 1; $i <= 10; $i++) {//10 queues, send total 2500*10 device token
            $j = 2500 * ($i - 1);            
            shell_exec("/opt/lampp/bin/php /opt/lampp/htdocs/push-queue/console/cli.php --from=" . $j . " --size=2500 --msg_id=" . $msg_id . " > /dev/null &");
        }
        $feedbackArray = ['feedback' => 'success', 'feedback_msg' => "Queued","message_id"=>$msg_id];
        echo json_encode($feedbackArray, JSON_UNESCAPED_SLASHES);
    }else{
        $feedbackArray = ['feedback' => 'error', 'feedback_msg' => "Error:Add Message Failed"];
        echo json_encode($feedbackArray, JSON_UNESCAPED_SLASHES);
    }
}else{
    $feedbackArray = ['feedback' => 'error', 'feedback_msg' => "Error:Missing Parameters"];
    echo json_encode($feedbackArray, JSON_UNESCAPED_SLASHES);
}

mysqli_close($conn);
