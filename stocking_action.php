<?php
require_once('./config.php');
$username = $_POST['username'];
$stocking_action = $_POST['stocking_action'];
$_SESSION['stocking_action'] = $stocking_action;
header('Location: stocking_input.php');
exit();
?>