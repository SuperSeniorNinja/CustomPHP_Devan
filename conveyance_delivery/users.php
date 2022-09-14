<?php
require_once('./config/config.php');
date_default_timezone_set('Europe/London');

$sql = "select * from {$tblUsers}";
$result =$db->query($sql);
$users = array();
$staff = "";
while ($res = mysqli_fetch_assoc($result)) {
	$users[] = ['username' => $res['username'], 'type' => $res['type'] == 1 ? 'Administrator' : 'User', 'last_login' => $res['last_login'], 'id' => $res['ID'], 'staff' => $res['staff'], 'typeId' => $res['type']];

	if ($staff == "")
		$staff = '"' . $res['staff'] . '"';
	else
		$staff .= ',"' . $res['staff'] . '"';
}

$sql = "select * from {$tblSelection}";
$result =$db->query($sql);
$selections = array();
while ($res = mysqli_fetch_assoc($result)) {
	$selections[] = ['id' => $res['ID'], 'name' => $res['name']];
}

$_SESSION['selection'] = $selections;

$sql = "select * from {$tblMelt} where `ID` != 3";
$result =$db->query($sql);
$melts = array();
while ($res = mysqli_fetch_assoc($result)) {
	$melts[] = ['id' => $res['ID'], 'name' => $res['name']];
}

$_SESSION['melt'] = $melts;

//$sql = "SELECT u.staff as staff, mi.ID, u.username, mi.weight, mi.input_time, m.name as melt, mi.subtotal as subtotal, mi.balance as balance, s.name as selection, mi.selection_id as sId, mi.melt_id as mId FROM `melt_input` as mi join `users` as u on mi.user_id = u.ID join `melt` as m on mi.melt_id = m.ID join `selection` as s on mi.selection_id = s.ID where mi.input_time like '" . date("Y-m-d") ."%' order by mi.input_time desc;";
$sql = "SELECT u.staff as staff, mi.ID, u.username, mi.weight, mi.input_time, mi.melt_id as melt, mi.subtotal as subtotal, mi.balance as balance, s.name as selection, mi.selection_id as sId, mi.melt_id as mId FROM {$tblMeltInput} as mi join {$tblUsers} as u on mi.user_id = u.ID join {$tblSelection} as s on mi.selection_id = s.ID where mi.input_time like '" . date("Y-m-d") ."%' order by mi.input_time desc;";
$result =$db->query($sql);
$meltInputs = array();
$totalWeight = 0;
$lastTime = 0;
while ($res = mysqli_fetch_assoc($result)) {
	$meltInputs[] = ['id' => $res['ID'], 'username' => $res['username'], 'weight' => $res['weight'], 'input_time' => substr($res['input_time'], 11, 5), 'melt' => $res['melt'], 'selection' => $res['selection'], 'sId' => $res['sId'], 'mId' => $res['mId'], 'staff' => $res['staff'], 'subtotal' => $res['subtotal'], 'balance' => $res['balance']];
	$totalWeight += intval($res['weight']);
	if ($lastTime == 0) {
		$lastTime = strtotime($res['input_time']);
	}
}

if ($lastTime == 0) 
	$lastTime = strtotime(date("Y-m-d") . " 00:00:00");

$_SESSION['meltInput'] = $meltInputs;
$_SESSION['totalWeight'] = $totalWeight;
$_SESSION['lastTime'] = $lastTime;

?>