<?php

include '/opt/lampp/htdocs/push-queue/core/global.php';
include '/opt/lampp/htdocs/push-queue/core/push.php';
include '/opt/lampp/htdocs/push-queue/core/FCM.php';
include '/opt/lampp/htdocs/push-queue/core/APNS.php';

//echo "<pre>";

$sent = [];
$fail = [];
$devices = [
    "aos" => [],
    "ios" => []
];
//$ /opt/lampp/bin/php /opt/lampp/htdocs/push-queue/console/cli.php --from=0 --size=5000 --msg_id=3
$from = getopt(null, ["from:"]);
$size = getopt(null, ["size:"]);
$msg_id = getopt(null, ["msg_id:"]);

$fp = fopen('/opt/lampp/htdocs/push-queue/console/cli.log', 'a');

$_from = !empty($from['from']) ? $from['from'] : 0;
$_size = !empty($size['size']) ? $size['size'] : 0;
$_msg_id = !empty($msg_id['msg_id']) ? $msg_id['msg_id'] : 0;
fwrite($fp, $_from . "-" . $_size . "-" . $_msg_id . "\n");

$sql = "select content from messages where id='" . ConvertString($_msg_id) . "'";
//echo $sql;
$result = mysqli_query($conn, $sql);
if ($result) {
    $message = "";
    while ($row = mysqli_fetch_assoc($result)) {
        $message = RevertConvertString($row['content']);
    }
    //echo $message;
    if (!empty($message)) {
        $sql = "select type,token from devices limit " . ConvertString($_from) . "," . ConvertString($_size);
        //echo $sql;
        $result = mysqli_query($conn, $sql);
        if ($result) {
            while ($row = mysqli_fetch_assoc($result)) {
                if ($row['type'] === 'aos') {
                    if(!in_array($row['token'],$devices['aos'])){
                        array_push($devices['aos'], $row['token']);
                    }
                } else if ($row['type'] === 'ios') {
                    if(!in_array($row['token'],$devices['aos'])){
                        array_push($devices['ios'], $row['token']);
                    }
                }
            }
        }
        //echo json_encode($devices);
        foreach ($devices as $deviceType => $deviceTokens) {
            if ($deviceType === 'aos') {
                foreach ($deviceTokens as $deviceToken) {
                    $res = AOSPush("Android App Title", $message, $deviceToken, []);//the [] is extra data
                    $resp = json_decode($res, true);
                    //print_r($res);
                    if ($resp['feedback'] === 'success') {
                        if (is_array($resp['details']['value'])) {
                            array_merge($sent, $resp['details']['value']);
                        } else {
                            array_push($sent, $resp['details']['value']);
                        }
                    } else {
                        if (is_array($resp['details']['value'])) {
                            array_merge($fail, $resp['details']['value']);
                        } else {
                            array_push($fail, $resp['details']['value']);
                        }
                    }
                    message_logs_insert($conn,$_msg_id, $resp['details']);
                }
            }
            if ($deviceType === 'ios') {
                $pem = APNS_PEM;
                foreach ($deviceTokens as $deviceToken) {
                    $res = iOSPush($message, $deviceToken, [], $pem,'yourpempassword');//the [] is extra data
                    $resp = json_decode($res, true);
                    //print_r($res);
                    if ($resp['feedback'] === 'success') {
                        if (is_array($resp['details']['value'])) {
                            array_merge($sent, $resp['details']['value']);
                        } else {
                            array_push($sent, $resp['details']['value']);
                        }
                    } else {
                        if (is_array($resp['details']['value'])) {
                            array_merge($fail, $resp['details']['value']);
                        } else {
                            array_push($fail, $resp['details']['value']);
                        }
                    }
                    message_logs_insert($conn,$_msg_id, $resp['details']);
                }
            }
        }
        echo count($sent) . "-" . count($fail)."\n";
    }
}else{
    echo "fail sql";
}
mysqli_close($conn);
fclose($fp);

//business logic
