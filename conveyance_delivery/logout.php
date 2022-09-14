<?php
require_once('./config/config.php');

$user_id = $_SESSION['userId'];

//Register Login history
$insert = "INSERT INTO {$tblLoginHistory} (user_id, login_out, created_at) VALUES ({$user_id}, 'logout', '{$current}')";
$result = $db->query($insert);

session_destroy();
header('Location: ./index.php');