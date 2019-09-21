<?php
function _log_push_insert($conn, $post)
{
    $sql = "insert into _log_push("
        . "type, "
        . "value, "
        . "remark, "
        . "created) values("
        . "'" . ConvertString($post['type']) . "',"
        . "'" . ConvertString($post['value']) . "',"
        . "'" . ConvertString($post['remark']) . "',"
        . "'" . date('Y-m-d H:i:s') . "' "
        . ")";
    if (mysqli_query($conn, $sql)) {
        return mysqli_insert_id($conn);
    } else {
        return false;
    }
}

function message_logs_insert($conn, $msg_id, $post)
{
    $sql = "insert into message_logs("
        . "type, "
        . "message_id, "
        . "token, "
        . "remark, "
        . "date) values("
        . "'" . ConvertString($post['type']) . "',"
        . "'" . ConvertString($msg_id) . "',"
        . "'" . ConvertString($post['value']) . "',"
        . "'" . ConvertString($post['remark']) . "',"
        . "'" . date('Y-m-d H:i:s') . "' "
        . ")";
    if (mysqli_query($conn, $sql)) {
        return mysqli_insert_id($conn);
    } else {
        return false;
    }
}
