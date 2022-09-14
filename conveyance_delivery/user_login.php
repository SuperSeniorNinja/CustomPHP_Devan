<?php
require_once('./config/config.php');

$username = $_REQUEST['username'];

$query = "SELECT * FROM {$tblUsers} WHERE username = '{$username}' OR staff = '{$username}'";
$result = $db->query($query);
$user = mysqli_num_rows($result);

if($user == 0) {
    header('Location: index.php');
    exit();
} else {
    $sql = "update {$tblUsers} set `last_login` = '" . date("Y-m-d H:i:s") . "' where `username` = '" . $username . "'";
    $db->query($sql);

    $sql = "select username, ID, staff from {$tblUsers} where username = '{$username}' OR staff = '{$username}'";
    $result =$db->query($sql);
    $res = mysqli_fetch_assoc($result);

    $_SESSION['username'] = $res['username'];
    $_SESSION['userId'] = $res['ID'];
    $_SESSION['staff'] = $res['staff'];
    $_SESSION['last_login_timestamp'] = time();

    //Register Login history
    $insert = "INSERT INTO {$tblLoginHistory} (user_id, login_out, created_at) VALUES ({$res['ID']}, 'login', '{$current}')";
    $result = $db->query($insert);

    header('Location: input.php');
    exit();
}



?>