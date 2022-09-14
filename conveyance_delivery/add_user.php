<?php
require_once('./config/config.php');

if (empty($_SESSION['username'])) {
    echo "Action died";
    exit;
}

$sql = "insert into {$tblUsers} (username, password, type, last_login, staff) value ('" . $_POST['username'] . "', '', " . $_POST['usertype'] . ", '" . date("Y-m-d H:i:s") . "', '" . $_POST['staffID'] . "');";

$result = $db->query($sql);

if($result)
    echo "ok";
else
    echo "fail";




?>