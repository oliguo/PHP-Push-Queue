<?php

ini_set('memory_limit', '-1');
//ini_set('display_errors', 1);
//error_reporting(~0);
ini_set('log_errors', true);
ini_set('error_log', dirname(__FILE__) . '/errors.log');

$host = @$_SERVER['HTTP_HOST'];
$host_prefix="";
$UAT = false;
if ($UAT) {
    $host_prefix="http://";
    $absolute_path = "/Applications/XAMPP/xamppfiles/htdocs/push-queue";
    //database
    $server = "localhost";
    $user = "root"; //
    $password = ""; //					
    $database = "";
} else {
    $host_prefix="http://";
    $absolute_path = "/opt/lampp/htdocs/push-queue/";
    //database
    $server = "localhost";
    $user = "root";  //						
    $password = ""; //				
    $database = "push-queue";
}

//timezone
date_default_timezone_set('Asia/Hong_Kong');
//database
$conn = mysqli_connect($server, $user, $password,$database);
if (!$conn) {
    exit;
}
mysqli_query($conn,"SET NAMES utf8mb4");
//global url
define("ABS_PATH",$absolute_path);
define("APNS_PEM",ABS_PATH.'/core/apns_cert/yourAPNPemFile.pem');

function ConvertString($string) {
    return htmlspecialchars(removeEmoji($string), ENT_QUOTES);
}

function RevertConvertString($string) {
    return htmlspecialchars_decode($string, ENT_QUOTES);
}
