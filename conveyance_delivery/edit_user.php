<?php
require_once('./config/config.php');

if (empty($_SESSION['username'])) {
    echo "Action died";
    exit;
}

$sql = "update {$tblUsers} set username = '" . $_REQUEST['username'] . "', staff='" . $_REQUEST['staff'] . "', type=" . $_REQUEST['type'] . " where ID=" . $_REQUEST['id'];
$db->query($sql);

?>